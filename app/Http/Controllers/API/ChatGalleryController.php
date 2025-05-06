<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatGallery;
use App\Models\ChatGalleryFile;
use App\Models\ChatRoom;
use App\Models\ChatRoomComment;
use App\Models\UserFollower;
use GuzzleHttp\Client;
use App\Http\Resources\ChatGalleryResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Validator;
use Response;
use Carbon\Carbon;
use App\Models\Channel;
use App\Models\UserChannel;
use App\Models\ChatGalleryTag;
use Illuminate\Support\Facades\Auth;


class ChatGalleryController extends Controller
{
    public function list(Request $request, $id){

        $scheduled = 0;
        $channels = '';
        $now = Carbon::now();

        if($request->type && $request->type=="scheduled"){
            $scheduled = 1;
        }
        
        if($request->channel && in_array($request->channel, ["books", "movies", "tv"])){

            $channels = Channel::select('id')->where('name', $request->channel)->get()->toArray();
        
        }else{
            //Filter with the user channel selection
            $channels = UserChannel::select('channel_id')->where('user_id', $id)->get()->toArray();
            if(!count($channels)){
                $channels = Channel::select('id')->get()->toArray();
            }
        }
     
        $channel_galleries = ChatGallery::whereIn('channel_id', $channels)->orderBy('created_at', 'desc')->get()->toArray();
        

        if(!count($channel_galleries)){
            return response()->json([
                "success"         => false,
                "message"         => "No Chat gallery found",
            ]);
        };
        
        //List only selected chat galleries based on user channel selections
        if(!$scheduled){

            $chat_gallery = ChatGallery::whereIn('channel_id', $channels)->where(function ($query) {
                $now = Carbon::now();
                $query->where('live_schedule','=', null)
                ->orwhere('live_schedule','<', $now);   
            })->orderBy('created_at', 'desc')->get();

        }else{
          
            $chat_gallery = ChatGallery::whereIn('channel_id', $channels)->where(function ($query) {
                $now = Carbon::now();
                $query->where('live_schedule', '>', $now);
            })->orderBy('created_at', 'desc')->get();

        }

        $filtered_galleries = [];
        foreach($chat_gallery as $gallery){
            $user = User::where('id',$gallery->user_id)->first();
           
            if($gallery->private){
                if($tag = ChatGalleryTag::where('tag_id', $id)->where('chat_gallery_id', $gallery->id)->first()){
                    array_push($filtered_galleries, $gallery);
                }else{

                }
                
            }else{
                array_push($filtered_galleries, $gallery);
            }
        }

        
        return response()->json([
            "success"           => true,
            "chat_galleries"    => ChatGalleryResource::collection($filtered_galleries)
        ,
        ]);

    }
    //search chat gallery
    public function index(Request $request){
       
        $title = $request->topic;

        $client = new Client();
        $apiKey = 'caa6891d084141a386ce0d2d1eb8f620';
        $apiUrl = "https://api.themoviedb.org/3/"; 

        $response = $client->get($apiUrl . 'search/movie', [
            'query' => [
                'api_key' => $apiKey,
                'query'   => $title,
            ],
        ]);

        // Get the JSON response from the API
        $data = json_decode($response->getBody(), true);
        
        $foundtitles = [];

        // Loop through the results to find an exact match
        foreach ($data['results'] as $result) {
            // Add the movie poster and title to the array
            $chatgallery = ChatGallery::where('name', $title)->orderBy('created_at', 'desc')->first();

            $movies[] = [
                'poster' => 'https://image.tmdb.org/t/p/w500' . $result['poster_path'],
                'title'  => $result['title']
            ];
            
       }
       $movies = array_slice($movies, 0, 4);
      


        if(count(ChatGallery::where('topic', 'LIKE', '%'.$title.'%')->get())>0){
            $chat_galleries = ChatGallery::where('topic', 'LIKE', '%'.$title.'%')->orderBy('created_at', 'desc')->get();
            return response()->json([
                "success"           => true,
                "chat_galleries"    => $chat_galleries,
            ]);
        }
        return response()->json([
            "success"         => false,
            "message"         => "No results found. Search another topic or start a new discussion below",
        ]);
      
    }
    public function store(Request $request){

        $now = Carbon::now();
        $day = Carbon::now();
        $date = Carbon::now();
        $sevendays = $now->addDays(7);
        $aday = $day->addHours(24);

        $validator = Validator::make($request->all(),[ 
            'user_id'           => 'required|integer|exists:users,id',
            'uuid'              => 'required|string',
            'channel_id'        => 'required|integer|exists:channels,id',
            'chat_room_names'   => 'array',
            'chat_room_names.*' => 'string',
            'bucket_tag'        => 'integer|exists:tags,id',
            'topic'             => 'required|string',
            'name'              => 'required|string',
            'official'          => 'required|boolean',
            'private'           => 'required|boolean',
            'tagged_users'      => 'required_if:private,==,1|array',
            'tagged_users.*'    => 'exists:users,id',
            'live_schedule'     => 'date|before:'.$sevendays
            ],
            [
                'live_schedule.before' => 'Can not schedule more than 7 days in advance',
               //'live_schedule.after' => 'Must schedule at least 24 hours in advance'
            ],
        ); 
        
        $channels = UserChannel::select('channel_id')->where('user_id', $request->user_id)->get()->toArray();
        $channel_selections = [];

        foreach($channels as $key=>$selection){
            array_push($channel_selections, $selection['channel_id']);
        }

        if(!in_array($request->channel_id, $channel_selections)){
            return response()->json(['error'=>["You cannot create galleries under this channel"]], 401);                        
        }

        if($validator->fails()) {     
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 


        $channel_id = $request->channel_id;
        $uuid       = $request->uuid;
        $user_id    = $request->user_id;
        $topic      = $request->topic;
        $name       = $request->name;
        $official   = $request->official;
        $private    = $request->private;
        $tag        = $request->bucket_tag;
        $live_schedule  = $request->live_schedule ? $request->live_schedule : NULL;


        $user = User::whereId($user_id)->first();

        $created_chat_rooms = [];
        
        //If the chat gallery exists update the data
        if($chat_gallery = ChatGallery::where('uuid', $uuid)->first()){
            $chat_gallery->user_id = $user_id;
            $chat_gallery->uuid = $uuid;
            $chat_gallery->channel_id = $channel_id;
            $chat_gallery->topic = $topic;
            $chat_gallery->name = $name;
            $chat_gallery->tag_id = $tag;
            $chat_gallery->official = $official;
            $chat_gallery->private = $private;
            $chat_gallery->live_schedule = $live_schedule;
            $chat_gallery->update();

            $chat_gallery_files = ChatGalleryFile::where('uuid', $uuid)->first();

            if(isset($request->chat_room_names)){
                foreach($request->chat_room_names as $key=>$room){
                    if(!($chatroom = ChatRoom::where('chat_gallery_id', $chat_gallery->id)->where('name', $room)->first())){
                        $chat_room = new ChatRoom;
                        $chat_room->chat_gallery_id = $chat_gallery->id;
                        $chat_room->user_id = $user_id;
                        $chat_room->name = $room;
                        $chat_room->save();

                        array_push($created_chat_rooms, $chat_room);
                    }else{
                        $created_chat_rooms = ChatRoom::where('chat_gallery_id', $chat_gallery->id)->get();
                        $created_chat_rooms = $created_chat_rooms->toArray();
                    }
                }
            }

            if(isset($request->tagged_users)){
                foreach($request->tagged_users as $user){
                    if(!($tag = ChatGalleryTag::where('tag_id', $user)->first())){
                        $tag = new ChatGalleryTag;
                        $tag->tag_id = $user;
                        $tag->chat_gallery_id = $chat_gallery->id;
                        $tag->save();
                    }
                }
            }
            
    
            return response()->json([
                "success"            => true,
                "message"            => "Chat Gallery updated successfully!",
                "user"               => $user,
                "chat_gallery"       => $chat_gallery, 
                "chat_gallery_file"  => $chat_gallery_files,
                "chat_room"          => $created_chat_rooms
            ]);
        }
        //Else create new chat gallery
        $chat_gallery = new ChatGallery;
        $chat_gallery->user_id = $user_id;
        $chat_gallery->uuid = $uuid;
        $chat_gallery->topic = $topic;
        $chat_gallery->name = $name;
        $chat_gallery->tag_id = $tag;
        $chat_gallery->official = $official;
        $chat_gallery->channel_id = $channel_id;
        $chat_gallery->private = $private;
        $chat_gallery->live_schedule = $live_schedule;
        $chat_gallery->save();
        $gallery = ChatGallery::whereId($chat_gallery->id)->first();

        $chat_gallery_file = new ChatGalleryFile;
        $chat_gallery_file->uuid = $uuid;
        $chat_gallery_file->user_id = $user_id;
        $colors = ["F8B966","DD1E25","721003"];
        $rand_key = array_rand($colors, 1);
        $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
        $chat_gallery_file->cover_photo = $random_cover;   
        $chat_gallery_file->save();

        $chat_gallery_files = ChatGalleryFile::where('uuid', $uuid)->first();

        if(isset($request->chat_room_names)){
            foreach($request->chat_room_names as $key=>$room){
                if(!($chatroom = ChatRoom::where('chat_gallery_id', $chat_gallery->id)->where('name', $room)->first())){
                    $chat_room = new ChatRoom;
                    $chat_room->chat_gallery_id = $chat_gallery->id;
                    $chat_room->user_id = $user_id;
                    $chat_room->name = $room;
                    $chat_room->save();

                    array_push($created_chat_rooms, $chat_room);
                }else{
                    $created_chat_rooms = ChatRoom::where('chat_gallery_id', $chat_gallery->id)->get();
                }
            }
        }

        return response()->json([
            "success"           => true,
            "user"              => $user,
            "message"           => "Chat Gallery created succesfully!",
            "chat_gallery"      => $gallery,
            "chat_gallery_file" => $chat_gallery_file,
            "chat_room"         => $created_chat_rooms
        ]);
              
        }
        
        public function delete(Request $request){
            $validator = Validator::make($request->all(),[ 
                'chat_gallery_id'       => 'required|integer|exists:chat_galleries,id,deleted_at,NULL',
             ]); 
            
            if($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 404);              
            } 
    
           $chat_rooms = ChatRoom::where('chat_gallery_id', $request->chat_gallery_id)->get();

           foreach($chat_rooms as $chat_room){
                ChatRoomComment::where('commentable_id', $chat_room->id)->where('deleted_at', NULL)->delete();
           }

           ChatRoom::where('chat_gallery_id', $request->chat_gallery_id)->delete();

           $chat_gallery = ChatGallery::where('id', $request->chat_gallery_id)->first();
           
           $chat_gallery->delete();
           
           return response()->json([
                "success"           => true,
                "message"           => "Chat Gallery deleted successfully",
            ]);
            
    
            
    
        }
    
