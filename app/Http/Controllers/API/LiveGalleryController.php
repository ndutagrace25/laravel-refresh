<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ChatGallery;
use App\Models\Chat;
use App\Models\LiveScheduleGallery;
use Response;
use App\Models\User;
use App\Models\Tag;
use Carbon\Carbon;
use App\Models\Reservation;
use App\Http\Resources\LiveGalleryScheduleResource;


class LiveGalleryController extends BaseController
{
    //
    public function index(Request $request, $id){
       
        if(!($user = User::find($id))) {          
            return response()->json(['error'=>"User not found"], 404);                        
        }

        $list_of_lives = [];

        $gallery_list = LiveScheduleGallery::where('user_id', $id)->get();
        $reservations = Reservation::where('user_id', $id)->get();

        foreach($gallery_list as $gallery){
            array_push($list_of_lives, $gallery);
        }

        foreach($reservations as $reservation){
            if($live = LiveScheduleGallery::where('id', $reservation->live_schedule_gallery_id)->first()){
                array_push($list_of_lives, $live);
            }
        }

        $returnData = array(
            'status'         => true,
            'live_galleries' => LiveGalleryScheduleResource::collection($list_of_lives),
        );

        return Response::json($returnData, 200);  
    }

    public function view(Request $request, $id){
        if(!($gallery = LiveScheduleGallery::find($id))){
            return response()->json(['error'=>"live schedule not found"], 404);                        
        }


        $gallery = LiveScheduleGallery::find($id);
        $carbonTime = Carbon::parse($gallery->start_time); // Parse the time
        $formattedTime = $carbonTime->format('h:i A'); 

        $user = User::where('id', $gallery->user_id)->first(); 
        $date = Carbon::parse($gallery->date);
        $formattedDate = $date->format('l, F j');
        return response()->json([
            "success"           => true,
            "date"              => $formattedDate,
            "username"          => $user->username,
            "is_creator"        => $user->is_creator,
            "premimum_gallery"  => (int)$gallery->is_premium,
            "tag"               => Tag::where('id', $gallery->tag_id)->first()['name'],
            "title"             => $gallery->title,
            "plan_id"           => $gallery->product_id,
            "description"       => $gallery->description,
            "start_time"        => $formattedTime,
        ]);


    }
}
