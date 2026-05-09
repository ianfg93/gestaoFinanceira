<?php
namespace App\Http\Controllers\Api\V1;

use App\Domain\Dashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class DashboardController extends Controller {
    public function __construct(private DashboardService $service) {}

    public function monthly(Request $request, Group $group) {
        $month = $request->query('month', now()->format('Y-m'));
        return response()->json($this->service->monthly($group, $month));
    }

    public function evolution(Request $request, Group $group) {
        $from = $request->query('from', now()->subMonths(11)->format('Y-m'));
        $to   = $request->query('to',   now()->format('Y-m'));
        return response()->json($this->service->monthlyEvolution($group, $from, $to));
    }
}
