<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Channel;
use App\Models\ChatRoom;
use App\Models\ChatRoomComment;
use App\Models\ChatGallery;
use App\Models\ChatGalleryFile;
use App\Models\Tag;
use App\Models\UserAvatar;
use App\Models\User;
use App\Models\LiveScheduleGallery;
use DB;


class TrendingController extends Controller
{

    public function movies(Request $request){

        $movie_channel = Channel::where('name', 'Movies')->first();
        //$tvs_channel = Channel::where('name','TV')->first();
        $fetched_movie_galleries = ChatGallery::where('channel_id', $movie_channel->id)->get();
        $movie_galleries = $fetched_movie_galleries->unique('name', true);

        $tags = Tag::all();
        $tag_list = [];
        foreach($tags as $tag){
            array_push($tag_list, $tag->name);
        }

        $gallery_counts = [];

        foreach($movie_galleries as $gallery){
            $gallery_counts[$gallery->id] = 0;
            if($gallery->chatrooms){
                foreach($gallery->chatrooms as $chatroom){
                    $gallery_counts[$gallery->id] += count($chatroom->allcomments);
                }
            }
        }
        arsort($gallery_counts);

        $titles = [];

        foreach($gallery_counts as $key=>$gallery){
            $gallery = ChatGallery::where('id', $key)->first();

            array_push($titles, $gallery->name);
        }
        // Array to store the movie posters and titles
        $movies = [];
        $movies_list = [];
        $client = new Client();
        $apiKey = 'caa6891d084141a386ce0d2d1eb8f620';
        $apiUrl = "https://api.themoviedb.org/3/";
        $foundtitles = [];

        foreach ($titles as $title) {
            // Make a request to TMDB API to search for the movie
            $response = $client->get($apiUrl . 'search/movie', [
                'query' => [
                    'api_key' => $apiKey,
                    'query' => $title,
                ],
            ]);

            // Get the JSON response from the API
            $data = json_decode($response->getBody(), true);

            // Loop through the results to find an exact match
            foreach ($data['results'] as $result) {
                if (strtolower($result['title']) === strtolower($title)) {
                    // Add the movie poster and title to the array
                $chatgallery = ChatGallery::where('name', $title)->first();

                    $movies[] = [
                        'id'    => $chatgallery->id,
                        'poster' => 'https://image.tmdb.org/t/p/w500' . $result['poster_path'],
                        'title' => $result['title']
                    ];
                    array_push($foundtitles, $title);
                    break; // Break out of the inner loop once an exact match is found
                }
            }

            if(!in_array($title, $foundtitles)){

                $chatgallery = ChatGallery::where('name', $title)->first();

                $default_files = [
                    "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/721003.png",
                    "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/DD1E25.png",
                    "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/F8B966.png",
                ];

                if(!($test = ChatGalleryFile::where('uuid', $chatgallery->uuid)->first())){
                    $rand_index = rand(0,2);
                    $cover = $default_files[$rand_index];
                }else{
                    $data_available = false;
                    $cover = ChatGalleryFile::where('uuid', $chatgallery->uuid)->first()->cover_photo;
                }

                array_push($movies, [
                    'id'     => $chatgallery->id,
                    'poster' => $cover,
                    'title'  => $title
                ]);



            }

        }

        $movies_list = [];
                for ($i=0; $i < 10; $i++){
                    if(isset($movies[$i])){
                        array_push($movies_list, $movies[$i]);
                    }
                }
        $uniqueArray = [];

        foreach ($movies_list as $item) {
            $id = $item['id'];
            if (!isset($uniqueArray[$id])) {
                $uniqueArray[$id] = $item;
            }
        }

        $array = [];
        foreach($uniqueArray as $array_item){
            array_push($array, $array_item);
        }

        return response()->json($array);



    }

