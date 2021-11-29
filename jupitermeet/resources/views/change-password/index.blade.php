@extends('layouts.app')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card">
        <div class="card-header">{{ __('Change Password') }}</div>

        <div class="card-body">
          <form id="changePasswordEdit">
            <div class="form-group row">
              <div class="col-md-4">
                <label>Current password</label>
              </div>
              <div class="col-md-8">
                <input type="password" name="current" class="form-control" placeholder="Enter Current Password" maxlength="50" required>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-md-4">
                <label>New password</label>
              </div>
              <div class="col-md-8">
                <input type="password" name="new" class="form-control" placeholder="Enter New Password" maxlength="50" required>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-md-4">
                <label>Confirm password</label>
              </div>
              <div class="col-md-8">
                  <input type="password" name="confirm" class="form-control" placeholder="Confirm New Password" maxlength="50" required>
              </div>
            </div>
            <div class="col-md-8 offset-md-4">
              <button type="submit" id="save" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
