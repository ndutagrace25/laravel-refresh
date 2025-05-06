<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatGallery;
use App\Models\ChatRoomComment;
use App\Http\Resources\ChatRoomResource;
use Validator;

class ChatRoomController extends Controller
{
    public function index(Request $request, $id){

        if(!count(ChatRoom::where('id', $id)->get())){
            return response()->json([
                "success"         => false,
                "message"         => "No Chat Room found",
            ]);
        };
        $chat_room = ChatRoom::where('id', $id)->first();
        $chat_room->views += 1;
        $chat_room->save();
        return response()->json([
            "success"           => true,
            "chat_room"         => new ChatRoomResource($chat_room),
        ]);
    }
    public function delete(Request $request){
        $validator = Validator::make($request->all(),[ 
            'chat_room_id'       => 'required|integer|exists:chat_rooms,id,deleted_at,NULL',
         ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 404);     
                               
        } 

        ChatRoomComment::where('commentable_id', $request->chat_room_id)->where('deleted_at', NULL)->delete();
       
        $chat_room = ChatRoom::where('id', $request->chat_room_id)->first();
        $chat_room->delete();
        return response()->json([
            "success"           => true,
            "message"           => "Chat Room deleted successfully",
        ]);
        

        

    }

    public function edit(Request $request){

        $validator = Validator::make($request->all(),[ 
            'chat_room_id'       => 'required|integer|exists:chat_rooms,id',
            'name'               => 'required|string',
            'chat_gallery_id'    => 'required|integer|exists:chat_galleries,id',   
            'user_id'            => 'required|integer|exists:users,id'
         ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);     
                               
        } 

        /////////////////////////////////////////////////
        $id = $request->chat_room_id;
        $chat_room = ChatRoom::where('id', $id)->first();
        $chat_room->name = $request->name;
        $chat_room->chat_gallery_id = $request->chat_gallery_id;
        $chat_room->save();

        return response()->json([
            "success"           => true,
            "chat_room"         => new ChatRoomResource($chat_room),
        ]);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(),[ 
            'name'               => 'required|string',
            'chat_gallery_id'    => 'required|integer|exists:chat_galleries,id',   
            'user_id'            => 'required|integer|exists:users,id'
         ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);     
                               
        } 
        $chat_gallery = ChatGallery::where('id', $request->chat_gallery_id)->first();
        if( $chat_gallery->private && ($request->user_id != $chat_gallery->user_id)){
            return response()->json([
                'error'=>'only chat gallery creator can add chat room',
            ]);
        }

        $chat_room = new ChatRoom;
        $chat_room->name = $request->name;
        $chat_room->user_id = $request->user_id;
        $chat_room->chat_gallery_id = $request->chat_gallery_id;
        $chat_room->save();

        return response()->json([
            "success"           => true,
            "message"           => "chat room created successfully",
        ]);
    }

}
