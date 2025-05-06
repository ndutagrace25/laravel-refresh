<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = null;
        $jsondata = json_decode($this->data, true);
        
        if(isset($jsondata['user']['id'])){
            $user = User::whereId($jsondata['user']['id'])->first();
        }

        return [    
            'id'            => $this->id,
            'message'       => $jsondata['message'],
            'user'          => new UserResource($user),
            'datetime'      => $this->created_at->diffForHumans()
        ];
    }
}
