<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportReason;
use App\Models\Report;
use App\Models\ChatRoom;
use App\Models\ChatGallery;
use App\Http\Resources\ReportResource;

use Validator;

class ReportController extends Controller
{
    //
    public function index(Request $request){

        if(!count(ReportReason::all())){
            return response()->json([
                "success"         => false,
                "message"         => "No Reasons found!",
            ]);
        };
        $reasons = ReportReason::all();
        return response()->json([
            "success"           => true,
            "Report Reasons"    => $reasons,
        ]);
    }

    public function reports(Request $request)
    {
        $reports = Report::all();
        return response()->json([
            "success"    => true,
            "Reports"    => ReportResource::collection($reports),
        ]);

    }

    public function report_chat_room(Request $request){
       
        $validator = Validator::make($request->all(),[ 
            'chat_room_id'      => 'required|integer|exists:chat_rooms,id', 
            'report_reason_id'  => 'required|integer|exists:report_reasons,id',
            'reason'            => 'string'
        ]);   

        if($validator->fails()) {            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $chat_room = ChatRoom::where('id', $request->chat_room_id)->first();
        $chat_room_report = new Report();
        $chat_room_report->report_reason_id = $request->report_reason_id;
        $request->reason ? $chat_room_report->reason = $request->reason : "";
        $chat_room_report->reportable_type = "App\Models\ChatRoom";
        $chat_room_report->reportable_id = $request->chat_room_id;
        $chat_room_report->save();

        return response()->json([
            "success" => true,
            "message" => "your report is submitted"
        ]);
    }

    public function report_chat_gallery(Request $request){

        $validator = Validator::make($request->all(),[ 
            'chat_gallery_id'    => 'required|integer|exists:chat_galleries,id', 
            'report_reason_id'   => 'required|integer|exists:report_reasons,id',
            'reason'             => 'string'
        ]);   

        if($validator->fails()) {            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $chat_gallery = ChatGallery::where('id', $request->chat_gallery_id)->first();
        $chat_gallery_report = new Report();
        $chat_gallery_report->report_reason_id = $request->report_reason_id;
        $request->reason ? $chat_gallery_report->reason = $request->reason : "";
        $chat_gallery_report->reportable_type = "App\Models\ChatGallery";
        $chat_gallery_report->reportable_id = $request->chat_gallery_id;
        $chat_gallery_report->save();

        return response()->json([
            "success" => true,
            "message" => "your report is submitted"
        ]);
    }

    public function ignore(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            'report_id'   => 'required|integer|exists:reports,id',
        ]);  

        if($validator->fails()) {            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $report = Report::where('id', $request->report_id)->first();
        $report->delete();

        return response()->json([
            "success" => true,
            "message" => "Report ignored and deleted"
        ]);
    }
}
