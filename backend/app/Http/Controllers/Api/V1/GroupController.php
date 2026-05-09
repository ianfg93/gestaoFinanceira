<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller {
    public function index(Request $request) {
        $groups = $request->user()->groups()->with('members')->get();
        return GroupResource::collection($groups);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'currency'    => 'sometimes|string|size:3',
        ]);

        $user  = $request->user();
        $slug  = Str::slug($data['name']) . '-' . $user->id . '-' . time();
        $group = Group::create(array_merge($data, ['owner_id' => $user->id, 'slug' => $slug]));
        $group->members()->attach($user->id, ['role' => 'owner', 'accepted_at' => now()]);

        return new GroupResource($group->load('members'));
    }

    public function show(Group $group) {
        return new GroupResource($group->load('members'));
    }

    public function update(Request $request, Group $group) {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'currency'    => 'sometimes|string|size:3',
        ]);
        $group->update($data);
        return new GroupResource($group->load('members'));
    }

    public function invite(Request $request, Group $group) {
        $data = $request->validate([
            'email' => 'required|email',
            'role'  => 'sometimes|in:editor,viewer,admin',
        ]);

        $invite = GroupInvite::create([
            'group_id'   => $group->id,
            'invited_by' => $request->user()->id,
            'email'      => $data['email'],
            'token'      => Str::random(40),
            'role'       => $data['role'] ?? 'editor',
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['message' => 'Invite sent', 'token' => $invite->token], 201);
    }

    public function acceptInvite(Request $request, string $token) {
        $invite = GroupInvite::where('token', $token)->firstOrFail();
        $user   = $request->user();

        if (!$invite->isValid()) {
            return response()->json(['message' => 'Invite expired or already used'], 422);
        }

        $invite->group->members()->syncWithoutDetaching([
            $user->id => ['role' => $invite->role, 'accepted_at' => now(), 'invited_by' => $invite->invited_by]
        ]);

        $invite->update(['accepted_at' => now()]);
        $user->update(['current_group_id' => $invite->group_id]);

        return response()->json(['message' => 'Joined group', 'group_id' => $invite->group_id]);
    }

    public function members(Group $group) {
        $members = $group->members()->get()->map(fn($u) => [
            'id'    => $u->id, 'name' => $u->name, 'email' => $u->email,
            'role'  => $u->pivot->role, 'avatar_url' => $u->avatar_url,
        ]);
        return response()->json($members);
    }

    public function updateMember(Request $request, Group $group, User $user) {
        $data = $request->validate(['role' => 'required|in:editor,viewer,admin']);
        $group->members()->updateExistingPivot($user->id, ['role' => $data['role']]);
        return response()->json(['message' => 'Role updated']);
    }

    public function removeMember(Group $group, User $user) {
        $group->members()->detach($user->id);
        return response()->json(['message' => 'Member removed']);
    }
}
