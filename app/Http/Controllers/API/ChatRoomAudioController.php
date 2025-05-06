<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\models\ChatRoom;

class ChatRoomAudioController extends Controller
{
    //
    public function store(Request $request){

        $validator = Validator::make($request->all(),[ 
            'chat_room_id'       => 'required|integer|exists:chat_rooms,id',
            'audio'              => 'required|mimes:mp3,acc,wav,mp4,aif,aiff,caf,m4a,ec3,3gp',
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $chat_room = ChatRoom::where('id', $request->chat_room_id)->first();

        if ($file  = $request->file('audio')) {
            $path  = $file->store('chat_galleries/greeting_audios', 's3');
            $name  = $file->getClientOriginalName();
        
            $chat_room->audio = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            $chat_room->save();

            return response()->json([
                "success"             => true,
                "message"             => "Audio added to chat room successfully",
            ]);
        }
    }
}
