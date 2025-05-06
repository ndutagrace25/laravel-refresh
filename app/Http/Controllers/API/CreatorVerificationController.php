<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CreatorVerification;
use App\Models\User;
use App\Http\Resources\CreatorVerificationResource;
use App\Models\UserProfile;
use Validator;

class CreatorVerificationController extends Controller
{
    //
    public function index(Request $request){
        $requests = CreatorVerification::where('status',0)->get();
        
        return response()->json([
            "success" => true,
            "verification_requests" => CreatorVerificationResource::collection($requests),
        ]);
        
    }
    public function view($id){
        
        if(!($verification = CreatorVerification::where('id', $id)->first())){
            return response()->json([
                "success" => false,
                "message" => "This verification request doesn't exist",
            ]);
        }
        $verification = CreatorVerification::where('id', $id)->first();

        return response()->json([
            "success" => true,
            "verification_request" => $verification,
        ]);

    }
    // Creator verification request files and requests
    public function store(Request $request){


        $validator = Validator::make($request->all(),[ 
            'user_id'          => 'required|integer|exists:users,id',
            'known_as'         => 'required|string',
            'channels'         => 'required|array',
            'channels.*'       => 'required|integer|exists:channels,id',
            'tier'             => 'required|integer|between:1,2',
            'proof_of_work'    => 'required|string',
            ]);   

        if($validator->fails()) {          
            
            return response()->json(['error'=>$validator->errors()], 401);                        
        } 

        $user_id = $request->user_id;
        $known_as = $request->known_as;
        $channel_id = "";
        foreach($request->channels as $key=>$channel){
            $channel_id .= $channel;
            if($key <= strlen($channel)){
                $channel_id .= "-";
            }
        }
        $channel_id = rtrim($channel_id, '-');
        $proof_of_work = $request->proof_of_work;
        $tier = $request->tier;

        $user = User::whereId($user_id)->first();

        if($creator_request = CreatorVerification::where('user_id', $request->user_id)->first()){
            $creator_request->user_id = $user_id;
            $creator_request->known_as = $known_as;
            $creator_request->channel_id = $channel_id;
            $creator_request->tier = $tier;
            $creator_request->proof_of_work = $proof_of_work;
            $creator_request->update();

            $user_profile = UserProfile::where('user_id', $request->user_id)->first();

            $full_name = $user_profile->name;
            $user_email = "";
            if($user_email = $user_profile->email){
                $user_email = $user_email;
            }
            
            //$link   = "https://connect.stripe.com/oauth/authorize?response_type=code&client_id=ca_LtId1NhTuchV7iI0XcuILdjRumoMx5dF&scope=read_write&stripe_user[email]=".$user_email;
            $link = "https://dashboard.stripe.com/register";

            $content   = "<html> <p>Hi ".$full_name.", </p> <p>Congratulations! Your Creator Verification Request for The Breakdown App has been approved. 
                    As verified creator, enjoy the ability to confirm your likeness, live stream video, and monitize your content. If you haven't already, set-up an account 
                    through stripe and connect to our dashboard by selecting this link: <a href='$link'>Connect to Stripe.</a>, allowing you to receive compensation for users viewing your 
                    premium content. Then read up on details and features of our offerings by visiting the <a href='https://thebreakdownapp.info/creator-offerings/'> Creator Offerings </a> page of our website.</br>
                    </br>
                    <p>Thanks,</p>
                    </br>
                    <p>Brian & Antoinette Haygood</span><br>
                    <span>The Breakdown App, Co-founders</span><br>
                    <span>w: thebreakdownapp.info</span><br>
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
                
            } catch (Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."</br>";
                    }

            return response()->json([
                "success" => true,
                "user"    => $user,
                "message" => "Creator verification updated succesfully!",
                "creator" => $creator_request 
            ]);
        }
        $creator_verification = new CreatorVerification;
        $creator_verification->user_id = $user_id;
        $creator_verification->known_as = $known_as;
        $creator_verification->channel_id = $channel_id;
        $creator_verification->tier = $tier;
        $creator_verification->proof_of_work = $proof_of_work;
        $creator_verification->save();
        $creator_request = CreatorVerification::where('user_id', $request->user_id)->first();

