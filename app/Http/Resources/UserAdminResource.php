<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserAvatar;
use App\Models\UserProfile;
use App\Models\UserFollower;
use App\Models\ChatRoom;
use App\Models\User;

class UserAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $avatar = "";
        $followers = 0;
        $following = 0;
        $chat_rooms = 0;
        $full_name = "";
        $user = User::withTrashed()->find($this->id);
        $username = $user->username;

        if($user_profile = UserProfile::withTrashed()->where('user_id', $user->id)->first()){
            $full_name = $user_profile->name;
        }

        if($user_avatar = UserAvatar::withTrashed()->where('user_id', $user->id)->first()){
            if($user_avatar->path){
                $avatar = $user_avatar->path;
            }
        }

        if($user_follower = UserFollower::withTrashed()->where('user_id', $user->id)->get()){
            $followers = count($user_follower);
        }
        
        if($user_following = UserFollower::withTrashed()->where('follower_id', $user->id)->get()){
            $following = count($user_following);
        }

        if($chat_room = ChatRoom::withTrashed()->where('user_id', $user->id)->get()){
            $chat_rooms = count($chat_room);
        }

        return [
            'id'                => $this->id,
            'username'          => $username,
            'fullname'          => $full_name,
            'avatar'            => $avatar,
            'followers'         => $followers,
            'following'         => $following,
            'chat_rooms'        => $chat_rooms
            ];
    }
}
