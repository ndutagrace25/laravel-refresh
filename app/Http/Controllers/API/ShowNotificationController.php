<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class ShowNotificationController extends Controller
{
    //
    public function index(Request $request, $id){

        if(!($user = User::where('id', $id)->first())){
            return response()->json([
                "success" => false,
                "message" => "user not found",
            ], 404);
        };

        $date_notifications = [
            "Today"=>[],
            "This Week"=>[]
        ];

        $todaynotification = Notification::where('notifiable_id', $id)->whereDate('created_at', Carbon::today())->get();
        $thisweeknotification = Notification::where('notifiable_id', $id)->whereBetween('created_at', [Carbon::now()->startOfWeek(),
        Carbon::now()->endOfWeek()])->whereDate('created_at','!=',Carbon::today())->get();

        return response()->json([
            "success"   => true,
             ["title" => "Today",      "data" => NotificationResource::collection($todaynotification)],
             ["title" => "This week",  "data" => NotificationResource::collection($thisweeknotification)]
        ]);
    }

    public function count(Request $request, $id){

        if(!($user = User::where('id', $id)->first())){
            return response()->json([
                "success" => false,
                "message" => "user not found",

            ], 404);
        };

        $todaynotification = Notification::where('notifiable_id', $id)->whereDate('created_at', Carbon::today())->get();
        return response()->json([
            "success"   => true,
            "data" => $todaynotification->count(),
        ]);
    }
}
