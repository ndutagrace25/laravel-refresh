<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\CreatorVerification;
use App\Models\UserChannel;

class UserProfileStageController extends Controller
{
    public function index(Request $request, $id)
    {
        if (!($user = User::find($id))) {
            return response()->json(['error' => "User not found"], 404);
        }
        
        $user = User::where('id', $id)->first();
        $profile_stage = 1;
        
        # Level 2  - 
        if($profile = UserProfile::where('user_id', $id)->first()){
            $profile_stage = 2;
        }
        
        # Level 3
        if($channel = UserChannel::where('user_id', $id)->first()){
            $profile_stage = 3;
        }

        # Level 4
        if($verification = CreatorVerification::where('user_id', $id)->first()){
            $profile_stage = 4;
        }

        return response()->json([
            "success"        => true,
            "profile_stage"  => $profile_stage
        ]);

    }
}
