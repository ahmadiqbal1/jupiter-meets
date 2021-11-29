@extends('layouts.application')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('style')
<link href="{{ asset('css/home.css') }}" rel="stylesheet">
@endsection

@section('content')
<section class="dashboard-section">
    <div id="permission"></div>
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col col-md-6 col-12 pos-relative">
                <div class="left-center">
                    <p class="site-detail">{!! getContent('HOME_PAGE') !!}</p>
                </div>
            </div>
            <div class="col col-md-6 col-12 pos-relative">
                <div class="right-center">
                    <div class="entering-info">
                        <form id="meeting">
                            <div class="input-group mb-2">
                                <input
                                    type="text"
                                    name="id"
                                    class="form-control conference-id"
                                    id="conferenceId"
                                    aria-label="Meeting ID"
                                    aria-describedby="initiate"
                                    required
                                    autofocus
                                    autocomplete="off"
                                    maxlength="9"
                                    pattern="^[a-zA-Z0-9]+$"
                                    title="Special characters and spaces are not allowed"
                                />
                                <label class="form-control-placeholder" for="conferenceId">Meeting ID</label>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit" id="initiate" title="Start the meeting"><i class="fa fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script type="text/javascript">
    let errorExist = "{{ $errors->any() }}";

    if (errorExist) {
        showError("{{ $errors->first() }}"); 
    }
</script>
@endsection