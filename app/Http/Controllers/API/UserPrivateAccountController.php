<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class UserPrivateAccountController extends Controller
{
    //
    public function store(Request $request){
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id,deleted_at,NULL',
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $user = User::where('id', $request->user_id)->first();
        $user->is_private = 1;
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "User account now private",
        ]);
    }
    public function public(Request $request){
        $validator = Validator::make($request->all(),[ 
            'user_id'  => 'required|integer|exists:users,id,deleted_at,NULL',
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $user = User::where('id', $request->user_id)->first();
        $user->is_private = 0;
        $user->save();

        return response()->json([
            "success" => true,
            "message" => "User account now public",
        ]);
    }
}
