<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserChannel;
use Validator;
use App\Models\User;
use App\Models\UploadVideo;
use App\Models\UploadVideoFile;
use App\Models\LiveCentral;

class VideoUploadController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(),[ 
            'user_id'           => 'required|integer|exists:users,id',
            'uuid'              => 'required|string',
            'channel_id'        => 'required|integer|exists:channels,id',
            'bucket_tag'        => 'integer|exists:tags,id',
            'caption'           => 'string',
            'location'          => 'string',
            'age_restriction'   => 'boolean',
            'minimum_age'       => 'integer|between:1,100',
            ]);   


        $channels = UserChannel::select('channel_id')->where('user_id', $request->user_id)->get()->toArray();
        $channel_selections = [];

        foreach($channels as $key=>$selection){
            array_push($channel_selections, $selection['channel_id']);
        }

        // if(!in_array($request->channel_id, $channel_selections)){
        //     return response()->json(['error'=>["You cannot create galleries under this channel"]], 401);                        
        // }

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }
        
        $caption = "";
        if($request->caption){
            $caption = $request->caption;
        }

        $channel_id       = $request->channel_id;
        $location         = $request->location;
        $age_restriction  = $request->age_restriction;
        $minimum_age      = $request->minimum_age;
        $uuid             = $request->uuid;
        $user_id          = $request->user_id;
        $tag              = $request->bucket_tag;


        $user = User::whereId($user_id)->first();

        //If video upload  exists update the data
        if($video_upload = UploadVideo::where('uuid', $uuid)->first()){
               
                $video_upload->uuid = $uuid;
                $video_upload->channel_id = $channel_id;
                $video_upload->tag_id = $tag;
                $video_upload->minimum_age = $minimum_age;
                $video_upload->age_restriction = $age_restriction;
                $video_upload->location = $location;
                $video_upload->caption = $caption;
                $video_upload->update();

                $username = $user->username;
                $live_id = substr(str_shuffle('0123456789ABCDEFGHIJIKLMNOPQRSTUVWYZabcdefghijklmnopqrstuvwxyz'), 0, 10);
                $room_id = substr(str_shuffle('0123456789'), 0, 4);
                $live_central  = new LiveCentral;
                $live_central->user_id = $user_id;
                $live_central->room_id = $room_id;
                $live_central->live_id = $live_id;
                $live_central->username = $username;
                $live_central->uuid = $uuid;
                $live_central->caption = $caption;
                $live_central->status = 1;
                $live_central->save();
    
                $video_upload_files = UploadVideoFile::where('uuid', $uuid)->first();
                return response()->json([
                    "success"             => true,
                    "message"             => "Video Upload updated successfully!",
                    "user"                => $user,
                    "video"               => $video_upload, 
                    "video_upload_files"  => $video_upload_files,
                ]);
            }else{
            //Else create new chat gallery
            $video_upload = new UploadVideo;
          
            $video_upload->uuid = $uuid;
            $video_upload->tag_id = $tag;
            $video_upload->channel_id = $channel_id;
            $video_upload->minimum_age = $minimum_age;
            $video_upload->age_restriction = $age_restriction;
            $video_upload->location = $location;
            $video_upload->caption = $caption;
            $video_upload->save();
            $video_upload = UploadVideo::whereId($video_upload->id)->first();
    
            $video_upload_file = new UploadVideoFile;
            $video_upload_file->uuid = $uuid;

            $username = $user->username;
            $live_id = substr(str_shuffle('0123456789ABCDEFGHIJIKLMNOPQRSTUVWYZabcdefghijklmnopqrstuvwxyz'), 0, 10);
            $room_id = substr(str_shuffle('0123456789'), 0, 4);
            $live_central  = new LiveCentral;
            $live_central->user_id = $user_id;
            $live_central->room_id = $room_id;
            $live_central->live_id = $live_id;
            $live_central->uuid = $uuid;
            $live_central->username = $username;
            $live_central->caption = $caption;
            $live_central->status = 1;
            $live_central->save();
            
            $colors = ["F8B966","DD1E25","721003"];
            $rand_key = array_rand($colors, 1);
            $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
            $video_upload_file->cover_photo = $random_cover;  
            $video_upload_file->user_id = $user_id; 
            $video_upload_file->save();
    
            $video_upload_file = UploadVideoFile::where('uuid', $uuid)->first();
    
            return response()->json([
                "success"           => true,
                "user"              => $user,
                "message"           => "Video uploaded succesfully!",
                "video_upload"      => $video_upload,
                "video_upload_file" => $video_upload_file,
            ]);
        }

    }
}
