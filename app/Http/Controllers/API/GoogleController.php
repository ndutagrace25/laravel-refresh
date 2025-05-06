<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProfile;
use App\Models\UserAvatar;


class GoogleController extends BaseController
{
    //
    public function store(Request $request)
    {
        //Correct issues related to google signin/signup payload
        
        //Retrieve data from googe sign in payload
        if($user = $request['user']){
            $email = $user['email'];
            $name = $user['name'];
            $id = $user['id'];
            $photo = $user['photo'];
            
            //Check if a user with this email is registered
            if($breakdown_user_profile = UserProfile::where('email', $email)->first()){
                    $auth = User::where('id', $breakdown_user_profile->user_id)->first(); 
                    $userProfile = UserProfile::where('user_id', $auth->id)->first();
                    $userAvatar = UserAvatar::where('user_id', $auth->id)->first();
                    $success['token'] =  $auth->createToken('LaravelSanctumAuth')->plainTextToken; 
                    $success['user']  =  $auth;
                    $success['user_profile'] = $userProfile;
                    $success['user_avatar'] = $userAvatar;
        
           
                    return $this->handleResponse($success, 'User Logged-in!');
            }else{
               
                $new_user = new User;
                $new_user->username = $name;
                $new_user->password = bcrypt($id);
                $new_user->save();

                $new_user_profile = new UserProfile;
                $new_user_profile->user_id = $new_user->id;
                $new_user_profile->email = $email;
                $new_user_profile->save();

                $new_user_avatar = new UserAvatar;
                $new_user_avatar->user_id = $new_user->id;
                $new_user_avatar->path = $photo;
                $new_user_avatar->save();

                $success['token'] =  $new_user->createToken('LaravelSanctumAuth')->plainTextToken;
                $success['user'] = $new_user;
                $success['user_profile'] = $new_user_profile;
                $success['user_avatar'] = $new_user_avatar;
           
                return $this->handleResponse($success, 'User registered!');
            }
            
            return $this->handleError("error sign in / sign up with google", '');

        }
        
        return $this->handleError("Error on the payload", '');
        
    }
}
