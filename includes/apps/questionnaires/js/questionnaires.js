console.log("hellO");
$("#RQvalNUMBType").change(function(){
	console.log("changed the type to " + $(this).val());
	if($(this).val() == 1 || $(this).val() == 2){
		$("tr.option-row").show();
	}else{
		$("tr.option-row").hide();
	}
});