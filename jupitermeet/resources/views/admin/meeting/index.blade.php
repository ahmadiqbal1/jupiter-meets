@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="card">
    <div class="card-body">
      <table class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
          <th>ID</th>
          <th>Meeting ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Username</th>
          <th>Password</th>
          <th>Created Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody>
          @foreach ($meetings as $key => $value)
          <tr>
            <td>{{ $value->id }}</td>
            <td>{{ $value->meeting_id }}</td>
            <td>{{ $value->title }}</td>
            <td>{{ $value->description ? $value->description : '-' }}</td>
            <td>{{ $value->username }}</td>
            <td>{{ $value->password ? $value->password : '-' }}</td>
            <td>{{ $value->created_at }}</td>
            <td>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input meeting-status" data-id="{{ $value->id }}" id="customSwitch{{ $value->id }}" {{ $value->status == 'active' ? 'checked' : '' }}>
                <label class="custom-control-label" for="customSwitch{{ $value->id }}"></label>
              </div>
            </td>
            <td>
              <button class="btn btn-danger btn-sm delete-meeting-admin" data-id="{{ $value->id }}" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
        <tr>
          <th>ID</th>
          <th>Meeting ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Username</th>
          <th>Password</th>
          <th>Created Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        </tfoot>
      </table>
    </div>
  </div>
@endsection
