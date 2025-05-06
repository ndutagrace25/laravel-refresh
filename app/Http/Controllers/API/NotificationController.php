<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatGallery;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationController extends Controller
{
    //
    public function index($id){

        if(!($user = User::where('id', $id)->first())){
            $returnData = array(
                'status'  => false,
                'message' => 'User does not exist!'
            );
            return Response::json($returnData, 500);    
        }

        $now = Carbon::now();
        $notifiables = [];
        $user_galleries = ChatGallery::where('live_schedule', '>=' , $now)->where('user_id', $user->id)->get();
        
        foreach($user_galleries as $gallery ){
            $now = Carbon::createFromFormat('Y-m-d H:s:i', $now);
            $from = Carbon::createFromFormat('Y-m-d H:s:i', $gallery->live_schedule);
            
            $diff_in_minutes = $now->diffInMinutes($from);

            //Four minutes difference for live estimation
            if($diff_in_minutes <= 2400 && $diff_in_minutes > 10){
                array_push($notifiables, $gallery);
            }
        }

        foreach($notifiables as $gallery){
            $notify = true;
            //Check if this user has already been notified
            if($notification = Notification::where('action', 'live_stream')->where('subject', $gallery->id)
            ->where('user_id', $user->id)->first()){
                $notify = false;
            }

            //If not create notification
            if($notify){
                // $to_be_live = new Notification();
                // $to_be_live->action = "live_stream";
                // $to_be_live->subject = $gallery->id;
                // $to_be_live->user_id = $user->id;
                // $to_be_live->message = "Your scheduled live stream ".$gallery->name."begins in 1hr";
                // $to_be_live->status = 0;
                // $to_be_live->save();
            }
        }


        if(!$notifications = Notification::where('user_id', $id)->get()){
            $returnData = array(
                'status'  => true,
                'message' => 'No notifications'
            );
            return Response::json($returnData, 200); 
        }

        
        $returnData = array(
            'status'  => true,
            'notifications' => $notifications
        );
        foreach($notifications as $notification){
            $notification->status = 1;
            $notification->save();
        }
        return Response::json($returnData, 200);  

    }
}
