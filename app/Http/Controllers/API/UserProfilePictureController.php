<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAvatar;
use Response;


class UserProfilePictureController extends BaseController
{
    public function index(Request $request, $id)
    {
    
        if(!($user_avatar = UserAvatar::where('user_id', $id)->first())){
            $returnData = array(
                'status'  => 'error',
                'message' => 'User Profile Picture Does not exist!'
            );
            return Response::json($returnData, 500);    
        }
        $success["profile"] = $user_avatar;
    
        return $this->handleResponse($success, 'User Profile registered!');
   
    }
}
