<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Response;
use App\Models\Reservation;
use App\Models\User;
use App\Models\LiveScheduleGallery;

class ReservationController extends Controller
{
     //
     public function store(Request $request){
       
        $validator = Validator::make($request->all(),[ 
            'user_id'                   => 'required|integer|exists:users,id',  
            'live_schedule_gallery_id'  => 'required|integer|exists:live_schedule_galleries,id',   
            ]); 
        
        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

        if(!($reservation = Reservation::where('user_id', $request->user_id)->where('live_schedule_gallery_id', $request->live_schedule_gallery_id)->first())){
            $reservation = Reservation::create($request->all());
            $gallery = LiveScheduleGallery::find($request->live_schedule_gallery_id);

            ////////////////////////////////////////////
            $user = User::find($request->user_id);
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

            if(!$user->stripe_id){
                $stripe_customer = $stripe->customers->create([]);
                $user->stripe_id = $stripe_customer->id;
            }
            $stripe_customer = $stripe->customers->retrieve($user->stripe_id,[]);
            
            //SamplePaymentMethod
            $paymentMethod = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 8,
                'exp_year' => 2024,
                'cvc' => '314',
            ],
            ]);

                
            $stripe->paymentMethods->attach(
                    $paymentMethod->id,
                    ['customer' => $stripe_customer->id]
                  );
                  
                $stripe->customers->update(
                $stripe_customer->id,
            ['invoice_settings' => ['default_payment_method' => $paymentMethod->id]]
            );
                
                  

            if(isset($gallery->product_id) && isset($user->stripe_id)){
              
                $stripe_subscription = $stripe->subscriptions->create([
                    'customer' => $stripe_customer->id,
                    'items' => [
                        ['price' => $gallery->product_id],
                    ]
                    ]);
                    $reservation->subscription_id = $stripe_subscription->id;
                    $reservation->save();
            }
            
            //////////////////////////////////////////////////////////

            // $follower = User::where('id', $request->follower_id)->first();
            // $user_followed = User::where('id', $request->followee_id)->first();

            // $data['message'] = 'User '.$follower->username." has started following you" ;
            // $data['user']  = $follower;
    
            // $user_followed->notify(new FollowUserAccountNotification($data));

            return response()->json([
                "success"    => true,
                "message"    => "User has successfully reserved for the event"
            ]);

        }else{
            return response()->json([
                "success"    => false,
                "message"    => "User has already made an RSVP to this event"
            ]);
        }

   
}

public function index(Request $request, $live_schedule_gallery_id){
    
    if(!($gallery = LiveScheduleGallery::where('id', $live_schedule_gallery_id)->first())){
        return response()->json([
            "success" => false,
            "message" => "Gallery not found",
            
        ], 404);
    };
    $reservations = Reservation::where('live_schedule_gallery_id', $live_schedule_gallery_id)->get();

    $returnData = array(
        'status'         => true,
        'reservations'   => $reservations,
    );

    return Response::json($returnData, 200); 

}

}