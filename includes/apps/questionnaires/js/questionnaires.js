$("#RQvalNUMBType").change(function () {
    if ($(this).val() == 1 || $(this).val() == 2) {
        $("tr.option-row").show();
    } else {
        $("tr.option-row").hide();
    }
});

$('select').live("change", function() {

    var thisClass = $(this).attr('class');
    var getClassCount = thisClass.split('_');
    var classCount = getClassCount[1];
    console.log(classCount);

    console.log($(this).find(":selected").text());
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


$('.add').live('click', function() { 
    
    $count = +$(this).data('count') + 1;
    $label = $(this).data('label');
    
    $('<tr><td>' + $label + '</td><td colspan="2"><input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" placeholder="Required Skill" value="" style="width:250px;" /> <select class="DefaultQs_' + $count + '"" name="Generic Questions" style="width:400px;"><option value="fiveYearPlan">What are your goals and objectives for the next five years?</option><option value="careerGoals">How do you plan to achieve your career goals?</option><option value="rewarding">What do you find most rewarding in your career?</option><option value="chooseCareer">Why did you choose the career for which you are in?</option><option value="strengthWeakness">What are your strengths, weaknesses, and interests?</option><option value="professorDescribe">How do you think a friend or professor who knows you well would describe you?</option><option value="difficultPerson">Describe how you handle working with a difficult person?</option><option value="greatestEffort">What motivates you to put forth your greatest effort? Describe a situation in which you did so.</option><option value="evaluateSuccess">How do you determine or evaluate success?</option><option value="contributionOrganization">In what ways do you think you can make a contribution to our organization?</option><option value="contributionProject">Describe a contribution you have made to a project on which you worked.</option><option value="successfulManager">What qualities should a successful manager/leader/supervisor/etc. possess?</option><option value="occasionDisagree">Describe how you handle an occasion when you disagree with a supervisor\'s decision?</option><option value="threeAccomplishments">What two or three accomplishments have given you the most satisfaction? Why?</option><option value="workEnvironment">In what kind of work environment are you most comfortable?</option><option value="underPressure">How do you work under pressure?</option><option value="teamEnvironment">What role do you best fit in when working in a team environment? Why?</option><option value="seekPosition">Why did you decide to seek a position with our organization?</option><option value="threeImporatnt">What two or three things would be most important to you in your job?</option><option value="evaluateOrganization">What criteria are you using to evaluate the organization for which you hope to work?</option><option value="relocationConstraints">How would you view needing to relocate for the job? Do you have any constraints on relocation?</option><option value="travelAmount">Are you comfortable with the amount of travel this job requires?</option><option value="sixMonths">Are you willing to spend at least six months as a trainee?</option></select> <br><a href="#" data-count="' + $count + '" data-label="' + $label + '" class="add">Add Another Question</a></td></tr>').insertAfter($(this).parent().parent());
    $(this).remove();
    return false;
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