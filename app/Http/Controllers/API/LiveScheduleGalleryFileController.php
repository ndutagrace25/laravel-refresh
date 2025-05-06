<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\LiveScheduleGalleryFile;
use App\Models\User;

class LiveScheduleGalleryFileController extends Controller
{
    //
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[  
            'user_id'            => 'required|integer|exists:users,id',
            'uuid'               => 'required|string|unique:live_schedule_gallery_files,uuid',
            'cover_photo'        => 'mimes:jpeg,png,jpg,gif'
         ]);

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 
        $user_id = $request->user_id;
        $uuid    = $request->uuid;
        $user = User::whereId($user_id)->first();

        //Store request of Chat Gallery Files
        if($chat_gallery_file = LiveScheduleGalleryFile::where('uuid', $uuid)->first()){
    
        if ($file = $request->file('cover_photo')) {
            $path  = $file->store('chat_galleries/gallery_covers', 's3');
            $name  = $file->getClientOriginalName();
            $chat_gallery_file->cover_photo = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        
        }elseif($chat_gallery_file->cover_photo == NULL) {
            $colors = ["F8B966","DD1E25","721003"];
            $rand_key = array_rand($colors, 1);
            $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
            $chat_gallery_file->cover_photo = $random_cover;   
        }

        $chat_gallery_file->uuid = $uuid;
        $chat_gallery_file->save();

        $chat_gallery_file = LiveScheduleGalleryFile::where('uuid', $uuid)->first();
        
            return response()->json([
                    "success" => true,
                    "user"    => $user,
                    "message" => "Cover photo file updated succesfully!",
                    "chat_gallery_file" => $chat_gallery_file 
                ]);
        }
        else{
            $chat_gallery_file = new LiveScheduleGalleryFile;
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
                   
            }
    
            $chat_gallery_file->save();

            $chat_gallery_file = LiveScheduleGalleryFile::where('user_id', $user_id)->first();

            return response()->json([
                "success" => true,
                "user"    => $user,
                "message" => "Cover photo Gallery uploaded created succesfully!",
                "creator" => $chat_gallery_file 
            ]);
        }

        return $this->handleError("Error while uploading files", '');
    }
}
