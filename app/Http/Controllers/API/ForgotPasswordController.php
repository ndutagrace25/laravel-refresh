<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Twilio\Rest\Client;
use App\Models\ForgotPassword;
use App\Models\UserProfile;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),[ 
            //Check if the user email exists in the table
            'email'    => 'required_without:phone|nullable|email|exists:user_profiles,email', 
            //Check if the user phone exits in the table of user profiles
            'phone'    => 'required_without:email|nullable|integer|exists:user_profiles,phone',
        ]);   

        if($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 401);                        
        }

        if(isset($request->phone)){
            $userProfile = UserProfile::where('phone', $request->phone)->first();
        }else{
            $userProfile = UserProfile::where('email', $request->email)->first();
        }

        $user = User::whereId($userProfile->user_id)->first();
        $code = random_int(100000, 999999);

        $forgotPassword = new ForgotPassword();
        $forgotPassword->user_profile_id = $userProfile->id;
        $forgotPassword->code = $code;
        $forgotPassword->status = 0;
        $forgotPassword->expired = 0;
        $forgotPassword->save();

        $extension = "+1";
        $phone = $extension.$userProfile->phone;
        

        if(isset($request->phone))
        {
            // Your Account SID and Auth Token from twilio.com/console
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $client = new Client($sid, $token);

            // Use the client to do fun stuff like send text messages!
            $client->messages->create(
                // the number you'd like to send the message to
                $phone,
                [
                    // A Twilio phone number you purchased at twilio.com/console
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    // the body of the text message you'd like to send
                    'body' => 'Your password reset code is '.$code,
                ]
            );
            $response = json_encode(array(
                "success" => true,
                "message" => "A reset code has been sent to your mobile number"
            ));
        }else{
            $email = new \SendGrid\Mail\Mail(); 
            $email->setFrom("yadu@dedicateddev.team", "Reset Password Code");
            $email->setSubject("Reset Password code");
            $email->addTo($request->email, "Reset Password");
            $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
            $email->addContent(
                "text/html", 'Your password reset code is '.$code,
            );
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            try {
                $response_mail = $sendgrid->send($email);
                $response = json_encode(array(
                    "success" => true,
                    "message" => "A reset code has been sent to your email"
                ));
            } catch (Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."\n";
            }
        }
        return $response;
    
    }
}