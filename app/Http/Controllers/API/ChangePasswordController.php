<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use Validator;
use App\Models\ForgotPassword;

class ChangePasswordController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            'user_id'       => 'required|integer|exists:users,id',
            'new_password'  => 'required',
            'code'          => 'required|integer',
        ]);   

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }
        $user = User::whereId($request->user_id)->first();
        if(isset($user) && $userProfile = UserProfile::where('user_id', $request->user_id)->first()){
            $forgotPassword = ForgotPassword::where('user_profile_id', $userProfile->id)
                                             ->where('code', $request->code)
                                             ->where('status', 1)
                                             ->where('expired', 0)->first();
            
            if($forgotPassword){
                $user->password = bcrypt($request->new_password);
                $user->update();
                $forgotPassword->expired = 1;
                $forgotPassword->update();
           
                return response()->json([
                    "success" => true,
                    "message" => "Password has been reset successfully!",
                    "user_id" => $user->id,
                ]);
            }
        }

        return response()->json([
            "success" => false,
            "message" => "Could not change password!",
        ]);
        

    }
}
