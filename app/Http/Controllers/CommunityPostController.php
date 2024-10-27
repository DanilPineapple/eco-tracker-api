<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommunityPostRequest;
use App\Models\Community;
use App\Models\CommunityPost;

class CommunityPostController extends Controller
{
    public function index($communityId)
    {
        $community = Community::findOrFail($communityId);

        $posts = $community->posts()->orderBy('created_at', 'desc')->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'В сообществе нет публикаций'], 200);
        }

        return response()->json(['message' => 'Посты сообщества успешно получены', 'data' => $posts], 200);
    }

    public function create(CommunityPostRequest $request, $communityId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userId = auth('sanctum')->user()->id;

        $community = Community::find($communityId);

        if ($community->creator_id !== $userId) {
            return response()->json(['message' => 'У вас нет прав для создания поста в данном сообществе'], 403);
        }

        $post = $community->posts()->create([
            'community_id' => $communityId,
            'title' => $request->input('title'),
            'content' => $request->input('content')
        ]);

        return response()->json(['message' => 'Пост успешно создан', 'data' => $post], 201);
    }

    public function update(CommunityPostRequest $request, $postId)
    {
        $currentUser = auth('sanctum')->user();

        if (!$currentUser) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $post = CommunityPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        if ($post->community->creator_id !== $currentUser->id) {
            return response()->json(['message' => 'Нет прав для обновления этого поста'], 403);
        }

        $validatedData = $request->validated();

        $post->title = $validatedData['title'];
        $post->content = $validatedData['content'];
        $post->save();

        return response()->json(['message' => 'Пост успешно обновлен', 'post' => $post], 200);
    }

    public function delete($postId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userId = auth('sanctum')->user()->id;

        $post = CommunityPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        $community = $post->community;

        if ($community->creator_id !== $userId) {
            return response()->json(['message' => 'У вас нет прав для удаления поста в данном сообществе'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Пост успешно удален'], 200);
    }

}
