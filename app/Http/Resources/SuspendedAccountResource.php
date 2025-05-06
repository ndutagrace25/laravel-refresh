<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\AccountSuspension;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAvatar;
use App\Models\CreatorVerification;
use App\Models\UserFollower;
use App\Models\ChatRoom;


class SuspendedAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */ 
    public function toArray($request)
    {
        
        $suspension = AccountSuspension::where('user_id', $this->id)->first()? "Suspended" : "Active";
        $username = "";
        $full_name = "";
        $tier = "";
        $avatar = "";
 
        $username = $this->username;
        if($user_profile = UserProfile::where('user_id', $this->id)->first()){
            $full_name = $user_profile->name;
        }
        if($user_avatar = UserAvatar::where('user_id', $this->id)->first()){
            if($user_avatar->path){
                $avatar = $user_avatar->path;
            }
        }
        if($this->is_creator){
            $tier = "Creator";
        }else{
            $tier = "Admirer";
        }
        if($user_verification = CreatorVerification::where('user_id', $this->id)->first()){
            if($user_verification->status == 1){
                $tier = $tier. " - Tier ".$user_verification->tier;
            }
        }

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
            'avatar'            => $avatar,
            'username'          => $username,
            'account_type'      => $tier,
            'account_status'    => $suspension,
            'fullname'          => $full_name,
            'followers'         => $followers,
            'following'         => $following,
            'chat_rooms'        => $chat_rooms
            
            ];
    }
}
