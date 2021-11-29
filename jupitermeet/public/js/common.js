//set current nav-link active
$('a[data-name="' + location.pathname.split("/")[1] + '"]').addClass("active");

//add headers to all the ajax requests
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

//initialize datatable
$("table").not("#globalConfig").DataTable({
    responsive: true,
    autoWidth: false,
    order: [0, "desc"],
    pageLength: 50,
    lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, "All"]
    ]
});

//initialize global config table
$("#globalConfig").DataTable({
    responsive: true,
    autoWidth: false,
    pageLength: 50,
    lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, "All"]
    ]
});

//show success toaster
function showSuccess(message) {
    toastr.success(message);
}

//show warning toaster
function showInfo(message) {
    toastr.info(message);
}

//show error toaster
function showError(message) {
    toastr.error(message || "An error occurred, please try again.");
}

//ajax call to update content
$("#contentEdit").on("submit", function(e) {
    e.preventDefault();

    $("#save").attr("disabled", true);

    $.ajax({
            url: "/update-content",
            data: {
                id: $("#id").val(),
                value: $("#content").summernote("code"),
            },
            type: "post"
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#save").attr("disabled", false);

            if (data.success) {
                showSuccess("Data updated successfully");
            } else {
                showError(data.error);
            }
        })
        .catch(function() {
            showError();
            $("#save").attr("disabled", false);
        });
});

//ajax call to global config
$("#globalConfigEdit").on("submit", function(e) {
    e.preventDefault();

    $("#save").attr("disabled", true);

    let form = new FormData();
    form.append("id", $("#id").val());
    form.append("key", $("#key").val());
    form.append("value", $("#value").val());
    form.append("image", $("#value").prop("files") ? $("#value").prop("files")[0] : "");

    $.ajax({
            url: "/update-global-config",
            data: form,
            type: "post",
            cache: false,
            contentType: false,
            processData: false,
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#save").attr("disabled", false);

            if (data.success) {
                showSuccess("Data updated successfully");
            } else {
                showError(data.error);
            }
        })
        .catch(function() {
            showError();
            $("#save").attr("disabled", false);
        });
});

//ajax call to meeting status
$(".meeting-status").on("click", function() {
    let currentRow = $(this);
    let meetingId = currentRow.data("id");
    let checked = currentRow.is(":checked");

    currentRow.attr("disabled", true);

    $.ajax({
            url: "/update-meeting-status",
            type: "post",
            data: {
                id: meetingId,
                checked: checked,
            },
        })
        .done(function(data) {
            data = JSON.parse(data);
            currentRow.attr("disabled", false);

            if (data.success) {
                showSuccess("Status updated successfully");
            } else {
                currentRow.prop('checked', true);
                showError(data.error);
            }
        })
        .catch(function() {
            currentRow.attr("disabled", false);
            showError();
        });
});

//ajax call to update user status
$(".user-status").on("click", function() {
    let currentRow = $(this);
    let userId = currentRow.data("id");
    let checked = currentRow.is(":checked");

    currentRow.attr("disabled", true);

    $.ajax({
            url: "/update-user-status",
            type: "post",
            data: {
                id: userId,
                checked: checked,
            },
        })
        .done(function(data) {
            data = JSON.parse(data);
            currentRow.attr("disabled", false);

            if (data.success) {
                showSuccess("Status updated successfully");
            } else {
                currentRow.prop('checked', true);
                showError(data.error);
            }
        })
        .catch(function() {
            currentRow.attr("disabled", false);
            showError();
        });
});

//ajax call to verify license
$("#verifyLicense").on("click", function() {
    $(this).attr('disabled', true);

    $.ajax({
            url: "/verify-license",
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#verifyLicense").attr('disabled', false);

            if (data.success) {
                showSuccess("Your license is valid. Type: " + data.type);
            } else {
                showError("Your license is invalid. Error: " + data.error);
            }
        })
        .catch(function() {
            $("#verifyLicense").attr('disabled', false);
            showError();
        });
});

//ajax call to uninstall license
$("#uninstallLicense").on("click", function() {
    if (!confirm('Are you sure you want to uninstall the license?')) return;

    $(this).attr('disabled', true);

    $.ajax({
            url: "/uninstall-license",
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#uninstallLicense").attr('disabled', false);

            if (data.success) {
                showSuccess("License uninstalled");
            } else {
                showError("License uninstallation failed. Error: " + data.error);
            }
        })
        .catch(function() {
            $("#uninstallLicense").attr('disabled', false);
            showError();
        });
});

