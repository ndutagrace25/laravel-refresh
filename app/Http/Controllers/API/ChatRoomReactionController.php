<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ChatRoomReaction;
use App\Models\Notification;
use App\Models\ChatRoomComment;
use App\Notifications\ReactToChatRoomCommentNotification;
use Validator;

class ChatRoomReactionController extends Controller
{
    public function index(Request $request, $id){

        if($comment = ChatRoomComment::where('id', $id)->first()){

            $light_bulb_reactions = ChatRoomReaction::where('reaction','U+1F4A1')->where('chat_room_comment_id', $id)->get();
            $thumbs_up_reactions = ChatRoomReaction::where('reaction','U+1F44D')->where('chat_room_comment_id', $id)->get();
            $red_heart_reactions = ChatRoomReaction::where('reaction','U+2764')->where('chat_room_comment_id', $id)->get();

            $reactions = [
                'U+1F4A1' => count($light_bulb_reactions),
                'U+1F44D' => count($thumbs_up_reactions),
                'U+2764' => count($red_heart_reactions)
            ];

            return response()->json([
                "success"              => true,
                "reaction_count"       => $reactions,
            ]);

        }
        else{
            return response()->json([
                "success"       => false,
                "message"       => "Comment Not found",
            ], 404);
        }
    }

    //
    public function store(Request $request){
        $validator = Validator::make($request->all(),[ 
            'comment_id'    => 'required|integer|exists:chat_room_comments,id',   
            'reactor_id'    => 'required|integer|exists:users,id',
            /*
            reaction emojis should be one of these:
                U+1F4A1 - Light bulb
                U+1F44D - Thumbs Up
                U+2764	- Red Heart
            */
            'reaction'      => 'required|string', 
            ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 
        $reactor_id = $request->reactor_id;

       

        $comment = ChatRoomComment::where('id', $request->comment_id)->first();
        $reactor = User::where('id', $reactor_id)->first()?->username;

        $chat_room_reaction = new ChatRoomReaction;
        $chat_room_reaction->chat_room_comment_id = $request->comment_id;
        $chat_room_reaction->reaction = $request->reaction;
        $chat_room_reaction->reactor_id = $reactor_id;
        $chat_room_reaction->save();


        $user = User::where('id', $comment->user_id)->first();

        $followData['message'] = 'User'.$reactor." has reacted to your comment" ;
        $followData['user'] = $user;

        $user->notify(new ReactToChatRoomCommentNotification($followData));
        
        return response()->json([
            "success"       => true
        ]);

    }
}
