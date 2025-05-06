<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserSubscription;
use App\Models\UserAvatar;
use App\Models\User;
use App\Models\UserPaymentInformation;
use App\Models\UserFollower;
use App\Models\UserSubscriber;
use App\Models\Notification;
use App\Notifications\FollowUserAccountNotification;
use App\Http\Resources\UserResource;
use Validator;

class UserSubscriptionController extends Controller
{
    
    public function subscribe_follow(Request $request){
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
                $user_follower->subscriber_id = $request->subscriber_id;
                $user_follower->user_id = $request->user_id;
                $user_follower->save();
            }
            
            
           
            // $follower = User::where('id', $request->subscriber_id)->first();
            // $user_followed = User::where('id', $request->user_id)->first();

            // $data['message'] = 'User '.$follower->username." has started following you" ;
            // $data['user']  = $follower;
    
            // $user_followed->notify(new FollowUserAccountNotification($data));


            if(!($following_action = UserFollower::where('user_id', $request->user_id)->where('follower_id', $request->subscriber_id)->first())){
                $user_follower = new UserFollower();
                $user_follower->user_id = $request->user_id;
                $user_follower->follower_id = $request->subscriber_id;
                $user_follower->save();

                $follower = User::where('id', $request->subscriber_id)->first();
                $user_followed = User::where('id', $request->user_id)->first();


                return response()->json([
                    "success"    => true,
                    "message"    => "You have now subscribed and followed successfully"

                ]);

            }

            return response()->json([
                "success"    => true,
                "message"    => "You have now subscribed and followed successfully"
            ]);

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

            # Remove user following action

            if(($following_action = UserFollower::where('user_id', $request->user_id)->where('follower_id', $request->subscriber_id)->first())){

                $following_action = UserFollower::where('user_id', $request->user_id)->where('follower_id', $request->subscriber_id)->first();
                $following_action->delete();
            }
            



