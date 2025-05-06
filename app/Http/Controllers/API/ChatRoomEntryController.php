<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoomEntry;
use App\Models\ChatRoom;
use Validator;

class ChatRoomEntryController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(),[ 
            'chat_room_id'    => 'required|integer|exists:chat_rooms,id',
            'user_id'         => 'required|integer|exists:users,id',   
         ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

        if(!(ChatRoomEntry::where('user_id', $request->user_id)->where('chat_room_id', $request->chat_room_id)->first())){

            $chat_room_entry = new ChatRoomEntry;
            $chat_room_entry->chat_room_id = $request->chat_room_id;
            $chat_room_entry->user_id = $request->user_id;
            $chat_room_entry->save();
        }


        $chat_room = ChatRoom::with('members')->find($request->chat_room_id);
        
        return response()->json([
            'chat_room' => $chat_room,
            'members'   => count($chat_room->members)
        ]);
    }

    public function delete(Request $request){
        $validator = Validator::make($request->all(),[ 
            'chat_room_id'    => 'required|integer|exists:chat_rooms,id',
            'user_id'         => 'required|integer|exists:users,id',   
         ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

        if($chat_room_entry = ChatRoomEntry::where('user_id', $request->user_id)->where('chat_room_id', $request->chat_room_id)->first()){
            $chat_room_entry->delete();
         };
       

        $chat_room = ChatRoom::with('members')->find($request->chat_room_id);
        
        return response()->json([
            'chat_room' => $chat_room,
            'members'   => count($chat_room->members)
        ]);

    }
}
