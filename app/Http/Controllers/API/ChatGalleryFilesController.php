<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatGallery;
use App\Models\ChatGalleryFile;
use App\Models\User;
use Validator;

class ChatGalleryFilesController extends Controller
{
    //
    public function store(Request $request)
    {
        //This used to upload files realted to chat Gallery
        $validator = Validator::make($request->all(),[ 
            'user_id'            => 'required|integer|exists:users,id',
            'uuid'               => 'required|string',
            'cover_photo'        => '',
            'greeting_audio'     => 'mimes:mp3,acc,wav,mp4,aif,aiff,caf,m4a,ec3,3gp',
            'greeting_video'     => 'mimes:mp4,mov,ogg,qt'
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 
        $user_id = $request->user_id;
        $uuid    = $request->uuid;
        $user = User::whereId($user_id)->first();

        //Store request of Chat Gallery Files
        if($chat_gallery_file = ChatGalleryFile::where('uuid', $uuid)->first()){
    
        if ($file = $request->file('cover_photo')) {
            $path  = $file->store('chat_galleries/gallery_covers', 's3');
            $name  = $file->getClientOriginalName();
            $chat_gallery_file->cover_photo = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        
        }elseif($chat_gallery_file->cover_photo == NULL) {
            $colors = ["F8B966","DD1E25","721003"];
            $rand_key = array_rand($colors, 1);
            $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
            $chat_gallery_file->cover_photo = $random_cover;   

        }elseif(is_string($request->cover_photo)) {
                
            $chat_gallery_file->cover_photo = $request->cover_photo;
        }

        if ($file = $request->file('greeting_audio')) {
            $path  = $file->store('chat_galleries/greeting_audios', 's3');
            $name  = $file->getClientOriginalName();
            $chat_gallery_file->greeting_audio = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        }

        if ($file = $request->file('greeting_video')) {
            $path  = $file->store('chat_galleries/greeting_videos', 's3');
            $name  = $file->getClientOriginalName();
            $chat_gallery_file->greeting_video = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        }
        $chat_gallery_file->uuid = $uuid;
        $chat_gallery_file->save();

        $chat_gallery_file = ChatGalleryFile::where('uuid', $uuid)->first();
        
            return response()->json([
                    "success" => true,
                    "user"    => $user,
                    "message" => "Chat Gallery files updated succesfully!",
                    "chat_gallery_file" => $chat_gallery_file 
                ]);
        }
        else{
            $chat_gallery_file = new ChatGalleryFile;
            $chat_gallery_file->uuid = $uuid;
            $chat_gallery_file->user_id = $user_id;

            
            if ($file = $request->file('cover_photo')) {
                $path  = $file->store('chat_galleries/gallery_covers', 's3');
                $name  = $file->getClientOriginalName();
                $chat_gallery_file->cover_photo = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            
            }elseif($chat_gallery_file->cover_photo == NULL){
                $colors = ["F8B966","DD1E25","721003"];
                $rand_key = array_rand($colors, 1);
                $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
                $chat_gallery_file->cover_photo = $random_cover;   
                   
            }elseif(is_string($request->cover_photo)) {

                $chat_gallery_file->cover_photo = $request->cover_photo;
            }
    
            if ($file = $request->file('greeting_audio')) {
                $path  = $file->store('chat_galleries/greeting_audios', 's3');
                $name  = $file->getClientOriginalName();
            
                $chat_gallery_file->greeting_audio = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            }
    
            if ($file = $request->file('greeting_video')) {
                $path  = $file->store('chat_galleries/greeting_videos', 's3');
                $name  = $file->getClientOriginalName();
            
                $chat_gallery_file->greeting_video = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            }
        
            $chat_gallery_file->save();

            $chat_gallery_file = ChatGalleryFile::where('user_id', $user_id)->first();

            return response()->json([
                "success" => true,
                "user"    => $user,
                "message" => "Chat Gallery uploaded created succesfully!",
                "creator" => $chat_gallery_file 
            ]);
        }

        return $this->handleError("Error while uploading files", '');

    }
}
