//update meeting details when a meeting is clicked
$(document).on('click', '.meeting-card', function () {
    let meetingTitle = $(this).find('.meeting-title').html(),
        meetingDescription = $(this).attr('data-description'),
        password = $(this).attr('data-password'),
        meetingIdAuto = $(this).attr('data-auto');

    meetingId = $(this).data('id');

    $('.active-meeting').removeClass('active-meeting');
    $(this).addClass('active-meeting');

    updateDetail(meetingTitle, meetingDescription, password, meetingIdAuto);
});

//set meeting details
function updateDetail(title, description, password, meetingIdAuto) {
    title = title.trim();
    $('#meetingTitleDetail, .meeting-card[data-id="'+ meetingId + '"] .meeting-title').html(title);
    $('#meetingDescriptionDetail').html(description ? description : "-");
    $('.meeting-card[data-id="'+ meetingId + '"] .meeting-description').html(description ? (description.length > 40 ? description.substring(0, 40) + '...' : description) : "-");
    $('#meetingStart').attr('href', 'meeting/' + meetingIdAuto);
    $('#invite, #edit, #delete').attr('data-id', meetingId);
    $('#meetingPasswordDetail').html(password ? password : '-');
    $('#meetingIdDetail').html(meetingIdAuto);
}

//ajax call to create a meeting
$('#meetingsForm').on('submit', function (e) {
    e.preventDefault();

    $('#save').attr('disabled', true);

    $.ajax({
        url: 'create-meeting',
        data: $(this).serialize(),
        type: 'post',
    })
        .done(function (data) {
            data = JSON.parse(data);
            $('#save').attr('disabled', false);

            if (data.success) {
                showSuccess('The meeting has been created');
                $('#meetingsForm')[0].reset();
                $('#emptyDetails').attr('hidden', true);
                $('#meetingDetail').removeAttr('hidden');
                $('#createMeeting').modal('hide');

                addMeeting(data.data);
            } else {
                showError(data.error);
            }
        })
        .catch(function () {
            showError();
            $('#save').attr('disabled', false);
        });
});

//add newly created meeting to the div
function addMeeting(data) {
    meetingId = data.id;

    $('#emptyMeeting').attr('hidden', true);
    if ($('.active-meeting')) {
        $('.active-meeting').removeClass('active-meeting');
    }

    let meeting =
        '<div class="card w-100 mb-2 mt-1 pr-4 meeting-card active-meeting" data-description="' +
        (data.description ? data.description : '') +
        '" data-id="' +
        data.id +
        '" data-auto="' +
        data.meeting_id +
        '" data-password="' +
        (data.password ? data.password : '') +
        '">' +
        '<div class="card-body">' +
        '<h5 class="card-title meeting-title font-weight-bold mb-3">' +
        data.title +
        '</h5>' +
        '<p class="card-text meeting-description">' +
        (data.description ? (data.description.length > 40 ? data.description.substring(0, 40) + '...' : data.description) : "-") +
        '</p>' +
        '</div>' +
        '</div>';

    $('.meeting-list').prepend(meeting);
    updateDetail(data.title, data.description, data.password, data.meeting_id);
}

//generate a random meeting ID for new meetings
function generateMeetingId() {
    return Math.random().toString(36).substr(2, 9);
}

//set meeting ID in the modal
$('#createMeeting').on('show.bs.modal', function () {
    let meetingId = generateMeetingId();

    $('#meetingId').html(meetingId);
    $('#meetingsFormId').val(meetingId);
});

//ajax call to delete a meeting
$('#delete').on('click', function (e) {
    e.preventDefault();

    if (confirm('Are you sure you want to delete this meeting?')) {
        $.ajax({
            url: 'delete-meeting',
            data: {
                id: meetingId,
            },
            type: 'post',
        })
            .done(function (data) {
                data = JSON.parse(data);

                if (data.success) {
                    showSuccess('The meeting has been deleted');
                    $('.active-meeting').remove();

                    if ($('.meeting-card').length) {
                        $('.meeting-card')[0].click();
                    } else {
                        $('#meetingDetail').attr('hidden', true);
                        $('#emptyMeeting, #emptyDetails').removeAttr('hidden');
                    }
                } else {
                    showError();
                }
            })
            .catch(function () {
                showError();
                $('#save').attr('disabled', false);
            });
    }
});

