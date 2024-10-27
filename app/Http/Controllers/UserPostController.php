<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserPostRequest;
use App\Models\User;
use App\Models\UserPost;
use Illuminate\Http\Request;

class UserPostController extends Controller
{
    public function index($userId)
    {
        $user = User::findOrFail($userId);

        $posts = $user->posts()->orderBy('created_at', 'desc')->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'У пользователя нет публикаций'], 200);
        }

        return response()->json(['message' => 'Посты сообщества успешно получены', 'data' => $posts], 200);
    }

    public function create(UserPostRequest $request, $userId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userId = auth('sanctum')->user()->id;

        $user = User::find($userId);

        $post = $user->posts()->create([
            'user_id' => $userId,
            'title' => $request->input('title'),
            'content' => $request->input('content')
        ]);

        return response()->json(['message' => 'Пост успешно создан', 'data' => $post], 201);
    }

    public function delete($postId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userId = auth('sanctum')->user()->id;

        $post = UserPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        $user = $post->user;

        if ($post->user_id !== $userId) {
            return response()->json(['message' => 'У вас нет прав для удаления этого поста'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Пост успешно удален'], 200);
    }

    public function update(UserPostRequest $request, $postId)
    {
        $currentUser = auth('sanctum')->user();

        if (!$currentUser) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $post = UserPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        if ($post->user_id !== $currentUser->id) {
            return response()->json(['message' => 'Нет прав для обновления этого поста'], 403);
        }

        $validatedData = $request->validated();

        $post->title = $validatedData['title'];
        $post->content = $validatedData['content'];
        $post->save();

        return response()->json(['message' => 'Пост успешно обновлен', 'post' => $post], 200);
    }
}