    public function movie_detail(Request $request, $id)
{
    if (!($gallery = ChatGallery::find($id))) {
        return response()->json(['error' => "Live Gallery not found"], 404);
    }
    $gallery = ChatGallery::where('id', $id)->first();

    $subquery = ChatRoomComment::select('commentable_id', DB::raw('MAX(created_at) as latest_comment'))
    ->groupBy('commentable_id');

    $chatRooms = ChatRoom::leftJoinSub($subquery, 'latest_comments', function ($join) {
        $join->on('chat_rooms.id', '=', 'latest_comments.commentable_id');
    })
        ->orderByDesc('latest_comments.latest_comment')
        ->get();

    $chatters = [];
    foreach($chatRooms as $chat_room){
        if($chat_room->chat_gallery_id == $gallery->id){

            $total_comments = ChatRoomComment::where('commentable_id', $chat_room->id)->count();
            array_push($chatters, [
                'id'             => $chat_room->id,
                'name'           => $chat_room->name,
                'total_comments' => $total_comments
            ]);
        }
        if (count($chatters) < 4) {
           // Return the entire array
        } else {
            $chatters = array_slice($chatters, 0, 3); // Return the first 4 elements
        }

    }


    $client = new Client();
    $apiKey = 'caa6891d084141a386ce0d2d1eb8f620';
    $searchedTitle = $gallery->name;

    // Search for movies based on the searched title
    $searchUrl = "https://api.themoviedb.org/3/search/movie?api_key={$apiKey}&query={$searchedTitle}";
    $searchResponse = $client->get($searchUrl);
    $searchResults = json_decode($searchResponse->getBody()->getContents());

    $movies_list = $searchResults->results;
    $movies = [];


    if(count($movies_list) > 0){
        // Loop through the results to find an exact match
        if (strtolower($movies_list[0]->title) === strtolower($searchedTitle)) {
            // Add the movie poster and title to the array
            array_push($movies, $movies_list[0]);
        }
    }

    $limitedMovies = [];

    if (count($movies) > 0) {
        $movie = $movies[0];
        $movieId = $movie->id;

        // Retrieve movie details
        $movieUrl = "https://api.themoviedb.org/3/movie/{$movieId}?api_key={$apiKey}";
        $movieResponse = $client->get($movieUrl);
        $movieDetails = json_decode($movieResponse->getBody()->getContents());

        // Retrieve movie credits
        $creditsUrl = "https://api.themoviedb.org/3/movie/{$movieId}/credits?api_key={$apiKey}";
        $creditsResponse = $client->get($creditsUrl);
        $credits = json_decode($creditsResponse->getBody()->getContents());

        // Extract top 3 cast members
        $castMembers = $credits->cast;
        usort($castMembers, function ($a, $b) {
            return $b->popularity <=> $a->popularity;
        });
        $topCastMembers = array_slice($castMembers, 0, 5);


        $casts = [];
        foreach ($topCastMembers as $castMember) {
            $castMemberDetails = [
                'name'        => $castMember->name,
                'character'   => $castMember->character,
                'profile_pic' => 'https://image.tmdb.org/t/p/w500' . $castMember->profile_path,
            ];
            array_push($casts, $castMemberDetails);
        }

        // Extract top 3 crew members
        $crews = [];
        $crewMembers = $credits->crew;
        $topCrewMembers = array_slice($crewMembers, 0, 5);

        foreach ($topCrewMembers as $crewMember) {
            $crewMemberDetails = [
                'name'        => $crewMember->name,
                'job'         => $crewMember->job,
                'profile_pic' => 'https://image.tmdb.org/t/p/w500' . $crewMember->profile_path,
            ];
            array_push($crews, $crewMemberDetails);
        }

        $synopsisUrl = "https://api.themoviedb.org/3/movie/{$movieId}/translations?api_key={$apiKey}";
        $synopsisResponse = $client->get($synopsisUrl);
        $translations = json_decode($synopsisResponse->getBody()->getContents())->translations;

        $videosUrl = "https://api.themoviedb.org/3/movie/{$movieId}/videos?api_key={$apiKey}";
        $videosResponse = $client->get($videosUrl);
        $videos = json_decode($videosResponse->getBody()->getContents())->results;

        $synopsis = '';
        foreach ($translations as $translation) {
            if (isset($translation->iso_3166_1) && isset($translation->iso_639_1)) {
                if ($translation->iso_3166_1 === 'US' && $translation->iso_639_1 === 'en') {
                    if (isset($translation->data->overview)) {
                        $synopsis = $translation->data->overview;
                    }
                    break;
                }
            }
        }

        $trailer = "";
        $video_count = 0;
        $related_videos = [];
        $usedForTrailer = 1;
        // Retrieve movie trailer or synopsis
        foreach ($videos as $key => $video) {
            if ($video->type === "Trailer") {
                $trailer = $video->key;
                $usedForTrailer = $key;
            }

            if ($video->site === "YouTube" && $video_count < 4 && $key != $usedForTrailer) {
                $video_count += 1;
                $video_url = $video->key;
                array_push($related_videos, $video_url);
            }
        }

        // Format the movie data
        $formattedMovie = [
            'data_available' => true,
            'title'    => $movieDetails->title,
            'poster'   => 'https://image.tmdb.org/t/p/w500' . $movieDetails->poster_path,
            'synopsis' => $synopsis,
            'trailer'  => $trailer,
            'chatters' => $chatters,
            'videos'   => $related_videos,
            'cast'     => $casts,
            'crew'     => $crews,
        ];
    } else {
        $cover = "";
        if ($test = ChatGalleryFile::where('uuid', $gallery->uuid)->first()) {
            $cover = ChatGalleryFile::where('uuid', $gallery->uuid)->first()->cover_photo;
        }
        // Format the movie data
        $formattedMovie = [
            'data_available' => false,
            'title'          => $gallery->name,
            'poster'         => $cover,
            'synopsis'       => '',
            'trailer'        => '',
            'chatters'       => $chatters,
            'videos'         => [],
            'cast'           => [],
            'crew'           => [],
        ];
    }

    return response()->json($formattedMovie);
}


