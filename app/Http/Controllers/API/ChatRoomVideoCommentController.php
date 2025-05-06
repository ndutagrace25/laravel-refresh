<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatRoomComment;
use App\Models\User;
use Validator;
use App\Notifications\ReplyToCommentNotification;
use App\Notifications\MentionedInCommentNotification;
use App\Http\Resources\ChatRoomResource;

class ChatRoomVideoCommentController extends Controller
{
    public function store(Request $request){
    
        $validator = Validator::make($request->all(),[ 
        'user_id'          => 'required|integer|exists:users,id',  
        'parent_id'        => 'integer|exists:chat_room_comments,id',   
        'chat_room_id'     => 'required|integer|exists:chat_rooms,id',
        'video_comment'    => 'required|mimes:mp4,mov,ogg,qt'
        ]); 
    
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

    $comment = new ChatRoomComment;

    $comment->comment = "";

    if ($file = $request->file('video_comment')) {

        $path  = $file->store('chat_galleries/greeting_videos', 's3');

        $name  = $file->getClientOriginalName();

        $comment->video_comment = "https://breakdown-bucket.s3.amazonaws.com/".$path;

    }

    $comment->parent_id = null;

    $user = User::where('id', $request->user_id)->first();

    if(isset($request->parent_id)){
        $comment->parent_id = $request->parent_id;

        $parent_comment = ChatRoomComment::where('id', $request->parent_id)->first();
        
        //notify user for comment reply
        $user_parent = User::where('id', $parent_comment->user_id)->first();

        $data['message'] = 'User '.$user->username." replied to your comment" ;

        $user_parent->notify(new ReplyToCommentNotification($data));
    };

    $user = User::where('id', $request->user_id)->first();

    $comment->user()->associate($user);

    $chat_room_id = $request->chat_room_id;

    $chat_room = ChatRoom::find($chat_room_id);

    $chat_room->comments()->save($comment);

    $chat_room = ChatRoom::where('id', $request->chat_room_id)->first();

    return response()->json([
        "success"             => true,
        "message"             => "comment added successfully",
        "chat_room"         => new ChatRoomResource($chat_room),
    ]);
}
}
