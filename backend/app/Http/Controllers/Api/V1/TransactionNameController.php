<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\TransactionName;
use Illuminate\Http\Request;

class TransactionNameController extends Controller {
    public function index(Request $request, Group $group) {
        $q = $request->query('q', '');
        $names = TransactionName::where('group_id', $group->id)
            ->when($q, fn($query) => $query->where('normalized', 'like', '%' . mb_strtolower($q) . '%'))
            ->orderByDesc('usage_count')
            ->limit(20)
            ->get(['id', 'name', 'type', 'category_id', 'usage_count']);
        return response()->json($names);
    }
}
