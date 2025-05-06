<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class UserNameController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'    => 'required|string',
        ]);
   
        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }

        $message = "This username is available";
        $success = true;
        if($rows = User::where('username', $request->username)->count()){
            $message = "this username is taken";
            $success = false;
        };

        return response()->json([
            "success" => $success,
            "username" => $request->username,
            "message" => $message
        ]);
        
        
    }
}
