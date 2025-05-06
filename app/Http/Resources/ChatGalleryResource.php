<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Channel;
use App\Models\User;
use App\Models\Tag;
use App\Models\ChatGalleryFile;
use App\Models\ChatRoom;
use App\Models\ChatRoomEntry;
use App\Models\ChatRoomComment;
use App\Models\UserAvatar as Avatar;
use Carbon\Carbon;

class ChatGalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $channel = $this->channel_id;
        $channel_item = Channel::where('id', $channel)->first();
        $user = User::where('id', $this->user_id)->first();

        $chat_rooms = ChatRoom::where('chat_gallery_id', $this->id)->get();

        $t1 = Carbon::now();
        $t2 = $this->live_schedule;
        $interval = $t1->diff($t2);
        $diff = $interval->format('%h:%i:%S');

        $comment_count = 0;
        $members_count = 0;

        if($chat_rooms_list = ChatRoom::where('chat_gallery_id', $this->id)->get()){
            foreach($chat_rooms_list as $chat_room){
                $chat_room_comments = count(ChatRoomComment::where('commentable_id', $chat_room->id)->where('parent_id', NULL)->get());
                $comment_count += $chat_room_comments;
            }
        };

        if($chat_rooms_entries_list = ChatRoom::where('chat_gallery_id', $this->id)->get()){
            foreach($chat_rooms_entries_list as $chat_room){
                $chat_room_entries = count(ChatRoomEntry::where('chat_room_id', $chat_room->id)->get());
                $members_count += $chat_room_entries;
            }
        };

        $is_cover_photo_color = 0;
        $check = "";
        for ($i=-5; $i > -11 ; $i--) { 
            $check .= substr(ChatGalleryFile::where('uuid', $this->uuid)->first()?->cover_photo, $i,1);
        }
        
        $colors = ["F8B966","DD1E25","721003"];
        $check = strrev($check);
        if(in_array($check, $colors)){
            $is_cover_photo_color = 1;
        }

        $show_count_down = 0;
        if($this->live_schedule > $t1){
            $show_count_down = 1;
        }


        return [
            'id'                => $this->id,
            'comments'          => $comment_count,
            'views'             => $this->views,
            'members'           => $members_count,
            'number_of_chat_rooms' => count($chat_rooms),
            'user'              => new UserResource($user),
            'bucket_tag'        => Tag::where('id', $this->tag_id)->first()?->name, 
            'is_cover_photo_color' => $is_cover_photo_color,
            'show_count_down'   => $show_count_down,
            'cover_photo'       => ChatGalleryFile::where('uuid', $this->uuid)->first()?->cover_photo,
            'greeting_audio'    => ChatGalleryFile::where('uuid', $this->uuid)->first()?->greeting_audio,
            'greeting_video'    => ChatGalleryFile::where('uuid', $this->uuid)->first()?->greeting_video,
            'channel'           => $channel_item,
            'topic'             => $this->topic,
            'name'              => $this->name,
            'official'          => $this->official,
            'private'           => $this->private,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'live_schedule'     => $this->live_schedule,
            'time_until_live'   => $diff,
            'chat_rooms'        => ChatRoomResource::collection($chat_rooms),
            ];
    }
}
