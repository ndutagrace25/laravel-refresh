<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAvatar;

class AdminListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::where('id', $this->user_id)->first();
        $full_name = "";
        $avatar = "";
        $username = $user->username;

        
        if($user_profile = UserProfile::where('user_id', $this->user_id)->first()){
            $full_name = $user_profile->name;
        }

        if($user_avatar = UserAvatar::where('user_id', $this->user_id)->first()){
            if($user_avatar->path){
                $avatar = $user_avatar->path;
            }
        }

        $role = $this->is_root ? "Super Administrator" : "Administrator";

        return [
            'id'                => $this->id,
            'username'          => $username,
            'avatar'            => $avatar,
            'full_name'         => $full_name,
            'role'              => $role
        ];
    }
}
