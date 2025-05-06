<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;

class CalendarGalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $schedule = new DateTime($this->live_schedule);
        return [
            "date"          => $this->live_schedule,
            "date_word"     => $schedule->format('l')
        ];
    }
}
