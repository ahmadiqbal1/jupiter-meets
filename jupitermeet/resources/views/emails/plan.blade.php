<body>
	<p>Hi {{ $username }}, your plan has expired! </p>

    <p>Click <a href="{{ Request::root() }}/pricing">here</a> to renew now.</p>

    <p>Thank you!</p>
</body>
