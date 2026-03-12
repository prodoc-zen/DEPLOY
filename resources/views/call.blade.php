<!DOCTYPE html>
<html>
    <head>
    <title>Laravel WebRTC Video Call</title>
        <style>
            video{
            width:45%;
            border:2px solid black;
            }
        </style>
        </head>
    <body>

        <h2>Laravel WebRTC Demo</h2>

        <input id="room" placeholder="Room name">
        <button onclick="startCall()">Start Call</button>
        <button onclick="joinCall()">Join Call</button>

        <br><br>

        <video id="localVideo" autoplay muted></video>
        <video id="remoteVideo" autoplay></video>

        <script>

            let pc;
            let localStream;

            const configuration = {
            iceServers:[
            {urls:"stun:stun.l.google.com:19302"}
            ]
            };

            async function init(){

            pc = new RTCPeerConnection(configuration);

            localStream = await navigator.mediaDevices.getUserMedia({
            video:true,
            audio:true
            });

            document.getElementById("localVideo").srcObject = localStream;

            localStream.getTracks().forEach(track=>{
            pc.addTrack(track,localStream);
            });

            pc.ontrack = e=>{
            document.getElementById("remoteVideo").srcObject = e.streams[0];
            };

            }

            async function startCall(){

            await init();

            let room=document.getElementById("room").value;

            const offer=await pc.createOffer();

            await pc.setLocalDescription(offer);

            await fetch('/offer',{
            method:'POST',
            headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body:JSON.stringify({
            room:room,
            offer:offer
            })
            });

            setInterval(checkAnswer,2000);

            }

            async function checkAnswer(){

            let room=document.getElementById("room").value;

            let res=await fetch('/answer/'+room);

            let answer=await res.json();

            if(answer){

            await pc.setRemoteDescription(answer);

            }

            }

            async function joinCall(){

            await init();

            let room=document.getElementById("room").value;

            let res=await fetch('/offer/'+room);

            let offer=await res.json();

            await pc.setRemoteDescription(offer);

            const answer=await pc.createAnswer();

            await pc.setLocalDescription(answer);

            await fetch('/answer',{
            method:'POST',
            headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body:JSON.stringify({
            room:room,
            answer:answer
            })
            });

            }

        </script>

    </body>
</html>