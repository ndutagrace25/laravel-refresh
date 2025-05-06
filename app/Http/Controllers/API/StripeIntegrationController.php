<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class StripeIntegrationController extends Controller
{
    //
    public function index(Request $request){
        $user = User::first();
        $stripeCustomer = $user->asStripeCustomer();
        /*
            The first element is the amount in four digit
        */
        $user->applyBalance(-1100, 'Bad usage penalty.'); 
        $items = [];

        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET_KEY')
        );

        $all_transcation =  $stripe->customers->allBalanceTransactions(
           $stripeCustomer->id,
         );

         $mon = Carbon::now()->startOfWeek()->format('d-m-Y');
         $tue = Carbon::now()->startOfWeek()->addDays(1)->format('d-m-Y');
         $wed = Carbon::now()->startOfWeek()->addDays(2)->format('d-m-Y');
         $thu = Carbon::now()->startOfWeek()->addDays(3)->format('d-m-Y');
         $fri = Carbon::now()->startOfWeek()->addDays(4)->format('d-m-Y');
         $sat = Carbon::now()->startOfWeek()->addDays(5)->format('d-m-Y');
         $sun = Carbon::now()->endOfWeek()->format('d-m-Y');
         
         foreach($all_transcation as $transcation){
             $format = date("d-m-Y", $transcation['created']);

             
         }
        
       
    }
}
