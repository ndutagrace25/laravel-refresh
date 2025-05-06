<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
class LiveGalleryScheduleResource extends JsonResource
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
        $is_creator = $user['is_creator'];

        return [
            'id'                => $this->id,
            'is_creator'        => $is_creator,
            'entry_fee'         => $this->entry_fee,
            'user_id'           => $this->user_id,
            'channel_id'        => $this->channel_id,
            'tag_id'            => $this->tag_id,
            'title'             => $this->title,
            'uuid'              => $this->uuid,
            'cover_photo'       => $this->cover_photo,
            'start_time'        => $this->start_time,
            'end_time'          => $this->end_time,
            'is_premium'        => $this->is_premium,
            'description'       => $this->description,
            'date'              => $this->date,
            'timezone'          => $this->timezone,
            'pre-live'          => $this->pre_live,
            'stream_key'        => $this->stream_key,
            'url'               => $this->url,
            'status'            => $this->status,
            'product_id'        => $this->product_id
        ];
    }
}
