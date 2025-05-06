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

class ChatRoomAudioCommentController extends Controller
{
    public function store(Request $request){
    
        $validator = Validator::make($request->all(),[ 
        'user_id'          => 'required|integer|exists:users,id',  
        'parent_id'        => 'integer|exists:chat_room_comments,id',   
        'chat_room_id'     => 'required|integer|exists:chat_rooms,id',
        'audio_comment'    => 'required|mimes:mp3,acc,wav,mp4,aif,aiff,caf,m4a,ec3,3gp'
        ]); 
    
    if($validator->fails()) {          
        return response()->json(['error'=>$validator->errors()], 401);                        
    } 

    $comment = new ChatRoomComment;

    $comment->comment = "";

    if ($file = $request->file('audio_comment')) {

        $path  = $file->store('chat_galleries/greeting_audios', 's3');

        $name  = $file->getClientOriginalName();

        $comment->audio_comment = "https://breakdown-bucket.s3.amazonaws.com/".$path;

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

    
    /**
     * Make notification if user gets mentioned in comment
     */
    $regex = '~(@\w+)~';
   
    if (preg_match_all($regex, $comment->comment, $matches, PREG_PATTERN_ORDER)) {
        foreach ($matches[1] as $found_username) {

            $found_username = substr($found_username, 1);
            
            if($found_user = User::where('username', $found_username)->first()){
                
                $data['message'] = 'You were mentioned in a comment by '.$user->username;

                $found_user->notify(new MentionedInCommentNotification($data));
            }
         }
    }

    $chat_room = ChatRoom::where('id', $request->chat_room_id)->first();

    return response()->json([
        "success"             => true,
        "message"             => "comment added successfully",
        "chat_room"         => new ChatRoomResource($chat_room),
    ]);
}
}