<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserAvatar;
use App\Models\LiveCentral;
use App\Models\UploadVideoFile;
use App\Models\LiveScheduleGallery;

class LiveCentralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $profile = UserAvatar::where('user_id', $this->user_id)->first();
        $is_uploaded = 0;
        $is_premium = 0;
        $video_url = "";

        if($this->uuid != ""){
            $is_uploaded = 1;

            $live_file = UploadVideoFile::where('uuid', $this->uuid)->first();
            if($live_file){
                $video_url = $live_file->video;
            }

            $live_schedule = LiveScheduleGallery::where('uuid', $this->uuid)->first();
            if ($live_schedule) {
                $is_premium = $live_schedule->is_premium;
            }
        }

        return [
            'id'            => $this->id,
            'AppID'         => "307008811",
            'AppSign'       => "e76d8bf4c23a80df1208d5494e7c222c213daca2f75b8e71df7ecf70c5f909db",
            'created_at'    => $this->created_at->format('Y-m-d'),
            'live_id'       => $this->live_id,
            'room_id'       => $this->room_id,
            'user_id'       => $this->user_id,
            'is_uploaded'   => $is_uploaded,
            'is_premium'    => $is_premium,
            'video_url'     => $video_url,
            'profile'       => $profile?->path,
            'username'      => $this->username
        ];
    }
}
