<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class ChatRoomReactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $reactions = $this->reactions;
        $reactor = User::where('id', $this->reactor_id)->first();

        return [
            'id'                => $this->id,
            'reaction'          => $this->reaction,
            'reactor'           => new UserResource($reactor),
            'created_at'        => $this->created_at->diffForHumans()
            ];
    }
}
