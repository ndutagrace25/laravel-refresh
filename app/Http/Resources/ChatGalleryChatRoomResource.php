<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ChatRoomEntry;
use App\Models\ChatRoom;
use App\Models\ChatRoomComment;;

class ChatGalleryChatRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $chat_room_entries = ChatRoomEntry::where('chat_room_id', $this->id)->get();
        $comments =  ChatRoomComment::where('commentable_id', $this->id)->get();
        $comment_count = count($this->comments);

        return [
            'id'                => $this->id,
            'title'             => $this->name,
            'view'              => $this->views,
            'comment_count'     => $comment_count,
            'audio'             => $this->audio,
            'members'           => count($chat_room_entries),
        ];
    }
}
