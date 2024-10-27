<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Community;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => 'icon_avatar1.png',
            'description' => null,
        ]);

        return response()->json([
            'status' => true,
            'user' => $user,
        ])->setStatusCode(201, 'Account registered');
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = auth()->user()->createToken('EcoTrackerToken');

            return response()->json([
                'status' => true,
                'user' => $user,
                'access_token' => $token->plainTextToken,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->json(['message' => 'Вы успешно вышли из учетной записи']);
    }

    public function updateProfile(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:155',
            'avatar' => 'required|string',
        ]);

        $user->update([
            'name' => $request->input('name'),
            'avatar' => $request->input('avatar'),
        ]);

        if ($request->has('description')) {
            $user->update(['description' => $request->input('description')]);
        }

        return response()->json(['message' => 'Профиль успешно обновлен', 'data' => $user]);
    }

    public function getUserById($userId)
    {
        $user = auth('sanctum')->user();

        if ($user && $user->id == $userId) {
            return response()->json(['message' => 'Это ваш профиль'], 200);
        }

        $user = User::find($userId);

        if ($user === null) {
            return response()->json(['message' => 'Пользователь не существует'], 404);
        }

        return response()->json(['data' => $user], 200);
    }

    public function deleteUser($userId)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        if ($user->id != $userId) {
            return response()->json(['message' => 'Вы не можете удалить чужой профиль'], 403);
        }

        DB::beginTransaction();

        try {
            $communities = Community::where('creator_id', $user->id)->get();

            foreach ($communities as $community) {
                $newCreator = $community->members()
                    ->where('users.id', '!=', $user->id)
                    ->orderBy('community_user.joined_at', 'asc')
                    ->first();

                if ($newCreator) {
                    $community->creator_id = $newCreator->id;
                    $community->save();
                } else {
                    $community->delete();
                }
            }

            $user->delete();

            DB::commit();

            return response()->json(['message' => 'Пользователь успешно удален'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ошибка при удалении пользователя', 'error' => $e->getMessage()], 500);
        }
    }

    public function getPopularUsers(Request $request)
    {
        $currentUser = Auth::guard('sanctum')->user();
        $currentUserId = $currentUser ? $currentUser->id : null;

        $sortBy = $request->query('sort_by', 'followers');
        $sortOrder = $request->query('sort_order', 'desc');

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort_order parameter'], 400);
        }

        $query = User::select('users.*', DB::raw('COUNT(follows.follower_id) as followers_count'))
            ->leftJoin('follows', 'users.id', '=', 'follows.followed_id')
            ->groupBy('users.id');

        if ($sortBy === 'followers') {
            $query->orderBy('followers_count', $sortOrder);
        } elseif ($sortBy === 'registration_date') {
            $query->orderBy('users.created_at', $sortOrder);
        } else {
            return response()->json(['message' => 'Invalid sort_by parameter'], 400);
        }

        $popularUsers = $query->get()->map(function ($user) use ($currentUserId) {
            if ($currentUserId !== null) {
                $user->is_current_user = ($user->id == $currentUserId);
            }
            return $user;
        });

        return response()->json(['popular_users' => $popularUsers], 200);
    }
}
