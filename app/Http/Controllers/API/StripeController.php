<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use Validator;

class StripeController extends Controller
{
    //Connect to an Account
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            'user_id'   => 'required|integer|exists:users,id', 
        ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 
        
        if($user_profile = UserProfile::where('user_id', $request->user_id)->first())
        {   
            $full_name = $user_profile->name;
            $user_email = "";
            if($user_email = $user_profile->email){
                $user_email = $user_email;
            }
            
            //$link   = "https://connect.stripe.com/oauth/authorize?response_type=code&client_id=ca_LtId1NhTuchV7iI0XcuILdjRumoMx5dF&scope=read_write&stripe_user[email]=".$user_email;
            $link = "https://dashboard.stripe.com/register";

            
            $content = "<html> <p>Hi ".$full_name.", </p> <p>Welcome to The Breakdown App. You've joined a community of Verified Creators focused on </br>
            sharing their arts & entertainment journey, expertise, and opinions </br>
            with others. We value our Creators and want you to feel that love through </br>
            providing the ability to monetize your content. To recieve bi-weekly </br>
            compensation for users viewing your premium content, please set-up an account </br>
            through Stripe by selecting this link <a href='$link'>Signup on Stripe.</a> Then, read up on the details and </br>
            features of our offerings by visiting the <a href='https://thebreakdownapp.info/creator-offerings/'> Creator Offerings </a> page of our website.</br></p>
            </br>
            <p>Thanks,</p>
            </br>
            <p>Brian & Antoinette Haygood</span><br>
            <span>The Breakdown App, Co-founders</span><br>
            <span> w: thebreakdownapp.info</span><br>
            <span>e: admin@thebreakdown.app</p>
            <html>
            ";
            $email = new \SendGrid\Mail\Mail(); 
            $email->setFrom("admin@thebreakdown.app", "Create a Stripe account");
            $email->setSubject("Create Stripe account");
            $email->addTo($user_email, "Create a Stripe account");
            
            $email->addContent(
                "text/html", $content,
            );
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            try {
                $response_mail = $sendgrid->send($email);
                $response = json_encode(array(
                    "success" => true,
                    "message" => "Stripe Connect hyperlink has been sent"
                ));
            } catch (Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."</br>";
                    }
                
            return $response;
        }
        return response()->json(['error'=>''], 401);                        
        
    }
        

}