//ajax call to check for update
$("#checkForUpdate").on("click", function() {
    $(this).attr('disabled', true);

    $.ajax({
            url: "/check-for-update",
        })
        .done(function(data) {
            data = JSON.parse(data);

            if (data.success) {
                $("#downloadUpdate").removeAttr('hidden');
                $("#changelog").html(data.changelog || '-')
                showSuccess("An update is available: Version: " + data.version);
            } else if (data.error) {
                showError(data.error);
            } else {
                $("#checkForUpdate").attr('disabled', false);
                showInfo("Application is already at latest version. Version: " + data.version);
            }
        })
        .catch(function() {
            $("#checkForUpdate").attr('disabled', false);
            showError();
        });
});

//ajax call to download the update
$("#downloadUpdate").on("click", function() {
    $(this).attr('disabled', true);

    $.ajax({
            url: "/download-update",
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#downloadUpdate").removeAttr('hidden');

            if (data.success) {
                showSuccess("The application has been successfully updated to the latest version.");
            } else if (data.error) {
                showError(data.error);
            } else {
                $("#downloadUpdate").attr('disabled', false);
                showError("Update failed. Error: " + data.error);
            }
        })
        .catch(function() {
            $("#downloadUpdate").attr('disabled', false);
            showError();
        });
});

//ajax call to download the update
$("#checkSignaling").on("click", function() {
    $("#checkSignaling").attr('disabled', true);

    $.ajax({
            url: "/check-signaling",
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#checkSignaling").attr('disabled', false);
            $("#status").html(data.status);

            if (data.status == 'Running') {
                $("#status").removeClass('badge-danger').addClass('badge-success');
            } else {
                $("#status").removeClass('badge-success').addClass('badge-danger');
            }
        })
        .catch(function() {
            $("#checkSignaling").attr('disabled', false);
            showError();
        });
});

//ajax call to delete a meeting
$(".delete-meeting-admin").on("click", function() {
    if (!confirm('Are you sure you want to delete this meeting?')) return;
    let currentRow = $(this);
    currentRow.attr("disabled", true);

    let form = new FormData();
    form.append("id", currentRow.data("id"));

    $.ajax({
            url: "/delete-meeting-admin",
            data: form,
            type: "post",
            cache: false,
            contentType: false,
            processData: false,
        })
        .done(function(data) {
            data = JSON.parse(data);

            if (data.success) {
                currentRow.parent().parent().remove();
                showSuccess("Meeting deleted successfully");
            } else {
                showError(data.error);
            }
        })
        .catch(function() {
            currentRow.attr("disabled", false);
            showError();
        });
});

//ajax call to delete user
$(".delete-user").on("click", function() {
    if (!confirm('Are you sure you want to delete this user?')) return;
    let currentRow = $(this);
    currentRow.attr("disabled", true);

    let form = new FormData();
    form.append("id", currentRow.data("id"));

    $.ajax({
            url: "/delete-user",
            data: form,
            type: "post",
            cache: false,
            contentType: false,
            processData: false,
        })
        .done(function(data) {
            data = JSON.parse(data);

            if (data.success) {
                currentRow.parent().parent().remove();
                showSuccess("User deleted successfully");
            } else {
                showError(data.error);
            }
        })
        .catch(function() {
            currentRow.attr("disabled", false);
            showError();
        });
});

//ajax call to create user
$("#createUser").on("submit", function(e) {
    e.preventDefault();
    $("#save").attr("disabled", true);

    $.ajax({
            url: "/create-user",
            data: $(this).serialize(),
            type: "post",
        })
        .done(function(data) {
            data = JSON.parse(data);
            $("#save").attr("disabled", false);

            if (data.success) {
                $('#createUser')[0].reset();
                $("#generateRandomPassword").trigger('click');
                showSuccess("User created successfully");
            } else {
                showError(data.error);
            }
        })
        .catch(function() {
            showError();
            $("#save").attr("disabled", false);
        });
});

//toggle password type
$("#togglePassword").on('click', function() {
    let el = $("input[name='password']");
    el.attr('type', el.attr('type') == 'text' ? 'password' : 'text');
});

//generate random password
$("#generateRandomPassword").on('click', function() {
    let el = $("input[name='password']");
    el.val(Math.random().toString(36).substr(2, 9));
});