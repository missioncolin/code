$(function () {
    
    
    $('.activate').click(function() {
        var $jobID = $(this).data('job');
        var $this = $(this);
        $.post('/toggle-job', {
            job: $jobID
        }, function () {
            if ($this.hasClass('grey')) {
                $this.addClass('black').removeClass('grey').html('Remove');
            } else {
                $this.addClass('grey').removeClass('black').html('Publish');
            }
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

    
    $('.delete').click(function () {
        var $jobID = $(this).data('job');
        var $this = $(this);
        confirmAction("Delete Job?", "Once this job is deleted, you will no longer be able to edit it, or view applications");
        $('.popUp #popUpNo').on('click', clearPopUp);
        $('.popUp #popUpOk ').on('click', function(){
            $.post('/delete-job', {
                job: $jobID
            }, function() {
                $this.parent().parent().remove();
                $('.alert').removeClass('fail').addClass('success').html('<span></span>Job Deleted Successfully');
            });
            clearPopUp();
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
        var $jobID = $(this).data('job');
        var react = $(this);
        confirmAction("Re-Publish Job", "Re-publishing this job will cost one (1) credit");
        $('.popUp #popUpNo').on('click', clearPopUp);
        $('.popUp #popUpOk ').on('click', function(){
            var parTD = react.parent();
            var parTR = react.parents('tr').index();
            $.post('/reactivate-job', {
                job: $jobID
            }, function(data) {
                clearPopUp();
                if (data == 'success'){
                    //create active elements
                    var credits = $('#loggedInButtons a:eq(0)').html().match(/^(\d+)\sCredits$/);
                    if (typeof credits != 'undefined' && credits[1] > 0){
                        var creditHTML = (parseInt(credits[1], 10) - 1)+' Credits';
                        $('#loggedInButtons a:eq(0)').html(creditHTML);
                        
                        var domTR = document.getElementById('hrListJobs').getElementsByTagName('tr');
                        var actTD = domTR[parTR].getElementsByTagName('td');
                        
                        parTD.attr('colspan','1');
                        $('a', parTD).removeClass('green').removeClass('reactivate').addClass('black').addClass('activate').html('Remove');
                        
                        var tdLink = document.createElement('td');
                        var linkText = document.createTextNode(location.host+"/apply/"+$jobID);
                        tdLink.appendChild(linkText);
                        
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
                        
                        domTR[parTR].insertBefore(tdLink, actTD[1]);
                        domTR[parTR].appendChild(tdDel);
                        domTR[parTR].appendChild(tdEdit);
                        $('.alert').removeClass('fail').addClass('success').html('<span></span>Job Re-published Successfully. Your account was debited one (1) credit');
                    }
                    
                }
                else{
                    $('.alert').removeClass('success').addClass('fail').html('<span></span>Job not Re-published. '+data);
                }
                clearPopUp();
            });
            
        });
        
    });
    
    $('.buy').click(function(e){
        e.preventDefault();
        var $jobID = $(this).data('job');
        var react = $(this);
        confirmAction("Re-Publish Job", "You do not have enough credits. Re-publishing this job will cost one (1) credit.<br />Do you want to buy credits?");
        $('.popUp #popUpNo').on('click', function(){
            clearPopUp();
        });
        $('.popUp #popUpOk ').on('click', function(){
            clearPopUp();
            window.location.href = react.attr('href');
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