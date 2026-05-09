<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
    public function register(Request $request) {
        $data = $request->validate([
            'name'       => 'required|string|max:150',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|string|min:8|confirmed',
            'group_name' => 'sometimes|string|max:150',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        NotificationPreference::create(['user_id' => $user->id]);

        // Auto-create a personal group
        $groupName = $data['group_name'] ?? $data['name'];
        $group = Group::create([
            'name'     => $groupName,
            'slug'     => Str::slug($groupName) . '-' . $user->id,
            'owner_id' => $user->id,
        ]);

        $group->members()->attach($user->id, ['role' => 'owner', 'accepted_at' => now()]);

        $user->update(['current_group_id' => $group->id]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'group' => ['id' => $group->id, 'name' => $group->name],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request) {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Credenciais inválidas.']]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $group = $user->currentGroup ?? $user->groups()->first();

        return response()->json([
            'user'  => new UserResource($user),
            'group' => $group ? ['id' => $group->id, 'name' => $group->name] : null,
            'token' => $token,
        ]);
    }

    public function me(Request $request) {
        $user  = $request->user()->load('groups');
        $group = $user->currentGroup ?? $user->groups()->first();

        return response()->json([
            'user'   => new UserResource($user),
            'groups' => $user->groups->map(fn($g) => ['id' => $g->id, 'name' => $g->name, 'role' => $g->pivot->role]),
            'current_group' => $group ? ['id' => $group->id, 'name' => $group->name] : null,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function switchGroup(Request $request) {
        $data  = $request->validate(['group_id' => 'required|integer']);
        $user  = $request->user();
        $group = $user->groups()->findOrFail($data['group_id']);
        $user->update(['current_group_id' => $group->id]);
        return response()->json(['group' => ['id' => $group->id, 'name' => $group->name]]);
    }
}
