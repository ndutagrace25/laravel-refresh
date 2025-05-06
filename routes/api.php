<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserAvatarController;
use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\UserProfilePictureController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\VerifyForgotPasswordController;
use App\Http\Controllers\API\ChangePasswordController;
use App\Http\Controllers\API\UserNameController;
use App\Http\Controllers\API\UserChannelSelectionController;
use App\Http\Controllers\API\UserChannelController;
use App\Http\Controllers\API\UserCreatorController;

use App\Http\Controllers\API\GoogleController;

use App\Http\Controllers\API\CreatorVerificationController;
use App\Http\Controllers\API\CreatorVerificationFilesController;

use App\Http\Controllers\API\ChatGalleryController;
use App\Http\Controllers\API\ChatGalleryFilesController;
use App\Http\Controllers\API\ChatGalleryListController;

use App\Http\Controllers\API\StripeController;
use App\Http\Controllers\API\TagController;
use App\Http\Controllers\API\ChatGalleryUUIDController;
use App\Http\Controllers\API\AdminUserNameController;
use App\Http\Controllers\API\UserChatGalleryController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\LiveGalleryController;
use App\Http\Controllers\API\ChatGalleryUserNameController;
use App\Http\Controllers\API\ChatRoomController;
use App\Http\Controllers\API\ChatRoomReactionController;
use App\Http\Controllers\API\ChatRoomEntryController;
use App\Http\Controllers\API\ChatRoomCommentController;
use App\Http\Controllers\API\LiveScheduleGalleryController;
use App\Http\Controllers\API\CreatorDashboardController;
use App\Http\Controllers\API\AdmirerDashboardController;
use App\Http\Controllers\API\UserFollowerController;
use App\Http\Controllers\API\ReactionEmojiController;
use App\Http\Controllers\API\ChatRoomAudioController;
use App\Http\Controllers\API\ChatRoomAudioCommentController;
use App\Http\Controllers\API\ChatRoomVideoCommentController;
use App\Http\Controllers\API\ShowNotificationController;
use App\Http\Controllers\API\UserPasswordChangeController;
use App\Http\Controllers\API\CalendarLiveGalleryController;
use App\Http\Controllers\API\LibraryController;
use App\Http\Controllers\API\UserChatRoomFollowController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\DeleteUserController;
use App\Http\Controllers\API\UserPrivateAccountController;
use App\Http\Controllers\API\UserSubscriptionController;
use App\Http\Controllers\API\LiveStreamController;
use App\Http\Controllers\API\LiveQuestionController;
use App\Http\Controllers\API\CreatorReportDashboardController;
use App\Http\Controllers\API\StripeIntegrationController;
use App\Http\Controllers\API\TimeZoneController;
use App\Http\Controllers\API\UserSubscriberController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\TrendingController;
use App\Http\Controllers\API\ZegoController;
use App\Http\Controllers\API\VideoUploadController;
use App\Http\Controllers\API\PreLiveQuestionController;
use App\Http\Controllers\API\LiveScheduleGalleryFileController;
use App\Http\Controllers\API\StripeCustomerController;
use App\Http\Controllers\API\VideoUploadFileController;
use App\Http\Controllers\API\ArchLiveController;
use App\Http\Controllers\API\UserProfileStageController;
use App\Http\Controllers\API\CreatorOfficialController;
use App\Http\Controllers\API\AccountSuspensionController;
use App\Http\Controllers\API\ExportController;
use App\Http\Controllers\API\AdminListController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// SPRINT 1
Route::post('login',                    [ AuthController::class,                  'login'    ]);
Route::post('logout',                   [ AuthController::class,                  'logout'    ]);
Route::post('register',                 [ AuthController::class,                  'register' ]);
Route::post('avatar-upload',            [ UserAvatarController::class,            'index'    ]);
Route::post('profile',                  [ UserProfileController::class,           'store'    ]);
Route::post('reset-password',           [ ForgotPasswordController::class,        'index'    ]);
Route::post('verify-reset-password',    [ VerifyForgotPasswordController::class,  'index'    ]);
Route::post('change-password',          [ ChangePasswordController::class,        'index'    ]);
Route::post('check-username',           [ UserNameController::class,              'index'    ]);
Route::get('user_profile/{id}',         [ UserProfileController::class,           'index'    ]);
Route::get('user_profile_picture/{id}', [ UserProfilePictureController::class,    'index'    ]);
Route::get('profile_stage/{id}',             [ UserProfileStageController::class, 'index']);
// SPRINT 2
Route::get('channel_list',              [ UserChannelController::class,           'index'    ]);
Route::get('channel_choice/{id}',       [ UserChannelSelectionController::class,  'index'    ]);
Route::post('channel_choice',           [ UserChannelSelectionController::class,  'store'    ]);
Route::post('creator',                  [ UserCreatorController::class,           'store'    ]);

