<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;use App\Models\User;
use App\Models\UserProfile;
use App\Models\ChatRoom;
use App\Models\UserChatRoomFollow;
use App\Models\Notification;
use App\Http\Resources\UserResource;
use App\Notifications\UserFollowChatRoomNotification;
use Validator;

class UserChatRoomFollowController extends BaseController
{
    //
    //
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|integer|exists:users,id', 
            'chat_room_id'    => 'required|integer|exists:chat_rooms,id'
        ]);

        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }

        $user_id = $request->user_id;
        $chat_room_id = $request->chat_room_id;

        if($user_chat_room_follows = UserChatRoomFollow::where('user_id', $user_id)->where('chat_room_id', $chat_room_id)->first()){
            
            return $this->handleError('This user has already followed this chat room!',''); 

        }else{
            $user_chat_room_follows = new UserChatRoomFollow;
            $user_chat_room_follows->user_id = $user_id;
            $user_chat_room_follows->chat_room_id = $chat_room_id;
            $user_chat_room_follows->save();

            $user = User::where('id', $user_id)->first();
            $user_profile = UserProfile::where('user_id', $user->id)->first();
            $chat_room = ChatRoom::where('id', $chat_room_id)->first();
            $chat_room_user = User::where('id', $chat_room->user_id)->first();


            $data['message'] = 'User '.$user->username." followed your chat room ".$chat_room->name ;
            $data['user']  = $user;
            $chat_room_user->notify(new UserFollowChatRoomNotification($data));

            return response()->json([
                "success"    => true
            ]);

           
        }
    }

    public function index(Request $request, $id)
    {
        if($chat_room = ChatRoom::where('id',$id)->first()){
            $followers = UserChatRoomFollow::select('user_id')->where('chat_room_id', $id)->get();
            
            $users = User::whereIn('id', $followers)->get();
         
            return response()->json([
                "success"    => true,
                "followers"  => UserResource::collection($users),
                "chat_room"  => $chat_room
            ]);

        }else{
            return response()->json([
                "success"    => false,
                "message"    => "Chat room not found!"
            ], 404);
        }
    }

    public function check_follow(Request $request, $chat_room_id, $user_id){

        $follow = UserChatRoomFollow::where('user_id', $user_id)->where('chat_room_id', $chat_room_id)->first();
        if($follow){
            return response()->json([
                true
            ]);
        }
        return response()->json([
            false
        ]);
    }

    public function delete(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|integer|exists:users,id', 
            'chat_room_id'    => 'required|integer|exists:chat_rooms,id'
        ]);

        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }

        if($follow = UserChatRoomFollow::where('user_id', $request->user_id)->where('chat_room_id', $request->chat_room_id)->first()){
            $follow->delete();

            return response()->json([
                "success"    => true,
                "message"    => "You have unfollowed this chat room",
            ], 200);
        }

        return response()->json([
            "success"    => false
        ], 404);

    }
}
