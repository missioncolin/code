$("#payment-form").submit(function (event) {
	//are boxes checked? 
	if ( $('#termsConditions').is(':checked') && $('#privacyPolicy').is(':checked') )
	{
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
    }else{
    	alert ( "Please accept both Terms and Conditions and Privacy Policy to continue." );
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


$('div.credits input').bind('click',function(){
    // console.log("You clicked me " + $(this).attr("id"));
    
    
    var idSplit = $(this).attr("id");
    var provID = $("#Billing_Province").val();
    idSplit = idSplit.split("_");
    var myDS = "ds_credit_" + idSplit[1];
    myDS = eval(myDS);
    
    if(provID < 10) {
        
        provID = "0" + provID;
    }
    
    var myTaxDS = "ds_tax_" + provID;
    myTaxDS = eval(myTaxDS);
    // console.log(myTaxDS[1]);
    var taxes = parseFloat(myDS[0]) * parseFloat(myTaxDS[1]);
    
    var taxLabel = myTaxDS[0];
    
    if(myTaxDS[3]) {
        taxes = taxes + (parseFloat(myDS[0]) * parseFloat(myTaxDS[3]));
        taxLabel = taxLabel + " + " + myTaxDS[2];
    }
    
    
    
    var myTotal = parseFloat(myDS[0]) + taxes;
    myTotal = Math.round(myTotal*Math.pow(10,2))/Math.pow(10,2);
    taxes = Math.round(taxes*Math.pow(10,2))/Math.pow(10,2);
    
    
    
    // console.log(myTotal);
    
    $("#whatAreYouBuying").html("You are purchasing <strong>"+myDS[1]+"</strong> job credit(s) for <strong>$"+myDS[0]+"</strong> CAD plus "+taxLabel+" ($"+taxes+") Your total will be <strong>$"+myTotal+" CAD</strong>");
    
    
    
    
    
});

$('#Billing_Province').bind('change',function(){    
    
    var idSplit = $("div.credits input[type='radio']:checked").val();
    var provID = $(this).val();
    var myDS = "ds_credit_" + idSplit;
    myDS = eval(myDS);
    
    if(provID < 10) {
        
        provID = "0" + provID;
    }
    
    var myTaxDS = "ds_tax_" + provID;
    myTaxDS = eval(myTaxDS);
    // console.log(myTaxDS[1]);
    var taxes = parseFloat(myDS[0]) * parseFloat(myTaxDS[1]);
    
    var taxLabel = myTaxDS[0];
    
    if(myTaxDS[3]) {
        taxes = taxes + (parseFloat(myDS[0]) * parseFloat(myTaxDS[3]));
        taxLabel = taxLabel + " + " + myTaxDS[2];
    }
    
    
    
    var myTotal = parseFloat(myDS[0]) + taxes;
    myTotal = Math.round(myTotal*Math.pow(10,2))/Math.pow(10,2);
    taxes = Math.round(taxes*Math.pow(10,2))/Math.pow(10,2);
    
    
    
    // console.log(myTotal);
    
    $("#whatAreYouBuying").html("You are purchasing <strong>"+myDS[1]+"</strong> job credit(s) for <strong>$"+myDS[0]+"</strong> CAD plus "+taxLabel+" ($"+taxes+") Your total will be <strong>$"+myTotal+" CAD</strong>");
    
    
    
    
    
});


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



$('.activate').bind('click',function(){
    var $jobID = $(this).data('job');
    var $this = $(this);
    $.post('/activate-job', {
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