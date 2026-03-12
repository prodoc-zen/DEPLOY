<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agora\RtcTokenBuilder2 as RtcTokenBuilder;

class AgoraController extends Controller
{
    public function token(Request $request)
    {
        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE'); // optional if certificate disabled

        $channelName = $request->channel;
        $uid = rand(1, 999999);
        $role = RtcTokenBuilder::ROLE_PUBLISHER;
        $expireTime = 3600; // 1 hour
        $privilegeExpiredTs = now()->timestamp + $expireTime;

        $token = RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpiredTs
        );

        return response()->json([
            "token" => $token,
            "uid" => $uid
        ]);
    }
}