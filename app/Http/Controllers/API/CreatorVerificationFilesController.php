<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\CreatorVerification;
use App\Models\User;


class CreatorVerificationFilesController extends Controller
{
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id',
            'video'            => 'required_without:photo_id|mimes:mp4,mov,ogg,qt',
            'photo_id'         => 'required_without:video|mimes:jpeg,png,jpg,gif'
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 
        $user_id = $request->user_id;
        $user = User::whereId($user_id)->first();


        //Store request of Creator Verifications
        if($creator_verification = CreatorVerification::where('user_id', $user_id)->first()){
    
        if ($file = $request->file('video')) {
            $path  = $file->store('verifications', 's3');
            $name  = $file->getClientOriginalName();
        
            $creator_verification->video = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        }

        if ($file = $request->file('photo_id')) {
            $path  = $file->store('verifications', 's3');
            $name  = $file->getClientOriginalName();
        
            $creator_verification->photo_id = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        }


        
        $creator_verification->update();
            return response()->json([
                    "success" => true,
                    "user"    => $user,
                    "message" => "Creator verification files uploaded succesfully!",
                    "creator" => $creator_verification 
                ]);
        }
        else{
            $creator_verification = new CreatorVerification;
            $creator_verification->user_id = $user_id;
            if ($file = $request->file('video')) {
                $path  = $file->store('verifications', 's3');
                $name  = $file->getClientOriginalName();
            
                $creator_verification->video = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            }
    
            if ($file = $request->file('photo_id')) {
                $path  = $file->store('verifications', 's3');
                $name  = $file->getClientOriginalName();
            
                $creator_verification->photo_id = "https://breakdown-bucket.s3.amazonaws.com/".$path;
            }

            $creator_verification->save();
            $creator_verification = CreatorVerification::where('user_id', $user_id)->first();

            return response()->json([
                "success" => true,
                "user"    => $user,
                "message" => "Creator verification created succesfully!",
                "creator" => $creator_verification 
            ]);
        }

        return $this->handleError("Error while uploading files", '');

    }
}
