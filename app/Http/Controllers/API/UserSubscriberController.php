<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserSubscriber;
use App\Models\UserSubscription;
use App\Models\User;
use Validator;

class UserSubscriberController extends Controller
{
    //
    public function store(Request $request){
       
            $validator = Validator::make($request->all(),[ 
                'user_id'        => 'required|integer|exists:users,id',  
                'subscriber_id'  => 'required|integer|exists:users,id',   
                ]); 
            
            if($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 401);                        
            } 
    
            if(!($subscriber_action = UserSubscriber::where('user_id', $request->user_id)->where('subscriber_id', $request->subscriber_id)->first())){
                $user_subscription = UserSubscription::where('user_id', $request->user_id)->first();
                $user = User::find($request->subscriber_id);
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

                if(!$user->stripe_id){
                    $stripe_customer = $stripe->customers->create([
                    ]);
                    $user->stripe_id = $stripe_customer->id;
                }
                $stripe_customer = $stripe->customers->retrieve(
                    $user->stripe_id,
                    []
                  );

                  $payment_methods = $stripe->customers->allPaymentMethods(
                    $stripe_customer->id,
                    ['type' => 'card']
                  );

                
                //   //SamplePaymentMethod
                //   $paymentMethod = $stripe->paymentMethods->create([
                //     'type' => 'card',
                //     'card' => [
                //         'number' => '4242424242424242',
                //         'exp_month' => 8,
                //         'exp_year' => 2024,
                //         'cvc' => '314',
                //     ],
                //     ]);

                    
                //     $stripe->paymentMethods->attach(
                //         $paymentMethod->id,
                //         ['customer' => $stripe_customer->id]
                //       );


                  if(!$payment_methods->data){
                    return response()->json([
                        "success"    => false,
                        "message"    => "You have not set up any payment method on Stripe, please go to stripe and add Payment method"
                    ]);
                 }
                //   }else{
                //       return $payment_methods;
                //   }
                    
                //       $stripe->customers->update(
                //         $stripe_customer->id,
                //         ['invoice_settings' => ['default_payment_method' => $paymentMethod->id]]
                //       );
                    
                      

                if(isset($user_subscription))
                {
                    if($user_subscription->plan_id && $user->stripe_id){
                        $stripe_subscription = $stripe->subscriptions->create([
                            'customer' => $stripe_customer->id,
                            'items' => [
                                ['price' => $user_subscription->plan_id],
                            ]
                            ]);
                    }

                $user_subscriber = UserSubscriber::create($request->all());
                $user_subscriber->subscription_id = $stripe_subscription->id;
                $user_subscriber->save();

                $user_follower = new UserFollower;
                $user_follower->follower_id = $request->subscriber_id;
                $user_follower->user_id = $request->user_id;
                $user_follower->save();
                // $follower = User::where('id', $request->follower_id)->first();
                // $user_followed = User::where('id', $request->followee_id)->first();
    
                // $data['message'] = 'User '.$follower->username." has started following you" ;
                // $data['user']  = $follower;
        
                // $user_followed->notify(new FollowUserAccountNotification($data));
    
                return response()->json([
                    "success"    => true,
                    "message"    => "subscribed successfully"
                ]);
                }
                
                
                
                
    
            }else{
                $subscriber_action = UserSubscriber::where('user_id', $request->user_id)->where('subscriber_id', $request->subscriber_id)->first();
                if($subscriber_action->subscription_id){
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                    $stripe->subscriptions->cancel(
                        $subscriber_action->subscription_id,
                        []
                      );
                }
                $subscriber_action->delete();



                return response()->json([
                    "success"    => true,
                    "message"    => "Unsubscribed successfully"
                ]);
            }
    
       
    }

    

    public function index(Request $request, $subscriber_id, $user_id){
        $subscriber = UserSubscriber::where('user_id', $user_id)->where('subscriber_id', $subscriber_id)->first();
        if($subscriber){
            return response()->json([
                true
            ]);
        }
        return response()->json([
            false
        ]);
    }
}