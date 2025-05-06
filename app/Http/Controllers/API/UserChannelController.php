<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;

class UserChannelController extends BaseController
{
    //
    public function index(Request $request)
    {
        $success['channels'] = Channel::all();
        return $this->handleResponse($success, '');
    }
}
