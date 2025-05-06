<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ChatGallery;
use Carbon\Carbon;
use App\Models\Tag;
use Stripe\StripeClient;
use Laravel\Cashier\Cashier;

class CreatorReportDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $daily_live_view = [
            "S"=>0,
            "M"=>0,
            "T"=>0,
            "W"=>0,
            "T"=>0,
            "F"=>0,
            "S"=>0
        ];
        $daily_live_market = [
            "S"=>0,
            "M"=>0,
            "T"=>0,
            "W"=>0,
            "T"=>0,
            "F"=>0,
            "S"=>0
        ];

        //Chad Gallery
        $chat_gallery_name = "";
        $unique_visitors = 0;

        //Scheduled Chat Gallery
        $scheduled_chat_gallery_name = "";
        $wait_listed_visitors = 0;

        $chat_gallery = ChatGallery::where('user_id', $this->id)->orderBy('views', 'desc')->first();
        if($chat_gallery){
            $chat_gallery_name = $chat_gallery->name;
            $unique_visitors = $chat_gallery->views;
        }

        $unique_visitors = 0;

        
        $engagement = [
            "live_central" => [
                "daily_live_view"    => $daily_live_view,
                "daily_live_market"  => $daily_live_market
            ],
            "chat_gallery"  => [
                "chat_gallery_name"  => $chat_gallery_name,
                "unique_visitors"    => $unique_visitors
            ],
            "scheduled_chat_gallery" => [
                "scheduled_chat_gallery_name" => $scheduled_chat_gallery_name,
                "wait_listed_visitors"        => $wait_listed_visitors
            ],

        ];
     
       
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET_KEY')
          );
        
    
        try {
            $stripeCustomer = $this->asStripeCustomer();
        } catch (\Exception $e) {
            $stripeCustomer = $this->createAsStripeCustomer();
        }
    
        $all_transcation =  $stripe->customers->allBalanceTransactions(
           $stripeCustomer->id,
          );
        
       

        //Revenue Stream Earnings
        $revenue_stream = [];
        $revenue_stream ["premium_content"] = 0;
        $revenue_stream ["subscriptions"] = 0;
        $revenue_stream ["tips"] = 0;

        //Bucket Tag Earnings
        $tags = Tag::all();
        foreach($tags as $tag){
            $bucket_tag_earnings[$tag->name] = 0;
        }

        
         $day = Carbon::now()->format('d-m-Y');
         $start_of_week = Carbon::now()->startOfWeek()->format('d-m-Y'); 
         $end_of_week = Carbon::now()->endOfWeek()->format('d-m-Y'); 
         $month = Carbon::now()->format('m');
         $year = Carbon::now()->format('Y');

         $income = [];
         $income['day'] =0;
         $income['week'] = 0;
         $income['month'] = 0;
         $income['year'] = 0;
         
         foreach($all_transcation as $transcation){
             $created = date("d-m-Y", $transcation['created']);
             $created_month = date("m", $transcation['created']); 
             $created_year = date("Y", $transcation['created']); 

            //Get list of credit amounts only
            if($transcation['amount'] > 0){
                if($created == $day ){
                    $income['day'] += (double)$transcation['amount'];
                }
                if($created <= $end_of_week && $created >= $start_of_week){
                    $income['week'] += (double)$transcation['amount'];
                }
                if($created_month == $month){
                    $income['month'] += (double)$transcation['amount'];
                }
                if($created_year == $year){
                    $income['year'] += (double)$transcation['amount'];
                }
            }

            if($transcation->metadata){
                if($transcation->metadata["revenue_stream"] == "premium_content"){
                    
                    $revenue_stream["premium_content"] += (double)$transcation['amount'];

                }elseif($transcation->metadata["revenue_stream"] == "subscriptions"){
                    $revenue_stream["subscriptions"] += (double)$transcation['amount'];

                }elseif($transcation->metadata["revenue_stream"] == "tips"){
                    $revenue_stream["tips"] += (double)$transcation['amount'];
                }

                //Uncommentable
                // foreach($tags as $tag){
                //     if($transcation->metadata['bucket_tag'] == $tag->name){
                //        $bucket_tag_earnings[$tag->name] += (double)$transcation['amount'];
                //     }
                // }                
            }
            

         }

          //Uncommentable
                 foreach($tags as $tag){
                //     if($transcation->metadata['bucket_tag'] == $tag->name){
                        $bucket_tag_earnings[$tag->name] += rand(0,100);
                //     }
                 }
        
        //Dummy Data for testing
         $revenue_stream["premium_content"] = rand(10, 110);
         $revenue_stream["subscriptions"] = rand(20,120);
         $revenue_stream["tips"] = rand(1,30);

         
         $profit_breakdown = [
            "by_revenue_system" => $revenue_stream,
            "by_bucket_tag" => $bucket_tag_earnings
        ];
       

        return [
            'id'                 => $this->id,
            'Earned_Income'      => $income,
            'engagement'         => $engagement,
            'profit_breakdown'   => $profit_breakdown
            ];
    }
}