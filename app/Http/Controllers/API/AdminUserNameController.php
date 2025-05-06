<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Admin;
use Validator;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserAdminResource;
use App\Models\UserAvatar;

class AdminUserNameController extends Controller
{
    //
    public function index(Request $request)
    {
        if(isset($request->username)){
            $key = $request->username;

            if(count(User::where('username', 'LIKE', '%'.$key.'%')->get())>0){
                $users = User::where('username', 'LIKE', '%'.$key.'%')->get();
                return response()->json([
                    "success"  => true,
                    "users"    => UserResource::collection($users),
                ]);
            }
            return response()->json([
                "success"         => false,
                "message"         => "No users with this username found",
            ]);
       }
        return response()->json([
            "success"         => false,
            "message"         => "enter username to search",
        ]);
    }

    public function view(Request $request, $id)
    {

        if(!($user = User::withTrashed()->find($id))) {          
            return response()->json(['error'=>"User not found"], 404);                        
        }

        $user = User::withTrashed()->find($id);

        return response()->json([
            "success"      => true,
            "user"         => new UserAdminResource($user) 
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            'user_id'       => 'required|integer|exists:users,id',
        ]);   

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        $user_id = $request->user_id;

        $admin = new Admin();
        $admin->user_id = $user_id;
        $admin->is_root = 0;
        $admin->save();

        $user = User::where('id', $user_id)->first();
        $user_avatar = UserAvatar::where('user_id', $user_id)->first();

        return response()->json([
            "success"      => true,
            "message"      => "Admin set successfully",
            "user"         => $user,
            "user_avatar"  => $user_avatar->path,
            "admin"        => $admin 
        ]);

    }
}
