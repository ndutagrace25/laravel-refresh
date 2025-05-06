<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use MuxPhp;
use GuzzleHttp;
use App\Http\Resources\LiveStreamResource;
use App\Models\LiveScheduleGallery;

class LiveStreamController extends Controller
{
   
    public function index(Request $request){

        // Authentication Setup
        $config = MuxPhp\Configuration::getDefaultConfiguration()
        ->setUsername(getenv('MUX_TOKEN_ID'))
        ->setPassword(getenv('MUX_TOKEN_SECRET'));

        // API Client Initialization
        $liveApi = new MuxPhp\Api\LiveStreamsApi(
            new GuzzleHttp\Client(),
            $config
        );
        $playbackIdApi = new MuxPhp\Api\PlaybackIDApi(
            new GuzzleHttp\Client(),
            $config
        );

        $createAssetRequest = new MuxPhp\Models\CreateAssetRequest(["playback_policy" => [MuxPhp\Models\PlaybackPolicy::_PUBLIC]]);
        $createLiveStreamRequest = new MuxPhp\Models\CreateLiveStreamRequest(["playback_policy" => [MuxPhp\Models\PlaybackPolicy::_PUBLIC], "new_asset_settings" => $createAssetRequest, "reduced_latency" => true]);
        $stream = $liveApi->createLiveStream($createLiveStreamRequest);
        $url =  "https://stream.mux.com/" . $stream->getData()->getPlaybackIds()[0]->getId() . ".m3u8";
        return response()->json([
            "success"        => true,
            "url"            => $url,
            "stream_key"     => $stream->getData()["stream_key"],
           
        ]);
    }

    public function live_stream(Request $request, $live_gallery_id){

       

        // Authentication Setup
        $config = MuxPhp\Configuration::getDefaultConfiguration()
        ->setUsername(getenv('MUX_TOKEN_ID'))
        ->setPassword(getenv('MUX_TOKEN_SECRET'));

        // API Client Initialization
        $liveApi = new MuxPhp\Api\LiveStreamsApi(
            new GuzzleHttp\Client(),
            $config
        );
        $playbackIdApi = new MuxPhp\Api\PlaybackIDApi(
            new GuzzleHttp\Client(),
            $config
        );

        $createAssetRequest = new MuxPhp\Models\CreateAssetRequest(["playback_policy" => [MuxPhp\Models\PlaybackPolicy::_PUBLIC]]);
        $createLiveStreamRequest = new MuxPhp\Models\CreateLiveStreamRequest(["playback_policy" => [MuxPhp\Models\PlaybackPolicy::_PUBLIC], "new_asset_settings" => $createAssetRequest, "reduced_latency" => true]);
        $stream = $liveApi->createLiveStream($createLiveStreamRequest);
        $url =  "https://stream.mux.com/" . $stream->getData()->getPlaybackIds()[0]->getId() . ".m3u8";

        if(!($gallery = LiveScheduleGallery::find($live_gallery_id))) {          
            return response()->json(['error'=>"gallery not found"], 404);                        
        }else{
            $gallery = LiveScheduleGallery::find($live_gallery_id)->first();
            $gallery->url = $url;
            $gallery->stream_key = $stream->getData()["stream_key"];
            $gallery->save();
        }

        return response()->json([
            "success"        => true,
            "url"            => $url,
            "stream_key"     => $stream->getData()["stream_key"],
            "gallery"        => $gallery
        ]);
    }

    public function list(Request $request){
        $config = MuxPhp\Configuration::getDefaultConfiguration()
        ->setUsername(getenv('MUX_TOKEN_ID'))
        ->setPassword(getenv('MUX_TOKEN_SECRET'));

        // API Client Initialization
        $liveApi = new MuxPhp\Api\LiveStreamsApi(
            new GuzzleHttp\Client(),
            $config
        );
        $playbackIdApi = new MuxPhp\Api\PlaybackIDApi(
            new GuzzleHttp\Client(),
            $config
        );
        $streams = $liveApi->listLiveStreams();

        //List all live stream links
        return response()->json([
            "success"       => true, 
            "stream"         => $streams,
           
        ]);
    }

    //Disable live stream ? can get back to the live
    public function delete(Request $request)
    {
        $config = MuxPhp\Configuration::getDefaultConfiguration()
        ->setUsername(getenv('MUX_TOKEN_ID'))
        ->setPassword(getenv('MUX_TOKEN_SECRET'));

        // API Client Initialization
        $liveApi = new MuxPhp\Api\LiveStreamsApi(
            new GuzzleHttp\Client(),
            $config
        );
        $playbackIdApi = new MuxPhp\Api\PlaybackIDApi(
            new GuzzleHttp\Client(),
            $config
        );

            try {
                $getStream = $liveApi->getLiveStream($request->stream_id);
                $liveApi->disableLiveStream($stream->getData()->getId());
                return response()->json([
                    "success"       => true, 
                    "message"       => "Left Live",
                ]);
            }
            catch (Exception $e) {
                return response()->json([
                    "success"       => false, 
                    "message"       => "Should not have errored when disabling live stream ❌ ",
                ]);
            }
        
    }
}
