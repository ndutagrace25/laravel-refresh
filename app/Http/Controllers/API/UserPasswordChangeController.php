<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserPasswordChangeController extends Controller
{
    //
    public function store(Request $request){
       
        $validator = Validator::make($request->all(),[ 
            'user_id'           => 'required|integer|exists:users,id',
            'old_password'      => 'required',
            'new_password'      => 'required|',
            'confirm_password'  => 'required|same:new_password'
         ]); 
     
         if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $user = User::whereId($request->user_id)->first();

        #Match The Old Password
        if(!Hash::check($request->old_password, $user->password)){
        
            return response()->json([
                'success' => "false",
                'error'  => "Old Password Doesn't match"], 
                401);                        

        }
        
        #Update the new Password
        User::whereId($user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);
        return response()->json([
            "success" => "true",
            "message" => "Password changed successfully",
            "user"    => $user
        ]);
   
    }
}
