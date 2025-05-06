<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Models\ChatRoomReaction;

class ReplyResource extends JsonResource
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
        $replies = $this->replies;

        $light_bulb_reactions = ChatRoomReaction::where('reaction','U+1F4A1')->where('chat_room_comment_id', $this->id)->get();
        $thumbs_up_reactions = ChatRoomReaction::where('reaction','U+1F44D')->where('chat_room_comment_id', $this->id)->get();
        $red_heart_reactions = ChatRoomReaction::where('reaction','U+2764')->where('chat_room_comment_id', $this->id)->get();

        $reactions = [
            'U+1F4A1' => count($light_bulb_reactions),
            'U+1F44D' => count($thumbs_up_reactions),
            'U+2764' => count($red_heart_reactions),
            'total'  => count($light_bulb_reactions) +  count($thumbs_up_reactions) + count($red_heart_reactions)
        ];

        return [
            'id'                => $this->id,
            'comment'           => $this->comment,
            'created_at'        => $this->created_at->diffForHumans(),
            'user'              => new UserResource($user),
            'replies_count'     => count($replies),
            'reactions_count'   => $reactions,
            'name'              => $this->name,
            'reactions'         => ChatRoomReactionResource::collection($this->reactions),
            'replies'           => ReplyResource::collection($replies),
            ];
    }
}
