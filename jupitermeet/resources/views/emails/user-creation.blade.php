<body>
	<p>Greetings! You can now host meetings.</p>

	<ul>
		<li><b>Username</b>: {{ $user['username'] }}</li>
		<li><b>Email</b>: {{ $user['email'] }}</li>
		<li><b>Password</b>: {{ $user['password'] }}</li>
	</ul>

    <p>Click <a href="{{ Request::root() }}/login">here</a> to login.</p>

    <p>Thank you!</p>
</body>
