//add headers to all the ajax requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
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
    toastr.error(message || 'An error occurred, please try again.');
}

//ajax call to update the password
$('#changePasswordEdit').on('submit', function (e) {
    e.preventDefault();

    $('#save').attr('disabled', true);

    $.ajax({
        url: 'update-password',
        data: $(this).serialize(),
        type: 'post',
    })
        .done(function (data) {
            data = JSON.parse(data);
            $('#save').attr('disabled', false);

            if (data.success) {
                showSuccess('Data updated successfully');
                $('#changePasswordEdit')[0].reset();
            } else {
                showError();
            }
        })
        .catch(function () {
            showError();
            $('#save').attr('disabled', false);
        });
});

//ajax call to check if the meeting exist or not
$('#meeting').on('submit', function (e) {
    e.preventDefault();

    $('#initiate').attr('disabled', true);

    $.ajax({
        url: 'check-meeting',
        data: $(this).serialize(),
        type: 'post',
    })
        .done(function (data) {
            data = JSON.parse(data);
            $('#initiate').attr('disabled', false);

            if (data.success) {
                location.href = 'meeting/' + data.id;
            } else {
                showError('The meeting does not exist');
            }
        })
        .catch(function () {
            showError('The meeting does not exist');
            $('#initiate').attr('disabled', false);
        });
});

//switch monthly and yearly packages
$('input[name=period]').change(function() {
    if (this.value == 'monthly') {
        $("#yearlyPrice").attr('hidden', 'true');
        $("#montlyPrice").removeAttr('hidden');
        $("#type").val('monthly');
    } else {
        $("#montlyPrice").attr('hidden', 'true');
        $("#yearlyPrice").removeAttr('hidden');
        $("#type").val('yearly');
    }
});

//stripe payment handler
var $form = $(".validation");
$("form.validation").bind("submit", function (e) {
    var $form = $(".validation"),
        inputVal = ["input[type=email]", "input[type=password]", "input[type=text]", "input[type=file]", "textarea"].join(", "),
        $inputs = $form.find(".required").find(inputVal),
        $errorStatus = $form.find("div.error"),
        valid = true;
    $errorStatus.addClass("hide");
    $("#payNow").attr('disabled', true);

    $(".has-error").removeClass("has-error");
    $inputs.each(function (i, el) {
        var $input = $(el);
        if ($input.val() === "") {
            $input.parent().addClass("has-error");
            $errorStatus.removeClass("hide");
            e.preventDefault();
        }
    });

    if (!$form.data("cc-on-file")) {
        e.preventDefault();
        Stripe.setPublishableKey($form.data("stripe-publishable-key"));
        Stripe.createToken(
            {
                number: $(".card-number").val(),
                cvc: $(".card-cvc").val(),
                exp_month: $(".card-expiry-month").val(),
                exp_year: $(".card-expiry-year").val(),
            },
            stripeHandleResponse
        );
    }
});

function stripeHandleResponse(status, response) {
    if (response.error) {
        $(".error").removeClass("hide").find(".alert").text(response.error.message);
        $("#payNow").attr('disabled', false);
    } else {
        var token = response["id"];
        $form.find("input[type=text]").empty();
        $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
        $form.get(0).submit();
    }
}