<?php
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\TransactionNameController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('v1')->group(function () {
    Route::options('{any}', function () {
        $origin = request()->header('Origin', env('FRONTEND_URL', '*'));
        return response()->noContent(204, [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => request()->header('Access-Control-Request-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin'),
            'Vary' => 'Origin, Access-Control-Request-Method, Access-Control-Request-Headers',
        ]);
    })->where('any', '.*');

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login',    [AuthController::class, 'login']);
    Route::post('invites/{token}/accept', [GroupController::class, 'acceptInvite'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout',       [AuthController::class, 'logout']);
        Route::get('auth/me',            [AuthController::class, 'me']);
        Route::post('auth/switch-group', [AuthController::class, 'switchGroup']);

        // Groups
        Route::get('groups',  [GroupController::class, 'index']);
        Route::post('groups', [GroupController::class, 'store']);
        Route::get('groups/{group}',  [GroupController::class, 'show']);
        Route::put('groups/{group}',  [GroupController::class, 'update']);

        // Group members & invites (require at least editor role)
        Route::middleware('group.member:admin')->group(function () {
            Route::post('groups/{group}/invite',              [GroupController::class, 'invite']);
            Route::patch('groups/{group}/members/{user}',     [GroupController::class, 'updateMember']);
            Route::delete('groups/{group}/members/{user}',    [GroupController::class, 'removeMember']);
        });
        Route::get('groups/{group}/members', [GroupController::class, 'members'])->middleware('group.member');

        // Categories & Tags (viewer+)
        Route::get('groups/{group}/categories',        [CategoryController::class, 'index'])->middleware('group.member');
        Route::post('groups/{group}/categories',       [CategoryController::class, 'store'])->middleware('group.member:editor');
        Route::put('groups/{group}/categories/{category}', [CategoryController::class, 'update'])->middleware('group.member:editor');
        Route::delete('groups/{group}/categories/{category}', [CategoryController::class, 'destroy'])->middleware('group.member:editor');

        Route::get('groups/{group}/tags',         [TagController::class, 'index'])->middleware('group.member');
        Route::post('groups/{group}/tags',        [TagController::class, 'store'])->middleware('group.member:editor');
        Route::delete('groups/{group}/tags/{tag}',[TagController::class, 'destroy'])->middleware('group.member:editor');

        // Transaction names autocomplete
        Route::get('groups/{group}/transaction-names', [TransactionNameController::class, 'index'])->middleware('group.member');

        // Dashboard
        Route::get('groups/{group}/dashboard',          [DashboardController::class, 'monthly'])->middleware('group.member');
        Route::get('groups/{group}/dashboard/evolution',[DashboardController::class, 'evolution'])->middleware('group.member');

        // Transactions
        Route::middleware('group.member')->group(function () {
            Route::get('groups/{group}/transactions',               [TransactionController::class, 'index']);
            Route::get('groups/{group}/transactions/{transaction}', [TransactionController::class, 'show']);
            Route::get('groups/{group}/transactions/{transaction}/history', [TransactionController::class, 'history']);
        });
        Route::middleware('group.member:editor')->group(function () {
            Route::post('groups/{group}/transactions',                  [TransactionController::class, 'store']);
            Route::patch('groups/{group}/transactions/{transaction}',   [TransactionController::class, 'update']);
            Route::delete('groups/{group}/transactions/{transaction}',  [TransactionController::class, 'destroy']);
        });

        // Notifications
        Route::get('notifications',              [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/read-all',    [NotificationController::class, 'markAllRead']);
        Route::patch('notifications/{id}/read',  [NotificationController::class, 'markRead']);
        Route::get('notifications/preferences',  [NotificationController::class, 'preferences']);
        Route::put('notifications/preferences',  [NotificationController::class, 'updatePreferences']);
    });
});
