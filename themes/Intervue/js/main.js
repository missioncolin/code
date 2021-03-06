$(document).ready(function() {
    
    $('#testimonialRotator') 
    .cycle({ 
        fx:     'fade', 
        speed:  'fast', 
        timeout: 8000, 
        pager:  '#testimonialNav' 
    });
    
    /*
$(window).scroll(function() {
        var a=$(window).scrollTop();
        if(a > 280) {
            $('#card').addClass('fixed');
        } else {
            $('#card').removeClass('fixed');
        }
    });
*/

    $('.credits #credit_2').next('label').append('<span class="saleTag">Save<br />10%</span>');
    $('.credits #credit_3').next('label').append('<span class="saleTag">Save<br />25%</span>');
    
    $('#companyLogo').customFileInput();
    $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
    
    $('#RQvalALPHQuestionnaire_Title').focus();
    
    $('.credits label').click(function(){
        $(this).addClass('selected').siblings().removeClass('selected');
    });
    
    $('#credit_1').click();
    
    $('#applicantList table th:last-child').addClass('lastly');
    
    $('#applications #hrListJobs table.simpleTable tr td:nth-child(3n)').addClass('expiryFallBack');
    
    $('#sectionOne li:nth-child(odd)').addClass('oddHome');
    
    $('table.simpleTable td').each(function() {
    	var content = $(this).html();
    	if(content == '&nbsp;') {
			$(this).addClass("empty");
		}
    });
    
    var counter = 0;
    $('table').each(function() {
       $(this).children('thead').children('tr').children('th').each(function() {
          counter++; 
       }); 
       if(counter == 1) {
           $(this).addClass('oneTH');
       }
    });
    
    $('ul.sliderList li:nth-child(3n)').addClass('noMargins');
    
    $('#newFooter ul:nth-child(3)').addClass('lastColumn');
    
    $('#invoice table.simpleTable td:nth-child(even)').addClass('rightRounds');
    
    
    $('#steps').not(':has(li)').addClass('hide');
    
    $('body#configure-question').keyup(function(e) {
	    console.log('keyup called');
	    var code = e.keyCode || e.which;
	    if (code == '9') { 
    
	       var count = $('a.add').last().data('count');
	       var label = $('a.add').last().data('label');      

	       totalCountQ.push(count);
		   console.log(totalCountQ);

           $('<tr><td colspan="2"><div class="sliderText"><input size="75" type="text" autocomplete="off" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + count + '" placeholder="Example: Data Entry" value="" /></div><div class="experienceSlider"><label for="idealSlider">Ideal Years of Experience  </label><span id="idealValue_' + count + '">15</span><input size="10" name="idealValues[]" type="hidden" id="hiddenIdealValue_' + count + '" value=""/></br><div class="idealSlider" id="idealSlider_' + count + '" data-count="' + count + '" data-value="15"></div></div><a href="#" data-count="' + count + '" class="removeSkillQ btn">x</a></td></tr>').insertBefore($('a.add').closest('tr'));
            $('a.add').last().data('count', count + 1);
            $('input:text').last().focus();
            $("#idealSlider_" + count).trigger('initIdealSlider'); 
            return false;

	    }
    });

});

function newVideo() {
     $(".nextbutton").hide();
     $(".instButton").show();
}

function doneVideo() {
    if(!$("#instructions").is(":visible")) {
        $(".nextbutton").show();
    }
}

function alertBox(liClass, message){
    if (document.getElementById('steps')){
        if ($('#steps li:last-child').hasClass('alert')){
            $('#steps li:last-child').removeClass('success').removeClass('fail').addClass(liClass).html('<span></span>'+message);
        }
        else{
            var liAlert = document.createElement('li');
            liAlert.className = "alert "+liClass;
            liAlert.innerHTML = '<span></span>'+message;
            document.getElementById('steps').appendChild(liAlert);
        }
        $('#steps').removeClass('hide');
    }    
    
}

/* Disables form submission on enter key -- for job creation */
function disableKeyPress(event) {

	var code = event.keyCode || event.which;

	if (code == '13') {
		event.preventDefault();
		return false;
	}
}
