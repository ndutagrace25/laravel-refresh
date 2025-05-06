<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\UserAvatar;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchLiveResource extends JsonResource
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
        $avatar = UserAvatar::where('user_id', $this->user_id)->first()['path'];
    
        return [
            "username"     => $user->username,
            "video"        => $this->video,
            "avatar"       => $avatar,
            "official"     => $user->is_official      
        ];
    }
}
