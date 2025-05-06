<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\ChatGallery;
use App\Models\UserChannel;
use App\Models\Channel;
use App\Models\UserFollower;
use App\Models\CreatorLibrary;
use App\Models\LiveScheduleGallery;

class CreatorDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $channel = $this->channel_id;
        // $channel_item = Channel::where('id', $channel)->first();
        // $user = User::where('id', $this->user_id)->first();

        // $chat_rooms = ChatRoom::where('chat_gallery_id', $this->id)->get();

         $t1 = Carbon::now();
         $date1 = Carbon::parse($t1)->format('Y-m-d');


        $next_live_gallery = LiveScheduleGallery::where('user_id', $this->id)->where('date', '>=', $date1)
                                ->orderBy('date','asc')->first();


        $all_galleries = 0;

        if($all_galleries = ChatGallery::where('user_id', $this->id)->get()){
            $all_galleries = count($all_galleries);
        }

        $channel_list = UserChannel::where('user_id', $this->id)->get();
        $user_id = $this->id;

        if(isset($next_live_gallery)){
            $date = Carbon::parse($next_live_gallery->date)->format('Y/m/d');
            $time = $next_live_gallery->start_time;
            $new_date = $date." ".$time;

            $t2 = $new_date;
            $interval = $t1->diff($t2);
            $days = $t1->diffIndays($t2);
            $hours = $interval->format('%h');
            $minutes = $interval->format('%i');
            $hours = (24*$days)+$hours;

            //format minutes less than 10, as eg: "01", "06"
            if($minutes < 10){
                $minutes = "0".$minutes;
            }
            $diff = $hours.":".$minutes;
        }else{
            $diff = null;
        }

        $chat_galleries = ChatGallery::where('user_id', $this->id)->orderBy('created_at', 'desc')->get();

        $followers = 0;

        if($followers = UserFollower::where('user_id', $this->id)->get()){
            $followers = count($followers);
        }

        $library = CreatorLibrary::where('user_id', $this->id)->first();

        return [
            'id'                        => $this->id,
            'url'                       => $library?->url,
            'bio'                       => $library?->bio,
            'user'                      => new UserResource($this),
            'official'                  => $this->is_official?true:false,
            'next_live'                 => $diff,
            'gallery_count'             => $all_galleries,
            'followers'                 => $followers,
            'archlives'                 => [],
            'chat_galleries'            => ChatGalleryResource::collection($chat_galleries),

            ];
    }
}
