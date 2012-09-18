$("#RQvalNUMBType").change(function(){
	console.log("changed the type to " + $(this).val());
	if($(this).val() == 1 || $(this).val() == 2){
		$("tr.option-row").show();
	}else{
		$("tr.option-row").hide();
	}
});

$(".slider").each(function(){
	var slideVal = parseInt($(this).attr('alt'));
	//console.log(slideVal);
	if(!slideVal > 0) slideVal = 0;
	
	$(this).slider({
		range: "min",
		value: slideVal,
		min: 0,
		max: 20,
		step: 1,
		slide: function(event, ui) {
			var slideInput = $(this).attr('rel');
			$("#" + slideInput).val(ui.value);
			$('.sliderValueHolder[rel='+ slideInput +']').html(ui.value + "/10");
		}
	});
});