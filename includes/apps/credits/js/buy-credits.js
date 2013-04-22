$("#payment-form").submit(function (event) {
    //are boxes checked? 
    if ($('#termsConditions').is(':checked') && $('#privacyPolicy').is(':checked')) {
        // disable the submit button to prevent repeated clicks
        $('.submit-button').attr("disabled", "disabled").attr("class", "btn grey submit-button");


        //create stripe tokens
        Stripe.createToken({
            number: $('.card-number').val(),
            name: $('.card-name').val(),
            cvc: $('.card-cvc').val(),
            exp_month: $('.card-expiry-month').val(),
            exp_year: $('.card-expiry-year').val()
        }, stripeResponseHandler);
    } else {
        alert("Please accept both Terms and Conditions and Privacy Policy to continue.");
    }
    return false;
});

function stripeResponseHandler(status, response) {
    if (response.error) {
        // show the errors on the form
        $(".payment-errors").fadeIn().html("<div class='fail'><span></span></div>" + response.error.message);
        $(".submit-button").removeAttr("disabled").attr("class", "btn green submit-button");
    } else {
        var form$ = $("#payment-form");
        // token contains id, last4, and card type
        var token = response['id'];
        form$.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
        form$.get(0).submit();
    }
}


$('div.credits input').bind('click', function () {

    var idSplit = $(this).attr("id").split("_"),
        myDS = "ds_credit_" + idSplit[1];

    calculateTax(eval(myDS), $("#Billing_Province").val())

});

$('#Billing_Province').bind('change', function () {

    calculateTax(eval("ds_credit_" + $("div.credits input[type='radio']:checked").val()), $(this).val())

});


function calculateTax(creditSelection, provinceID) {

    var myTaxDS = "ds_tax_" + provinceID;

    try {
        myTaxDS = eval(myTaxDS);

        var taxes = parseFloat(creditSelection[0]) * parseFloat(myTaxDS[1]),
         taxLabel = myTaxDS[0];

        if (myTaxDS[3]) {
            taxes = taxes + (parseFloat(creditSelection[0]) * parseFloat(myTaxDS[3]));
            taxLabel = taxLabel + " + " + myTaxDS[2];
        }

        var myTotal = parseFloat(creditSelection[0]) + taxes;
        
        myTotal = Math.round(myTotal * Math.pow(10, 2)) / Math.pow(10, 2);
        taxes   = Math.round(taxes * Math.pow(10, 2)) / Math.pow(10, 2);

        $("#whatAreYouBuying").html("You are purchasing <strong>" + creditSelection[1] + "</strong> job credit(s) for <strong>$" + creditSelection[0] + "</strong> CAD plus " + taxLabel + " ($" + taxes + "). Your total will be <strong>$" + myTotal + " CAD</strong>");

    } catch (e) {
        $("#whatAreYouBuying").html("You are purchasing <strong>" + creditSelection[1] + "</strong> job credit(s) for <strong>$" + creditSelection[0] + "</strong> CAD");
    }



}

$('.reactivate').bind('click', function () {
    var $jobID = $(this).data('job');
    var $this = $(this);
    $.post('/reactivate-job', {
        job: $jobID
    }, function (data) {
        if (data == 'success') {
            //create active elements
            window.location.href = $this.attr('href');

        } else {
            //error message in a dialog box of some kind
        }
    });
    return false;
});



$('.activate').bind('click', function () {
    var $jobID = $(this).data('job');
    var $this = $(this);
    $.post('/activate-job', {
        job: $jobID
    }, function (data) {
        if (data == 'success') {
            //create active elements
            window.location.href = $this.attr('href');

        } else {
            //error message in a dialog box of some kind
        }
    });
    return false;
});