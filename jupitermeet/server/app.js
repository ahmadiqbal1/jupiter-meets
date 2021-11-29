/*jshint esversion: 6 */
/*jshint node: true */
"use strict";

require('dotenv').config();

const express = require('express');
const app = express();
const fs = require('fs');
const options = {
		key: fs.readFileSync(process.env.KEY_PATH),
		cert: fs.readFileSync(process.env.CERT_PATH)
	};
const https = require('https').Server(options, app);
const io = require('socket.io')(https);
const listner = https.listen(process.env.PORT, function() {
	console.log('Listening on ', listner.address().port);
});

//allow only the specified domain to connect
io.set('origins', process.env.DOMAIN + ':*');

require('./socket')(io);

app.get('/', function (req, res) {
	res.send('Ok');
});