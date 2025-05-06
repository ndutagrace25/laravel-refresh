<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use App\Models\ChatGallery;
use App\Models\ChatGalleryFile;
use App\Models\ChatRoom;
use App\Models\ChatRoomComment;
use App\Models\ChatRoomEntry;
use App\Models\ChatRoomReaction;
use App\Models\CreatorVerification;
use App\Models\CreatorLibrary;
use App\Models\ForgotPassword;
use App\Models\LiveGalleryTag;
use App\Models\LiveScheduleGallery;
use App\Models\Notification;
use App\Models\UserAvatar;
use App\Models\UserChannel;
use App\Models\UserChatRoomFollow;
use App\Models\UserFollower;
use App\Models\UserGalleryFollow;
use App\Models\UserProfile;
use App\Models\AccountSuspension;
use Validator;  


class DeleteUserController extends Controller
{
    public function delete(Request $request){
         
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id,deleted_at,NULL',
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }
        $user_id = $request->user_id;
        $user_profile_id = UserProfile::where('user_id', $user_id)->first()?->id;


        $user = User::find($user_id)->first();
        $admin = Admin::where('user_id', $user_id)->delete();
        $chat_galleries = ChatGallery::where('user_id', $user_id)->delete();
        
        $chat_gallery_files = ChatGalleryFile::where('user_id', $user_id)->delete();
        $chat_rooms = ChatRoom::where('user_id', $user_id)->delete();
        /*TODO: Delete comments inside the chat room*/
        $chat_room_comments = ChatRoomComment::where('user_id', $user_id)->delete();
        /*TODO: Set parent comment to null*/
        $chat_room_entries = ChatRoomEntry::where('user_id', $user_id)->delete();
        $chat_room_reactions = ChatRoomReaction::where('reactor_id', $user_id)->delete();
        $creator_libraries = CreatorLibrary::where('user_id', $user_id)->delete();
        $creator_verifications = CreatorVerification::where('user_id', $user_id)->delete();
        $forgot_passwords = ForgotPassword::where('user_profile_id', $user_profile_id)->delete();
        $live_gallery_tags = LiveGalleryTag::where('user_id', $user_id)->delete();
        $live_schedule_galleries = LiveScheduleGallery::where('user_id', $user_id)->delete();
        $notifications = Notification::where('notifiable_id', $user_id)->delete();
        $user_avatars = UserAvatar::where('user_id', $user_id)->delete();
        $user_channels = UserChannel::where('user_id', $user_id)->delete();
        $user_chat_room_follows = UserChatRoomFollow::where('user_id', $user_id)->delete();
        $user_followers = UserFollower::where('user_id', $user_id)->delete();
        $user_gallery_follows = UserGalleryFollow::where('user_id', $user_id)->delete();
        $user_profiles = UserProfile::where('user_id', $user_id)->delete();
        $users = User::where('id', $user_id)->delete();

        return response()->json([
            "success" => true,
            "message" => "User Account deleted succesfully!",
        ]);

    }

    public function suspend_account(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id,deleted_at,NULL',
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }
        $user_id = $request->user_id;
        $user_profile_id = UserProfile::where('user_id', $user_id)->first()?->id;
        $suspension = new AccountSuspension();
        $suspension->user_id = $user_id;
        $suspension->save();


        $user = User::find($user_id)->first();
        $admin = Admin::where('user_id', $user_id)->delete();
        $chat_galleries = ChatGallery::where('user_id', $user_id)->delete();
        $chat_gallery_files = ChatGalleryFile::where('user_id', $user_id)->delete();
        $chat_rooms = ChatRoom::where('user_id', $user_id)->delete();
        $chat_room_comments = ChatRoomComment::where('user_id', $user_id)->delete();
        $chat_room_entries = ChatRoomEntry::where('user_id', $user_id)->delete();
        $chat_room_reactions = ChatRoomReaction::where('reactor_id', $user_id)->delete();
        $creator_libraries = CreatorLibrary::where('user_id', $user_id)->delete();
        $creator_verifications = CreatorVerification::where('user_id', $user_id)->delete();
        $forgot_passwords = ForgotPassword::where('user_profile_id', $user_profile_id)->delete();
        $live_gallery_tags = LiveGalleryTag::where('user_id', $user_id)->delete();
        $live_schedule_galleries = LiveScheduleGallery::where('user_id', $user_id)->delete();
        $notifications = Notification::where('notifiable_id', $user_id)->delete();
        $user_avatars = UserAvatar::where('user_id', $user_id)->delete();
        $user_channels = UserChannel::where('user_id', $user_id)->delete();
        $user_chat_room_follows = UserChatRoomFollow::where('user_id', $user_id)->delete();
        $user_followers = UserFollower::where('user_id', $user_id)->delete();
        $user_gallery_follows = UserGalleryFollow::where('user_id', $user_id)->delete();
        $user_profiles = UserProfile::where('user_id', $user_id)->delete();
        $users = User::where('id', $user_id)->delete();

        return response()->json([
            "success" => true,
            "message" => "User Account Suspended!",
        ]);
    }
    public function restore_account(Request $request)
    {
        $user_id = $request->user_id;
        User::where('id', $user_id)->restore();
        $user = User::where('id', $user_id)->first();
        UserProfile::where('user_id', $user_id)->restore();
        $user_profile_id = UserProfile::where('user_id', $user_id)->first()->id;

    
        // Restore the user account by setting the deleted_at field to null
    
        // Restore related data if they were soft-deleted
        UserProfile::where('user_id', $user_id)->restore();
        Admin::where('user_id', $user_id)->restore();
        ChatGallery::where('user_id', $user_id)->restore();
        ChatGalleryFile::where('user_id', $user_id)->restore();
        ChatRoom::where('user_id', $user_id)->restore();
        ChatRoomComment::where('user_id', $user_id)->restore();
        ChatRoomEntry::where('user_id', $user_id)->restore();
        ChatRoomReaction::where('reactor_id', $user_id)->restore();
        CreatorLibrary::where('user_id', $user_id)->restore();
        CreatorVerification::where('user_id', $user_id)->restore();
        ForgotPassword::where('user_profile_id', $user_profile_id)->restore();
        LiveGalleryTag::where('user_id', $user_id)->restore();
        LiveScheduleGallery::where('user_id', $user_id)->restore();
        Notification::where('notifiable_id', $user_id)->restore();
        UserAvatar::where('user_id', $user_id)->restore();
        UserChannel::where('user_id', $user_id)->restore();
        UserChatRoomFollow::where('user_id', $user_id)->restore();
        UserFollower::where('user_id', $user_id)->restore();
        UserGalleryFollow::where('user_id', $user_id)->restore();
    
        return response()->json([
            "success" => true,
            "message" => "User Account Restored!",
        ]);
    }
    
}
