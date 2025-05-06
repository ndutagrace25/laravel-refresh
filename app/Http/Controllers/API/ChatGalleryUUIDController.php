<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatGalleryUUIDController extends Controller
{
    //
    public function index(Request $request){
        $str = Str::uuid();
        
        return response()->json([
            "uuid"    => $str,
        ]);
    }
}
