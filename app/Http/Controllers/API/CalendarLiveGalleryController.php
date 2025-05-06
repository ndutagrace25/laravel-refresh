<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ChatGallery;
use App\Http\Resources\CalendarGalleryResource;
use App\Http\Resources\LiveScheduleGalleryResource;
use App\Models\LiveScheduleGallery;
use App\Models\LiveQuestion;
use App\Models\Reservation;
use DateTime;

class CalendarLiveGalleryController extends Controller
{
    //Get list of calendar - for each gallery list
    public function index(Request $request, $user_id){

        if(!($user = User::find($user_id))){
            return response()->json(['error'=>"user not found"], 404);                        
        }

        $current_week_galleries = LiveScheduleGallery::where('user_id', $user_id)->whereBetween('date', [Carbon::now()->startOfWeek(), 
        Carbon::now()->endOfWeek()])->get();

        $weekly_galleries = [
            'Sunday'=>[],
            'Monday'=>[],
            'Tuesday'=>[],
            'Wednesday'=>[],
            'Thursday'=>[],
            'Friday'=>[],
            'Saturday'=>[]
        ];

        foreach($weekly_galleries as $weekday=>$galleries){
            foreach($current_week_galleries as $gallery){
                $schedule = new DateTime($gallery->date);
                $date = $schedule->format('l');
                $gallery['pre-live'] = count(LiveQuestion::where('live_schedule_gallery_id', $gallery->id)->get());
                
                if($weekday == $date){
                    array_push($weekly_galleries[$weekday], new LiveScheduleGalleryResource($gallery));
                }   
            }
        }
        

        return response()->json([
            "success" => true,
            "week_galleries" => $weekly_galleries,
        ]);
        
    }

    public function creator_index(Request $request, $user_id){

        if(!($user = User::find($user_id))){
            return response()->json(['error'=>"user not found"], 404);                        
        }
        
        $reservations = Reservation::where('user_id',$user_id)->pluck('live_schedule_gallery_id');

        $current_week_galleries = LiveScheduleGallery::whereIn('id', $reservations)->whereBetween('date', [Carbon::now()->startOfWeek(), 
        Carbon::now()->endOfWeek()])->get();

        $weekly_galleries = [
            'Sunday'    => [],
            'Monday'    => [],
            'Tuesday'   => [],
            'Wednesday' => [],
            'Thursday'  => [],
            'Friday'    => [],
            'Saturday'  => []
        ];

        foreach($weekly_galleries as $weekday=>$galleries){
            foreach($current_week_galleries as $gallery){
                $schedule = new DateTime($gallery->date);
                $date = $schedule->format('l');
                $gallery['pre-live'] = count(LiveQuestion::where('live_schedule_gallery_id', $gallery->id)->get());
                
                if($weekday == $date){
                    array_push($weekly_galleries[$weekday], new LiveScheduleGalleryResource($gallery));
                }   
            }
        }
        

        return response()->json([
            "success" => true,
            "week_galleries" => $weekly_galleries,
        ]);
    }
}