Route::post('google-signin',            [ GoogleController::class,                'store'    ]);

Route::post('creator/verify',           [ CreatorVerificationController::class,        'store'    ]);

Route::post('creator/verify/files',     [ CreatorVerificationFilesController::class,   'store'     ]);
Route::get('verification_requests',     [ CreatorVerificationController::class, 'index']);
Route::get('verification_request/{id}', [ CreatorVerificationController::class, 'view']);
Route::post('verification/verify',      [ CreatorVerificationController::class, 'verify']);
Route::post('verification/unverify',      [ CreatorVerificationController::class, 'unverify']);

//SPRINT 3

Route::post('chat_gallery',             [ ChatGalleryController::class, 'store']);
Route::post('chat_gallery/files',       [ ChatGalleryFilesController::class, 'store']);
Route::get('chat_gallery',              [ ChatGalleryController::class, 'index']);
Route::get('search',                    [ ChatGalleryUserNameController::class, 'index']);
Route::get('chat_gallery_list/{id}',    [ ChatGalleryController::class, 'list']);
Route::get('view_chat_gallery/{id}',    [ ChatGalleryListController::class, 'index']);

//Stripe
Route::post('stripe/connect',           [ StripeController::class, 'index']);
Route::get('tags',                      [ TagController::class, 'index']);

//Generate UUID
Route::get('chat_gallery/uuid',         [ ChatGalleryUUIDController::class, 'index']);

//Search username
Route::get('search_username',           [ AdminUserNameController::class, 'index']);
Route::post('assign_admin',             [ AdminUserNameController::class, 'store']);

Route::post('chat_gallery/follow_gallery',   [UserChatGalleryController::class, 'store']);
Route::get('chat_gallery/gallery_followers/{id}',    [UserChatGalleryController::class, 'index']);

//Notifications
Route::get('notifications/{id}', [ ShowNotificationController::class, 'index']);
Route::get('notifications/{id}/count', [ ShowNotificationController::class, 'count']);
Route::get('chat_gallery/list_live/{id}', [ LiveGalleryController::class, 'index']);
Route::get('chat_gallery/live_gallery/{id}', [LiveGalleryController::class, 'view']);


//Sprint 5
Route::get('chat_room/{id}', [ChatRoomController::class, 'index']);
Route::post('join_chat_room',     [ChatRoomEntryController::class, 'store']);
Route::delete('leave_chat_room',      [ChatRoomEntryController::class, 'delete']);
Route::post('comment/reaction', [ChatRoomReactionController::class, 'store']);
Route::post('comment', [ChatRoomCommentController::class, 'store']);

//ChatRoom
Route::post('chat_room', [ChatRoomController::class, 'store']);
Route::patch('chat_room', [ChatRoomController::class, 'edit']);
Route::delete('chat_room', [ChatRoomController::class, 'delete']);
Route::post('live_gallery_schedule', [LiveScheduleGalleryController::class, 'store']);
Route::get('live_gallery_schedule', [LiveScheduleGalleryController::class, 'index']);

Route::get('creator/dashboard/{id}', [CreatorDashboardController::class, 'index']);
Route::get('admirer/dashboard/{id}', [AdmirerDashboardController::class, 'index']);
Route::post('user/follow', [UserFollowerController::class, 'store']);
Route::delete('user/follow', [UserFollowerController::class, 'delete']);

Route::get('live_stream',[LiveStreamController::class, 'index']);
Route::get('live_stream/{live_gallery_id}',[LiveStreamController::class, 'live_stream']);
Route::get('list_live_stream',[LiveStreamController::class, 'list']);
Route::post('leave_live_stream',[LiveStreamController::class, 'delete']);
Route::get('/send-notification', [NotificationController::class, 'sendOfferNotification']);
Route::get('comment/reaction/emojis', [ReactionEmojiController::class, 'index']);

Route::post('chat_room_audio', [ChatRoomAudioController::class, 'store']);
Route::post('comment/audio', [ChatRoomAudioCommentController::class, 'store']);
Route::post('comment/video', [ChatRoomVideoCommentController::class, 'store']);
Route::get('comment/reaction/{id}', [ChatRoomReactionController::class, 'index']);

Route::post('user/password', [UserPasswordChangeController::class, 'store']);
Route::get('creator/live/calendar/{user_id}', [CalendarLiveGalleryController::class, 'index']);
Route::patch('library', [LibraryController::class, 'patch']);

Route::get('library/{id}',[LibraryController::class, 'index']);

Route::post('chat_room/follow',   [UserChatRoomFollowController::class, 'store']);
Route::post('chat_room/unfollow',   [UserChatRoomFollowController::class, 'delete']);
Route::get('chat_room/followers/{id}',    [UserChatRoomFollowController::class, 'index']);
Route::get('chat_room/check_follow/{chat_room_id}/{user_id}',    [UserChatRoomFollowController::class, 'check_follow']);


