<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveScheduleGallery;
use App\Models\LiveScheduleGalleryFile;
use App\Models\LiveGalleryTag;
use App\Models\User;
use Validator;
use Carbon\Carbon;

class LiveScheduleGalleryController extends Controller
{
    //
    public function store(Request $request){
        $validator = Validator::make($request->all(),[  
            'is_premium'         => 'required|boolean',
            'uuid'               => 'required|string',
            'entry_fee'          => 'required_if:is_premium,==,true|between:0,9999999.99',
            'user_id'            => 'required|integer|exists:users,id',
            'channel_id'         => 'required|integer|exists:channels,id',
            'tag_id'             => 'required|integer|exists:tags,id', 
            'start_time'         => 'required', 
            'end_time'           => 'after:start_time',
            'date'               => 'required|date_format:m/d/Y',
            'timezone'           => 'integer|between:0,33',
            'title'              => 'required|string',
            'description'        => 'string', 
            'pre-live'           => 'integer|', 
            'bucket_tag'         => 'integer|exists:tags,id'
         ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                         
        } 
        // if(!($live_schedule_galleries = LiveScheduleGallery::find($id))) {          
        //     return response()->json(['error'=>"Live Scheduled Gallery not found"], 404);                        
        // }
        $dateInput = $request->date; // Replace with your input date string
        $date = \DateTime::createFromFormat('m/d/Y', $dateInput);
        $formattedDate = $date->format('Y-m-d H:i:s');


        $timezone_array = [
            '+0:00',
            '+0:00',
            '+1:00',
            '+2:00',
            '+2:00',
            '+3:00',
            '+3:30',
            '+4:00',
            '+5:00',
            '+5:30',
            '+6:00',
            '+7:00',
            '+8:00',
            '+9:00',
            '+9:30',
            '+10:00',
            '+11:00',
            '+12:00',
            '-11:00',
            '-10:00',
            '-9:00',
            '-8:00',
            '-7:00',
            '-7:00',
            '-6:00',
            '-5:00',
            '-5:00',
            '-4:00',
            '-3:30',
            '-3:00',
            '-3:00',
            '-1:00'
        ];

        $time_string = (string)$timezone_array[$request->timezone];
                  
        // if(strpos($time_string, "-") !== false){
        //     return "has minus";
        //     $time_string = str_replace("-", "", $time_string);
        //     // Use regex to match the hour and minute in the string
        //     preg_match('/(\d{1,2}):(\d{2})/', $time_string, $matches);

        //     // Extract the hour and minute from the matched array
        //     $hour = $matches[1];
        //     $minute = $matches[2];

        //     $start_time = $request->start_time;
        //     $end_time = $request->end_time;
            
        //     $start_carbon = Carbon::parse($start_time)->subHours($hour)->subMinutes($minute);
        //     $end_carbon = Carbon::parse($end_time)->subHours($hour)->subMinutes($minute);
        
        // }else{
             
             // Use regex to match the hour and minute in the string
             preg_match('/(\d{1,2}):(\d{2})/', $time_string, $matches);


             // Extract the hour and minute from the matched array
             $hour = $matches[1];
             $minute = $matches[2];
             
 
             $start_time = $request->start_time;
             $end_time = $request->end_time;
 
             $start_carbon = Carbon::parse($start_time)->addHours($hour)->addMinutes($minute);    
             $end_carbon = Carbon::parse($end_time)->addHours($hour)->addMinutes($minute);
            
        
        
        $data = $request->all();

        if($chat_gallery = LiveScheduleGallery::where('uuid', $request->uuid)->first()){
            $chat_gallery->user_id = $request->user_id;
            $chat_gallery->entry_fee = $request?->entry_fee;
            $chat_gallery->uuid = $request->uuid;
            $chat_gallery->channel_id = $request->channel_id;
            $chat_gallery->is_premium = $request->is_premium;
            $chat_gallery->start_time = $start_carbon->toTimeString();;
            $chat_gallery->end_time = $end_carbon->toTimeString();
            $chat_gallery->date =  Carbon::parse($request->date);;
            $chat_gallery->timezone = $request->timezone;
            $chat_gallery->tag_id = $request->tag_id;
            $chat_gallery->title = $request->title;
            $chat_gallery['pre-live'] = $request['pre-live'];
            $chat_gallery->description = $request->description;
            $user = User::find($request->user_id);

            //Create Stripe product for user with monthly subscription
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
            if(!isset($chat_gallery->plan_id)){
                $product = $stripe->products->create([
                    'name' => $chat_gallery->uuid."-subscription-".rand(1200,190000),
                ]);
                
                //Create Plan with the above product
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                $plan = $stripe->plans->create([
                    'amount'   => ($request->entry_fee)*100,
                    'currency' => 'usd',
                    'interval' => 'month',
                    'product'  =>  $product->id,
                ]);
        
                $chat_gallery->product_id = $plan->id;
            }

            $chat_gallery->update();

            return response()->json([
                "success"        => true,
                "message"        => "Live Gallery schedule updated successfully",
                "schedule"       => $chat_gallery
            ]);

        }else{
            $chat_gallery = new LiveScheduleGallery;
            $chat_gallery->user_id = $request->user_id;
            $chat_gallery->entry_fee = $request?->entry_fee;
            $chat_gallery->uuid = $request->uuid;
            $chat_gallery->channel_id = $request->channel_id;
            $chat_gallery->is_premium = $request->is_premium;
            $chat_gallery->start_time = $start_carbon->toTimeString();;
            $chat_gallery->end_time = $end_carbon->toTimeString();
            $chat_gallery->date = Carbon::parse($request->date);
            $chat_gallery->timezone = $request->timezone;
            $chat_gallery->tag_id =  $request->tag_id;
            $chat_gallery->title = $request->title;
            $chat_gallery['pre-live'] = $request['pre-live'];
            $chat_gallery->description = $request->description;

            $user = User::find($request->user_id);

            //Create Stripe product for user with monthly subscription
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
            $product = $stripe->products->create([
                'name' => $chat_gallery->uuid."-subscription-".rand(1200,190000),
            ]);
            
            //Create Plan with the above product
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
            $plan = $stripe->plans->create([
                'amount'   => ($request->entry_fee)*100,
                'currency' => 'usd',
                'interval' => 'month',
                'product'  =>  $product->id,
            ]);
        
            $chat_gallery->product_id = $plan->id;

            $chat_gallery->save();

            $chat_gallery_file = new LiveScheduleGalleryFile;
            $chat_gallery_file->user_id = $request->user_id;
            $chat_gallery_file->uuid = $request->uuid;
            $chat_gallery_file->save();


              
            return response()->json([
                "success"           => true,
                "message"           => "Live Gallery scheduled successfully",
                "schedule"          => $chat_gallery
            ]);
            
        }
        
        // // Set the date with specific formatted data
        // $data['date'] = $formattedDate; 
        
        // if(!($request->cover_photo)){
        //     $colors = ["F8B966","DD1E25","721003"];
        //     $rand_key = array_rand($colors, 1);
        //     $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
        //     $data['cover_photo'] = $random_cover;  
        // }elseif($file = $request->file('cover_photo')) {
        //         $path  = $file->store('chat_galleries/gallery_covers', 's3');
        //         $name  = $file->getClientOriginalName();
        //         $data['cover_photo'] = "https://breakdown-bucket.s3.amazonaws.com/".$path;
        // } 
        // $live_gallery_schedule =  LiveScheduleGallery::create($data);
        // $live_gallery_schedule->start_time = $start_carbon->toTimeString();
        // $live_gallery_schedule->end_time = $end_carbon->toTimeString();
        // $live_gallery_schedule->save();

    
    }

    public function index(Request $request){
        
    }
}