    public function tv_show_detail(Request $request, $id)
{
    if (!($gallery = ChatGallery::find($id))) {
        return response()->json(['error' => "Gallery not found"], 404);
    }

    $gallery = ChatGallery::where('id', $id)->first();
    $subquery = ChatRoomComment::select('commentable_id', DB::raw('MAX(created_at) as latest_comment'))
    ->groupBy('commentable_id');

    $chatRooms = ChatRoom::leftJoinSub($subquery, 'latest_comments', function ($join) {
        $join->on('chat_rooms.id', '=', 'latest_comments.commentable_id');
    })
        ->orderByDesc('latest_comments.latest_comment')
        ->get();

    $chatters = [];
    $unique_users = [];
    foreach($chatRooms as $chat_room){
        $user_id = $chat_room->user_id;
        if(!in_array($user_id, $unique_users) && ($user = User::find($user_id)) ){
            $unique_users[] = $user_id;
        }
    }

    $user_data = [];
    foreach($unique_users as $user_id){
        $username = User::where('id', $user_id)->first()['username'];
        $profile = UserAvatar::where('user_id', $user_id)->first()?->path;
        $user_data[$user_id] = [
            "username" => $username,
            "avatar"   => $profile
        ];
    }


    foreach ($unique_users as $user_id) {
        $username = User::where('id', $user_id)->first()['username'];
        $chatters[$user_id] = [];
        foreach ($chatRooms as $key => $room) {
            if($room->user_id == $user_id){
                if($room->chat_gallery_id == $gallery->id){
                $total_comments = ChatRoomComment::where('commentable_id', $chat_room->id)->count();
                array_push($chatters[$user_id], [
                    'id'             => $room->id,
                    'name'           => $room->name,
                    'total_comments' => $total_comments
                ]);
            }
        }
    }

    }

    if (count($chatters) < 4) {
        // Return the entire array
     } else {
         $chatters = array_slice($chatters, 0, 3); // Return the first 4 elements
     }

    $client = new Client();
    $apiKey = 'caa6891d084141a386ce0d2d1eb8f620';
    $searchedTitle = $gallery->name;

    // Search for TV shows based on the searched title
    $searchUrl = "https://api.themoviedb.org/3/search/tv?api_key={$apiKey}&query={$searchedTitle}";
    $searchResponse = $client->get($searchUrl);
    $searchResults = json_decode($searchResponse->getBody()->getContents());

    $tvShowsList = $searchResults->results;
    $tvShows = [];

    // Loop through the results to find an exact match
    foreach ($tvShowsList as $tvShow) {
        if (strtolower($tvShow->name) === strtolower($searchedTitle)) {
            // Add the TV show details to the array
            $tvShows[] = $tvShow;
        }
    }



    $limitedTVShows = [];

    if (count($tvShows) > 0) {
        $tvShow = $tvShows[0];
        $tvShowId = $tvShow->id;

        // Retrieve TV show details
        $tvShowUrl = "https://api.themoviedb.org/3/tv/{$tvShowId}?api_key={$apiKey}";
        $tvShowResponse = $client->get($tvShowUrl);
        $tvShowDetails = json_decode($tvShowResponse->getBody()->getContents());

        // Retrieve TV show videos
        $videosUrl = "https://api.themoviedb.org/3/tv/{$tvShowId}/videos?api_key={$apiKey}";
        $videosResponse = $client->get($videosUrl);
        $videos = json_decode($videosResponse->getBody()->getContents())->results;

        // Retrieve TV Credits
        $creditsUrl = "https://api.themoviedb.org/3/tv/{$tvShowId}/credits?api_key={$apiKey}";
        $creditsResponse = $client->get($creditsUrl);
        $credits = json_decode($creditsResponse->getBody()->getContents());

        // Extract top 3 cast members
        $castMembers = $credits->cast;
        usort($castMembers, function ($a, $b) {
            return $b->popularity <=> $a->popularity;
        });
        $topCastMembers = array_slice($castMembers, 0, 5);


        $casts = [];
        foreach ($topCastMembers as $castMember) {
            $castMemberDetails = [
                'name'        => $castMember->name,
                'character'   => $castMember->character,
                'profile_pic' => 'https://image.tmdb.org/t/p/w500' . $castMember->profile_path,
            ];
            array_push($casts, $castMemberDetails);
        }

        // Extract top 3 crew members
        $crews = [];
        $crewMembers = $credits->crew;
        $topCrewMembers = array_slice($crewMembers, 0, 5);

        foreach ($topCrewMembers as $crewMember) {
            $crewMemberDetails = [
                'name'        => $crewMember->name,
                'job'         => $crewMember->job,
                'profile_pic' => 'https://image.tmdb.org/t/p/w500' . $crewMember->profile_path,
            ];
            array_push($crews, $crewMemberDetails);
        }

        $trailer = "";
        $video_count = 0;
        $related_videos = [];

        // Retrieve TV show trailer and related videos
        foreach ($videos as $key => $video) {
            if ($video->type === "Trailer") {
                $trailer = $video->key;
            } elseif ($video->site === "YouTube" && $video_count < 4) {
                $video_count += 1;
                $video_url = $video->key;
                $related_videos[] = $video_url;
            }
        }

        // Format the TV show data
        $formattedTVShow = [
            'data_available' => true,
            'title'    => $tvShowDetails->name,
            'poster'   => 'https://image.tmdb.org/t/p/w500' . $tvShowDetails->poster_path,
            'synopsis' => $tvShowDetails->overview,
            'trailer'  => $trailer,
            'user_data'=> $user_data,
            'chatters' => $chatters,
            'videos'   => $related_videos,
            'casts'    => $casts,
            'crews'    => $crews,
        ];
    } else {
        $cover = ChatGalleryFile::where('uuid', $gallery->uuid)->first()->cover_photo;
        // Format the TV show data
        $formattedTVShow = [
            'data_available' => false,
            'title'    => $gallery->name,
            'poster'   => $cover,
            'synopsis' => '',
            'trailer'  => '',
            'user_data'=> $user_data,
            'chatters' => $chatters,
            'videos'   => [],
            'casts'    => [],
            'crews'    => [],
        ];
    }

    return response()->json($formattedTVShow);
}


