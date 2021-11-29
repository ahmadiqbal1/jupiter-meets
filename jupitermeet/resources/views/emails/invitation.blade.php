<body>
	<p>Greetings! {{ auth()->user()->username }} has invited you to attend a virtual meeting.</p>

	<ul>
		<li><b>Meeting ID</b>: {{ $meeting['meeting_id'] }}</li>
		<li><b>Title</b>: {{ $meeting['title'] }}</li>
		<li><b>Password</b>: {{ $meeting['password'] ? $meeting['password'] : '-' }}</li>
	    <li><b>Description</b>: {{ $meeting['description'] ? $meeting['description'] : '-' }}</li>
	</ul>

    <p>Click <a href="{{ Request::root() }}/meeting/{{ $meeting['meeting_id'] }}">here</a> to join the meeting!</p>

    <p>Thank you!</p>
</body>