            return response()->json([
                "success"    => true,
                "message"    => "Unsubscribed successfully"
            ]);
        }
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id,deleted_at,NULL',
            'fee'              => 'required|numeric|between:0,9999.999'
            ]);   

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        if($subscription = UserSubscription::where('user_id', $request->user_id)->first()){
            $subscription->Fee = $request->fee;
            $subscription->save();

            return response()->json([
                "success" => true,
                "message" => "Subscription fee updated successfully",
            ]);
        }

        $subscription = new UserSubscription;
        $subscription->user_id = $request->user_id;
        $subscription->fee = $request->fee;
        $user = User::find($request->user_id);

        //Create Stripe product for user with monthly subscription
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $product = $stripe->products->create([
            'name' => $user->username."-subscription-".rand(1200,190000),
        ]);
        
        //Create Plan with the above product
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $plan = $stripe->plans->create([
            'amount'   => ($request->fee)*100,
            'currency' => 'usd',
            'interval' => 'month',
            'product'  =>  $product->id,
        ]);

        $subscription->plan_id = $plan->id;
        $subscription->save();

        return response()->json([
            "success" => true,
            "message" => "Subscription fee set successfully",
        ]);
    }

    public function index(Request $request, $id){
        if(!($user = User::find($id))) {          
            return response()->json(['error'=>"User not found"], 404);                        
        }

        if($subscription = UserSubscription::where('user_id', $id)->first()){
            $fee = $subscription->fee;
            $plan_id = $subscription?->plan_id;
        }else{
            $fee = "Fee Not set";
            $plan_id = null;
        };
        
        $user = User::where('id', $id)->first();
        $user_avatar = UserAvatar::where('user_id', $id)->first();
        
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        if(!$user->stripe_id){
            $stripe_customer = $stripe->customers->create([
            ]);
            $user->stripe_id = $stripe_customer->id;
            $user->save();
        }
        
        return response()->json([
            "success"        => true,
            "username"       => $user->username,
            "subscription"   => $fee,
            "user_avatar"    => $user_avatar->path,
            "stripe_id"      => $user->stripe_id,
            "plan_id"        => $plan_id
        ]);
    }
    public function payment_sheet(Request $request, $id){
        if(!($user = User::find($id))) {          
            return response()->json(['error'=>"User not found"], 404);                        
        }

        if($subscription = UserSubscription::where('user_id', $id)->first()){
            $fee = $subscription->fee;
            $plan_id = $subscription?->plan_id;
        }else{
            $fee = 1;
            $plan_id = null;
        };
        
        $user = User::where('id', $id)->first();
        $user_avatar = UserAvatar::where('user_id', $id)->first();
        
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        if(!$user->stripe_id){
            $stripe_customer = $stripe->customers->create([
            ]);
            $user->stripe_id = $stripe_customer->id;
            $user->save();
        }

        # check for saved user profile
        if($user_payment_info     = UserPaymentInformation::where('user_id', $user->id)->first()){
            $ephemeralKeyValue    = $user_payment_info->ephemeral_key;
            $paymentIntentValue   = $user_payment_info->payment_intent;
            $customerIdValue      = $user_payment_info->customer_id;
            $publishableKeyValue  = $user_payment_info->publishable_key;
        }else{
       
        $new_user_payment_info          = new UserPaymentInformation;
        $new_user_payment_info->user_id = $user->id;

        // Use an existing Customer ID if this is a returning customer.
       
        $ephemeralKey = $stripe->ephemeralKeys->create([
            'customer' => $user->stripe_id,
        ], [
        'stripe_version' => '2022-08-01',
        ]);

        $ephemeralKeyValue = $ephemeralKey->secret;
        $new_user_payment_info->ephemeral_key = $ephemeralKeyValue;
    
        
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => ($fee)*100,
            'currency' => 'usd',
            'customer' => $user->stripe_id,
            'automatic_payment_methods' => [
                'enabled' => 'true',
        ],
        ]);
        $customerIdValue     = $user->stripe_id;
        $paymentIntentValue  = $paymentIntent->client_secret;
        $publishableKeyValue = "pk_test_51LjiSLKWdAsGLGM56VGVLz218uIIDeJlRv7DE4CRQCI9GvHWPKJncDQrjX0OJKFlgUj1ajgo4TpSfktxX4YfByAp00qt4drGH6";

        $new_user_payment_info->customer_id = $customerIdValue;
        $new_user_payment_info->payment_intent = $paymentIntentValue;
        $new_user_payment_info->publishable_key = $publishableKeyValue;
        $new_user_payment_info->save();
    }

        return response()->json([
            "success"        => true,
            "paymentIntent"  => $paymentIntentValue,
            "ephemeralKey"   => $ephemeralKeyValue,
            "customer"       => $customerIdValue,
            "publishableKey" => $publishableKeyValue

        ]);
    }

    public function setup_payment_sheet(Request $request, $id){
        if(!($user = User::find($id))) {          
            return response()->json(['error'=>"User not found"], 404);                        
        }
        if($subscription = UserSubscription::where('user_id', $id)->first()){
            $fee = $subscription->fee;
            $plan_id = $subscription?->plan_id;
        }else{
            $fee = "Fee Not set";
            $plan_id = null;
        };
        
        $user = User::where('id', $id)->first();
        $user_avatar = UserAvatar::where('user_id', $id)->first();
        
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        if(!$user->stripe_id){
            $stripe_customer = $stripe->customers->create([
            ]);
            $user->stripe_id = $stripe_customer->id;
            $user->save();
        }
        
        // Use an existing Customer ID if this is a returning customer.
       
        $ephemeralKey = $stripe->ephemeralKeys->create([
            'customer' => $user->stripe_id,
        ], [
        'stripe_version' => '2022-08-01',
        ]);
        
        $setupIntent = $stripe->setupIntents->create([
            'customer' => $user->stripe_id,
            'automatic_payment_methods' => [
                'enabled' => 'true',
        ],
        ]);
        return response()->json([
            "success"        => true,
            "setupIntent"    => $setupIntent->client_secret,
            "ephemeralKey"   => $ephemeralKey->secret,
            "customer"       => $user->stripe_id,
            "publishableKey" => "pk_test_51LjiSLKWdAsGLGM56VGVLz218uIIDeJlRv7DE4CRQCI9GvHWPKJncDQrjX0OJKFlgUj1ajgo4TpSfktxX4YfByAp00qt4drGH6"
        ]);
    }
}