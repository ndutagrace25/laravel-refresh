<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatGalleryUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'user'              => User::where('id',$this->user_id)->first(),
            'cover_photo'       => ChatGalleryFile::where('uuid', $this->uuid)->first()?->cover_photo,
            'channel'           => $channel_item,
            'topic'             => $this->topic,
            'name'              => $this->name,
            'official'          => $this->official,
            'private'           => $this->private,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'live_schedule'     => $this->live_schedule,
            'time_until_live'   => $diff,
            ];
    }
}