//SPRINT - 6
Route::get('report/reasons',       [ReportController::class, 'index']);
Route::post('report/chat_room',    [ReportController::class, 'report_chat_room']);
Route::post('report/chat_gallery', [ReportController::class, 'report_chat_gallery']);
Route::delete('user',              [DeleteUserController::class, 'delete']);
Route::post('user/account/private',  [UserPrivateAccountController::class, 'store']);
Route::post('user/account/public',   [UserPrivateAccountController::class, 'public']);
Route::post('user/subscription/fee', [UserSubscriptionController::class, 'store']);


Route::patch('chat_gallery',  [ChatGalleryController::class, 'edit']);
Route::delete('chat_gallery', [ChatGalleryController::class, 'delete']);

Route::post('live_questions', [LiveQuestionController::class, 'store']);
Route::get('live_questions/{id}', [LiveQuestionController::class, 'index']);

Route::get('creator_dashboard/{id}', [CreatorReportDashboardController::class, 'index']);
Route::post('create_stripe_user',[StripeIntegrationController::class, 'index']);

Route::get('timezones', [TimeZoneController::class, 'index']);
Route::get('user_follow/check_follow/{follower_id}/{followee_id}',    [UserFollowerController::class, 'index']);


//User Subscriptions
Route::post('user/subscribe', [UserSubscriberController::class, 'store']);
Route::get('user_subscriber/check_subscriber/{subscriber_id}/{user_id}',    [UserSubscriberController::class, 'index']);

Route::post('user/reservation', [ReservationController::class, 'store']);
Route::get('user/live_gallery/reservations/{live_schedule_gallery_id}', [ReservationController::class, 'index']);

Route::get('subscription_info/{id}', [UserSubscriptionController::class, 'index']);
Route::post('select_question', [LiveQuestionController::class, 'select']);
Route::get('trending_movies', [TrendingController::class, 'movies']);
Route::get('movie_detail/{id}', [TrendingController::class, 'movie_detail']);
Route::get('tv_show_detail/{id}', [TrendingController::class, 'tv_show_detail']);
Route::get('book_detail/{id}', [TrendingController::class, 'book_detail']);
Route::get('trending_tvs', [TrendingController::class, 'tvs']);
Route::get('trending_books', [TrendingController::class, 'books']);
Route::get('get_stream_data/{id}', [ZegoController::class, 'getStream']);
Route::post('stop_live_stream', [ZegoController::class, 'stop_live_stream']);
Route::post('start_live_stream', [ZegoController::class, 'start_live_stream']);
Route::get('live_central', [ZegoController::class, 'live_central']);

//Upload video related APIs
Route::post('video_upload', [VideoUploadController::class, 'store']);
Route::get('pre_live/{gallery_id}/{user_id}', [PreLiveQuestionController::class, 'index']);

Route::post('live_schedule_gallery_file', [LiveScheduleGalleryFileController::class, 'store']);
Route::post('live_video_files', [VideoUploadFileController::class,'store']);

Route::get('list_customers', [StripeCustomerController::class, 'index']);
Route::get('subscription', [StripeCustomerController::class, 'subscription']);
Route::post('stripe_session', [StripeCustomerControloler::class, 'session']);

Route::get('stripe_detail/{user_id}', [StripeCustomerController::class, 'stripe_detail']);

Route::get('/payment_sheet/{user_id}', [UserSubscriptionController::class, 'payment_sheet']);
Route::get('/setup_payment_sheet/{user_id}', [UserSubscriptionController::class, 'setup_payment_sheet']);
Route::get('admirer/live/calendar/{user_id}', [CalendarLiveGalleryController::class, 'creator_index']);
Route::get('arch_lives/{user_id}', [ArchLiveController::class, 'index']);
Route::get('/tagged_users/{gallery_id}', [ChatGalleryController::class, 'tagged']);
Route::get('list_followers/{user_id}', [UserFollowerController::class, 'list']);

##Admin portal official requests
Route::get('official_requests',     [ CreatorOfficialController::class, 'index']);
Route::post('official/verify',      [ CreatorOfficialController::class, 'verify']);
Route::post('official/unverify',    [ CreatorOfficialController::class, 'unverify']);

Route::post('report/ignore', [ReportController::class, 'ignore']);
Route::post('subscribe_follow', [UserSubscriptionController::class, 'subscribe_follow']);
Route::get('reports', [ReportController::class, 'reports']);

Route::get('app_users', [AccountSuspensionController::class, 'index']);
Route::post('suspend_account', [DeleteUserController::class, 'suspend_account']);
Route::post('restore_account', [DeleteUserController::class, 'restore_account']);
Route::get('data_export', [ExportController::class, 'data_export']);
Route::get('administrators', [AdminListController::class, 'index']);
Route::get('user/{id}', [AdminUserNameController::class, 'view']);

