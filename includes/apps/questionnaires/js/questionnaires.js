// Stores count of each question to tell 
// total number of questions added per 'step'
totalCountDD = new Array();
totalCountDD.push(1);

totalCountQ = new Array();
totalCountQ.push(1);

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
        max: 20,
        step: 1,
        slide: function (event, ui) {
            var slideInput = $(this).attr('rel');
            $("#" + slideInput).val(ui.value);
            $('.sliderValueHolder[rel=' + slideInput + ']').html(ui.value + "/20");
        }
    });
});

/* Initialize first slider */
$('div[id^="idealSlider_"]').each( function() {

	$(this).slider({
        range: "max",
        min: 0,
        max: 50,
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
        max: 50,
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

    $count = +$(this).data('count') + 1;
    totalCountDD.push($count);
/*     console.log(totalCount); */
    
    $label = $(this).data('label');
    
    $('<tr><td>' + $label + '</td><td colspan="2"><input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" placeholder="Select a default question, or create your own." /><br> <select class="DefaultQs_' + $count + '"" name="Generic Questions" style="width:400px;"><option>Optionally select a default question.</option><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select><a href="#" data-label="' + $label + '" data-count="' + $count + '" class="removeDropDown btn red">&nbsp;x</a><br><a href="#" data-count="' + $count + '" data-label="' + $label + '" class="add_dropdown_q btn blue">Add Another Question</a></td></tr>').insertAfter($(this).parent().parent());
    $(this).remove();
    return false;
});


// Add slider question
$('.add').live('click', function() { 

    $count = +$(this).data('count') + 1;
    $label = $(this).data('label');
    
    totalCountQ.push($count);
    
    $('<tr><td>' + $label + '</td><td colspan="2"><input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" placeholder="Required Skill" value="" /></br></br><div class="experienceSlider"><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_' + $count + '">0</span><input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_' + $count + '" value=""/></br><div class="idealSlider" id="idealSlider_' + $count + '" data-count="' + $count + '" data-value="0"></div></div></br><a href="#" data-count="' + $count + '" data-label="' + $label + '" class="add btn blue">Add Another Question</a><a href="#" data-count="' + $count + '" class="removeSkillQ btn red">&nbsp;x</a></td></tr>').insertAfter($(this).closest('tr'));
    $(this).remove();
    
    /* Trigger the new slider */
    $("#idealSlider_" + $count).trigger('initIdealSlider'); 
    return false;
});

// Add an extra question to the edit page
$('.addEditQuestion').click(function () {
	
	editCount = editCount + 1;
	
	var thisID = $(this).data('id') + editCount;
	
	if ($(this).data('type') == 3) {
		
		$('<tr><td width="30%"><label for="RQvalALPHQuestion_' + thisID + '">How Many Years Experience...</label></td><td><input type="text" class="' + thisID + '" name="RQvalALPHQuestion_' + thisID + '_new_3" value=""/></br></br><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_' + thisID + '">0</span><input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_' + thisID + '" value=""/></br><div class="idealSlider" id="idealSlider_' + thisID + '" data-count="' + thisID + '" data-value="0"></div></br><td width="5%"><a href="#" data-type="3" id="' + thisID + '" class="removeQuestion"> x</a></td></tr>').insertBefore($(this).closest('tr').parent());
	
	/* Trigger the new slider */
    $("#idealSlider_" + thisID).trigger('initIdealSlider'); 
    	
	}
	
	else {
		
		$('<tr><td width="30%"><label for="RQvalALPHQuestion_' + thisID + '">Question</label></td><td><input type="text" class="' + thisID + '" placeholder="Select a default question, or create your own." name="RQvalALPHQuestion_' + thisID + '_new_4" value=""/><br><select class="DefaultQs_' + thisID + '"" name="Generic Questions" style="width:400px;"><option>Optionally select a default question.</option><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select></td><td width="5%"><a href="#" data-type="4" id="' + thisID + '" class="removeQuestion"> x</a></td></tr>').insertBefore($(this).closest('tr').parent());
		
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
	
	// If at first question, replace whatever is here with an option to create a new question
	// otherwise, just stick the 'add question' to the previous 	
	if (totalCountQ.length == 1) {

		$('#RQvalALPHQuestion_' + $count).attr('value', "");
		$('#idealSlider_' + $count).slider({value: 0});
		$('#idealValue_' + $count).html(0);
	    $label = $(this).data('label');
		
	}
	
	// If at the end of the list, remove and append 'add' link to previous question
	else if (($count == totalCountQ[totalCountQ.length - 1]) && (totalCountQ.length != 1)){
		
		console.log(totalCountQ);
		totalCountQ.splice(totalCountQ.indexOf($count), 1);

		$('#RQvalALPHQuestion_' + $count).remove();
		$('#idealValue_' + $count).remove();
		$(this).closest('tr').remove();
		$(this).closest('td').remove();
		$('<a href="#" data-count="1" data-label="How many years experience…" class="add btn blue">Add Another Question</a>').insertAfter($('#idealSlider_' + (totalCountQ[totalCountQ.length - 1])));
		$(this).remove();
		
	}
	else {
		
		totalCountQ.splice(totalCountQ.indexOf($count), 1);
		
		// If not at end, just remove 'add' since likely doesn't have an 'add' link anyways
		$('#RQvalALPHQuestion_' + $count).remove();
		$('#idealValue_' + $count).remove();
		$(this).closest('tr').remove();
		$(this).closest('td').remove();
		$(this).remove();
	}

	
	return false;
});



$('.removeDropDown').live('click', function() {
	
	$count = $(this).data('count');
	console.log("Count: " + $count);

	// If at first question, replace whatever is here with an option to create a new question
	// otherwise, just stick the 'add question' to the previous 	
	if (totalCountDD.length == 1) {

		$('.DefaultQs_' + $count).remove();
		$('#RQvalALPHQuestion_' + $count).remove();
		
		console.log("Only one element");
		
	    $label = $(this).data('label');
	    $('<input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" placeholder="Select a default question, or create your own." /><select class="DefaultQs_' + $count + '"" name="Generic Questions" style="width:400px;"><option>Optionally select a default question.</option><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select>').insertBefore($(this));
	    $('<a href="#" data-label="' + $label + '" data-count="' + $count + '" class="removeDropDown btn red">&nbsp;x</a>').insertBefore($(this));
	    
		$(this).remove();
		
	}
	// If at the end of the list, remove and append 'add' link to previous question
	else if (($count == totalCountDD[totalCountDD.length - 1]) && (totalCountDD.length != 1)){
		
		totalCountDD.splice(totalCountDD.indexOf($count), 1);
		$('.DefaultQs_' + $count).remove();

		$('#RQvalALPHQuestion_' + $count).remove();
		$('<a href="#" data-count="' + $count + '" data-label="' + $label + '" class="add_dropdown_q btn blue">Add Another Question</a>').insertAfter($('.DefaultQs_' + (totalCountDD[totalCountDD.length - 1])));
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