<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveQuestion;
use App\Models\LiveScheduleGallery;
use Validator;

class LiveQuestionController extends Controller
{

    public function select(Request $request){
        $validator = Validator::make($request->all(),[ 
            'live_questions'                    => 'required|array', 
            'live_questions.*'                  => 'required|integer|exists:live_questions,id'
        ]);

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                      
        }

        foreach($request->live_questions as $question){
            $live_question = LiveQuestion::where('id', $question)->first();
            $live_question->status = 1;
            $live_question->save();
        }
        return response()->json([
            "success"           => true,
            "message"           => "Pre live questions selected!",
        ]);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(),[ 
            'live_schedule_gallery_id'      => 'required|integer|exists:live_schedule_galleries,id', 
            'question'                      => 'required|string',
            'user_id'                       => 'required|integer|exists:users,id'
        ]);   

        if($validator->fails()) {            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }
        $live_questions = LiveQuestion::where('live_schedule_gallery_id', $request->live_schedule_gallery_id)->get();
        $live_schedule_gallery = LiveScheduleGallery::where('id', $request->live_schedule_gallery_id)->first();
        
        if($live_schedule_gallery){
            $user_live_question = LiveQuestion::where('live_schedule_gallery_id', $request->live_schedule_gallery_id)
            ->where('user_id', $request->user_id)->first();

            if($val = $live_schedule_gallery['pre-live']){
            
                if(count($live_questions) >= $val){
                    return response()->json([
                        "success"           => false,
                        "message"           => "You can't submit more than ".$val." Pre-live Questions",
                    ]);
                }
            };

            if($user_live_question){
                return response()->json([
                    "success"           => false,
                    "message"           => "You can't submit more than one Pre-live Question",
                ]);
            }
     
           
        };
   
        $question = new LiveQuestion($request->all());
        $question->save();

        return response()->json([
            "success"           => true,
            "message"           => "Pre live question submitted successfully",
        ]);
    }
    public function index(Request $request, $id){

        if(!($live_schedule_galleries = LiveScheduleGallery::find($id))) {          
            return response()->json(['error'=>"Live Scheduled Gallery not found"], 404);                        
        }

        $live_schedule_gallery = LiveScheduleGallery::where('id', $id)->first();

        $questions = LiveQuestion::where('live_schedule_gallery_id', $id)->get();
        $total_questions = count($questions);

        return response()->json([
            "success"           => true,
            "total_submitted"   => $total_questions,
            "total_requested"   => $live_schedule_gallery['pre-live'],
            "questions"         => $questions,
        ]);
    }
}
