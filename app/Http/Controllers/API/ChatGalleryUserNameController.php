<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatGallery;
use GuzzleHttp\Client;
use App\Models\UserProfile;
use App\Models\UserAvatar;
use App\Models\User;
use App\Models\Channel;
use App\Http\Resources\ChatGalleryUserResource;

class ChatGalleryUserNameController extends Controller
{
    //search chat gallery
    public function index(Request $request){
        $movies = [];
        $tvs = [];
        $books = [];
        if($request->key){
        $key = $request->key;

        $client = new Client();
        $apiKey = 'caa6891d084141a386ce0d2d1eb8f620';
        $apiUrl = "https://api.themoviedb.org/3/"; 

        $movie_response = $client->get($apiUrl . 'search/movie', [
            'query' => [
                'api_key' => $apiKey,
                'query'   => $key,
            ],
        ]);

        $tv_response = $client->get($apiUrl . 'search/tv', [
            'query' => [
                'api_key' => $apiKey,
                'query' => $key,
            ],
        ]);

        // Get the JSON response from the API
        $movie_data = json_decode($movie_response->getBody(), true);
        $tv_data = json_decode($tv_response->getBody(), true);
        $tv_channel_id = Channel::where('name', 'TV')->first()?->id;
        $movie_channel_id = Channel::where('name', 'Movies')->first()?->id;
        $book_channel_id = Channel::where('name', 'Books')->first()?->id;

        $foundtitles = [];
        // Loop through the results to find an exact match
        foreach ($movie_data['results'] as $result) {
            // Add the movie poster and title to the array
            $chatgallery = ChatGallery::where('name', $key)->first();

            $movies[] = [
                'poster'     => 'https://image.tmdb.org/t/p/w500' . $result['poster_path'],
                'title'      => $result['title'],
                'channel_id' => $movie_channel_id
            ];
            
        }

       foreach ($tv_data['results'] as $result) {

            
            // Add the movie poster and title to the array
            $tvs[] = [
                'poster'   => 'https://image.tmdb.org/t/p/w500' . $result['poster_path'],
                'title'    => $result['name'],
                'channel_id' => $tv_channel_id
            ];
    }


        if(isset($movies)){
            $movies = array_slice($movies, 0, 5);
        }
        if(isset($tvs)){
            $tvs = array_slice($tvs,0 , 5);
        }
       
        $title = $key;

        // Set the API key.
        $apiKey = env('AIzaSyB4uBSkelS7xwzstLu_3oy7MSdNdvc6ioE');
        $books = [];

        try {
            $endpointUrl = "https://www.googleapis.com/books/v1/volumes?q=intitle:" . urlencode($title) . "&key=" . $apiKey;

            // Send the HTTP request.
            $response = $client->request('GET', $endpointUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                ],
            ]);
            // Get the response body.
            $responseBody = $response->getBody();
        
            // Parse the response JSON data.
            $responseData = json_decode($responseBody, true);
       
            $books = [];
                
                    $thumbnail = "";
                    if(isset($responseData['items'])){
                    foreach ($responseData['items'] as $key => $item) {
                        if(count($books) < 5){
                        $name = $item['volumeInfo']['title'];

                        if( isset($item['volumeInfo']['imageLinks'])){
                            $thumbnail = $item['volumeInfo']['imageLinks']['thumbnail'];
                        }else{
                            $colors = ["F8B966","DD1E25","721003"];
                            $rand_key = array_rand($colors, 1);
                            $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
                            $thumbnail = $random_cover;
                        }
                        array_push($books, ["title"=>$name, "poster"=>$thumbnail, 'channel_id'=>$book_channel_id]);
                    }else{
                        break;
                    }
                    }
                } 
        } catch(ClientException $e) {
            echo 'Caught an exception: ' . $e->getMessage() . "\n";
    
           
        
        }

        /*
            List galleries and user profiles
        */
        $chat_galleries = ChatGallery::where('topic', 'LIKE', '%'.$title.'%')->get();
        $user_profiles = UserProfile::where('name','LIKE','%'.$title.'%')->get();
        $users_data = [];
        $galleries_data = [];

        foreach($user_profiles as $user_profile){
            $avatar = UserAvatar::where('user_id', $user_profile->user_id)->first();
            $user = User::where('id', $user_profile->user_id)->first();
            array_push($users_data, ["user_id"=> $user->id, "avatar" => $avatar->path,"username" => $user->username ]);
        }

        foreach($chat_galleries as $gallery){
            $comment_count = 0;
            if(count($gallery->chatrooms) > 0){
                foreach ($gallery->chatrooms as $room){
                    $comment_count += $room->comments->count();
                }
            }
            
            array_push($galleries_data, [
                "comments"       => 0,
                "title"          => $gallery->topic,
                "category"       => $gallery->category->name,
                "is_official"    => $gallery->official,
                "comment_count"  => $comment_count
            ]);
        }

        return response()->json([
            "success"     => true,
            "data"        => [
                [
                "title" => "Creators",
                "data"  => $users_data
            ],
            [
                "title" => "Chat Gallery Topics",
                "data"  => $galleries_data
            ],
            [
                "title" => "Movies",
                "data"  => $movies
            ],
            [
                "title" => "Tvs",
                "data"  => $tvs
            ],
            [
                "title" => "Books",
                "data"  => $books
            ]
        ]]);
        
       }
        return response()->json([
            "success"         => false,
            "message"         => "No chat gallery or user with this key found",
        ]);
      
    }
}
