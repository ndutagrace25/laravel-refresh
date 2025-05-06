<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAvatar;
use Auth;
use App\Models\User;
use Validator;

class UserAvatarController extends Controller
{
    //
    public function index(Request $request)
    {
       
        $validator = Validator::make($request->all(),[ 
            'file'    => 'required|mimes:jpeg,png,jpg,gif',
            'user_id' => 'required|integer|exists:users,id',
        ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }  

        if ($file = $request->file('file')) {
            $path    = $file->store('images', 's3');
            $name    = $file->getClientOriginalName();
            $user_id = $request->user_id;
              $user = User::find($user_id);
              if(isset($user)){
                //If user avatar does not exist, create the user avatar
                if(!($user_avatar = UserAvatar::where('user_id', $user_id)->first())){
                  $user_avatar = new UserAvatar();
                  $user_avatar->user_id = $user_id;
                }

                //Save the new avatar
                $user_avatar->name = $file;
                $user_avatar->path = "https://breakdown-bucket.s3.amazonaws.com/".$path;
                $user_avatar->save();

                return response()->json([
                    "success" => true,
                    "user_id" => $user_id,
                    "message" => "File successfully uploaded",
                    "file" => "https://breakdown-bucket.s3.amazonaws.com/".$path
                ]);
              }
        }
        else{
              return response()->json([
                  "success" => false,
                  "message" => "Could not update"
              ]);
              }
    }
}
