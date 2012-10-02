$(document).ready(function() {
    
    $('#testimonialRotator') 
    .cycle({ 
        fx:     'fade', 
        speed:  'fast', 
        timeout: 8000, 
        pager:  '#testimonialNav' 
    });
    
    $(window).scroll(function() {
        var a=$(window).scrollTop();
        if(a > 280) {
            $('#card').addClass('fixed');
        } else {
            $('#card').removeClass('fixed');
        }
    });
    
    $('#companyLogo').customFileInput();
    $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
    
    $('#RQvalALPHQuestionnaire_Title').focus();
    
    $('.credits label').click(function(){
        $(this).addClass('selected').siblings().removeClass('selected');
    });
    
    $('table.simpleTable td').each(function() {
    	var content = $(this).html();
    	if(content == '&nbsp;') {
			$(this).addClass("empty");
		}
    });
    
    $('#jobManagerEdit #questionnaire').change(function(){
        $('#newQuestionnaire').val('');
        
        var selected = document.getElementById('questionnaire').options[document.getElementById('questionnaire').selectedIndex].value;
        
        console.log(selected);
        
        if ($('option:eq(1)',this).is(':selected')){
            $('#newQuestionnaire').attr('disabled',false);
            $('#rCreateNew').show();
        }
        else{
            $('#newQuestionnaire').attr('disabled',true);
            $('#rCreateNew').hide();
        }
        
    });
});