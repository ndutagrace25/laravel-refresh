<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;

class ReactionEmojiController extends Controller
{
    //
    public function index(Request $request)
    {
        /*
        reaction emojis should be one of these:
        U+1F4A1 - Light bulb
        U+1F44D - Thums Up
        U+2764	- Red Hear
    */
    $returnData = array(
        array(
            'name'=>'Light Bulb',
            'code'=>'U+1F4A1',
            'emoji'=>"💡",
        ),

        array(
            'name'=>'Thumbs Up',
            'code'=>'U+1F44D',
            'emoji'=>"👍",
        ),

        array(
            'name'=>'Red Heart',
            'code'=>'U+2764',
            'emoji'=>"❤️"
        ),
    );

    return Response::json($returnData, 200); 
    }
}
