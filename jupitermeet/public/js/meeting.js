(function() {
    'use strict';

    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    const audioInputSelect = document.querySelector('select#audioSource');
    const videoInputSelect = document.querySelector('select#videoSource');
    const selectors = [audioInputSelect, videoInputSelect];

    let socket;
    let constraints;
    let localStream;
    let meetingType;
    let currentMeetingTime;
    let layoutContainer = document.getElementById('videos');
    let layout = initLayoutContainer(layoutContainer).layout;

    let uploader;
    let screenStream;
    let localVideoTrack;
    let mouseMoveTimer;
    let displayFileUrl;
    let resizeTimeout;
    let connections = [];
    let usernames = [];
    let settings = {};
    let configuration = {};
    let facingMode = 'user';
    let micMuted = false;
    let videoMuted = false;
    let screenShared = false;
    let inviteMessage = 'Hey there! Join me for a meeting at this link: ';
    let timer = new easytimer.Timer();
    let notificationTone = new Audio('/sounds/notification.mp3');

    //get the details
    (function() {
        $.ajax({
                url: "/get-details",
            })
            .done(function(data) {
                data = JSON.parse(data);

                if (data.success) {
                    settings = data.data;

                    initializeSocket(settings.signalingURL);
                    $("#joinMeeting").attr('disabled', false);

                    configuration = {
                        iceServers: [{
                                urls: settings.stunUrl,
                            },
                            {
                                urls: settings.turnUrl,
                                username: settings.turnUsername,
                                credential: settings.turnPassword,
                            },
                        ],
                    };
                } else {
                    showError("Could not get the session details.");
                }
            })
            .catch(function() {
                showError("Could not get the session details.");
            });
    })();

    //connect to the signaling server and add listeners
    function initializeSocket(signalingURL) {
        socket = io.connect(signalingURL);
        uploader = new SocketIOFileUpload(socket);

        //handle socket file event
        socket.on("file", function(data) {
            if ($(".chat-panel").is(":hidden")) {
                $("#openChat").addClass("notify");
                showOptions();
                notificationTone.play();
            }
            appendFile(data.file, data.extension, data.username, false);
        });

        //listen for socket message event and handle it
        socket.on('message', function(data) {
            data = JSON.parse(data);

            switch (data.type) {
                case 'join':
                    handleJoin(data);
                    break;
                case 'offer':
                    handleOffer(data);
                    break;
                case 'answer':
                    handleAnswer(data);
                    break;
                case 'candidate':
                    handleCandidate(data);
                    break;
                case 'leave':
                    handleLeave(data);
                    break;
                case 'checkMeetingResult':
                case 'permissionResult':
                    checkMeetingResult(data);
                    break;
                case 'meetingMessage':
                    handlemeetingMessage(data);
                    break;
                case 'permission':
                    handlePermission(data);
                    break;
                case 'info':
                    showInfo(data.message);
                    break;
                case 'kick':
                    showInfo('You have been kicked out of the meeting!');
                    reload(0);
                    break;
                case 'currentTime':
                    //update the timer if the user joins an existing room
                    timer.stop();
                    timer.start({
                        precision: 'seconds',
                        startValues: {
                            seconds: data.currentTime,
                        },
                        target: {
                            seconds: settings.timeLimit * 60 - 60,
                        },
                    });
                    break;
            }
        });

        //listen on sendFile button click event
        uploader.listenOnSubmit($('#sendFile')[0], $('#file')[0]);

        //start file upload
        uploader.addEventListener('start', function(event) {
            event.file.meta.extension = event.file.name.substring(event.file.name.lastIndexOf('.'));
            event.file.meta.username = userInfo.username;
            showInfo('Uploading the file...');
        });

        //append file when file upload is completed
        uploader.addEventListener('complete', function(event) {
            appendFile(event.detail.file, event.detail.extension, null, true);
        });

        //handle file upload error
        uploader.addEventListener('error', function(event) {
            showError(event.message);
        });

        //get username for guest users
        if (!userInfo.username) userInfo.username = username.value = localStorage.getItem('username') || settings.defaultUsername;

        //get item from localStorage and set to html
        muteCamera.checked = localStorage.getItem('muteCamera') === 'true';
        videoQualitySelect.value = localStorage.getItem('videoQuality') || 'VGA';
        if (passwordRequired) password.value = localStorage.getItem('password');
    }

    //listen for timer update event and display during the meeting
    timer.addEventListener('secondsUpdated', function() {
        currentMeetingTime = timer.getTimeValues().minutes * 60 + timer.getTimeValues().seconds;
        $('#timer').html(getCurrentTime());
    });

    //start the timer for last one minute and end the meeting after that
    timer.addEventListener('targetAchieved', function() {
        $('#timer').css('color', 'red');
        timer.stop();
        timer.start({
            precision: 'seconds',
            startValues: {
                seconds: currentMeetingTime,
            },
        });
        setTimeout(function() {
            showInfo('Meeting ended!');
            reload(1);
        }, 60 * 1000);
    });

    //ajax call to check password, continue to meeting if valid
    $('#passwordCheck').on('submit', function(e) {
        e.preventDefault();
        $('#joinMeeting').attr('disabled', true);

        //show an error if the signaling server is not connected
        if (!socket.connected) {
            showError('Could not connect to the server, please try again later.');
            $('#joinMeeting').attr('disabled', false);
            return;
        }

        if (passwordRequired) {
            $.ajax({
                    url: '/check-meeting-password',
                    data: $(this).serialize(),
                    type: 'post',
                })
                .done(function(data) {
                    data = JSON.parse(data);
                    $('#joinMeeting').attr('disabled', false);

                    if (data.success) {
                        continueToMeeting();
                    } else {
                        showError('The password is invalid');
                    }
                })
                .catch(function() {
                    showError();
                    $('#joinMeeting').attr('disabled', false);
                });
        } else {
            continueToMeeting();
        }
    });

    //set details into localStorage and notify server to check meeting status
    function continueToMeeting() {
        meetingType = muteCamera.checked ? 'audio' : 'video';

        //set details to the localstorage
        localStorage.setItem('muteCamera', $('#muteCamera').is(':checked'));
        if (passwordRequired) localStorage.setItem('password', password.value);

        userInfo.username = username.value;
        localStorage.setItem('username', userInfo.username);

        //check if the meeting is full or not
        sendMessage({
            type: 'checkMeeting',
            username: userInfo.username,
            meetingId: userInfo.meetingId,
            moderator: isModerator,
            authMode: settings.authMode,
            moderatorRights: settings.moderatorRights,
        });
    }

    //stringify the data and send it to opponent
    function sendMessage(data) {
        socket.emit('message', JSON.stringify(data));
    }

    //get current meeting time in readable format
    function getCurrentTime() {
        return timer.getTimeValues().toString(['hours', 'minutes', 'seconds']);
    }

    //reload after a specific seconds
    function reload(seconds) {
        setTimeout(function() {
            window.location.reload();
        }, seconds * 1000);
    }

    //check meeting request
    async function checkMeetingResult(data) {
        if (data.result) {
            //the room has space, get the media and initiate the meeting
            constraints = {
                audio: getAudioConstraints(),
                video: getVideoConstraints(),
            };

            try {
                //get user media
                localStream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (e) {
                //show an error if the media device is not available
                showError('Could not get the devices, please check the permissions and try again. Error: ' + e.name);
            }

            //init the meeting if media is available
            if (localStream) {
                init();
            }
        } else {
            //there is an error, show it to the user
            showError(data.message);
            $('#joinMeeting').attr('disabled', false);
        }
    }

    //notify the moderator for new request
    function handlePermission(data) {
        toastr.info(
            '<br><button type="button" class="btn btn-primary btn-sm clear approve" data-from="' +
            data.fromSocketId +
            '">Approve</button><button type="button" class="btn btn-warning btn-sm clear ml-2 decline" data-from="' +
            data.fromSocketId +
            '">Decline</button>',
            data.username + ' has request to join the meeting.', {
                tapToDismiss: false,
                timeOut: 0,
                extendedTimeOut: 0,
                newestOnTop: false,
            }
        );
    }

    //notify participant about the request aapproval
    $(document).on('click', '.approve', function() {
        $(this).closest('.toast').remove();
        sendMessage({
            type: 'permissionResult',
            result: true,
            toSocketId: $(this).data('from'),
        });
    });

    //notify participant about the request rejection
    $(document).on('click', '.decline', function() {
        $(this).closest('.toast').remove();
        sendMessage({
            type: 'permissionResult',
            result: false,
            toSocketId: $(this).data('from'),
            message: 'Your request has been declined by the moderator.',
        });
    });

    //initiate meeting
    function init() {
        $('.meeting-details, .navbar, footer').hide();
        $('.meeting-section').show();
        $('.local-user-name').text(userInfo.username);
        localVideo.srcObject = localStream;
        layout();
        sendMessage({
            type: 'join',
            username: userInfo.username,
            meetingId: userInfo.meetingId,
            moderator: isModerator,
        });
        if (limitedTimeMeeting) {
            //start with a time limit for limited time meeting
            timer.start({ precision: 'seconds', startValues: { seconds: 0 }, target: { seconds: settings.timeLimit * 60 - 60 } });
        } else {
            //start with no time limit
            timer.start({ precision: 'seconds', startValues: { seconds: 0 } });
        }
        manageOptions();
        if (isMobile && meetingType === 'video') $('#toggleCam').show();
        if (!isMobile) $('.updateDevices').show();
        initKeyShortcuts();
        if (!localStorage.getItem('tripDone')) {
            setTimeout(function() {
                showInfo('Double click on the video to make it fullscreen!');
                showInfo('Single click on the video to turn picture-in-picture mode on.');
                localStorage.setItem('tripDone', true);
            }, 3000);
        }
    }

    //hide/show certain meeting related details
    function manageOptions() {
        $('.meeting-options').show();
        $('#meetingIdInfo').html(userInfo.meetingId);
        localStorage.setItem('videoQuality', videoQualitySelect.value);

        if (meetingType === 'video') {
            $('#toggleVideo').show();
        }

        setTimeout(function() {
            hideOptions();
        }, 3000);

        $('body').mousemove(function() {
            showOptions();
        });
    }

    //hide meeting ID and options
    function hideOptions() {
        $('.meeting-options, .local-user-name, .remote-user-name, .meeting-info, .kick').hide();
    }

    //show meeting ID and options
    function showOptions() {
        $('.meeting-options, .local-user-name, .remote-user-name, .meeting-info, .kick').show();

        if (mouseMoveTimer) {
            clearTimeout(mouseMoveTimer);
        }

        mouseMoveTimer = setTimeout(function() {
            hideOptions();
        }, 3000);
    }

    //create and send an offer for newly joined user
    function handleJoin(data) {
        usernames[data.socketId] = data.username;

        //initialize a new connection
        let connection = new RTCPeerConnection(configuration);
        connections[data.socketId] = connection;

        setupListeners(connection, data.socketId, data.uuid);

        connection
            .createOffer({
                offerToReceiveVideo: true,
            })
            .then(function(offer) {
                return connection.setLocalDescription(offer);
            })
            .then(function() {
                sendMessage({
                    type: 'offer',
                    sdp: connection.localDescription,
                    username: userInfo.username,
                    fromSocketId: socket.id,
                    toSocketId: data.socketId,
                    uuid: userInfo.uuid,
                });
            })
            .catch(function(e) {
                console.log('An error occurred: ', e);
            });
    }

    //handle offer from initiator, create and send an answer
    function handleOffer(data) {
        usernames[data.fromSocketId] = data.username;

        //initialize a new connection
        let connection = new RTCPeerConnection(configuration);
        connections[data.fromSocketId] = connection;

        connection.setRemoteDescription(data.sdp);
        setupListeners(connection, data.fromSocketId, data.uuid);

        connection
            .createAnswer()
            .then(function(answer) {
                setDescriptionAndSendAnswer(answer, data.fromSocketId);
            })
            .catch(function(e) {
                console.log(e);
            });
    }

    //set local description and send the answer
    function setDescriptionAndSendAnswer(answer, fromSocketId) {
        connections[fromSocketId].setLocalDescription(answer);
        sendMessage({
            type: 'answer',
            answer: answer,
            fromSocketId: socket.id,
            toSocketId: fromSocketId,
        });
    }

    //handle answer and set remote description
    function handleAnswer(data) {
        let currentConnection = connections[data.fromSocketId];
        if (currentConnection) {
            currentConnection.setRemoteDescription(data.answer);
        }
    }

    //handle candidate and add ice candidate
    function handleCandidate(data) {
        let currentConnection = connections[data.fromSocketId];
        if (data.candidate && currentConnection) {
            currentConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
        }
    }

    //change the video size on window resize
    window.onresize = function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            layout();
        }, 20);
    };

    //add local track to the connection,
    //manage remote track,
    //ice candidate and state change event
    function setupListeners(connection, socketId, opponentUuid) {
        localStream.getTracks().forEach((track) => connection.addTrack(track, localStream));

        connection.onicecandidate = (event) => {
            if (event.candidate) {
                sendMessage({
                    type: 'candidate',
                    candidate: event.candidate,
                    fromSocketId: socket.id,
                    toSocketId: socketId,
                });
            }
        };

        connection.ontrack = (event) => {
            if (document.getElementById('video-' + socketId)) {
                return;
            }

            let videoRemote = document.createElement('video');
            videoRemote.id = 'video-' + socketId;
            videoRemote.setAttribute('autoplay', '');
            videoRemote.setAttribute('playsinline', '');
            videoRemote.srcObject = event.streams[0];

            videoRemote.onloadedmetadata = function(e) {
                videoRemote.play();
            };

            let containerDiv = document.createElement('div');
            containerDiv.id = 'container-' + socketId;
            containerDiv.className = 'videoContainer';

            let containerText = document.createElement('span');
            containerText.className = 'remote-user-name';
            containerText.innerText = usernames[socketId];

            if (isModerator && settings.moderatorRights == "enabled") {
                let kickButton = document.createElement('button');
                kickButton.className = 'btn meeting-option kick';
                kickButton.innerHTML = '<i class="fa fa-ban"></i>';
                kickButton.setAttribute('data-id', socketId);
                kickButton.setAttribute('title', 'Kick this user');

                containerDiv.appendChild(kickButton);
            }

            containerDiv.appendChild(videoRemote);
            containerDiv.appendChild(containerText);
            videos.appendChild(containerDiv);

            layout();
        };

        connection.addEventListener('connectionstatechange', () => {
            if (connection.connectionState === 'connected') {
                if (!isMobile) $('#screenShare').show();
                showSuccess(usernames[socketId] + ' has joined the meeting.');
                if (isModerator) {
                    sendMessage({
                        type: 'currentTime',
                        currentTime: timer.getTimeValues().minutes * 60 + timer.getTimeValues().seconds,
                        fromSocketId: socket.id,
                        toSocketId: socketId,
                    });
                }
            }
        });
    }

    //kick the participant out of the meeting
    $(document).on('click', '.kick', function() {
        if (confirm('Are you sure you want to kick this user?')) {
            $(this).attr('disabled', true);
            sendMessage({
                type: 'kick',
                toSocketId: $(this).data('id'),
            });
        }
    });

    //handle when opponent leaves the meeting
    function handleLeave(data) {
        showInfo(usernames[data.fromSocketId] + ' has left the meeting.');

        if (data.isModerator) {
            reload(1);
        }

        let video = document.getElementById('video-' + data.fromSocketId);
        let container = document.getElementById('container-' + data.fromSocketId);

        if (video && container) {
            video.pause();
            video.srcObject = null;
            video.load();
            container.removeChild(video);
            videos.removeChild(container);
            layout();
        }

        let currentConnection = connections[data.fromSocketId];

        if (currentConnection) {
            currentConnection.close();
            currentConnection.onicecandidate = null;
            currentConnection.ontrack = null;
            delete connections[data.fromSocketId];
        }
    }

    //mute/unmute local video
    $(document).on('click', '#toggleVideo', function() {
        if (videoMuted) {
            localStream.getVideoTracks().forEach((track) => (track.enabled = true));
            $(this).html('<i class="fa fa-video"></i>');
            videoMuted = false;
            showSuccess('Camera has been turned on.');
        } else {
            localStream.getVideoTracks().forEach((track) => (track.enabled = false));
            $(this).html('<i class="fa fa-video-slash"></i>');
            videoMuted = true;
            showSuccess('Camera has been turned off.');
        }
    });

    //mute/unmute local audio
    $(document).on('click', '#toggleMic', function() {
        if (micMuted) {
            localStream.getAudioTracks().forEach((track) => (track.enabled = true));
            $(this).html('<i class="fa fa-microphone"></i>');
            micMuted = false;
            showSuccess('Mic has been unmute.');
        } else {
            localStream.getAudioTracks().forEach((track) => (track.enabled = false));
            $(this).html('<i class="fa fa-microphone-slash"></i>');
            micMuted = true;
            showSuccess('Mic has been muted.');
        }
    });

    //leave the meeting
    $(document).on('click', '#leave', function() {
        showError('Meeting ended!');
        reload(0);
    });

    //switch front/back camera for mobile users
    $(document).on('click', '#toggleCam', function() {
        localStream.getVideoTracks().forEach((track) => track.stop());
        localStream.removeTrack(localStream.getVideoTracks()[0]);

        facingMode = facingMode === 'user' ? 'environment' : 'user';

        navigator.mediaDevices
            .getUserMedia({
                video: {
                    facingMode: {
                        exact: facingMode,
                    },
                },
            })
            .then(function(stream) {
                let videoTrack = stream.getVideoTracks()[0];
                localStream.addTrack(videoTrack);

                Object.values(connections).forEach((connection) => {
                    let sender = connection.getSenders().find(function(s) {
                        return s.track.kind === videoTrack.kind;
                    });

                    sender.replaceTrack(videoTrack);
                });
            })
            .catch(function() {
                showError();
            });
    });

    //warn the user if he tries to leave the page during the meeting
    window.onbeforeunload = function() {
        socket.close();
        Object.keys(connections).forEach((key) => {
            connections[key].close();
            let video = document.getElementById('video-' + key);
            video.pause();
            video.srcObject = null;
            video.load();
            video.parentNode.removeChild(video);
        });
    };

    //enter into fullscreen mode with double click on video
    $(document).on('dblclick', 'video', function() {
        if (this.readyState === 4 && this.srcObject.getVideoTracks().length) {
            try {
                this.requestFullscreen();
            } catch (e) {
                showError('Fullscreen mode is not supported in this browser.');
            }
        } else {
            showError('The video is not playing or has no video track.');
        }
    });

    //toggle picture-in-picture mode with click on video
    $(document).on('click', 'video', function() {
        if (isMobile) return;

        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else {
            if (this.readyState === 4 && this.srcObject.getVideoTracks().length) {
                try {
                    this.requestPictureInPicture();
                } catch (e) {
                    showError('Picture-in-picture mode is not supported in this browser.');
                }
            } else {
                showError('The video is not playing or has no video track.');
            }
        }
    });

    //toggle chat panel
    $(document).on('click', '#openChat', function() {
        $('.chat-panel').animate({
            width: 'toggle',
        });

        if ($(this).hasClass('notify')) $(this).removeClass('notify');
    });

    //close chat panel
    $(document).on('click', '.close-panel', function() {
        $('.chat-panel').animate({
            width: 'toggle',
        });
    });

    //copy/share the meeting invitation
    $(document).on('click', '#add', function() {
        let link = location.protocol + '//' + location.host + location.pathname;

        if (navigator.share) {
            try {
                navigator.share({
                    title: settings.appName,
                    url: link,
                    text: inviteMessage,
                });
            } catch (e) {
                showError(e);
            }
        } else {
            let inp = document.createElement('textarea');
            inp.style.display = 'hidden';
            document.body.appendChild(inp);
            inp.value = inviteMessage + link;
            inp.select();
            document.execCommand('copy', false);
            inp.remove();
            showSuccess('The meeting invitation link has been copied to the clipboard!');
        }
    });

    //listen for message form submit event and send message
    $(document).on('submit', '#chatForm', function(e) {
        e.preventDefault();

        let message = $('#messageInput').val().trim();

        if (message) {
            $('#messageInput').val('');
            appendMessage(message, null, true);

            sendMessage({
                type: 'meetingMessage',
                message: message,
                username: userInfo.username,
            });
        }
    });

    //handle message and append it
    function handlemeetingMessage(data) {
        if ($('.chat-panel').is(':hidden')) {
            $('#openChat').addClass('notify');
            showOptions();
            notificationTone.play();
        }
        appendMessage(data.message, data.username, false);
    }

    //append message to chat body
    function appendMessage(message, username, self) {
        if ($('.empty-chat-body')) {
            $('.empty-chat-body').remove();
        }

        let className = self ? 'local-chat' : 'remote-chat',
            messageDiv = '<div class="' + className + '">' + '<div>' + (username ? '<span class="remote-chat-name">' + username + ': </span>' : '') + linkify(message) + '</div>' + '</div>';

        $('.chat-body').append(messageDiv);
        $('.chat-body').animate({
                scrollTop: $('.chat-body').prop('scrollHeight'),
            },
            1000
        );
    }

    //toggle screen share
    $(document).on('click', '#screenShare', function() {
        if (screenShared) {
            stopScreenSharing();
        } else {
            startScreenSharing();
        }
    });

    //stop screen share
    function stopScreenSharing() {
        localStream.getVideoTracks().forEach((track) => track.stop());
        localStream.removeTrack(localStream.getVideoTracks()[0]);
        screenStream = null;
        replaceVideoTrack(localVideoTrack);
        screenShared = false;
    }

    //start screensharing
    async function startScreenSharing() {
        if (meetingType === 'audio') {
            showError('Please join with a camera to use Screen Share feature.');
            return;
        }

        let displayMediaOptions = {
            video: {
                cursor: 'always',
            },
            audio: false,
        };

        try {
            screenStream = await navigator.mediaDevices.getDisplayMedia(displayMediaOptions);
        } catch (e) {
            showError('Could not share the screen, please check the permissions and try again.');
        }

        if (screenStream) {
            screenShared = true;

            localVideoTrack = localStream.getVideoTracks()[0];
            localStream.removeTrack(localStream.getVideoTracks()[0]);
            replaceVideoTrack(screenStream.getVideoTracks()[0]);

            screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                stopScreenSharing();
            });
        }
    }

    //replace video track and add track to the localStream
    function replaceVideoTrack(videoTrack) {
        localStream.addTrack(videoTrack);

        Object.values(connections).forEach((connection) => {
            let sender = connection.getSenders().find(function(s) {
                return s.track.kind === videoTrack.kind;
            });

            sender.replaceTrack(videoTrack);
        });
    }

    //listen on file input change
    $('#file').on('change', function() {
        let inputFile = this.files;
        let maxFilesize = $(this).data('max');

        if (inputFile && inputFile[0]) {
            if (inputFile[0].size > maxFilesize * 1024 * 1024) {
                showError('Maximum file size allowed is ' + maxFilesize + 'MB.');
                return;
            }

            $('#previewImage').attr('src', 'images/loader.gif');
            $('#previewFilename').text(inputFile[0].name);
            $('#previewModal').modal('show');

            if (inputFile[0].type.includes('image')) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImage').attr('src', e.target.result);
                };
                reader.readAsDataURL(inputFile[0]);
            } else {
                $('#previewImage').attr('src', '/images/file.png');
            }
        } else {
            showError();
        }
    });

    //empty file value on modal close
    $('#previewModal').on('hidden.bs.modal', function() {
        $('#file').val('');
    });

    //hide modal on file send button click
    $(document).on('click', '#sendFile', function() {
        $('#previewModal').modal('hide');
    });

    //append file to the chat panel
    function appendFile(file, extension, username, self) {
        if ($('.empty-chat-body')) {
            $('.empty-chat-body').remove();
        }

        let remoteUsername = username ? '<span>' + username + ': </span>' : '';

        let className = self ? "local-chat" : "remote-chat",
            fileDiv = "<div class='" + className + "'>" + "<button class='btn btn-primary fileMessage' title='View File' data-file='" + file + "' data-extension='" + extension + "'>" + remoteUsername + "<i class='fa fa-file'></i></button>";

        $('.chat-body').append(fileDiv);
        $('.chat-body').animate({
                scrollTop: $('.chat-body').prop('scrollHeight'),
            },
            1000
        );
    }

    //dispay file on button click
    $(document).on('click', '.fileMessage', function() {
        let filename = $(this).data('file');
        let extension = $(this).data('extension');

        $('#displayImage').attr('src', '/images/loader.gif');
        $('#displayFilename').text(filename + extension);
        $('#displayModal').modal('show');

        fetch('/file_uploads/' + userInfo.meetingId + '/' + filename + extension)
            .then((res) => res.blob())
            .then((blob) => {
                displayFileUrl = window.URL.createObjectURL(blob);
                if (['.png', '.jpg', '.jpeg', '.gif'].includes(extension)) {
                    $('#displayImage').attr('src', displayFileUrl);
                } else {
                    $('#displayImage').attr('src', '/images/file.png');
                }
            })
            .catch(() => showError());
    });

    //download file on button click
    $(document).on('click', '#downloadFile', function() {
        const link = document.createElement('a');
        link.style.display = 'none';
        link.href = displayFileUrl;
        link.download = $('#displayFilename').text();
        document.body.appendChild(link);
        link.click();
        $('#displayModal').modal('hide');
        window.URL.revokeObjectURL(displayFileUrl);
    });

    //open file exploler
    $(document).on('click', '#selectFile', function() {
        $('#file').trigger('click');
    });

    //open device settings modal
    $('.updateDevices').on('click', function() {
        $('#deviceSettings').modal('show');
        getDevices();
    });

    //call getUserMedia
    function getDevices() {
        const constraints = {
            audio: getAudioConstraints(),
            video: getVideoConstraints(),
        };

        navigator.mediaDevices
            .getUserMedia(constraints)
            .then(gotStream)
            .then(gotDevices)
            .catch(() => showError());
    }

    //handle got stream
    function gotStream(stream) {
        window.stream = stream;
        return navigator.mediaDevices.enumerateDevices();
    }

    //set devices in select input
    function gotDevices(deviceInfos) {
        const values = selectors.map((select) => select.value);
        selectors.forEach((select) => {
            while (select.firstChild) {
                select.removeChild(select.firstChild);
            }
        });
        for (let i = 0; i !== deviceInfos.length; ++i) {
            const deviceInfo = deviceInfos[i];
            const option = document.createElement('option');
            option.value = deviceInfo.deviceId;
            if (deviceInfo.kind === 'audioinput') {
                option.text = deviceInfo.label || `microphone ${audioInputSelect.length + 1}`;
                audioInputSelect.appendChild(option);
            } else if (deviceInfo.kind === 'videoinput') {
                option.text = deviceInfo.label || `camera ${videoInputSelect.length + 1}`;
                videoInputSelect.appendChild(option);
            }
        }
        selectors.forEach((select, selectorIndex) => {
            if (Array.prototype.slice.call(select.childNodes).some((n) => n.value === values[selectorIndex])) {
                select.value = values[selectorIndex];
            }
        });

        window.stream.getTracks().forEach((track) => {
            track.stop();
        });
    }

    //get audio constraints
    function getAudioConstraints() {
        const audioSource = audioInputSelect.value;

        return {
            deviceId: audioSource ? { exact: audioSource } : undefined,
        };
    }

    //get video constraints
    function getVideoConstraints() {
        if (muteCamera.checked) {
            return false;
        } else {
            return {
                deviceId: videoInputSelect.value,
                width: { exact: $('#' + videoQualitySelect.value).data('width') },
                height: { exact: $('#' + videoQualitySelect.value).data('height') },
            };
        }
    }

    //video input change handler
    videoQualitySelect.onchange = videoInputSelect.onchange = async function() {
        if (!localStream) return;

        constraints = {
            video: getVideoConstraints(),
        };

        try {
            localStream.getVideoTracks().forEach((track) => track.stop());
            let videoStream = await navigator.mediaDevices.getUserMedia(constraints);
            localStream.removeTrack(localStream.getVideoTracks()[0]);
            replaceMediaTrack(videoStream.getVideoTracks()[0]);
            videoSource.value = localStream.getVideoTracks()[0].getSettings().deviceId;
            localStorage.setItem('videoQuality', videoQualitySelect.value);
        } catch (e) {
            console.log('Could not get the devices, please check the permissions and try again. Error: ' + e);
        }
    };

    //checks and audio input change handler
    audioSource.onchange = async function() {
        if (!localStream) return;

        constraints = {
            audio: getAudioConstraints(),
        };

        try {
            localStream.getAudioTracks().forEach((track) => track.stop());
            let audioStream = await navigator.mediaDevices.getUserMedia(constraints);
            localStream.removeTrack(localStream.getAudioTracks()[0]);
            replaceMediaTrack(audioStream.getAudioTracks()[0]);
        } catch (e) {
            console.log('Could not get the devices, please check the permissions and try again. Error: ' + e.name);
        }
    };

    //replace video track and add track to the localStream
    function replaceMediaTrack(track) {
        if (localStream) localStream.addTrack(track);

        Object.values(connections).forEach((connection) => {
            let sender = connection.getSenders().find(function(s) {
                return s.track.kind === track.kind;
            });

            sender.replaceTrack(track);
        });
    }

    //detect and replace text with url
    function linkify(text) {
        var urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gi;
        return text.replace(urlRegex, function(url) {
            return '<a href="' + url + '" target="_blank">' + url + '</a>';
        });
    }

    //initiate keyboard shortcuts
    function initKeyShortcuts() {
        $(document).on('keydown', function() {
            if ($('#messageInput').is(':focus')) return;

            switch (event.key) {
                case 'C':
                case 'c':
                    $('.chat-panel').animate({
                        width: 'toggle',
                    });
                    break;
                case 'F':
                case 'f':
                    if ($('.chat-panel').is(':hidden')) {
                        $('.chat-panel').animate({
                            width: 'toggle',
                        });
                    }
                    $('#selectFile').trigger('click');
                    break;
                case 'A':
                case 'a':
                    $('#toggleMic').trigger('click');
                    break;
                case 'L':
                case 'l':
                    $('#leave').trigger('click');
                    break;
                case 'V':
                case 'v':
                    if (meetingType === 'video') $('#toggleVideo').trigger('click');
                    break;
                case 'S':
                case 's':
                    if (initializedOnce) $('#screenShare').trigger('click');
                    break;
            }
        });
    }
})();