    public function tvs(Request $request){

        $tv_channel = Channel::where('name', 'TV')->first();
        $fetched_tv_galleries = ChatGallery::where('channel_id', $tv_channel->id)->get();
        $tv_galleries = $fetched_tv_galleries->unique('name', true);
        $tags = Tag::all();
        $tag_list = [];
        foreach($tags as $tag){
            array_push($tag_list, $tag->name);
        }

        $gallery_counts = [];
        foreach($tv_galleries as $gallery){
            $gallery_counts[$gallery->id] = 0;
            if($gallery->chatrooms){

                foreach($gallery->chatrooms as $chatroom){
                    $gallery_counts[$gallery->id] += count($chatroom->allcomments);
                }
            }
        }
        arsort($gallery_counts);

        $titles = [];

        foreach($gallery_counts as $key=>$gallery){
            $gallery = ChatGallery::where('id', $key)->first();

            array_push($titles, $gallery->name);
        }

        // Array to store the movie posters and titles
        $movies = [];
        $client = new Client();
        $apiKey = 'caa6891d084141a386ce0d2d1eb8f620';
        $apiUrl = "https://api.themoviedb.org/3/";
        $foundtitles = [];
        foreach ($titles as $title) {
            // Make a request to TMDB API to search for the movie
            $response = $client->get($apiUrl . 'search/tv', [
                'query' => [
                    'api_key' => $apiKey,
                    'query' => $title,
                ],
            ]);

            // Get the JSON response from the API
            $data = json_decode($response->getBody(), true);

            // Loop through the results to find an exact match
            foreach ($data['results'] as $result) {
                if (strtolower($result['name']) === strtolower($title)) {
                    // Add the movie poster and title to the array
                    $chatgallery = ChatGallery::where('name', $title)->first();
                    $movies[] = [
                        'id' => $chatgallery->id,
                        'poster' => 'https://image.tmdb.org/t/p/w500' . $result['poster_path'],
                        'title' => $result['name']
                    ];
                    array_push($foundtitles, $title);
                    break; // Break out of the inner loop once an exact match is found
                }
            }

            if(!in_array($title, $foundtitles)){
                $chatgallery = ChatGallery::where('name', $title)->first();

                $cover = ChatGalleryFile::where('uuid', $chatgallery->uuid)->first()->cover_photo;
                array_push($movies, [
                    'id'     => $chatgallery->id,
                    'poster' => $cover,
                    'title'  => $title
                ]);
            }
        }

        $movies_list = [];
        for ($i=0; $i < 10; $i++){
            if(isset($movies[$i])){
                array_push($movies_list, $movies[$i]);
            }
        }

        $uniqueArray = [];

        foreach ($movies_list as $item) {
            $id = $item['id'];
            if (!isset($uniqueArray[$id])) {
                $uniqueArray[$id] = $item;
            }
        }

        $array = [];
        foreach($uniqueArray as $array_item){
            array_push($array, $array_item);
        }

        return response()->json($array);


    }

