<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatGallery;
use App\Http\Resources\ChatGalleryResource;

class ChatGalleryListController extends Controller
{
    //
    public function index(Request $request, $id){

        if(!count(ChatGallery::where('id', $id)->get())){
            return response()->json([
                "success"         => false,
                "message"         => "No Chat gallery found",
            ]);
        };
        $chat_gallery = ChatGallery::where('id', $id)->first();
        $chat_gallery->views += 1;
        $chat_gallery->save();
        return response()->json([
            "success"           => true,
            "chat_galleries"    => new ChatGalleryResource($chat_gallery),
        ]);
    }

}
