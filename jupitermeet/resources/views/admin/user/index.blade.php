@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <a href="{{ route('createUser') }}"><button class="btn btn-primary btn-sm" id="createUser" title="Create User">Create</button></a>
            </div>
            <br>
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Plan Type</th>
                        <th>Plan Status</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $key => $value)
                        <tr>
                            <td>{{ $value->id }}</td>
                            <td>{{ $value->username }}</td>
                            <td>{{ $value->email }}</td>
                            <td>
                                @if ($value->plan_type == 'free')
                                    <span class="badge badge-info">Free</span>
                                @else
                                    <span class="badge badge-success">Paid</span>
                                @endif
                            </td>
                            <td>
                                @if ($value->plan_status == 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($value->plan_status == 'inactive')
                                    <span class="badge badge-info">Inactive</span>
                                @else
                                    <span class="badge badge-danger">Expired</span>
                                @endif
                            </td>
                            <td>{{ $value->created_at }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input user-status"
                                        data-id="{{ $value->id }}" id="customSwitch{{ $value->id }}"
                                        {{ $value->status == 'active' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="customSwitch{{ $value->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-user" data-id="{{ $value->id }}"
                                    title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Plan Type</th>
                        <th>Plan Status</th>
                        <th>Created Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
