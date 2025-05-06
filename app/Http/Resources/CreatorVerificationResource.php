<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Channel;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAvatar;


class CreatorVerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $channels = [];
        $channel_ids = explode(" ", $this->channel_id);
        foreach($channel_ids as $id){
            if($channel = Channel::where('id', $id)->first()){
                array_push($channels, $channel['name']);
            }
        }

        $user = User::where('id', $this->user_id)->first();
        
        $user_profile = UserProfile::where('user_id', $this->user_id)->first();
        $avatar = UserAvatar::where('user_id', $this->user_id)->first();

        $status = "Pending";
        if($this->status == 1){
            $status = "Verified";
        }elseif($this->status == 3){
            $status = "Denied";
        }

        return [
            "id" => $this->id,
            "avatar" => $avatar ? $avatar->path : null,
            "username" => $user->username,
            "fullname" => $user_profile->name,
            "known_as" => $this->known_as,
            "channels" => $channels,
            "tier" => $this->tier,
            "status" => $status,
            "proof_of_work" => $this->proof_of_work,
            "video" => $this->video,
            "photo_id" => $this->photo_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
