<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\UserProfile;
use App\Models\ReportReason;
use App\Models\ChatGallery;


class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $content_title = '';
        $user_profile = NULL;
        $username = "";
        $reason = ReportReason::where('id', $this->report_reason_id)->first();

        if($this->reportable_type == "App\Models\ChatRoom"){
            $chat_room = ChatRoom::where('id', $this->reportable_id)->first();
            if(isset($chat_room)){
                $content_title = $chat_room->name;
            }

            $lastSlash = strrchr($this->reportable_type, '\\'); // Find the last backslash and everything after it
            $reportable_type = substr($lastSlash, 1); // Remove the backslash itself

            if($chat_room){
                $user = '';
                $user = User::where('id', $chat_room->user_id)->first();
                if(isset($user)){
                    $user_profile = UserProfile::where('user_id', $user->id)->first();
                    $username = $user->username;
                }
            }
        }else {
            if($this->reportable_type == "App\Models\ChatGallery"){
                $chat_gallery = ChatGallery::where('id', $this->reportable_id)->first();
                if(isset($chat_gallery)){
                    $content_title = $chat_gallery->name;
                }
    
                $lastSlash = strrchr($this->reportable_type, '\\'); // Find the last backslash and everything after it
                $reportable_type = substr($lastSlash, 1); // Remove the backslash itself
    
                if($chat_gallery){
                    $user = '';
                    $user = User::where('id', $chat_gallery->user_id)->first();
                    if(isset($user)){
                        $user_profile = UserProfile::where('user_id', $user->id)->first();
                        $username = $user->username;

                    }
                }
            }
        }




        return [
            'id'               => $this->id,
            'avatar'           => $user_profile?->path,
            'username'         => $username,
            'content_type'     => $reportable_type,
            'content_title'    => $content_title,
            'reason'           => $reason?->reason,
            
            ];
    }
}
