<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CreatorVerification;
use App\Models\User;
use App\Http\Resources\CreatorVerificationResource;
use App\Models\UserProfile;
use Validator;

class CreatorOfficialController extends Controller
{
    //
    public function index(Request $request){
        $verification_requests = [];

        $v_requests = CreatorVerification::all();
        foreach ($v_requests as $key => $v) {
            $user = User::where('id', $v->user_id)->first();
            if($user){
                if($user->is_official == 0){
                    array_push($verification_requests, $v);
                }
            }      
        }
        
        return response()->json([
            "success"  => true,
            "requests" => CreatorVerificationResource::collection($verification_requests),
        ]);
        
    }

    public function verify(Request $request){
        $validator = Validator::make($request->all(),[ 
            'verification_id'  => 'required|integer|exists:creator_verifications,id',
            ]);   

        if($validator->fails()) {            
            return response()->json(['error'=>$validator->errors()], 404);                        
        } 
        
        $verification = CreatorVerification::find($request->verification_id);
        
        $user = User::where('id', $verification->user_id)->first();
        if($user){
            $user->is_official = 1;
            $user->save();
        }

        return response()->json([
            "success" => true,
            "message" => "Creator is official Request approved!",
        ]);
    }

    public function unverify(Request $request){
        $validator = Validator::make($request->all(),[ 
            'verification_id'  => 'required|integer|exists:creator_verifications,id',
            ]);   

        if($validator->fails()) {            
            return response()->json(['error'=>$validator->errors()], 404);                        
        } 
        
        $verification = CreatorVerification::find($request->verification_id);

        $user = User::where('id', $verification->user_id)->first();
        if($user){
            $user->is_official = 0;
            $user->save();
        }
     

        return response()->json([
            "success" => true,
            "message" => "Creator Official Request Denied",
        ]);
    }
}
