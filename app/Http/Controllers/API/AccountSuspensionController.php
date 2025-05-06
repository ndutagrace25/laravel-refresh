<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\SuspendedAccountResource;

class AccountSuspensionController extends Controller
{
    //
    public function index(Request $request){

        /*
        List of Suspended Accounts
        */
        $accounts = User::withTrashed()->get();

        return response()->json([
            "success"         => false,
            "total_counts"    => count($accounts),
            "app_users"       => SuspendedAccountResource::collection($accounts) 
        ]); 


        
    }

}
