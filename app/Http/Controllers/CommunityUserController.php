<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use App\Models\CommunityUser;
use Illuminate\Support\Facades\Auth;

class CommunityUserController extends Controller
{
    public function join(Request $request, $communityId)
    {
        $user = $request->user();
        $community = Community::find($communityId);

        if (!auth('sanctum')->check()) {
            return response()->json(['message' => 'Вы не авторизованы'], 401);
        }

        if (!$user || !$community) {
            return response()->json(['message' => 'Пользователь или сообщество не найдены'], 404);
        }

        if ($community->members()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Вы уже участник этого сообщества'], 400);
        }

        $community->members()->attach($user);

        return response()->json(['message' => 'Вы успешно вступили в сообщество'], 200);
    }

    public function leave(Request $request, $communityId)
    {
        $user = $request->user();
        $community = Community::find($communityId);

        if (!$user || !$community) {
            return response()->json(['message' => 'Пользователь или сообщество не найдены'], 404);
        }

        if ($community->members()->where('user_id', $user->id)->exists()) {
            $community->members()->detach($user);
            return response()->json(['message' => 'Вы успешно вышли из сообщества'], 200);
        } else {
            return response()->json(['message' => 'Вы не являетесь участником этого сообщества'], 400);
        }
    }

    public function getMembersCount($communityId)
    {
        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['message' => 'Сообщество не найдено'], 404);
        }

        $membersCount = $community->members()->count();

        return response()->json(['members_count' => $membersCount]);
    }

    public function getAllMembers($communityId)
    {
        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['message' => 'Сообщество не найдено'], 404);
        }

        $creator = $community->members()->where('users.id', $community->creator_id)->first();

        $otherMembers = $community->members()->where('users.id', '!=', $community->creator_id)->get();

        $membersList = collect([$creator])->merge($otherMembers)->map(function ($member) use ($community) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar,
                'is_creator' => $member->id == $community->creator_id,
            ];
        });

        return response()->json([
            'community_name' => $community->name,
            'members_list' => $membersList,
        ]);
    }

    public function checkMembership($communityId)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['is_member' => false], 200);
        }

        $isMember = CommunityUser::where('community_id', $communityId)
            ->where('user_id', $user->id)
            ->exists();

        return response()->json(['is_member' => (bool)$isMember]);
    }
}
