<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use Validator;
use App\Models\UserProfile;
use App\Models\UserAvatar;
use Illuminate\Support\Facades\Storage;


class UserProfileController extends BaseController
{
    public function index(Request $request, $id)
    {
        
        if(!($user = UserProfile::where('user_id', $id)->first())){
            $returnData = array(
                'status'  => false,
                'message' => 'User Does not have any profile!'
            );
            return Response::json($returnData, 500);    
        }

        if($user_avatar = UserAvatar::where('user_id', $user->user_id)->first()){
            $success["user_avatar"] = $user_avatar;
        }else{
            $success["user_avatar"] = null;
        }

        $success["user"] = $user;

        return $this->handleResponse($success, 'User Profile Exists!');  

    }

    public function store(Request $request)
    {
        $dt = new \Carbon\Carbon();
        $before = $dt->subYears(13)->format('Y-m-d');
       
        if(trim($request->phone) == '""' || trim($request->phone) == "''"){
            $request->merge(['phone' => null]);
        }

        if(isset($request->user_id)){
            if($user = UserProfile::where('user_id', $request->user_id)->first()){
                $validator = Validator::make($request->all(), [
                    'birthdate'     => 'date|required|before:'.$before,
                    'name'          => 'string|required',
                    'email'         => 'required_without:phone|nullable|email',
                    'phone'         => 'required_without:email|nullable|integer|digits_between:5,13',
                ],
                [
                    'birthdate.before' => 'You must be at least 13 years old.',
                ]
                );

                if($test_user = UserProfile::where('user_id', '!=', $request->user_id)->where('email', $request->email)->where('email', '!=', NULL)->first()){
                    $returnData = array(
                        'success'  => false,
                        'message' => 'Email already taken!',
                    );
                    return Response::json($returnData, 500);
                }

                if($test_user = UserProfile::where('user_id', '!=', $request->user_id)->where('phone', $request->phone)->where('phone', '!=', NULL)->first()){
                    $returnData = array(
                        'success'  => false,
                        'message' => 'Phone already taken!',
                    );
                    return Response::json($returnData, 500);
                }
        
                if($validator->fails()){
                    return $this->handleError($validator->errors());       
                }

                $user->update($request->all());
                $returnData = array(
                    'success'  => true,
                    'message' => 'User profile updated successfully!',
                    'user'    => $user
                );
                return Response::json($returnData, 200);
            }
        }

        $validator = Validator::make($request->all(), [
            'user_id'       => 'integer|exists:users,id', 
            'birthdate'     => 'date|required|before:'.$before,    
            'name'          => 'string|required',
            'country_code'  => 'integer',
            'email'         => 'required_without:phone|nullable|email',
            'phone'         => 'required_without:email|nullable|integer|digits_between:5,13',
        ]);
   
        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }
        //Commented User profile
        $input = $request->all();
        $user = UserProfile::create($input);
        $success['user'] =  $user;
   
        return $this->handleResponse($success, 'User Profile registered!');
    }
}
