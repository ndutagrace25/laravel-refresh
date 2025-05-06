<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\UploadVideoFile;
use App\Models\User;
use App\Models\LiveCentral;


class VideoUploadFileController extends Controller
{
    public function store(Request $request)
    {
        //This used to upload files realted to chat Gallery
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id',
            'uuid'             => 'required|string',
            'cover_photo'      => 'mimes:jpeg,png,jpg,gif',
            'video'            => ''
            ]);   

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 
        $user_id = $request->user_id;
        $uuid    = $request->uuid;
        $user = User::whereId($user_id)->first();

        //Store request of Chat Gallery Files
        if($video_upload_file = UploadVideoFile::where('uuid', $uuid)->first()){

            if ($file = $request->file('cover_photo')) {
                $path  = $file->store('chat_galleries/gallery_covers', 's3');
                $name  = $file->getClientOriginalName();
                $video_upload_file->cover_photo = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            
            }

            if ($file = $request->file('video')) {
                $path  = $file->store('chat_galleries/greeting_audios', 's3');
                $name  = $file->getClientOriginalName();
                $video_upload_file->video = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            }

       
        $video_upload_file->uuid = $uuid;
        $video_upload_file->save();

        $username = $user->username;
        $live_id = substr(str_shuffle('0123456789ABCDEFGHIJIKLMNOPQRSTUVWYZabcdefghijklmnopqrstuvwxyz'), 0, 10);
        $room_id = substr(str_shuffle('0123456789'), 0, 4);
        $live_central  = new LiveCentral;
        $live_central->user_id = $user_id;
        $live_central->room_id = $room_id;
        $live_central->live_id = $live_id;
        $live_central->uuid = $uuid;
        $live_central->username = $username;
        $live_central->status = 1;
        $live_central->save();

        $video_upload_file = UploadVideoFile::where('uuid', $uuid)->first();
        
            return response()->json([
                    "success" => true,
                    "user"    => $user,
                    "message" => "Live Video and cover photo updated succesfully!",
                    "video_upload" => $video_upload_file 
                ]);
        }
        else{
            $video_upload_file = new UploadVideoFile;
            $video_upload_file->uuid = $uuid;
            $video_upload_file->user_id = $user_id;

            
            if ($file = $request->file('cover_photo')) {
                $path  = $file->store('chat_galleries/gallery_covers', 's3');
                $name  = $file->getClientOriginalName();
                $video_upload_file->cover_photo = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            
            }
           
            if ($file = $request->file('video')) {
                $path  = $file->store('chat_galleries/greeting_videos', 's3');
                $name  = $file->getClientOriginalName();
                $video_upload_file->video = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            }

            $username = $user->username;
            $live_id = substr(str_shuffle('0123456789ABCDEFGHIJIKLMNOPQRSTUVWYZabcdefghijklmnopqrstuvwxyz'), 0, 10);
            $room_id = substr(str_shuffle('0123456789'), 0, 4);
            $live_central  = new LiveCentral;   
            $live_central->user_id = $user_id;
            $live_central->room_id = $room_id;
            $live_central->live_id = $live_id;
            $live_central->uuid = $uuid;
            $live_central->username = $username;
            $live_central->status = 1;
            $live_central->save();
        
            $video_upload_file->save();

            $video_upload_file = UploadVideoFile::where('user_id', $user_id)->first();

            return response()->json([
                "success" => true,
                "user"    => $user,
                "message" => "Live video uploaded created succesfully!",
                "creator" => $video_upload_file 
            ]);
        }

        return $this->handleError("Error while uploading files", '');

    }

    
    public function store_file(Request $request)
    {
        # Save profile 
        
    }
}
