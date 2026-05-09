<?php
namespace App\Http\Controllers\Api\V1;

use App\Domain\Notifications\Services\NotificationService;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\FinancialNotification;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller {
    public function __construct(private NotificationService $service) {}

    public function index(Request $request) {
        $notifications = FinancialNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(30);
        return NotificationResource::collection($notifications);
    }

    public function unreadCount(Request $request) {
        $count = FinancialNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();
        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, int $id) {
        $this->service->markAsRead($request->user()->id, $id);
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead(Request $request) {
        $this->service->markAsRead($request->user()->id);
        return response()->json(['message' => 'All marked as read']);
    }

    public function preferences(Request $request) {
        $pref = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);
        return response()->json($pref);
    }

    public function updatePreferences(Request $request) {
        $data = $request->validate([
            'email_enabled'         => 'sometimes|boolean',
            'email_due_tomorrow'    => 'sometimes|boolean',
            'email_due_today'       => 'sometimes|boolean',
            'email_overdue_daily'   => 'sometimes|boolean',
            'email_monthly_summary' => 'sometimes|boolean',
            'in_app_enabled'        => 'sometimes|boolean',
        ]);
        $pref = NotificationPreference::updateOrCreate(['user_id' => $request->user()->id], $data);
        return response()->json($pref);
    }
}
