<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\LiveScheduleGallery;
use App\Models\User;
use App\Models\Channel;
use App\Models\LiveCentral;
use App\Http\Resources\LiveCentralResource;
use Validator;

class ZegoController extends Controller
{
    public function start_live_stream(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'live_id'      => 'required|exists:live_centrals,live_id'
        ]);

        if($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $live = LiveCentral::where('live_id',$request->live_id)->first();
        $live->status = 1;
        $live->save();

        return response()->json([
            "success"           => true,
            "message"           => "Live Started"
        ]);

    }

    public function getStream(Request $request, $id){

        if(!($gallery = User::find($id))) {
            return response()->json(['error'=>"User not found"], 404);
        }

        #$gallery = LiveScheduleGallery::where('id',$id)->first();
        $user = User::where('id', $id)->first();
        $username = $user->username;
        $user_id = $id;
        $live_id = substr(str_shuffle('0123456789ABCDEFGHIJIKLMNOPQRSTUVWYZabcdefghijklmnopqrstuvwxyz'), 0, 10);
        $room_id = substr(str_shuffle('0123456789'), 0, 4);
        $live_central  = new LiveCentral;
        $live_central->user_id = $user_id;
        $live_central->room_id = $room_id;
        $live_central->live_id = $live_id;
        $live_central->username = $username;
        $live_central->status = 0;
        $live_central->save();


        return response()->json([
            "success"           => true,
            "AppID"             => "307008811",
            "AppSign"           => "e76d8bf4c23a80df1208d5494e7c222c213daca2f75b8e71df7ecf70c5f909db",
            "userId"            => $user->id,
            "username"          => $user->username,
            "live_id"           => $live_id,
            "room_id"           => $room_id
        ]);
    }

    public function stop_live_stream(Request $request){

        $validator = Validator::make($request->all(),[
            'live_id'      => 'required|exists:live_centrals,live_id'
        ]);

        if($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $live = LiveCentral::where('live_id',$request->live_id)->first();
        $live->status = 0;
        $live->save();

        return response()->json([
            "success"           => true,
            "message"           => "Successfully stopped live stream"
        ]);
    }

    public function live_central(Request $request){
        $live_galleries = LiveCentral::where('status',1)->get();

        if(isset($request->channel_id)){
            $channel = $request->channel_id;
            if(!($channel_found = Channel::find($channel))) {
                return response()->json(['error'=>"Channel Not found"], 404);
            }

            if(count(LiveCentral::where('channel_id', $channel)->where('status',1)->get())>0){

                $all_live = LiveCentral::where('channel_id', $channel)->where('status',1) ->orderBy('created_at', 'desc')->get();
                $lives = [];
                foreach($all_live as $live){
                        array_push($lives, $live);

                }

                $uniqueItems = [];
                foreach ($lives as $item) {
                    // Construct a unique key based on live_id and username
                    $key = $item['live_id'] . '|' . $item['username'];

                    // Check if the item is already in the uniqueItems array
                    if (!isset($uniqueItems[$key])) {
                        $uniqueItems[$key] = $item;
                    }
                }

                // Re-index the array to remove the keys
                $filteredItems = array_values($uniqueItems);


                return response()->json([
                    "success"           => true,
                    "live_centrals"     => LiveCentralResource::collection($filteredItems)
                ]);
            }
            return response()->json([
                "success"        => false,
                "live_centrals"  => [],
                "message"        => "No live in this channel",
            ]);
        }

        $uniqueItems = [];
        foreach ($live_galleries as $item) {
            // Construct a unique key based on live_id and username
            $key = $item['live_id'] . '|' . $item['username'];

            // Check if the item is already in the uniqueItems array
            if (!isset($uniqueItems[$key])) {
                $uniqueItems[$key] = $item;
            }
        }

        // Re-index the array to remove the keys
        $filteredItems = array_values($uniqueItems);


        return response()->json([
            "success"           => true,
            "live_galleries"    => LiveCentralResource::collection($filteredItems)
        ]);

    }

    // public function live_central(Request $request){
    //     $live_galleries = LiveCentral::where('status',1)->get();

    //     if(isset($request->channel_id)){
    //         $channel = $request->channel_id;
    //         if(!($channel_found = Channel::find($channel))) {
    //             return response()->json(['error'=>"Channel Not found"], 404);
    //         }

    //         if(count(LiveCentral::where('channel_id', $channel)->where('status',1)->get())>0){

    //             $all_live = LiveCentral::where('channel_id', $channel)->where('status',1)->get();
    //             $lives = [];
    //             foreach($all_live as $live){
    //                     array_push($lives, $live);

    //             }

    //             $uniqueItems = [];
    //             foreach ($lives as $item) {
    //                 // Construct a unique key based on live_id and username
    //                 $key = $item['live_id'] . '|' . $item['username'];

    //                 // Check if the item is already in the uniqueItems array
    //                 if (!isset($uniqueItems[$key])) {
    //                     $uniqueItems[$key] = $item;
    //                 }
    //             }

    //             // Re-index the array to remove the keys
    //             $filteredItems = array_values($uniqueItems);


    //             return response()->json([
    //                 "success"           => true,
    //                 "live_centrals"     => LiveCentralResource::collection($filteredItems)
    //             ]);
    //         }
    //         return response()->json([
    //             "success"        => false,
    //             "live_centrals"  => [],
    //             "message"        => "No live in this channel",
    //         ]);
    //     }

    //     $uniqueItems = [];
    //     foreach ($live_galleries as $item) {
    //         // Construct a unique key based on live_id and username
    //         $key = $item['live_id'] . '|' . $item['username'];

    //         // Check if the item is already in the uniqueItems array
    //         if (!isset($uniqueItems[$key])) {
    //             $uniqueItems[$key] = $item;
    //         }
    //     }

    //     // Re-index the array to remove the keys
    //     $filteredItems = array_values($uniqueItems);


    //     return response()->json([
    //         "success"           => true,
    //         "live_galleries"    => LiveCentralResource::collection($filteredItems)
    //     ]);

    // }

    function IsLive($live_id)
        {
        //Generate a random hex string of 16 hex digits.
        $signatureNonce = bin2hex(random_bytes(8));
        //Use the AppID and ServerSecret of your project.
        $appId = "307008811";
        $serverSecret = "1c8bc584ab615bfa53c9b2bf9b0c3988";
        $timestamp = time();
        $str = $appId.$signatureNonce.$serverSecret.$timestamp;
        $signature = md5($str);


        $url = "https://rtc-api.zego.im/?Action=DescribeRTCStreamState&StreamId=$live_id&Sequence=$timestamp&AppId=$appId&SignatureNonce=$signatureNonce&Timestamp=$timestamp&Signature=$signature&SignatureVersion=2.0&IsTest=false" ;

        $response = Http::get($url);

        if($response['Code'] == 0){
            return true;
        }
        return false;

        }
}
