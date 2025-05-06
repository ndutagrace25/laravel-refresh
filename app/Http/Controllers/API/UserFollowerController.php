<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFollower;
use App\Models\Notification;
use App\Notifications\FollowUserAccountNotification;
use App\Http\Resources\UserResource;
use Validator;

class UserFollowerController extends Controller
{
    //user_id => follower_id
    //follower_id => followee_id
    public function store(Request $request){
        $validator = Validator::make($request->all(),[ 
            'follower_id'        => 'required|integer|exists:users,id',  
            'followee_id'        => 'required|integer|exists:users,id',   
            ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

        if(!($following_action = UserFollower::where('user_id', $request->followee_id)->where('follower_id', $request->follower_id)->first())){
            $user_follower = new UserFollower();
            $user_follower->user_id = $request->followee_id;
            $user_follower->follower_id = $request->follower_id;
            $user_follower->save();

            $follower = User::where('id', $request->follower_id)->first();
            $user_followed = User::where('id', $request->followee_id)->first();

            $data['message'] = 'User '.$follower->username." has started following you" ;
            $data['user']  = $follower;
    
            $user_followed->notify(new FollowUserAccountNotification($data));

            return response()->json([
                "success"    => true
            ]);

        }else{
            $following_action = UserFollower::where('user_id', $request->followee_id)->where('follower_id', $request->follower_id)->first();
            $following_action->delete();

            return response()->json([
                "success"    => true,
                "message"    => "User unfollowed"
            ]);
        }

        

        
    }

    public function index(Request $request, $follower_id, $followee_id){
        $follow = UserFollower::where('user_id', $followee_id)->where('follower_id', $follower_id)->first();
        if($follow){
            return response()->json([
                true
            ]);
        }
        return response()->json([
            false
        ]);
    }

    public function delete(Request $request){
        
    }

    public function list(Request $request, $id){

        if(!($user = User::where('id', $id)->first())){
            $returnData = array(
                'status'  => false,
                'message' => 'User does exist!'
            );
            return Response::json($returnData, 500);    
        }

        $user_followers = UserFollower::where('user_id', $id)->get()->take(10);
        $users = [];
        foreach($user_followers as $follower)
        {
            $user = User::where('id', $follower->follower_id)->first();
            array_push($users, $user);
        }


        return response()->json([
            "success"  => true,
            "users"    => UserResource::collection($users),
        ]);
    }
}