        if($user_profile = UserProfile::where('user_id', $request->user_id)->first())
        {   
            $full_name = $user_profile->name;
            $user_email = "";
            if($user_email = $user_profile->email){
                $user_email = $user_email;
            }
            
            //$link   = "https://connect.stripe.com/oauth/authorize?response_type=code&client_id=ca_LtId1NhTuchV7iI0XcuILdjRumoMx5dF&scope=read_write&stripe_user[email]=".$user_email;
            $link = "https://dashboard.stripe.com/register";

            $content   = "<html> <p>Hi ".$full_name.", </p> <p>Welcome to The Breakdown App. You've joined a community of Verified Creators focuse </br>
                    Sharing their arts & entertainment journey, expertise, and opinions </br>
                    with other. We value our creators and want you feel that love through </br>
                    providing the ability to monetize your content. To recieve bi-weekly </br>
                    compensation for users viewing your premium content, please set-up an account </br>
                    through Stripe selecting this link <a href='$link'>Signup on Stripe</a> Then read up on the details and </br>
                    features of our offerings by vising the Creator offerings page of our website.</br></p>
                    </br>
                    <p>Thanks,</p>
                    </br>
                    <p>Brian & Antoinette Haygood</br></p>
                    <p>The Breakdown App, Co-founders</br></p>
                    <p> w: thebreakdownapp.info</br></p>
                    <p>e: admin@thebreakdown.app</br></p>
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
                
            } catch (Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."</br>";
                    }
                
        }
        
        return response()->json([
            "success" => true,
            "user"    => $user,
            "message" => "Creator verification created succesfully! and Sent email for Stripe signup",
            "creator" => $creator_request
        ]);
        
              
        }


        public function verify(Request $request){
            $validator = Validator::make($request->all(),[ 
                'verification_id'  => 'required|integer|exists:creator_verifications,id',
                ]);   
    
            if($validator->fails()) {            
                return response()->json(['error'=>$validator->errors()], 404);                        
            } 
            
            $verification = CreatorVerification::find($request->verification_id);
            $verification->status = 1;
            $verification->save();
            if($user_profile = UserProfile::where('user_id', $verification->user_id)->first())
            {   
                $full_name = $user_profile->name;
                $user_email = "";
                if($user_email = $user_profile->email){
                    $user_email = $user_email;
                }
                
                $link   = "https://connect.stripe.com/oauth/authorize?response_type=code&client_id=ca_LtId1NhTuchV7iI0XcuILdjRumoMx5dF&scope=read_write&stripe_user[email]=".$user_email;
    
                $content   = "<html> <p>Hi ".$full_name.",</p></br></br><p>  Congratulations!  Your Creator Verification Request for The Breakdown App has been approved.</br>  
                As a verified Creator, enjoy the ability to confirm your likeness, live stream video, and monetize your content. If you haven't already, </br>
                set-up an account through Stripe and connect to our dashboard by selecting this link: <a href='$link'>Connect through Stripe</a>, </br>
                allowing you to receive compensation for users viewing your premium content. </br>
                Then read up on the details and features of our offerings by visiting the <a href='https://thebreakdownapp.info/creator-offerings/'Creator Offerings </a> page of our website.</br></p>
                </br>
                <p>Thanks,</p>
                </br>
                <p>Brian & Antoinette Haygood</br></p>
                <p>The Breakdown App, Co-founders</br></p>
                <p> w: thebreakdownapp.info</br></p>
                <p>e: admin@thebreakdown.app</br></p>
                <html>
                ";
      
                
                // </p> <p>Welcome to The Breakdown App. You've joined a community of Verified Creators focuse </br>
                //         Sharing their arts & entertainment journey, expertise, and opinions </br>
                //         with other. We value our creators and want you feel that love through </br>
                //         providing the ability to monetize your content. To recieve bi-weekly </br>
                //         compensation for users viewing your premium content, please set-up an account </br>
                //         through Stripe and connect to our dashboard selecting this link <a href='$link'>Connect through Stripe</a> Then read up on the details and </br>
                //         features of our offerings by vising the Creator offerings page of our website.</br></p>
                        
                //         ";
                $email = new \SendGrid\Mail\Mail(); 
                $email->setFrom("admin@thebreakdown.app", "Connect a Stripe account");
                $email->setSubject("Connect a Stripe account");
                $email->addTo($user_email, "Connect a Stripe account");
                
                $email->addContent(
                    "text/html", $content,
                );
                $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
                try {
                    $response_mail = $sendgrid->send($email);
                    
                } catch (Exception $e) {
                    echo 'Caught exception: '. $e->getMessage() ."</br>";
                        }
                    
            }
             

            return response()->json([
                "success" => true,
                "message" => "Creator Verified and Stripe Connect link sent Succesfully!",
            ]);
        }

        public function unverify(Request $request){
            $validator = Validator::make($request->all(),[ 
                'verification_id'  => 'required|integer|exists:creator_verifications,id',
                ]);   
    
            if($validator->fails()) {            
                return response()->json(['error'=>$validator->errors()], 404);                        
            } 
            
            $verification = CreatorVerification::find($request->verification_id);
            $verification->status = 3;
            $verification->save();   

            return response()->json([
                "success" => true,
                "message" => "Creator Verification Denied",
            ]);
        }
        
    
}
