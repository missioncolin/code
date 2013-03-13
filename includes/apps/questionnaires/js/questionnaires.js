// Stores count of each question to tell 
// total number of questions added per 'step'
totalCountDD = new Array(1, 2, 3, 4, 5);

totalCountQ = new Array(1, 2, 3, 4);
totalCountQ.push(5);

editCount = 0;
    
$("#RQvalNUMBType").change(function () {
    if ($(this).val() == 1 || $(this).val() == 2) {
        $("tr.option-row").show();
    } else {
        $("tr.option-row").hide();
    }
});

// Overloaded for editing wizard
$('select').live("change", function() {

    var thisClass = $(this).attr('class');
    var getClassCount = thisClass.split('_');
    var classCount = getClassCount[1];

    $('.' + classCount).val($(this).find(":selected").text());
    
});

$('select').live("change", function() {

    var thisClass = $(this).attr('class');
    var getClassCount = thisClass.split('_');
    var classCount = getClassCount[1];

    $('#RQvalALPHQuestion_' + classCount).val($(this).find(":selected").text());
    
});

$(".slider").each(function () {
    var slideVal = parseInt($(this).attr('alt'));
    //console.log(slideVal);
    if (!slideVal > 0) slideVal = 0;
    $(this).slider({
        range: "min",
        value: slideVal,
        min: 0,
        max: 30,
        step: 1,
        slide: function (event, ui) {
            var slideInput = $(this).attr('rel');
            $("#" + slideInput).val(ui.value);
            $('.sliderValueHolder[rel=' + slideInput + ']').html(ui.value + "/30 years of experience");
        }
    });
});

/* Initialize first slider */
$('div[id^="idealSlider_"]').each( function() {

	$(this).slider({
        range: "max",
        min: 0,
        max: 30,
        value: $(this).data('value'),
        slide: function( event, ui ) {
            $( "#idealValue_" + $(this).data('count') ).html( ui.value );
        },
        stop: function (event, ui) {
	        /* Set visible slider count and hidden value to POST */
		    $( "#idealValue_" + $(this).data('count') ).html( ui.value );
		    $( "#hiddenIdealValue_" + $(this).data('count') ).val( ui.value );
        }
    });
    

});

/* Accesses sliders for ideal slider input */
$('div[id^="idealSlider_"]').live('initIdealSlider', function () {
   
    var count = $(this).data('count');
    var value = $(this).data('value');
    
    $(this).slider({
        range: "max",
        min: 0,
        max: 30,
        value: value,
        slide: function( event, ui ) {
            $( "#idealValue_" + count ).html( ui.value );
        },
        stop: function (event, ui) {
	        /* Set visible slider count and hidden value to POST */
		    $( "#idealValue_" + count ).html( ui.value );
		    $( "#hiddenIdealValue_" + count ).val( ui.value );
        }
    });
});

// Add a new dropdown question 
$('.add_dropdown_q').live('click', function() {     

	/* Increment button count & reassign data */
    $count = $(this).data('count') + 1;
    $(this).data('count', $count);
    totalCountDD.push($count);
    console.log(totalCountDD);
    
    $label = $(this).data('label');
    
    $('<tr><td>' + $label + '</td><td colspan="2"><input size="75" type="text" autocomplete="off" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" placeholder="Select a default question, or create your own." /><br> <select class="DefaultQs_' + $count + '"" name="Generic Questions" style="width:400px;"><option>Optionally select a default question.</option><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select><a href="#" data-label="' + $label + '" data-count="' + $count + '" class="removeDropDown btn red" id="removeDD_' + $count + '">&nbsp;x</a></td></tr>').insertBefore($(this).parent().parent().parent());
    return false;
});


// Add slider question
$('.add').live('click', function() { 

    $count = document.getElementById('configure').getElementsByTagName("tr").length - 1;
    $label = $(this).data('label');
    totalCountQ.push($count);
    
    $('<tr><td colspan="2"><div class="sliderText"><input size="75" type="text" autocomplete="off" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" placeholder="Example: Data Entry" value="" /></div><div class="experienceSlider"><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_' + $count + '">15</span><input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_' + $count + '" value=""/></br><div class="idealSlider" id="idealSlider_' + $count + '" data-count="' + $count + '" data-value="15"></div></div><a href="#" data-count="' + $count + '" class="removeSkillQ btn red">x</a></td></tr>').insertBefore($(this).closest('tr'));
    
    /* Trigger the new slider */
    $("#idealSlider_" + $count).trigger('initIdealSlider'); 
    return false;
});

