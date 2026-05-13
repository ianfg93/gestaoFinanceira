<?php
namespace App\Http\Controllers\Api\V1;

use App\Domain\Dashboard\Services\DashboardService;
use App\Domain\Transactions\Actions\CreateTransactionAction;
use App\Domain\Transactions\Actions\UpdateTransactionAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\TransactionName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller {
    public function __construct(
        private CreateTransactionAction $createAction,
        private UpdateTransactionAction $updateAction,
        private DashboardService        $dashboardService,
    ) {}

    public function index(Request $request, Group $group) {
        $query = Transaction::where('group_id', $group->id)
            ->whereNull('deleted_at')
            ->with([
                'transactionName:id,name',
                'category:id,name,color',
                'responsible:id,name',
            ])
            ->withCount('attachments');

        if ($month = $request->query('month')) {
            $query->where('reference_month', $month);
        }
        if ($types = $request->query('type')) {
            $query->whereIn('type', explode(',', $types));
        }
        if ($statuses = $request->query('status')) {
            $query->whereIn('status', explode(',', $statuses));
        }
        if ($cats = $request->query('category_id')) {
            $query->whereIn('category_id', explode(',', $cats));
        }
        if ($responsible = $request->query('responsible_id')) {
            $query->where('responsible_id', $responsible);
        }
        if ($seriesId = $request->query('series_id')) {
            $query->where('series_id', $seriesId);
        }
        if ($q = $request->query('q')) {
            $needle = '%' . mb_strtolower($q) . '%';
            $query->join('transaction_names', 'transaction_names.id', '=', 'transactions.transaction_name_id')
                ->where('transaction_names.normalized', 'like', $needle)
                ->select('transactions.*');
        }

        $sortBy  = in_array($request->query('sort_by'), ['due_date','amount','status','type','created_at']) ? $request->query('sort_by') : 'due_date';
        $sortDir = $request->query('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = max(1, min((int) $request->query('per_page', 100), 500));
        $result  = $query->paginate($perPage);

        return TransactionResource::collection($result);
    }

    public function store(Request $request, Group $group) {
        $data = $request->validate([
            'name'                => 'required|string|max:200',
            'type'                => 'required|in:expense,income,investment',
            'amount'              => 'required|numeric|min:0',
            'due_date'            => 'required|date',
            'status'              => 'sometimes|in:pending,paid,overdue,cancelled,partial',
            'category_id'         => 'nullable|integer|exists:categories,id',
            'responsible_id'      => 'nullable|integer|exists:users,id',
            'notes'               => 'nullable|string',
            'tags'                => 'sometimes|array',
            'tags.*'              => 'integer|exists:tags,id',
            'is_series'           => 'sometimes|boolean',
            'is_installment'      => 'sometimes|boolean',
            'recurrence_type'     => 'sometimes|in:monthly,weekly,biweekly,yearly,custom',
            'interval_days'       => 'sometimes|integer|min:1',
            'ends_at'             => 'nullable|date',
            'total_installments'  => 'sometimes|integer|min:2|max:360',
        ]);

        $data['group_id'] = $group->id;
        $result = $this->createAction->execute($data, $request->user()->id);

        if ($result instanceof Transaction) {
            if (!empty($data['tags'])) {
                $result->tags()->sync($data['tags']);
            }
            $this->dashboardService->invalidate($group, $result->reference_month);
            return new TransactionResource($result->load(['transactionName','category','responsible','tags']));
        }

        $this->dashboardService->invalidate($group, $result->starts_at->format('Y-m'));
        return response()->json(['message' => 'Series created', 'series_id' => $result->id], 201);
    }

    public function show(Group $group, Transaction $transaction) {
        return new TransactionResource($transaction->load(['transactionName','category','responsible','tags','attachments','history.user']));
    }

    public function update(Request $request, Group $group, Transaction $transaction) {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:200',
            'type'           => 'sometimes|in:expense,income,investment',
            'amount'         => 'sometimes|numeric|min:0',
            'due_date'       => 'sometimes|date',
            'paid_date'      => 'nullable|date',
            'status'         => 'sometimes|in:pending,paid,overdue,cancelled,partial',
            'category_id'    => 'nullable|integer|exists:categories,id',
            'responsible_id' => 'nullable|integer|exists:users,id',
            'notes'          => 'nullable|string',
            'tags'           => 'sometimes|array',
            'tags.*'         => 'integer|exists:tags,id',
            'scope'          => 'sometimes|in:this,future,all',
        ]);

        $scope = $data['scope'] ?? 'this';
        unset($data['scope']);

        // Resolve name → transaction_name_id
        if (isset($data['name'])) {
            $tn = TransactionName::resolve($group->id, $data['name']);
            $data['transaction_name_id'] = $tn->id;
            unset($data['name']);
        }

        if (isset($data['tags'])) {
            $transaction->tags()->sync($data['tags']);
            unset($data['tags']);
        }

        $updated = $this->updateAction->execute($transaction, $data, $request->user()->id, $scope);
        $this->dashboardService->invalidate($group, $transaction->reference_month);

        return new TransactionResource($updated->load(['transactionName','category','responsible','tags']));
    }

    public function destroy(Request $request, Group $group, Transaction $transaction) {
        $scope = $request->query('scope', 'this');

        DB::transaction(function () use ($transaction, $scope, $request) {
            if ($transaction->series_id && $scope !== 'this') {
                $query = Transaction::where('series_id', $transaction->series_id);
                if ($scope === 'future') {
                    $query->where('due_date', '>=', $transaction->due_date);
                }
                $query->each(fn($t) => $t->delete());
            } else {
                $transaction->delete();
            }

            TransactionHistory::create([
                'transaction_id' => $transaction->id,
                'user_id'        => $request->user()->id,
                'action'         => 'deleted',
            ]);
        });

        $this->dashboardService->invalidate($group, $transaction->reference_month);

        return response()->json(['message' => 'Deleted']);
    }

    public function history(Group $group, Transaction $transaction) {
        $history = $transaction->history()->with('user')->orderByDesc('changed_at')->get();
        return response()->json($history);
    }
}
