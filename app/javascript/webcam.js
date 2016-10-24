(function () {
    'use strict';

    var mediaStream = null;
    var webcamList;
    var currentCam = null;
    var photoReady = false;

    // writeError(string) - Provides a way to display errors to the user

    var writeError = function (string) {
        var elem = document.getElementById('error');
        var p = document.createElement('div');
        p.appendChild(document.createTextNode('ERROR: ' + string));
        elem.appendChild(p);
    };

    // initializeVideoStream() - Callback function when getUserMedia() returns successfully with a mediaStream object,
    // set the mediaStream on the video tag

    var initializeVideoStream = function (stream) {
        mediaStream = stream;

        var video = document.getElementById('videoTag');
        video.srcObject = mediaStream;

        if (video.paused) video.play();

        document.getElementById('videoViewText').innerHTML = 'Click or tap below to take a snapshot';

        if (webcamList.length > 1) {
            document.getElementById('switch').disabled = false;
        } else {
            document.getElementById('switch').style.display = 'none';
        }
    };

    // getUserMediaError() - Callback function when getUserMedia() returns error
    // 1. Show the error message with the error.name

    var getUserMediaError = function (e) {
        if (e.name.indexOf('NotFoundError') >= 0) {
            writeError('Webcam not found.');
        }
        else {
            writeError('The following error occurred: "' + e.name + '" Please check your webcam device(s) and try again.');
        }
    };

    // uploadPhoto() - Function invoked when click on the canvas element

    var uploadPhoto = function () {
        if (photoReady) {
            var canvas = document.getElementById('canvasTag');
            
            if (canvas.toBlob) {
                canvas.toBlob(
                    function (blob) {
                        // Do something with the blob object,
                        // e.g. creating a multipart form for file uploads:
                        /* ... */
                        var imgData = blob;
                        var imgUrl = canvas.toDataURL();

                        //Display analyzed data
                        result.innerText = "Analyzing";
                        loading.style.display = "inline-block";
                        $('#photo1').attr('src', imgUrl);

                        //Hide Webcam stuff
                        document.getElementById('democontent').style.display = 'none';
                        document.getElementById('switch').style.display = 'none';
                        var video = document.getElementById('videoTag');
                        if (typeof (video.srcObject) !== 'undefined') video.srcObject = null;
                        video.src = null;
                        if (mediaStream) {
                            var videoTracks = mediaStream.getVideoTracks();
                            videoTracks[0].stop();
                            mediaStream = null;
                        }
                        var data = new FormData();
                        data.append('file', imgData);

                        $.ajax({
                            type: 'POST',
                            url: "api.php",
                            data: data,
                            processData: false,
                            contentType: false
                        })
                        .done(function (response) {

                            loading.style.display = "none";

                            if (response.error != "") {
                                result.innerText = response.error;
                            }
                            else {
                                result.innerHTML = response.result;

                                //TODO: display the computed result instead
                                //$('#photo2').attr('src', 'Photos/' + response.result);
                            }

                        })
                        .fail(function (response) {
                            console.log("Ajax Call Failed:");
                            console.log(response);
                        })
                        .complete(function (response) {
                            restartButton.style.display = 'inline-block';
                        });
                        canvas.removeEventListener('click', uploadPhoto);
                        document.getElementById('photoViewText').innerHTML = '';
                        photoReady = false;
                    },
                    'image/jpeg'
                );
            }
        }
    };

    // capture() - Function called when click on video tag
    // 1. Capture a video frame from the video tag and render on the canvas element

    var capture = function () {

        if (!mediaStream) {
            return;
        }

        var video = document.getElementById('videoTag');
        var canvas = document.getElementById('canvasTag');
        canvas.removeEventListener('click', uploadPhoto);
        var videoWidth = video.videoWidth;
        var videoHeight = video.videoHeight;

        if (canvas.width !== videoWidth || canvas.height !== videoHeight) {
            canvas.width = videoWidth;
            canvas.height = videoHeight;
        }

        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
        photoReady = true;
        document.getElementById('photoViewText').innerHTML = 'Click or tap below to analyze your picture';
        canvas.addEventListener('click', uploadPhoto);

    };

    // nextWebCam() - Function to rotate through the webcam device list
    // 1. Release the current webcam (if there is one in use)
    // 2. Call getUserMedia() to access the next webcam

    var nextWebCam = function () {
        document.getElementById('switch').disabled = true;
        if (currentCam !== null) {
            currentCam++;
            if (currentCam >= webcamList.length) {
                currentCam = 0;
            }
            var video = document.getElementById('videoTag');
            if (typeof (video.srcObject) !== 'undefined') video.srcObject = null;
            video.src = null;
            if (mediaStream) {
                var videoTracks = mediaStream.getVideoTracks();
                videoTracks[0].stop();
                mediaStream = null;
            }
        }
        else {
            currentCam = 0;
        }

        navigator.mediaDevices.getUserMedia({
            video: {
                width: 640,
                height: 360,
                deviceId: { exact: webcamList[currentCam] }
            }
        }).then(initializeVideoStream).catch(getUserMediaError);
    };

    // enumerateMediaDevices() - function to start enumerateDevices() and define the callback functions

    var enumerateMediaDevices = function () {
        /*eslint-disable*/
        navigator.mediaDevices.enumerateDevices().then(devicesCallback).catch(getUserMediaError);
        /*eslint-enable*/
    };

    // deviceChanged() - Handle devicechange event
    // 1. Reset webcamList
    // 2. Re-enumerate webcam devices

    var deviceChanged = function () {
        navigator.mediaDevices.removeEventListener('devicechange', deviceChanged);
        // Reset the webcam list and re-enumerate
        webcamList = [];
        enumerateMediaDevices();
    };

    // devicesCallback() - Callback function for device enumeration
    // 1. Identify all webcam devices and store the info in the webcamList
    // 2. Start the demo with the first webcam on the list
    // 3. Show the webcam 'switch' button when there are multiple webcams
    // 4. Show error message when there is no webcam
    // 5. Register event listener (devicechange) to respond to device plugin or unplug

    var devicesCallback = function (devices) {
        // Identify all webcams
        webcamList = [];
        for (var i = 0; i < devices.length; i++) {
            if (devices[i].kind === 'videoinput') {
                webcamList[webcamList.length] = devices[i].deviceId;
            }
        }

        if (webcamList.length > 0) {
            // Start video with the first device on the list
            nextWebCam();
            if (webcamList.length > 1) {
                document.getElementById('switch').disabled = false;
            }
            else {
                document.getElementById('switch').disabled = true;
            }
        }
        else {
            writeError('Webcam not found.');
        }
        navigator.mediaDevices.addEventListener('devicechange', deviceChanged);
    };

    // demoSetup() - function to start the Media Capture API
    // 1. Enumerate the webcam devices
    // 2. Set up event listner for the webcam 'switch' button

    var demoSetup = function () {

        document.getElementById('videoTag').addEventListener('click', capture, false);
        document.getElementById('start').style.display = 'none';
        document.getElementById('switch').style.display = '';
        document.getElementById('democontent').style.display = '';

        enumerateMediaDevices();
        document.getElementById('switch').addEventListener('click', nextWebCam, false);
    };

    var demoEnd = function () {

    }

    // init() - The entry point to the demo code
    // 1. Detect whether getUserMedia() is supported, show an error if not

    var init = function () {
        if (navigator.getUserMedia) {
            document.getElementById('start').style.display = 'inline-block';
            document.getElementById('start').addEventListener('click', demoSetup, false);
        }
        else {
            writeError('You are using a browser that does not support the Media Capture API');
        }
    };

    init();

}());