// Add an extra question to the edit page
$('.addEditQuestion').click(function () {
	
	editCount = editCount + 1;
	
	var thisID = $(this).data('id') + editCount;
	
	if ($(this).data('type') == 3) {
		
		$('<tr><td colspan="2"><div class="sliderText"><input size="75" type="text" autocomplete="off" placeholder="Example: Data Entry" class="' + thisID + '" name="RQvalALPHQuestion_' + thisID + '_new_3" value=""/></div><div class="experienceSlider"><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_' + thisID + '">15</span><input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_' + thisID + '" value=""/></br><div class="idealSlider" id="idealSlider_' + thisID + '" data-count="' + thisID + '" data-value="15"></div></div><a href="#" data-type="3" id="' + thisID + '" class="removeQuestion btn"> x</a></td></tr>').insertBefore($(this).closest('tr'));
	
	/* Trigger the new slider */
    $("#idealSlider_" + thisID).trigger('initIdealSlider'); 
    	
	}
	
	else {
		
		$('<tr><td><label for="RQvalALPHQuestion_' + thisID + '">Question</label></td><td colspan="2"><input size="75" type="text" autocomplete="off" class="' + thisID + '" placeholder="Select a default question, or create your own." name="RQvalALPHQuestion_' + thisID + '_new_4" value=""/><br><select class="DefaultQs_' + thisID + '"" name="Generic Questions" style="width:400px;"><option>Optionally select a default question.</option><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select><a href="#" data-type="4" id="' + thisID + '" class="removeQuestion btn"> x</a></td></tr>').insertBefore($(this).closest('tr'));
		
	}


	
});

$('.delete').click(function (e) {
    e.preventDefault();
    var $question = $(this).data('question');
    var $this = $(this);
    confirmAction("Delete question?", "Once this question is deleted, you will no longer be able to edit it");
    $('.popUp #popUpNo').on('click', clearPopUp);
    $('.popUp #popUpOk').on('click', function(){
        $.post('/delete-question', {
            question: $question
        }, function() {
            $this.parent().parent().remove();
            $('.alert').removeClass('fail').addClass('success').html('<span></span>Question Deleted Successfully');
        });
        clearPopUp();
    });
}); 

$('.removeSkillQ').live('click', function() {
	
	$count = $(this).data('count');
	$label = $(this).data('label');
	$QsCount = document.getElementById('configure').getElementsByClassName("sliderText").length;
	log($QsCount + " " + $count);
	// If at first question, replace whatever is here with an option to create a new question
	// otherwise, just stick the 'add question' to the previous 	
	if ($QsCount == 1) {

		$('#RQvalALPHQuestion_' + $count).attr('value', "");
		$('#idealSlider_' + $count).slider({value: 0});
		$('#idealValue_' + $count).html(0);
	    $label = $(this).data('label');
		
	}
	
	// If at the end of the list, remove and append 'add' link to previous question
	//else if (($count == totalCountQ[totalCountQ.length - 1]) && (totalCountQ.length != 1)){
	if ($QsCount > 1){

		totalCountQ.splice(totalCountQ.indexOf($count), 1);

		$('#RQvalALPHQuestion_' + $count).remove();
		$('#idealValue_' + $count).remove();
		//$(this).closest('td').remove();
		$(this).parent().parent().remove();
		//$('<a href="#" data-count="1" data-label="How many years experience…" class="add btn blue">Add Another Question</a>').insertAfter($('#idealSlider_' + (totalCountQ[totalCountQ.length - 1])));
		$(this).remove();
		
	}
	/*else {
		
		totalCountQ.splice(totalCountQ.indexOf($count), 1);
		
		// If not at end, just remove 'add' since likely doesn't have an 'add' link anyways
		$('#RQvalALPHQuestion_' + $count).remove();
		$('#idealValue_' + $count).remove();
		$(this).closest('td').remove();
		$(this).closest('tr').remove();
		$(this).remove();
	}*/

	
	return false;
});



$('.removeDropDown').live('click', function() {
	
	$count = $(this).data('count');
	console.log("Count: " + $count);

	console.log("Total Count: " + totalCountDD);
	
	// If at first question, replace whatever is here with an option to create a new question
	// otherwise, just stick the 'add question' to the previous 	
	if (totalCountDD.length == 1) {

		$('.DefaultQs_' + $count).prop('selectedIndex', 0);
		$('#RQvalALPHQuestion_' + $count).val("");
		
	}
	// If at the end of the list, remove and append 'add' link to previous question
	else if (($count == totalCountDD[totalCountDD.length - 1]) && (totalCountDD.length != 1)){
		
		totalCountDD.splice(totalCountDD.indexOf($count), 1);
		$('.DefaultQs_' + $count).remove();

		$('#RQvalALPHQuestion_' + $count).remove();
		
		/* Get this row and previous */
		var thisRow = $(this).closest('tr');
		var prevRow = thisRow.prev();

		$(this).closest('tr').remove();
		$(this).closest('td').remove();
		
		$(this).remove();
		
	}
	else {
		
		totalCountDD.splice(totalCountDD.indexOf($count), 1);
		
		// If not at end, just remove 'add' since likely doesn't have an 'add' link anyways
		$('.DefaultQs_' + $count).remove();
		$('#RQvalALPHQuestion_' + $count).remove();
		$(this).closest('tr').remove();
		$(this).closest('td').remove();
		$(this).remove();
	}
	
	return false;
});