    public function books(Request $request){

        $book_channel = Channel::where('name', 'Books')->first();
        $fetched_book_galleries = ChatGallery::where('channel_id', $book_channel->id)->get();
        $book_galleries = $fetched_book_galleries->unique('name', true);

        $tags = Tag::all();
        $tag_list = [];
        foreach($tags as $tag){
            array_push($tag_list, $tag->name);
        }

        $gallery_counts = [];
        foreach($book_galleries as $gallery){
            $gallery_counts[$gallery->id] = 0;
            if($gallery->chatrooms){
                foreach($gallery->chatrooms as $chatroom){
                    $gallery_counts[$gallery->id] += count($chatroom->allcomments);
                }
            }
        }
        arsort($gallery_counts);

        $titles = [];

        foreach($gallery_counts as $key=>$gallery){
            $gallery = ChatGallery::where('id', $key)->first();

            array_push($titles, $gallery->name);
        }


            // Create a new Guzzle client object.
        $client = new Client();

        $trendingBooks = [];
        foreach($titles as $title){
            // Set the API key.
            $apiKey = env('AIzaSyB4uBSkelS7xwzstLu_3oy7MSdNdvc6ioE');
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

            // Get the name and cover of the top 10 trending books.

                $thumbnail = "";
                if(isset($responseData['items'][0])){

                        $name = $responseData['items'][0]['volumeInfo']['title'];
                        //Retrieve thumbnail if exists
                        if( isset($responseData['items'][0]['volumeInfo']['imageLinks'])){
                            $thumbnail = $responseData['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
                        }else{
                            $colors = ["F8B966","DD1E25","721003"];
                            $rand_key = array_rand($colors, 1);
                            $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
                            $thumbnail = $random_cover;
                        }

                }else{
                    $name = $title;
                    //Show random cover page if the books has no thumbnail
                    $colors = ["F8B966","DD1E25","721003"];
                    $rand_key = array_rand($colors, 1);
                    $random_cover = "https://breakdown-bucket.s3.amazonaws.com/chat_galleries/gallery_covers/".$colors[$rand_key].".png";
                    $thumbnail = $random_cover;
                }

                $chatgallery = ChatGallery::where('name', $title)->first();

                $formattedBookDetails = [
                    'id' => $chatgallery->id,
                    'name' => $name,
                    'cover' => $thumbnail
                ];
                array_push($trendingBooks, $formattedBookDetails);

        }

        // Return 10 trending books.
        $trendingBooks = array_slice($trendingBooks, 0, 10);

        return response()->json($trendingBooks);
    }


    public function book_detail(Request $request, $id)
    {
        if (!($gallery = ChatGallery::find($id))) {
            return response()->json(['error' => "Gallery not found"], 404);
        }
        $data_available = true;

        $gallery = ChatGallery::where('id', $id)->first();
        $subquery = ChatRoomComment::select('commentable_id', DB::raw('MAX(created_at) as latest_comment'))
            ->groupBy('commentable_id');

            $chatRooms = ChatRoom::leftJoinSub($subquery, 'latest_comments', function ($join) {
                $join->on('chat_rooms.id', '=', 'latest_comments.commentable_id');
            })
                ->orderByDesc('latest_comments.latest_comment')
                ->get();

            $chatters = [];
            foreach($chatRooms as $chat_room){
                if($chat_room->chat_gallery_id == $gallery->id){
                $total_comments = ChatRoomComment::where('commentable_id', $chat_room->id)->count();
                    array_push($chatters, [
                        'id'             => $chat_room->id,
                        'name'           => $chat_room->name,
                        'total_comments' => $total_comments
                    ]);
                }
            }

            if (count($chatters) < 4) {
                // Return the entire array
             } else {
                 $chatters = array_slice($chatters, 0, 3); // Return the first 4 elements
             }


        $bookTitle = $gallery->name;

        // Create a new Guzzle client
        $client = new Client();

        $endpointUrl = "https://www.googleapis.com/books/v1/volumes?q={$bookTitle}";
        // Set the API key.
        $apiKey = env('GOOGLE_BOOKS_API_KEY');

        // Make a request to the Google Books API
        $response = $client->request('GET', $endpointUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
            ],
        ]);

