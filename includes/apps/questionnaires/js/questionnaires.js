$("#RQvalNUMBType").change(function () {
    if ($(this).val() == 1 || $(this).val() == 2) {
        $("tr.option-row").show();
    } else {
        $("tr.option-row").hide();
    }
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
    
    $('<tr><td><label>' + $label + '</label></td><td colspan="2"><input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" value="" /> <a href="#" data-count="' + $count + '" data-label="' + $label + '" class="add">Add</a></td></tr>').insertAfter($(this).parent().parent());
    $(this).remove();
    return false;
});