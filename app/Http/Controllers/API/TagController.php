<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tag;

class TagController extends Controller
{
    //
    public function index(Request $request){

        if(!count(Tag::all())){
            return response()->json([
                "success"         => false,
                "message"         => "No Tags found",
            ]);
        };
        $tags = Tag::all();
        return response()->json([
            "success"           => true,
            "Bucket Tags"    => $tags,
        ]);
    }
}
