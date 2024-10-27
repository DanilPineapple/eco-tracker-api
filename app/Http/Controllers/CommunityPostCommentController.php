<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommunityPost;
use App\Http\Requests\CommunityPostCommentRequest;
use App\Models\CommunityPostComment;

class CommunityPostCommentController extends Controller
{
    public function index($postId)
    {
        $user = auth('sanctum')->user();

        $post = CommunityPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        $comments = $post->comments()->with('user')->get();

        if($comments->isEmpty()) {
            return response()->json(['message' => 'Нет комментариев'], 200);
        }

        $comments = $comments->map(function ($comment) use ($user) {
            $comment->is_creator = $user ? $comment->user_id == $user->id : false;
            return $comment;
        });

        return response()->json(['message' => 'Комментарии успешно получены', 'data' => $comments], 200);
    }

    public function create(CommunityPostCommentRequest $request, $postId)
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

        $isMember = $community->members()->where('user_id', $userId)->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Для добавления комментария необходимо вступить в сообщество'], 403);
        }

        $comment = CommunityPostComment::create([
            'community_id' => $community->id,
            'user_id' => $userId,
            'post_id' => $post->id,
            'comment' => $request->input('comment'),
        ]);

        return response()->json(['message' => 'Комментарий успешно создан', 'data' => $comment], 201);
    }

    public function update(CommunityPostCommentRequest $request, $postId, $commentId)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $post = CommunityPost::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        $comment = CommunityPostComment::find($commentId);
        if (!$comment) {
            return response()->json(['message' => 'Комментарий не найден'], 404);
        }

        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Нет прав для обновления этого комментария'], 403);
        }

        $comment->comment = $request->input('comment');
        $comment->save();

        return response()->json(['message' => 'Комментарий успешно обновлен', 'comment' => $comment], 200);
    }

    public function delete($communityId, $commentId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userId = auth('sanctum')->user()->id;

        $comment = CommunityPostComment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Комментарий не найден'], 404);
        }

        if ($comment->user_id !== $userId) {
            return response()->json(['message' => 'У вас нет прав для удаления этого комментария'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Комментарий успешно удален'], 200);
    }

}
