<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ChatGallery;
use App\Models\UserGalleryFollow;
use App\Models\Notification;
use App\Notifications\FollowChatGalleryNotification;
use Validator;

class UserChatGalleryController extends BaseController
{
    //
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|integer|exists:users,id', 
            'chat_gallery_id'    => 'required|integer|exists:chat_galleries,id'
        ]);

        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }

        $user_id = $request->user_id;
        $chat_gallery_id = $request->chat_gallery_id;

        if($user_gallery_follows = UserGalleryFollow::where('user_id', $user_id)->where('chat_gallery_id', $chat_gallery_id)->first()){
            
            return $this->handleError('This user has already been added to waiting list!',''); 

        }else{
            $user_gallery_follows = new UserGalleryFollow;
            $user_gallery_follows->user_id = $user_id;
            $user_gallery_follows->chat_gallery_id = $chat_gallery_id;
            $user_gallery_follows->save();

            $user = User::where('id', $user_id)->first();
            $user_profile = UserProfile::where('user_id', $user->id)->first();
            $gallery = ChatGallery::where('id', $chat_gallery_id)->first();

            // $notification = new Notification;
            // $notification->user_id = $gallery->user_id;
            // $notification->message = $user_profile->name." followed your gallery ". $gallery->name;
            // $notification->status = 0;
            // $notification->save();
              
            $gallery_user = User::where('id', $gallery->user_id)->first();

            $user_to_notified =

            $data['message'] = 'User '.$user->username." followed your chat gallery ".$gallery->name ;
            $data['user']  = $user;
            $gallery_user->notify(new FollowChatGalleryNotification($data));

            return response()->json([
                "success"    => true
            ]);

            $success['user'] = $user;
            $success['gallery'] = $gallery;
            $success['user_gallery_follow'] = $user_gallery_follows;

            return $this->handleResponse($success, $user_profile->name.'-You will be notified when this chat gallery goes live!'); 
        }
    }

    public function index($id)
    {
        if($chat_gallery = ChatGallery::where('id',$id)->first()){
            $followers = UserGalleryFollow::select('user_id')->where('chat_gallery_id', $id)->get();
            
            $users = User::whereIn('id', $followers)->get();
            $success['followers'] = $users;
            $success['gallery'] = $chat_gallery;

            return $this->handleResponse($success, 'List of all followers to be notified on Live'); 

        }
    }
}
