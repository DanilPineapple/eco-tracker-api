<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPost;
use App\Http\Requests\UserPostCommentRequest;
use App\Models\UserPostComment;

class UserPostCommentController extends Controller
{
    public function index($postId)
    {
        $user = auth('sanctum')->user();

        $post = UserPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        $comments = $post->comments()->with('creator')->get();

        if ($comments->isEmpty()) {
            return response()->json(['message' => 'Нет комментариев'], 200);
        }

        $comments = $comments->map(function ($comment) use ($user) {
            $comment->is_creator = $user ? $comment->creator_id == $user->id : false;
            return $comment;
        });

        return response()->json(['message' => 'Комментарии успешно получены', 'data' => $comments], 200);
    }

    public function create(UserPostCommentRequest $request, $postId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $creator = auth('sanctum')->user();

        $post = UserPost::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        if ($post->user_id !== $creator->id) {
            $isFollowing = $creator->following()->where('followed_id', $post->user_id)->exists();

            if (!$isFollowing) {
                return response()->json(['message' => 'Вы должны подписаться на пользователя, чтобы комментировать его посты'], 403);
            }
        }

        $comment = UserPostComment::create([
            'user_id' => $post->user_id,
            'creator_id' => $creator->id,
            'post_id' => $post->id,
            'comment' => $request->input('comment'),
        ]);

        return response()->json(['message' => 'Комментарий успешно создан', 'data' => $comment], 201);
    }

    public function update(UserPostCommentRequest $request, $postId, $commentId)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $post = UserPost::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 404);
        }

        $comment = UserPostComment::find($commentId);
        if (!$comment) {
            return response()->json(['message' => 'Комментарий не найден'], 404);
        }

        if ($comment->creator_id !== $user->id) {
            return response()->json(['message' => 'Нет прав для обновления этого комментария'], 403);
        }

        $comment->comment = $request->input('comment');
        $comment->save();

        return response()->json(['message' => 'Комментарий успешно обновлен', 'comment' => $comment], 200);
    }

    public function delete($postId, $commentId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $userId = auth('sanctum')->user()->id;

        $comment = UserPostComment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Комментарий не найден'], 404);
        }

        if ($comment->creator_id !== $userId) {
            return response()->json(['message' => 'У вас нет прав для удаления этого комментария'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Комментарий успешно удален'], 200);
    }
}