        public function edit(Request $request){
    
            $validator = Validator::make($request->all(),[ 
                'chat_gallery_id'    => 'required|integer|exists:chat_galleries,id',
                'topic'              => 'string',
                'name'               => 'required|string',
                'user_id'            => 'required|integer|exists:users,id'
             ]); 
            
            if($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 401);     
                                   
            } 
    
            /////////////////////////////////////////////////
            $id = $request->chat_gallery_id;
            $chat_gallery = ChatGallery::where('id', $id)->first();
            if($request->topic){
                $chat_gallery->topic = $request->topic;
            }
            $chat_gallery->name = $request->name;
            $chat_gallery->save();
    
            return response()->json([
                "success"           => true,
                "chat_gallery"      => new ChatGalleryResource($chat_gallery),
            ]);
        }


        public function tagged(Request $request, $id)
        {
            if(!($user = ChatGallery::where('id', $id)->first())){
                $returnData = array(
                    'status'  => false,
                    'message' => 'Chat gallery does not exist'
                );
                return Response::json($returnData, 500);    
            }

            $users = ChatGalleryTag::where('chat_gallery_id', $id)->get();
            $list_users = [];

            foreach ($users as $key => $user) {
                $user = User::where('id', $user->tag_id)->first();
                array_push($list_users, $user);
            }

            return response()->json([
                "success"        => true,
                "users"          => UserResource::collection($list_users),
            ]);


        }

    }
