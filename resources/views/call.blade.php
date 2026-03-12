<!DOCTYPE html>
<html>
<head>
    <title>Laravel Agora Video Call</title>
    <style>
        #videos { display:flex; flex-wrap:wrap; gap:10px; }
        .video-box { width:45%; height:300px; border:2px solid black; }
    </style>
</head>
<body>

<h2>Video Call</h2>

<input id="channel" placeholder="Channel name" value="test">
<button id="join">Join</button>
<button id="leave">Leave</button>

<div id="videos">
    <!-- Local video will be added here -->
</div>

<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.17.1.js"></script>
<script>
let client = null;
let localTracks = [];
let uid = 0;

function createVideoContainer(userId, isLocal=false){
    const container = document.createElement("div");
    container.id = `video-${userId}`;
    container.className = "video-box";
    container.textContent = isLocal ? `Local User ${userId}` : `Remote User ${userId}`;
    document.getElementById("videos").append(container);
    return container;
}

async function joinCall(){
    const channel = document.getElementById("channel").value;

    // 1️⃣ Fetch token from Laravel
    const response = await fetch(`/agora-token?channel=${channel}`);
    const data = await response.json();
    const token = data.token;
    uid = data.uid;

    // 2️⃣ Create Agora client
    client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

    // 3️⃣ Join channel
    await client.join("{{ env('AGORA_APP_ID') }}", channel, token, uid);

    // 4️⃣ Create local audio/video tracks
    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks();
    const localContainer = createVideoContainer(uid, true);
    localTracks[1].play(localContainer);

    // 5️⃣ Publish local tracks
    await client.publish(localTracks);

    // 6️⃣ Subscribe to all existing users (before you joined)
    for(let id in client.remoteUsers){
        subscribeToUser(client.remoteUsers[id]);
    }

    // 7️⃣ Subscribe to new users
    client.on("user-published", async (user, mediaType) => {
        await client.subscribe(user, mediaType);
        subscribeToUser(user, mediaType);
    });

    client.on("user-unpublished", (user) => {
        const el = document.getElementById(`video-${user.uid}`);
        el && el.remove();
    });
}

function subscribeToUser(user, mediaType=null){
    if(!mediaType) mediaType = "video";
    const container = createVideoContainer(user.uid);
    if(mediaType === "video") user.videoTrack.play(container);
    if(mediaType === "audio") user.audioTrack.play();
}

async function leaveCall(){
    // Stop local tracks
    localTracks.forEach(track => track.close());

    // Remove all video containers
    document.getElementById("videos").innerHTML = "";

    // Leave channel
    if(client){
        await client.leave();
        client = null;
    }
}

// Button handlers
document.getElementById("join").onclick = joinCall;
document.getElementById("leave").onclick = leaveCall;

</script>

</body>
</html>