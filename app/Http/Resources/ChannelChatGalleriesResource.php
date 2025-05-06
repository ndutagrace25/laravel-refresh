<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Channel;
use App\Models\ChatGallery;
use App\Models\ChatGalleryfile;
use App\Models\User;
use Carbon\Carbon;

class ChannelChatGalleriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $channel = $this->channel_id;
        $channel_item = Channel::where('id', $channel)->first();
        $user = User::where('id', $this->user_id)->first();

        $t1 = Carbon::now();
        $t2 = $this->live_schedule;
        $interval = $t1->diff($t2);
        $days = $t1->diffIndays($t2);
        $hours = $interval->format('%h');
        $minutes = $interval->format('%i');
        $seconds = $interval->format('%S');
        $hours = (24*$days)+$hours;
        $diff = $hours.":".$minutes.":".$seconds; 

        return [
            'id'                => $this->id,
            'cover_photo'       => ChatGalleryFile::where('uuid', $this->uuid)->first()?->cover_photo,
            'topic'             => $this->topic,
            'name'              => $this->name,
            'views'             => $this->views,
            'time_until_live'   => $diff
            ];
    }
}