/* Remove an edited question from form */
$('.removeQuestion').live('click', function() {
	
	// Hides input and sets action as DELETE for this question
	if ($(this).data('type') == 3) {
		$("input."+$(this).attr('id') + "'").attr('name', 'RQvalALPHQuestion_' + $(this).attr('id') + '_delete_3');
	}
	else {
		$("input."+$(this).attr('id') + "'").attr('name', 'RQvalALPHQuestion_' + $(this).attr('id') + '_delete_4');
	}
	$(this).closest('tr').hide();
	
});
	

var clearPopUp = function(){
    $('#confirm').fadeOut();
    $('.popUp h2').empty();
    $('.popUp p').empty();
    $('.popUp #popUpOk').off('click');
    $('.popUp #popUpNo').off('click');
}

var confirmAction = function(title, message){
    $('.popUp h2').html(title);
    $('.popUp p').html(message);
    $('#confirm').fadeIn();
}

//apply page
var $activeVideo = 0;
$('.nextbutton').click(function () {
	var $comingFrom = $(this).data('section');
			
	if ($comingFrom == 'instructions') {
		if (document.getElementById('privacyPolicy').checked){ 
			$(".userinfo").fadeOut();
			$("#submissions").fadeOut(400, function() {
				
				if ($("#video1").is('*')) {
					$("#video1").fadeIn();
					$activeVideo = 1;
					$("#videoInstructions").fadeOut();
					$("#videoInstructions").remove();
				}
			});
			
			$('.current').removeClass().next().addClass('current');
			
		}else{
			alert("Please accept our privacy policy before continuing");
			return false;
		}
		
		
	} else if ($comingFrom == 'video') {
		
		$("#video"+$activeVideo).fadeOut(400, function() {
			
			$activeVideo++;
			
			if ($("#video"+$activeVideo).is('*')) {
				$("#video"+$activeVideo).fadeIn();
			} else {
				$("#finalStep").fadeIn();
				$("#videoInstructions").fadeOut();
				$('.current').removeClass().next().addClass('current');
				
				$activeVideo--;
			}
		});
		
	}
	
});

$('.prevbutton').click(function () {
	
	var $comingFrom = $(this).data('section');
	
	if ($comingFrom == 'questions') {
		
	} else if ($comingFrom == 'instructions'){
		
		
	} else if ($comingFrom == 'video') {
		
		$("#video"+$activeVideo).fadeOut(400, function() {
			
			$activeVideo--;
			
			if ($("#video"+$activeVideo).is('*')) {
				$("#video"+$activeVideo).fadeIn();
			} else {
				$(".userinfo").fadeIn();
				$("#submissions").fadeIn();
				$("#videoInstructions").fadeOut();
				$('.current').removeClass().prev().addClass('current');
			}
		});
		
	} else if ($comingFrom == 'final') {
		
		$("#finalStep").fadeOut();
		
		if ($("#video"+$activeVideo).is('*')) {
			$("#video"+$activeVideo).fadeIn();
			$('.current').removeClass().prev().addClass('current');
			$("#videoInstructions").fadeIn();
		} else {
			$(".userinfo").fadeIn();
			$("#submissions").fadeIn();
			$('.current').removeClass().prev().prev().addClass('current');
		}
	}
});

$('#steps li').click(function () {
	var selectedIndex = $(this).index() + 1;
	
	$('.current').removeClass();
	$(this).addClass('current');
	
	
	switch(selectedIndex) {
	case 1:
		$("#finalStep").fadeOut();
		$("#videoInstructions").fadeOut();
		$(".video-q-holder").fadeOut(400, function() {
			
			$("#submissions").fadeIn();
			$(".userinfo").fadeIn();
		});
		
		
		
		break;
	case 2:
		$(".userinfo").fadeOut();
		$("#finalStep").fadeOut();
		$("#submissions").fadeOut(400, function() {
			
			$activeVideo = 1;
			
			if ($("#video"+$activeVideo).is('*')) {
				$("#video"+$activeVideo).fadeIn();
				$("#videoInstructions").fadeIn();
			} else {
				$(".userinfo").fadeIn();
				$("#submissions").fadeIn();
				$("#videoInstructions").fadeOut();
				$('.current').removeClass().prev().addClass('current');
			}
		});
		
		
		
		break;
	case 3:
		$(".userinfo").fadeOut();
		$(".video-q-holder").fadeOut();
		$("#videoInstructions").fadeOut();
		$("#submissions").fadeOut(400, function() {
			
			$("#finalStep").fadeIn();
		});
		
		
		
		break;
	default:
	
		break;
	}
});


$('.thankYou').click(function () {

	$.post('/submit-app', {
        user: $(this).data('user'),
        job: $(this).data('job')
    }, function(data) {
	    if ((data == 1) == 1) {
	        $(this).hide();
			$('#finalPrev').hide();
			$("#thankYouMsg").fadeIn();
		}
    });
	
});


$('.saveButton').click(function(){

	$("#takeAway").fadeIn();
});
