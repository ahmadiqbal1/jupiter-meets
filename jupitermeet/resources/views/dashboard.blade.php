@extends('layouts.app')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-lg-3 col-sm-5 col-md-4 col-xl-3 col-12 mb-3">
            <div class="col-12 col-xl-12 col-md-12 col-sm-12" style="padding: 0;">
                <button class="btn btn-primary btn-block shadow-sm" data-toggle="modal" data-target="#createMeeting"><i class="fa fa-plus-circle mr-3" aria-hidden="true"></i> Create Meeting</button>
            </div>
        </div>
        <div class="col-lg-9 col-sm-7 col-md-8 col-xl-9 col-12 p-0">
            <form id="meetingDashboard">
                <div class="input-group mb-3 col-sm-10 col-md-7 col-lg-4 col-xl-3 col-12 float-right">
                    <input type="text" class="form-control" name="id" placeholder="Enter Meeting ID" maxlength="9" required />
                    <div class="input-group-append">
                        <button id="join" type="submit" class="btn btn-primary">Join</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-xl-3 col-md-4 col-sm-5 col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Meetings</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group meeting-list pr-1">
                        <span id="emptyMeeting" @if($firstMeeting) hidden @endif>Your meetings will appear here!</span>

                        @if($firstMeeting) @foreach ($meetings as $key => $value)
                        <div class="card w-100 mb-2 mt-1 pr-4 meeting-card" data-description="<?= $value->description ?>" data-id="<?= $value->id ?>" data-auto="<?= $value->meeting_id ?>" data-password="<?= $value->password ?>">
                            <div class="card-body">
                                <h5 class="card-title meeting-title font-weight-bold mb-3"><?= $value->title ?></h5>
                                <p class="card-text meeting-description"><?= $value && $value->description ? (strlen($value->description) > 40 ? substr($value->description, 0, 40) . '...' : $value->description ) : '-' ?></p>
                            </div>
                        </div>
                        @endforeach @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-9 col-xl-9 col-md-8 col-sm-7">
            <div id="meetingDetail" class="card w-100" @if(!$firstMeeting) hidden @endif>
                <div class="card-header">
                    <h5 class="mb-0" id="meetingTitleDetail"><?= $firstMeeting ? $firstMeeting->title : '' ?></h5>
                </div>
                <div class="card-body">
                    <p id="meetingDescriptionDetail" class="card-text"><?= $firstMeeting && $firstMeeting->description ? $firstMeeting->description : '-' ?></p>
                    <p class="card-text">
                        Meeting ID: <span id="meetingIdDetail" class="font-weight-bold"><?= $firstMeeting ? $firstMeeting->meeting_id : '' ?></span>
                    </p>
                    <p class="card-text">
                        Password: <span id="meetingPasswordDetail" class="font-weight-bold"><?= $firstMeeting && $firstMeeting->password ? $firstMeeting->password : '-' ?></span>
                    </p>
                </div>
                <div class="card-body">
                    <a href="<?= $firstMeeting ? 'meeting/' . $firstMeeting->meeting_id : '' ?>" class="card-link" id="meetingStart">Start</a>
                    <a href="#" id="invite" class="card-link" data-id="<?= $firstMeeting ? $firstMeeting->id : '' ?>">Invite People</a>

                    <a href="#" id="edit" class="card-link" data-id="<?= $firstMeeting ? $firstMeeting->id : '' ?>">Edit</a>

                    <a href="#" id="delete" class="card-link" data-id="<?= $firstMeeting ? $firstMeeting->id : '' ?>">Delete</a>

                    <a href="#" id="copy" class="card-link" data-id="<?= $firstMeeting ? $firstMeeting->id : '' ?>">Copy Link</a>
                </div>
            </div>
            <div id="emptyDetails" class="w-100 text-center" @if($firstMeeting) hidden @endif>
                <img src="{{ asset('images/list.png') }}" width="100" alt="list">
                <p>Meeting details will appear here!</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createMeeting" tabindex="-1" role="dialog" aria-labelledby="createMeetingLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMeetingLabel">Create Meeting | ID: <span id="meetingId"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-lg-12">
                    <form id="meetingsForm">
                        <div class="form-group row">
                            <label for="title" class="col-md-4 col-lg-3">Title*</label>

                            <div class="col-md-8  col-lg-9">
                                <input id="title" type="text" class="form-control" name="title" placeholder="Enter meeting title" maxlength="100" required />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="description" class="col-md-4  col-lg-3">Description</label>

                            <div class="col-md-8 col-lg-9">
                                <textarea id="description" class="form-control" name="description" placeholder="Enter meeting description" maxlength="1000"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-lg-3">Password</label>

                            <div class="col-md-8 col-lg-9">
                                <input id="password" type="text" class="form-control" name="password" placeholder="Enter meeting password" maxlength="8" />
                            </div>
                        </div>
                        <input type="hidden" id="meetingsFormId" name="meeting_id" />

                        <hr />

                        <div class="text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="save">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editMeeting" tabindex="-1" role="dialog" aria-labelledby="editMeetingLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMeetingLabel">Edit Meeting | ID: <span id="meetingIdEdit"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-lg-12">
                    <form id="meetingsFormEdit">
                        <div class="form-group row">
                            <label for="titleEdit" class="col-md-4 col-lg-3 ">Title*</label>

                            <div class="col-md-8  col-lg-9">
                                <input id="titleEdit" type="text" class="form-control" name="title" placeholder="Enter meeting title" maxlength="100" required />
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="descriptionEdit" class="col-md-4 col-lg-3 ">Description</label>

                            <div class="col-md-8  col-lg-9">
                                <textarea id="descriptionEdit" class="form-control" name="description" placeholder="Enter meeting description" maxlength="1000"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="passwordEdit" class="col-md-4 col-lg-3 ">Password</label>

                            <div class="col-md-8  col-lg-9">
                                <input id="passwordEdit" type="text" class="form-control" name="password" placeholder="Enter meeting password" maxlength="8" />
                            </div>
                        </div>
                        <input type="hidden" id="meetingsFormIdEdit" name="id" />

                        <hr />

                        <div class="text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="saveEdit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="showInvites" tabindex="-1" role="dialog" aria-labelledby="showInvitesLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showInvitesLabel">Invite People</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-lg-12">
                    <form id="inviteForm">
                        <div class="form-group row">
                            <label for="passwordEdit" class="col-lg-3 col-md-3">Email*</label>
                            <div class="col-lg-6 col-md-6">
                                <input type="email" id="inviteEmail" class="form-control mb-2 mr-sm-2" name="email" placeholder="Enter an email" maxlength="50" required />
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <button type="submit" class="btn btn-primary">Invite</button>
                            </div>
                        </div>
                        <input type="hidden" id="inviteId" name="id" />
                    </form>
                    <div class="row">
                        <div class="col-12">
                            <ul class="list-group list-group-flush invite-list"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        let meetingExist = "{{ !$meetings->isEmpty() }}" || null;
        let meetingId;

        if (meetingExist) {
            $('.meeting-card:first').addClass('active-meeting');
            meetingId = "{{ $firstMeeting ? $firstMeeting->id : '' }}";
        }
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
