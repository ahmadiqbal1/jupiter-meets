@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.welcome.templateTitle') }}
@endsection

@section('title')
    {{ trans('installer_messages.welcome.title') }}
@endsection

@section('container')
    <form method="post" action="{{ route('LaravelInstaller::verify') }}" class="tabs-wrap">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <div class="form-group">
        <label>Envato Purchase Code</label>
        <input type="text" name="code" placeholder="Envato Purchase Code" value="{{ old('code') }}" required>
        <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-" target="_blank"><i class="fa fa-info-circle"></i> Where Is My Purchase Code?</a>
      </div>
      <hr>
      <div class="form-group">
        <label>Admin Email</label>
        <input type="text" name="email" placeholder="Admin Email" value="{{ old('email') }}" required>
      </div>
      <div class="form-group">
        <label>Admin Password</label>
        <input type="password" name="password" placeholder="Admin Password" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
      </div>
      <p class="text-center">
        <button type="submit" class="button">Submit <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i></button>
      </p>
    </form>
@endsection
