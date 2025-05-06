<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserAvatar;
use App\Models\CreatorVerification;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $avatar = UserAvatar::where('user_id', $this->id)->first();
        $verification = CreatorVerification::where('user_id', $this->id)->first();
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'is_creator'    => $this->is_creator,
            'is_official'   => $this->is_official,
            'is_private'    => $this->is_private,
            'is_verified'   => $verification?->status,
            'avatar'        => $avatar?->path,
            ];
    }
}
