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
    
    $('<tr><td colspan="2"><input size="75" type="text" name="RQvalALPHQuestions[]" id="RQvalALPHQuestion_' + $count + '" value="" /> <a href="#" data-count="' + $count + '" data-label="' + $label + '" class="add">Add Another Question</a></td></tr>').insertAfter($(this).parent().parent());
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