@extends('layouts.app')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
	<div class="container-fluid text-center">
		<h3>{{ $page }}</h3>
		<div class="col-12 col-md-10 mt-3 offset-md-1 text-justify">
    		{!! getContent('PRIVACY_POLICY') !!}
    	</div>
	</div>
@endsection
