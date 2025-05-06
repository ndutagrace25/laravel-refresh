<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;

class UserCreatorController extends BaseController
{
    //
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|integer|exists:users,id', 
            'is_creator'    => 'required|boolean'
        ]);
   
        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }

        $user = User::whereId($request->user_id)->first();
        $user->is_creator = $request->is_creator;
        $user->update();

        $success['user'] = $user;

        return $this->handleResponse($success, '');
    }
}
