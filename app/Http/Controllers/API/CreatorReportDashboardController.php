<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\CreatorReportDashboardResource;

class CreatorReportDashboardController extends Controller
{
    //
    public function index(Request $request, $id){
        if(!($user = User::find($id))) {          
            return response()->json(['error'=>"User not found"], 404);                        
        }

        return response()->json([
            "success"    => true,
            "data"       => new CreatorReportDashboardResource($user),
        ]);
    }
}
