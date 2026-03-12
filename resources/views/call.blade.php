<!DOCTYPE html>
<html>
<head>
    <title>Laravel Agora Video Call</title>
    <style>
        #videos {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .video-box {
            width: 45%;
            height: 300px;
            border: 2px solid black;
            position: relative;
            background: #000;
            color: white;
            font-size: 14px;
        }
    </style>
</head>
<body>

<h2>Video Call</h2>

<input id="channel" placeholder="Channel name" value="test">
<button id="join">Join</button>
<button id="leave">Leave</button>

<div id="videos">
    <!-- Local and remote videos will be appended here -->
</div>

<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.17.1.js"></script>
<script>
    let client = null;
let localTracks = [];
let uid = 0;

// Create a video container dynamically
function createVideoContainer(userId, isLocal = false) {
    let container = document.getElementById(`video-${userId}`);
    if (container) return container; // avoid duplicates
    
    container = document.createElement("div");
    container.id = `video-${userId}`;
    container.className = "video-box";
    container.textContent = isLocal ? `Local User ${userId}` : `Remote User ${userId}`;
    document.getElementById("videos").append(container);
    return container;
}

// Play the tracks once subscribed
function subscribeToUser(user, mediaType = "video") {
    const container = createVideoContainer(user.uid);
    
    // Safety check: ensure the track exists before trying to play it
    if (mediaType === "video" && user.videoTrack) {
        user.videoTrack.play(container);
    }
    if (mediaType === "audio" && user.audioTrack) {
        user.audioTrack.play();
    }
}

// Join the call
async function joinCall() {
    const channel = document.getElementById("channel").value;

    // 1️⃣ Fetch token from Laravel backend
    const response = await fetch(`/agora-token?channel=${channel}`);
    const data = await response.json();
    const token = data.token;
    uid = data.uid;

    // 2️⃣ Create Agora client
    client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

    // 3️⃣ Register event listeners BEFORE joining the channel
    // This ensures you catch the events for users who are ALREADY in the room
    client.on("user-published", async (user, mediaType) => {
        // Subscribe to the remote user's track
        await client.subscribe(user, mediaType);
        // Play the newly subscribed track
        subscribeToUser(user, mediaType);
    });

    client.on("user-unpublished", user => {
        const el = document.getElementById(`video-${user.uid}`);
        if (el) el.remove();
    });

    // 4️⃣ Join the channel
    await client.join("{{ env('AGORA_APP_ID') }}", channel, token, uid);

    // 5️⃣ Create and publish local tracks
    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks();
    const localContainer = createVideoContainer(uid, true);
    localTracks[1].play(localContainer); // Play local video
    await client.publish(localTracks);   // Publish to channel
}

// Leave the call
async function leaveCall() {
    if (localTracks.length > 0) {
        localTracks.forEach(track => {
            track.stop();
            track.close();
        });
        localTracks = [];
    }
    
    document.getElementById("videos").innerHTML = "";
    
    if (client) {
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