<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;
use App\Models\UserChannel;
use App\Models\User;
use App\Models\UserProfile;
use Validator;

class UserChannelSelectionController extends BaseController
{
    // 
    public function index(Request $request,$id)
    {
       

        if(!($user = User::where('id', $id)->first())){
                 
        }; 

        $user_channels = Channel::join('user_channels', 'channels.id', '=', 'user_channels.channel_id')
        ->where('user_id', $id)->get(['channels.name']);

        $success['user_choices'] = $user_channels;

        return $this->handleResponse($success,''); 

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channels'      => 'required|array',
            'channels.*'    => 'required|integer|exists:channels,id',
            'user_id'       => 'required|integer|exists:users,id', 
        ]);
   
        if($validator->fails()){
            return $this->handleError($validator->errors());       
        }

        $channels = $request->channels;
        $user_id = $request->user_id;


        if($user_channels = UserChannel::where('user_id', $user_id)->get()){
            foreach($user_channels as $user_channel){
                $user_channel->delete();
            }
        }
        
        foreach($channels as $channel)
        {
            $user_channel = new UserChannel();
            $user_channel->user_id = $user_id;
            $user_channel->channel_id = $channel;
            $user_channel->save();
        }

        $user = User::whereId($request->user_id)->first();
        $user_profile = UserProfile::where('user_id', $request->user_id)->first();
        $user_profile->user_profile_stage = 2; //Update user profile status
        $user_profile->update();
        $success['user'] = $user;
        $success['user_channel'] = $user_channel;
        $success['user_profile'] = $user_profile;

        return $this->handleResponse($success, 'User channel choice created!'); 

    }
}

