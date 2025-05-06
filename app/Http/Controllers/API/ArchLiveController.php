<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadVideoFile;
use App\Models\User;
use App\Http\Resources\ArchLiveResource;

class ArchLiveController extends Controller
{
    //
    public function index(Request $request, $user_id)
    {
        if(!($user = User::find($user_id))){
            return response()->json(['error'=>"user not found"], 404);
        }

        $upload_video_files = UploadVideoFile::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();

        return ArchLiveResource::collection($upload_video_files);
    }
}
