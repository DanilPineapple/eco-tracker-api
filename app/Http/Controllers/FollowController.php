<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function follow($userId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userToFollow = User::findOrFail($userId);

        if ($user->id === $userToFollow->id) {
            return response()->json(['message' => 'Вы не можете подписаться на самого себя'], 400);
        }

        $user->following()->attach($userToFollow->id);

        return response()->json(['message' => 'Вы успешно подписались'], 200);
    }

    public function unfollow($userId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userToUnfollow = User::findOrFail($userId);

        $user->following()->detach($userToUnfollow->id);

        return response()->json(['message' => 'Вы успешно отписались'], 200);
    }

    public function followers($userId)
    {
        $user = User::findOrFail($userId);
        $followers = $user->followers;

        if($followers->isEmpty()) {
            return response()->json(['message' => 'Нет подписчиков'], 200);
        }

        return response()->json(['data' => $followers], 200);
    }

    public function following($userId)
    {
        $user = User::findOrFail($userId);
        $following = $user->following;

        if($following->isEmpty()) {
            return response()->json(['message' => 'Вы ни на кого не подписаны'], 200);
        }


        return response()->json(['data' => $following], 200);
    }

    public function followersCount($userId)
    {
        $user = User::findOrFail($userId);
        $followersCount = $user->followers()->count();

        if ($followersCount === 0) {
            return response()->json(['message' => 'Нет подписчиков'], 200);
        }

        return response()->json(['followers_count' => $followersCount], 200);
    }

    public function followingCount($userId)
    {
        $user = User::findOrFail($userId);
        $followingCount = $user->following()->count();

        if ($followingCount === 0) {
            return response()->json(['message' => 'Вы ни на кого не подписаны'], 200);
        }

        return response()->json(['following_count' => $followingCount], 200);
    }

    public function isFollowing($userId)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['is_following' => false], 200);
        }

        $userToFollow = User::find($userId);

        if (!$userToFollow) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $isFollowing = $user->following()->where('followed_id', $userId)->exists();

        return response()->json(['is_following' => $isFollowing], 200);
    }
}
