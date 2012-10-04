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
    
    var counter = 0;
    $('table').each(function() {
       $(this).children('thead').children('tr').children('th').each(function() {
          counter++; 
       }); 
       if(counter == 1) {
           $(this).addClass('oneTH');
       }
    });
    
    $('#steps').not(':has(li)').addClass('hide');
    
    var getHeight = $('#hrListJobs table').height();
    $('#confirm').css('height', getHeight);
    
});
function alertBox(liClass, message){
    if (document.getElementById('steps')){
        if ($('#steps li:last-child').hasClass('alert')){
            $('#steps li:last-child').removeClass('success').removeClass('fail').addClass(liClass).html('<span></span>'+message);
        }
        else{
            var liAlert = document.createElement('li');
            liAlert.className = "alert "+liClass;
            var liSp = document.createElement('span');
            var liTxt = document.createTextNode(message);
            liAlert.appendChild(liSp);
            liAlert.appendChild(liTxt);
            document.getElementById('steps').appendChild(liAlert);
        }
        $('#steps').removeClass('hide');
    }    
    
}