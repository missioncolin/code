$("#payment-form").submit(function (event) {

    // disable the submit button to prevent repeated clicks
    $('.submit-button').attr("disabled", "disabled").attr("class", "btn grey submit-button");

    Stripe.createToken({
        number: $('.card-number').val(),
        name: $('.card-name').val(),
        cvc: $('.card-cvc').val(),
        exp_month: $('.card-expiry-month').val(),
        exp_year: $('.card-expiry-year').val()
    }, stripeResponseHandler);

    return false;
});

function stripeResponseHandler(status, response) {
    if (response.error) {
        // show the errors on the form
        $(".payment-errors").fadeIn().text(response.error.message);
        $(".submit-button").removeAttr("disabled").attr("class", "btn green submit-button");
    } else {
        var form$ = $("#payment-form");
        // token contains id, last4, and card type
        var token = response['id'];
        form$.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
        form$.get(0).submit();
    }
}

$('.reactivate').bind('click',function(){
    var $jobID = $(this).data('job');
    var $this = $(this);
    $.post('/reactivate-job', {
        job: $jobID
    }, function(data) {
        if (data == 'success'){
            //create active elements
            window.location.href = $this.attr('href');
            
        }
        else{
            //error message in a dialog box of some kind
        }
    });
    return false;
});