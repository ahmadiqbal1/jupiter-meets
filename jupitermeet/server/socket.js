/*jshint esversion: 6 */
/*jshint node: true */
//handle join event
const siofu = require("socketio-file-upload");
const fs = require('fs');
const path = require('path');
let meetings = [];

//handle join event
function handleJoin(socket, data) {
    socket.meetingId = data.meetingId;
    socket.moderator = data.moderator;
    socket.join(data.meetingId);
    data.socketId = socket.id;
    sendToMeeting(socket, data);
    handleFileTransfer(socket, data.meetingId);
}

//handle disconnect event
function handleDisconnect(socket, io) {
    //remove file_uploads folder by meetingId
    let dirName = path.join(__dirname, '../public/file_uploads/' + socket.meetingId);
    if (!io.sockets.adapter.rooms[socket.meetingId] && fs.existsSync(dirName)) {
        fs.rmdirSync(dirName, { recursive: true });
    }

    if (socket.moderator) delete meetings[socket.meetingId];

    socket.leave(socket.meetingId);
    //notify all the participants when anyone leaves the meeting
    sendToMeeting(socket, { type: 'leave', fromSocketId: socket.id, isModerator: socket.moderator });
}

//check meeting length and moderator availibility
function handleCheckMeeting(socket, data, io) {
    let result = !io.sockets.adapter.rooms[data.meetingId] || io.sockets.adapter.rooms[data.meetingId].length < process.env.USER_LIMIT_PER_MEETING;

    if (result) {
        if (data.authMode == "disabled" || data.moderator || data.moderatorRights == "disabled") {
            meetings[data.meetingId] = {
                isModeratorPresent: true,
                moderator: socket.id
            };
            //directly allow the user if he is the moderator or if the moderator rights are disabled
            sendToPeer(io, { type: 'checkMeetingResult', result: true, toSocketId: socket.id, message: '' });
        } else if (meetings[data.meetingId] && meetings[data.meetingId].isModeratorPresent) {
            //notify the moderator for new request
            sendToPeer(io, { type: 'permission', toSocketId: meetings[data.meetingId].moderator, fromSocketId: socket.id, username: data.username });
            sendToPeer(io, { type: 'info', toSocketId: socket.id, message: 'Please wait while the moderator check your request' });
        } else {
            //do not allow anyone in the meeting before moderator joins
            sendToPeer(io, { type: 'checkMeetingResult', result: false, toSocketId: socket.id, message: 'The meeting has not been started yet' });
        }
    } else {
        //USER_LIMIT_PER_MEETING capacity is reached
        sendToPeer(io, { type: 'checkMeetingResult', result: false, toSocketId: socket.id, message: 'The meeting is full' });
    }
}

//send the message to particular user
function sendToPeer(io, data) {
    io.to(data.toSocketId).emit('message', JSON.stringify(data));
}

//send the message to particular meeting
function sendToMeeting(socket, data) {
    socket.broadcast.to(socket.meetingId).emit('message', JSON.stringify(data));
}

//handle file transfer
function handleFileTransfer(socket, meetingId) {
    var uploader = new siofu();
    uploader.dir = path.join(__dirname, '../public/file_uploads/' + meetingId);

    if (!fs.existsSync(uploader.dir)) {
        fs.mkdirSync(uploader.dir);
    }

    uploader.maxFileSize = process.env.MAX_FILESIZE * 1024 * 1024;

    uploader.listen(socket);

    uploader.on("saved", function(event) {
        event.file.clientDetail.file = event.file.base;
        event.file.clientDetail.extension = event.file.meta.extension;
        event.file.clientDetail.username = event.file.meta.username;

        socket.broadcast.to(meetingId).emit('file', { file: event.file.base, extension: event.file.meta.extension, username: event.file.meta.username });
    });

    //keep this line to prevent crash
    uploader.on("error", function(event) {});
}

module.exports = function(io) {
    //handle connection event
    io.sockets.on('connection', function(socket) {
        socket.on('message', function(data) {
            data = JSON.parse(data);

            switch (data.type) {
                case 'join':
                    handleJoin(socket, data);
                    break;
                case 'checkMeeting':
                    handleCheckMeeting(socket, data, io);
                    break;
                case 'offer':
                case 'answer':
                case 'candidate':
                case 'message':
                case 'permissionResult':
                case 'currentTime':
                case 'kick':
                    sendToPeer(io, data);
                    break;
                case 'meetingMessage':
                    sendToMeeting(socket, data);
                    break;
            }
        });

        socket.on('disconnect', function() {
            handleDisconnect(socket, io);
        });
    });
}