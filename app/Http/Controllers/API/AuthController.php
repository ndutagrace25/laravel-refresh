<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserAvatar;
use App\Models\Admin;
use Validator;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        //Check if the username sent is phone or email
        $username = $request->username;

        if(is_numeric($request->username)){
            //Find the user
            if( $user = UserProfile::where('phone', $request->username)->first()){
                $user_id = $user->user_id;
                $user = User::whereId($user_id)->first();
                if(isset($user)){
                    $username = $user->username;
                }
            }else{
                return $this->handleError('Username or Password is incorrect.', ['error'=>'Unauthorised']);
            }   
        }
        if(filter_var($request->username, FILTER_VALIDATE_EMAIL))
        {
            //Find the user
            if( $user = UserProfile::where('email', $request->username)->first()){
                $user_id = $user->user_id;
                $user = User::whereId($user_id)->first();
                if(isset($user)){
                    $username = $user->username;
                }
            }else{
                return $this->handleError('Username or Password is incorrect.', ['error'=>'Unauthorised']);
            }
        }

        if(Auth::attempt(['username' => $username, 'password' => $request->password])){ 
            $auth = Auth::user(); 
            
            $userProfile = UserProfile::where('user_id', $auth->id)->first();
            $userAvatar = UserAvatar::where('user_id', $auth->id)->first();
            $success['token'] =  $auth->createToken('LaravelSanctumAuth')->plainTextToken; 
            $success['user']  =  $auth;
            $success['user_profile'] = $userProfile;
            $success['user_avatar'] = $userAvatar;
            $success['is_admin'] = false;
            $success['is_root'] = false;
            
            if($admin = Admin::where('user_id', $auth->id)->first()){
                $success['is_admin'] = true;
                $success['admin'] = $admin;
                if($admin->is_root){
                    $success['is_root'] = true;
                }
            }

   
            return $this->handleResponse($success, 'User logged-in!');
        } 
        else{ 
            return $this->handleError('Username or Password is incorrect.', ['error'=>'Unauthorised']);
        } 
    }
    public function logout(Request $request)
    {
        auth('web')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string|required|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $user->save();
        $userProfileObject = new UserProfile();
        $userProfileObject->user_id = $user->id;
        $userProfileObject->user_profile_stage = 1;
        $userProfileObject->save();

        $userProfile = UserProfile::where('user_id', $user->id)->first();

        $userAvatarObject = new UserAvatar();
        $userAvatarObject->user_id = $user->id;
        $userAvatarObject->save();

        $userAvatar = UserAvatar::where('user_id', $user->id)->first();

        $success['token'] =  $user->createToken('LaravelSanctumAuth')->plainTextToken;
        $success['user'] = $user;
        $success['user_profile'] = $userProfile;
        $success['user_avatar'] = $userAvatar;
   
        return $this->handleResponse($success, 'User successfully registered!');
    }


}
