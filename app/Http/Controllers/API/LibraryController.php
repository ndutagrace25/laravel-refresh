<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\CreatorLibrary;
use App\Models\User;
use Response;

class LibraryController extends BaseController
{
    //
    public function index(Request $request, $id)
    {
        if(!($user = User::find($id))){
            return response()->json(['error'=>"user not found"], 404);                        
        }

        $library = CreatorLibrary::where('user_id', $id)->first();
        $bio = "";
        $url = "";
        if($library){
            $bio = $library->bio;
            $url = $library->url;
        }
        $returnData = array(
            'status'      => true,
            'username'    => $user->username,
            'bio'         => $bio,
            'url'         => $url
        );

        return Response::json($returnData, 200);  
    }

    public function patch(Request $request){
        $validator = Validator::make($request->all(),[ 
            'user_id'     => 'required|integer|exists:users,id'
            ]);   
        
        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $existing_user = User::where('username', $request->username)->first();
       
        $user = User::where('id', $request->user_id)->first();

        if($existing_user && $user->username != $request->username){
            return response()->json(['error'=>'can not user this username'], 401);                        
        }


        if($library = CreatorLibrary::where('user_id', $request->user_id)->first()){
            if(isset($request->username)){
                $user = User::where('id', $request->user_id)->first();
                $user->username = $request->username;
                $user->save();
            }
            $library->update($request->all());
        }else{
         $library = CreatorLibrary::create($request->all());
        }
        $returnData = array(
            'status'      => true,
            "message"     => "succefully edited library",
            'library'     => $library,
        );

        return Response::json($returnData, 200);  

    }
}