//copy meeting URL to the clipboard
$('#copy').on('click', function (e) {
    e.preventDefault();
    let link = location.host + '/' + $('#meetingStart').attr('href');
    var inp = document.createElement('input');
    inp.style.display = 'hidden';
    document.body.appendChild(inp);
    inp.value = link;
    inp.select();
    document.execCommand('copy', false);
    inp.remove();
    showSuccess('Meeting link has been copied to the clipboard');
});

//open edit meeting modal and set the details
$('#edit').on('click', function () {
    let id = meetingId,
        meetingCard = $('.meeting-card[data-id="' + id + '"]'),
        title = meetingCard.find('.meeting-title').html().trim(),
        description = $("#meetingDescriptionDetail").html().trim(),
        password = meetingCard.attr('data-password'),
        meetingIdAuto = meetingCard.attr('data-auto');

    $('#editMeeting').modal('show');

    $('#titleEdit').val(title);
    $('#descriptionEdit').val(description);
    $('#passwordEdit').val(password);
    $('#meetingIdEdit').html(meetingIdAuto);
    $('#meetingsFormIdEdit').val(meetingId);
});

//ajax call to save the edited meeting
$('#meetingsFormEdit').on('submit', function (e) {
    e.preventDefault();

    $('#saveEdit').attr('disabled', true);

    $.ajax({
        url: 'edit-meeting',
        data: $(this).serialize(),
        type: 'post',
    })
        .done(function (data) {
            data = JSON.parse(data);
            $('#saveEdit').attr('disabled', false);

            if (data.success) {
                showSuccess('The meeting has been updated');
                $('#meetingsFormEdit')[0].reset();
                $('#emptyDetails').attr('hidden', true);
                $('#meetingDetail').removeAttr('hidden');
                $('#editMeeting').modal('hide');

                updateDetail(data.data.title, data.data.description, data.data.password, data.data.meeting_id);
            } else {
                showError();
            }
        })
        .catch(function () {
            showError();
            $('#saveEdit').attr('disabled', false);
        });
});

//ajax call to get the invited people's email
$('#invite').on('click', function () {
    $('.invite-list').html('');
    $('#showInvites').modal('show');
    $('#inviteId').val(meetingId);

    $.ajax({
        url: 'get-invites',
        data: {
            id: meetingId,
        },
    })
        .done(function (data) {
            data = JSON.parse(data);

            if (data.success) {
                data.data.forEach((email) => {
                    let section = '<li class="list-group-item" data-value="' + email + '">' + email + '</li >';
                    $('.invite-list').prepend(section);
                });
            } else {
                showError();
            }
        })
        .catch(function () {
            showError();
        });
});

//ajax call to send email invitation
$('#inviteForm').on('submit', function (e) {
    e.preventDefault();

    let formData = $(this).serialize();
    let email = $('#inviteEmail').val();

    $(this)[0].reset();

    showInfo('Sending the invitation...');

    $.ajax({
        url: 'send-invite',
        data: formData,
        type: 'post',
    })
        .done(function (data) {
            data = JSON.parse(data);

            if (data.success) {
                showSuccess('Invitation has been sent');
                let section = '<li class="list-group-item" data-value="' + email + '">' + email + '</li>';
                $('.invite-list').prepend(section);
            } else {
                showError();
            }
        })
        .catch(function () {
            showError();
        });
});

//ajax call to check if the meeting exist or not
$('#meetingDashboard').on('submit', function (e) {
    e.preventDefault();

    $('#join').attr('disabled', true);

    $.ajax({
        url: 'check-meeting',
        data: $(this).serialize(),
        type: 'post',
    })
        .done(function (data) {
            data = JSON.parse(data);
            $('#join').attr('disabled', false);

            if (data.success) {
                location.href = 'meeting/' + data.id;
            } else {
                showError('The meeting does not exist');
            }
        })
        .catch(function () {
            showError('The meeting does not exist');
            $('#join').attr('disabled', false);
        });
});
