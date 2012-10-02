$(function () {
    
    
    $('.activate').click(function() {
        var $jobID = $(this).data('job');
        var $this = $(this);
        $.post('/toggle-job', {
            job: $jobID
        }, function () {
            if ($this.hasClass('grey')) {
                $this.addClass('black').removeClass('grey').html('Active');
            } else {
                $this.addClass('grey').removeClass('black').html('Inactive');
            }
        });
    });
    
    
    $('.delete').click(function () {
        var $jobID = $(this).data('job');
        var $this = $(this);
        $.post('/delete-job', {
            job: $jobID
        }, function() {
            $this.parent().parent().remove();
        });
    }); 
    
    $('#jobManagerEdit #questionnaire').change(function(){
        $('#newQuestionnaire').val('');
        
        var selected = document.getElementById('questionnaire').options[document.getElementById('questionnaire').selectedIndex].value;
        
        if ($('option:eq(1)',this).is(':selected')){
            $('#newQuestionnaire').attr('disabled',false);
            $('#rCreateNew').show();
        }
        else{
            $('#newQuestionnaire').attr('disabled',true);
            $('#rCreateNew').hide();
        }
        
    }); 
    
    $('.reactivate').click(function() {
        
        $.post('/reactivate-job', {
            job: $jobID
        }, function(data) {
            console.log(data);
            if (data == 'success'){
                //create active elements
                var credits = $('#loggedInButtons a:eq(0)').html().match(/^(\d+)\sCredits$/);
                if (typeof credits != 'undefined' && credits[1] > 0){
                    var creditHTML = (parseInt(credits[1], 10) - 1)+' Credits';
                    $('#loggedInButtons a:eq(0)').html(creditHTML);
                    var $jobID = $(this).data('job');
                    var $this = $(this);
                    var parTD = $(this).parent();
                    var parTR = $(this).parents('tr').index();
                    console.log(parTD);
                    console.log(parTR);
                    
                    var domTR = document.getElementById('hrListJobs').getElementsByTagName('tr');
                    
                    parTD.attr('colspan','1');
                    $('a', parTD).removeClass('green').removeClass('reactivate').addClass('black').addClass('activate').html('active');
                    
                    var tdDel = document.createElement('td');
                    var aDel = document.createElement('a');
                    aDel.setAttribute('href', '#');
                    aDel.setAttribute('data-job', $jobID);
                    aDel.className = "btn red delete";
                    var delText = document.createTextNode('Delete');
                    aDel.appendChild(delText);
                    tdDel.appendChild(aDel);
                    var tdEdit = document.createElement('td');
                    var aEdit = document.createElement('a');
                    aEdit.setAttribute('href', '/edit-job?id='+$jobID);
                    aEdit.className = "btn";
                    var editText = document.createTextNode('Edit');
                    aEdit.appendChild(editText);
                    tdEdit.appendChild(aEdit);
                    
                    domTR[parTR].appendChild(tdDel);
                    domTR[parTR].appendChild(tdEdit);
                    //success message in a dialog box of some kind
                }
                
            }
            else{
                //error message in a dialog box of some kind
            }
        });
    });
   
    
    $('.grade').click(function () {
        var $application = $(this).data('application');
        var $grade = $(this).data('grade');
        var $this = $(this);
        $.post('/grade-applicant', {
            application: $application,
            grade: $grade
        }, function (response) {
            $this.siblings().removeClass('green').removeClass('yellow').removeClass('red').addClass('black');
            $this.removeClass('black').addClass(response);
        });
    });    
});