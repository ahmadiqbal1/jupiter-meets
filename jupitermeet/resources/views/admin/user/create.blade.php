@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
    <div class="card">
        <div class="card-body">
            <form id="createUser">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" placeholder="Username" class="form-control" maxlength="20"
                                autofocus required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Email" class="form-control" maxlength="50"
                                required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Password</label>
                            <div id="passwordContainer">
                                <input type="password" name="password" placeholder="Password" class="form-control"
                                    maxlength="50" required>
                                <button type="button" id="togglePassword" class="btn btn-info btn-sm ml-1"><i class="fa fa-eye"></i></button>
                                <button type="button" id="generateRandomPassword" class="btn btn-warning btn-sm ml-1"><i class="fa fa-random"></i></button>
                            </div>
                            <b>Note: An email will be sent to the user.</b>
                        </div>
                    </div>
                </div>

                <button type="submit" id="save" class="btn btn-primary">Save</button>
                <a href="{{ route('users') }}"><button type="button" class="btn btn-default">Cancel</button></a>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
  $("#generateRandomPassword").trigger('click');
</script>
@endsection
