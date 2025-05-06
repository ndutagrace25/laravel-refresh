<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Models\ChatGallery;
use App\Models\UserChannel;
use App\Models\Channel;
use App\Models\UserFollower;
use App\Models\CreatorLibrary;
use App\Models\UserGalleryFollow;
use App\Models\LiveScheduleGallery;

class AdmirerDashboardResource extends JsonResource
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
        
        $all_galleries = 0;

        
        $channel_list = UserChannel::where('user_id', $this->id)->get();
        $user_id = $this->id;
        $chat_galleries = [];
    
        if($all_galleries = UserGalleryFollow::where('user_id', $this->id)->get()){
            foreach($all_galleries as $gallery){
                $gallery_item = ChatGallery::where('id', $gallery->chat_gallery_id)->first();
                if($gallery_item){
                    array_push($chat_galleries, $gallery_item);
                }
            }
        }
 

        $following = 0;

        if($all_following = UserFollower::where('follower_id', $this->id)->get()){
            $following = count($all_following);
        }
           
        return [
            'id'                        => $this->id,
            'user'                      => new UserResource($this),
            'gallery_count'             => count($chat_galleries),
            'following'                 => $following,
            'archlives'                 => [],
            'chat_galleries'            => ChatGalleryResource::collection($chat_galleries),
            
            ];
    }
}
