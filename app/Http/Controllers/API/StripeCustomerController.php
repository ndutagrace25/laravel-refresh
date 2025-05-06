<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StripeCustomerController extends Controller
{
    //
    public function index(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $customers = $stripe->customers->all(['limit' => 100]);

        return $customers;
    }

    public function subscription(Request $request){
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $product = $stripe->products->create([
          'name' => 'user_subscription_uuid',
        ]);

        return $product;
    }

    public function session(Request $request)
    {
        
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        
            $session = \Stripe\Checkout\Session::create([
            'success_url' => 'https://thebreakdownapp.info/',
            'line_items' => [
                [
                  'price' => $request->plan_id,
                  'quantity' => 1,
                ],  
              ],
            'mode' => 'subscription',
            ]);
            
            return $session;
        
    }

    public function stripe_detail(Request $request, $id){
      
      $key = env('STRIPE_PUBLISHABLE_KEY', 'pk_test_51LjiSLKWdAsGLGM56VGVLz218uIIDeJlRv7DE4CRQCI9GvHWPKJncDQrjX0OJKFlgUj1ajgo4TpSfktxX4YfByAp00qt4drGH6');

      if(!($user = User::find($id))) {          
        return response()->json(['error'=>"User not found"], 404);                        
      }
      $stripe_id = null;
      if(isset($user->stripe_id)){
        $stripe_id = $user->stripe_id;
      }

      return response()->json([
        "success"            => true,
        "publishable_key"    => $key,
        "stripe_id"          => $stripe_id
      ]);
    }
}
