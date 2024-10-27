<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommunityRequest;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function index()
    {
        $communities = Community::with('user')->get();

        $formattedCommunities = $communities->map(function ($community) {
            return [
                'id' => $community->id,
                'name' => $community->name,
                'slogan' => $community->slogan,
                'creator' => [
                    'id' => $community->user->id,
                    'name' => $community->user->name,
                ],
            ];
        });

        return response()->json(['data' => $formattedCommunities]);
    }

    public function create(CommunityRequest $request)
    {
        if (!auth('sanctum')->check()) {
            return response()->json([
                "status" => "error",
                "message" => "Вы не авторизованы",
            ], 401);
        }

        if (auth('sanctum')->check()) {
            $data = $request->validated();

            $data["creator_id"] = auth('sanctum')->user()->id;

            $community = Community::create($data);

            $community->members()->attach(auth('sanctum')->user()->id, ['is_creator' => true]);

            return response()->json(['message' => 'Сообщество успешно создано', 'data' => $community], 201);
        }
    }

    public function update(CommunityRequest $request, $communityId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $user = auth('sanctum')->user();

        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['message' => 'Сообщество не найдено'], 404);
        }

        if ($community->creator_id !== $user->id) {
            return response()->json(['message' => 'У вас нет прав для обновления этого сообщества'], 403);
        }

        $validatedData = $request->validated();

        $community->name = $validatedData['name'];
        $community->slogan = $validatedData['slogan'];
        $community->save();

        return response()->json(['message' => 'Сообщество успешно обновлено', 'data' => $community], 200);
    }

    public function delete($communityId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        $user = auth('sanctum')->user();

        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['message' => 'Сообщество не найдено'], 404);
        }

        if ($community->creator_id !== $user->id) {
            return response()->json(['message' => 'У вас нет прав для удаления этого сообщества'], 403);
        }

        $community->delete();

        return response()->json(['message' => 'Сообщество успешно удалено'], 200);
    }

    public function isUserCreator($communityId)
    {
        if (!auth('sanctum')->check()) {
            return response()->json(['is_creator' => false], 200);
        }

        $user = auth('sanctum')->user();

        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['message' => 'Сообщество не найдено'], 404);
        }

        $isCreator = $community->creator_id == $user->id;

        return response()->json(['is_creator' => $isCreator], 200);
    }
}
