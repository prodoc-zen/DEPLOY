<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebRTCController extends Controller
{

    public function index()
    {
        return view('call');
    }

    public function storeOffer(Request $request)
    {
        Cache::put("offer_" . $request->room, $request->offer, 300);
        return response()->json(['status'=>'ok']);
    }

    public function getOffer($room)
    {
        return Cache::get("offer_" . $room);
    }

    public function storeAnswer(Request $request)
    {
        Cache::put("answer_" . $request->room, $request->answer, 300);
        return response()->json(['status'=>'ok']);
    }

    public function getAnswer($room)
    {
        return Cache::get("answer_" . $room);
    }

}