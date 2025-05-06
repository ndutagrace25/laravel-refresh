<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LiveQuestion;
use Response;
use App\Models\LiveScheduleGallery;
use App\Models\User;


class PreLiveQuestionController extends Controller
{
    public function index(Request $request, $gallery_id, $user_id){
        if(!($user = User::where('id', $user_id)->first())){
            $returnData = array(
                'status'  => false,
                'message' => 'User does not exist!'
            );
            return Response::json($returnData, 404);    
        }
        if(!($user = LiveScheduleGallery::where('id', $gallery_id)->first())){
            $returnData = array(
                'status'  => false,
                'message' => 'Live Gallery does not exist!'
            );
            return Response::json($returnData, 404);    
        }

        $user_live_question = LiveQuestion::where('live_schedule_gallery_id', $gallery_id)
            ->where('user_id', $user_id)->first();
        if($user_live_question){
            return 0;  
        }
        return 1;  
    }
}