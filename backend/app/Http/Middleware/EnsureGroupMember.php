<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGroupMember {
    public function handle(Request $request, Closure $next, string $minRole = 'viewer') {
        $group = $request->route('group');
        $user  = $request->user();

        if (!$group || !$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $member = $group->members()->where('user_id', $user->id)->first();

        if (!$member) {
            return response()->json(['message' => 'You are not a member of this group'], 403);
        }

        $roles = ['viewer' => 1, 'editor' => 2, 'admin' => 3, 'owner' => 4];
        $userLevel = $roles[$member->pivot->role] ?? 0;
        $required  = $roles[$minRole] ?? 1;

        if ($userLevel < $required) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        $request->merge(['current_group_member_role' => $member->pivot->role]);

        return $next($request);
    }
}
