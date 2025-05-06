<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Http\Resources\AdminListResource;

class AdminListController extends Controller
{
    //
    public function index(Request $request){
        $admins = Admin::all();

        return response()->json([
            "success" => true,
            "admins"  => AdminListResource::collection($admins),
        ]);
    }
}
