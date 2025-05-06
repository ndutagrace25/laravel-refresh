<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatRoomComment;
use App\Models\Notification;
use App\Models\User;
use Validator;
use App\Notifications\ReplyToCommentNotification;
use App\Notifications\MentionedInCommentNotification;

class ChatRoomCommentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id',  
            'parent_id'        => 'integer|exists:chat_room_comments,id',   
            'chat_room_id'     => 'required|integer|exists:chat_rooms,id',
            'comment'          => 'required',
            'audio_comment'    => 'mimes:mp3,acc,wav,mp4,aif,aiff,caf,m4a,ec3'
            ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

        $comment = new ChatRoomComment;

        $comment->comment = $request->comment;

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
            $data['user'] = $user;
    
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
                    $data['user']= $user;
                    $found_user->notify(new MentionedInCommentNotification($data));
                }
             }
        }


        $comments =  ChatRoom::with('comments.replies')->find($chat_room_id);

        return response()->json([
            "success"             => true,

            "message"             => "comment added successfully",
            "post_with_comments"  => $comments
        ]);
    }

    public function replyStore(Request $request)
    {
        $reply = new ChatRoomComment();

        $reply->comment = $request->get('comment');

        $reply->user()->associate($request->user());

        $reply->parent_id = $request->get('comment_id');

        $post = Post::find($request->get('post_id'));

        $post->comments()->save($reply);

        return back();

    }
}