        // Get the JSON response body
        $data = json_decode($response->getBody()->getContents(), true);

        // Check if any books were found
        if (isset($data['items']) && count($data['items']) > 0) {
            // Get the first book from the search results
            $book = $data['items'][0]['volumeInfo'];

            // Retrieve the book details
            $title = $book['title'];
            $poster = isset($book['imageLinks']['thumbnail']) ? $book['imageLinks']['thumbnail'] : null;
            $synopsis = isset($book['description']) ? $book['description'] : null;
            $aboutAuthor = isset($book['authors']) ? implode(', ', $book['authors']) : null;

            // Retrieve author information
            $author = isset($book['authors'][0]) ? $book['authors'][0] : null;
            $authorBio = null;
            if ($author) {
                $authorResponse = $client->get("https://www.googleapis.com/books/v1/volumes?q=inauthor:{$author}&maxResults=1");
                $authorData = json_decode($authorResponse->getBody()->getContents(), true);
                if (isset($authorData['items'][0]['volumeInfo']['description'])) {
                    $authorBio = $authorData['items'][0]['volumeInfo']['description'];
                }
            }

            // Retrieve similar books by the author
            $similarBooks = [];
            if ($author) {
                $similarBooksResponse = $client->get("https://www.googleapis.com/books/v1/volumes?q=inauthor:{$author}&maxResults=5");
                $similarBooksData = json_decode($similarBooksResponse->getBody()->getContents(), true);
                if (isset($similarBooksData['items'])) {
                    foreach ($similarBooksData['items'] as $similarBook) {
                        $similarTitle = $similarBook['volumeInfo']['title'];
                        $similarPoster = isset($similarBook['volumeInfo']['imageLinks']['thumbnail']) ? $similarBook['volumeInfo']['imageLinks']['thumbnail'] : null;
                        $similarBooks[] = [
                            'title' => $similarTitle,
                            'poster' => $similarPoster,
                        ];
                    }
                }
            }

            // Retrieve other books by the author
            $otherBooks = [];
            if ($author) {
                $otherBooksResponse = $client->get("https://www.googleapis.com/books/v1/volumes?q=inauthor:{$author}&maxResults=5");
                $otherBooksData = json_decode($otherBooksResponse->getBody()->getContents(), true);
                if (isset($otherBooksData['items'])) {
                    foreach ($otherBooksData['items'] as $otherBook) {
                        $otherTitle = $otherBook['volumeInfo']['title'];
                        $otherPoster = isset($otherBook['volumeInfo']['imageLinks']['thumbnail']) ? $otherBook['volumeInfo']['imageLinks']['thumbnail'] : null;
                        $otherBooks[] = [
                            'title' => $otherTitle,
                            'poster' => $otherPoster,
                            'bookstore_link' => isset($otherBook['volumeInfo']['infoLink']) ? $otherBook['volumeInfo']['infoLink'] : null,
                            'amazon_link' => isset($otherBook['volumeInfo']['industryIdentifiers'][0]['identifier']) ? 'https://www.amazon.com/dp/' . $otherBook['volumeInfo']['industryIdentifiers'][0]['identifier'] : null,
                        ];
                    }
                }
            }

              // Create a new Guzzle client
    // $client = new \GuzzleHttp\Client();

    // // Format the search query
    // $formattedTitle = urlencode($bookTitle);
    // $endpointUrl = "https://www.amazon.com/s?k={$formattedTitle}";

    // // Make a request to Amazon
    // $response = $client->request('GET', $endpointUrl);

    // // Get the HTML response
    // $html = $response->getBody()->getContents();
    // $book_link = "";

    // // Parse the HTML to extract the book link
    // $dom = new \DOMDocument();
    // @$dom->loadHTML($html);

    // // Find the first search result item
    // $searchResultItems = $dom->getElementById('search')
    //     ->getElementsByTagName('div');
    //         foreach ($searchResultItems as $item) {
    //             $class = $item->getAttribute('class');
    //             if (strpos($class, 's-result-item') !== false) {
    //                 // Get the book link
    //                 $linkElement = $item->getElementsByTagName('a')->item(0);
    //                 if ($linkElement) {
    //                     $bookLink = $linkElement->getAttribute('href');
    //                     $book_link = $bookLink;
    //                 }
    //             }
    //         }

        $book_link = "Amazon book link"; // Book link not found

        if(!isset($poster)){
            $data_available = false;
        }
        // Format the book details
        $bookDetails = [
            'data_available' => $data_available,
            'title' => $title,
            'poster' => $poster,
            'chatter' => $chatters,
            'bookstore_link' => isset($book['infoLink']) ? $book['infoLink'] : null,
            'amazon_link' => $book_link,
            'synopsis' => $synopsis,
            'about_author' => [
                'name' => $author,
                'biography' => $authorBio,
            ],
            'similar_books' => $similarBooks,
            'other_books_by_author' => $otherBooks,

        ];

            return response()->json($bookDetails);
        }

        // No books found with the given title
        return response()->json(['error' => 'Book not found'], 404);
    }


}
