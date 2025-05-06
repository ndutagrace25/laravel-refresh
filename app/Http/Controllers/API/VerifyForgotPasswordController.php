<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForgotPassword;
use Validator;
use App\Models\UserProfile;
use App\Models\User;

class VerifyForgotPasswordController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            //Check if the user email exists in the table
            'email'    => 'required_without:phone|email|exists:user_profiles,email', 
            //Check if the user phone exits in the table of user profiles
            'phone'    => 'required_without:email|integer|exists:user_profiles,phone',
            'code'     => 'required|integer',
        ]);   

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        if(isset($request->email)){
            $email = $request->email;
            $userProfile = UserProfile::where('email', $email)->first();
            $forgotPassword = ForgotPassword::where('code', $request->code)->where('user_profile_id', $userProfile->id)->where('expired', 0)->first();

        }else{
            $phone = $request->phone;
            $userProfile = UserProfile::where('phone', $phone)->first();
            $forgotPassword = ForgotPassword::where('code', $request->code)->where('user_profile_id', $userProfile->id)->where('expired', 0)->first();
        }

        $user = User::whereId($userProfile->user_id)->first();
        $code = $request->code;

        if($forgotPassword)
        {
            $forgotPassword->status = 1;
            $forgotPassword->update();

            return response()->json([
                "success" => true,
                "message" => "You can reset your password now",
                "user_id" => $user->id,
                "param"   => $code
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => "Invalid Code",
        ]);
       
        
    }
}
