$(document).ready(function() {

	$("div.bannerImg").cycle({ 
	    fx: 'fade',
	    timeout: 3000,
	    speed: 1000,
	    before: beforeCycle,
	    after: afterBCycle,
	    pause: 1,
	    prev: ".prev",
	    next: ".next"
	});
	
	function beforeCycle() {
	    $('.bannerOverlay').hide();
		var t1 = $(this).attr('data-title');
		var t2 = $(this).attr('data-bodytext');
		var link = $(this).attr('data-bannerlink');
		var label = $(this).attr('data-buttonLabel');
		var overlay = $(this).attr('data-overlay');
		if (overlay == true){
		  $('.bannerOverlay').show();
		}
		
		$('.bannerContent').slideUp(400, function() {
			$('.bannerContent h2').html(t1);
			$('.bannerContent .bannerBtn a').attr('href', link);
			$('.bannerContent .bannerBtn').html(label);
			$('.bannerContent p').html(t2);
			$('.bannerContent').slideDown();
		});
		
	}
	function afterBCycle(){
	   $('ul a','#bannerNav').each(function(){
            $(this).removeClass();
       });
       $('ul a:eq('+$(this).index()+')','#bannerNav').addClass('current');
	}
});