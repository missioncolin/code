$(document).ready(function(){
    $("div#testimonialQuotes").cycle({ 
	    fx: 'scrollRight',
	    timeout: 6000,
	    speed: 1000,
	    pause: 1,
	    after: afterTCycle
	});
    
    function afterTCycle(){
        $('#testimonialsNav a').each(function(){
            $(this).removeClass();
        });
        $('#testimonialsNav a:eq('+$(this).index()+')').addClass('current');
    }

});