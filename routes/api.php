<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\CommunityUserController;
use App\Http\Controllers\CommunityPostCommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserPostCommentController;
use App\Http\Controllers\UserPostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/register', [UserController::class, 'register']); // Register
Route::post('/login', [UserController::class, 'login'])->name("login"); // Login
Route::get('/communities', [CommunityController::class, 'index']);
Route::get('/community/{communityId}/usercount', [CommunityUserController::class, 'getMembersCount']); // Get user count
Route::get('/user/{userId}', [UserController::class, 'getUserById']); // Get user data by id
Route::get('/user/{userId}/followers-count', [FollowController::class, 'followersCount']);
Route::get('/user/{userId}/following-count', [FollowController::class, 'followingCount']);
Route::get('/popular-users', [UserController::class, 'getPopularUsers']); // Get popular user list
Route::get('/community/{communityId}/userlist', [CommunityUserController::class, 'getAllMembers']); // Get user list
Route::get('/community/{communityId}/posts', [CommunityPostController::class, 'index']); // Get community post
Route::get('/community/post/{postId}/comments', [CommunityPostCommentController::class, 'index']); // Get community post comments
Route::get('/user/{userId}/posts', [UserPostController::class, 'index']); // Get user post
Route::get('/user/post/{postId}/comments', [UserPostCommentController::class, 'index']); // Get user post comments
Route::get('community/{communityId}/is-creator', [CommunityController::class, 'isUserCreator']); // Check community creator
Route::get('/community/{communityId}/checkmembership', [CommunityUserController::class, 'checkMembership']); // Check membership from community
Route::get('/user/{userId}/is-following', [FollowController::class, 'isFollowing']); // Check following
Route::get('/followers/{userId}', [FollowController::class, 'followers']); // Followers list
Route::get('/following/{userId}', [FollowController::class, 'following']); // Following list
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/community/create', [CommunityController::class, 'create']); // Create community
    Route::post('community/{communityId}/update', [CommunityController::class, 'update']); // Update community
    Route::delete('community/{communityId}/delete', [CommunityController::class, 'delete']); // Delete community
    Route::post('/community/{communityId}/join', [CommunityUserController::class, 'join']); // Join in community
    Route::post('/community/{communityId}/post/create', [CommunityPostController::class, 'create']); // Create community post
    Route::post('/community/post/{postId}/update', [CommunityPostController::class, 'update']); // Update community post
    Route::delete('/community/post/{postId}/delete', [CommunityPostController::class, 'delete']); // Delete community post
    Route::post('community/post/{postId}/comment/create', [CommunityPostCommentController::class, 'create']); // Create community post comment
    Route::post('community/post/{postId}/comment/{commentId}/update', [CommunityPostCommentController::class, 'update']); // Update community post comment
    Route::delete('community/post/{postId}/comment/{commentId}/delete', [CommunityPostCommentController::class, 'delete']); // Delete community post comment
    Route::post('/community/{communityId}/leave', [CommunityUserController::class, 'leave']); // Leave from community
    Route::post('/user/{userId}/post/create', [UserPostController::class, 'create']); // Create user post
    Route::post('/user/post/{postId}/update', [UserPostController::class, 'update']); // Update user post
    Route::delete('/user/post/{postId}/delete', [UserPostController::class, 'delete']); // Delete user post
    Route::post('user/post/{postId}/comment/create', [UserPostCommentController::class, 'create']); // Create user post comment
    Route::post('user/post/{postId}/comment/{commentId}/update', [UserPostCommentController::class, 'update']); // Update user post comment
    Route::delete('user/post/{postId}/comment/{commentId}/delete', [UserPostCommentController::class, 'delete']); // Delete user post comment
    Route::post('/user/{userId}/update', [UserController::class, 'updateProfile']); // Update user profile
    Route::delete('user/{userId}/delete', [UserController::class, 'deleteUser']); // Delete user profile
    Route::post('/follow/{userId}', [FollowController::class, 'follow']); // Follow
    Route::post('/unfollow/{userId}', [FollowController::class, 'unfollow']); // Unfollow
});
