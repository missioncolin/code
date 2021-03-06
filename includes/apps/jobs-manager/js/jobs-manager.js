
$(function () {

    $('.activate').click(function(e) {
    	 e.preventDefault();
        var $jobID = $(this).data('job');
        var $expiry = $(this).data('expiry');
        var $this = $(this);
        var react = $(this);
        var linkText = document.createTextNode(location.host+"/apply/"+$jobID);
        confirmAction("Activate Joblink?", "Activating this joblink will cost you (1) credit");
        
        //client request - "make it green"
        $('.popUp').removeClass('fail').addClass('success');
        
        $('.popUp #popUpNo').on('click', clearPopUp);
        $('.popUp #popUpOk').on('click', function(){
               var parTD = react.parent();
               var parTR = react.parents('tr').index();     
		$.post('/reactivate-job', {
			job: $jobID
		}, function (data) {
			  if (data){

				$('.alert').removeClass('fail').addClass('success').html('<span></span>Joblink Re-activated Successfully. Your account was debited (1) credit');

				//show table
				$(".optionsTable").fadeOut();
				$(".optionsTable").remove();
				$(".activeTable").fadeIn();
			}else{
				$('.alert').removeClass('success').addClass('fail').html('<span></span>Joblink not Re-activated. '+data);
			}

			clearPopUp();
				
		}); 
     
       });
    });


    $('.activateList').click(function(e) {
    	 
    	if ($(this).data('credits') > 0) { 
	    	 e.preventDefault();
	        var $jobID = $(this).data('job');
	        var $expiry = $(this).data('expiry');
	        var $this = $(this);
	        var react = $(this);
	        var linkText = document.createTextNode(location.host+"/apply/"+$jobID);
	        confirmAction("Activate Joblink?", "Activating this joblink will cost (1) credit");
	        
	        //client request - "make it green"
	        $('.popUp').removeClass('fail').addClass('success');
	        
	        $('.popUp #popUpNo').on('click', clearPopUp);
	        $('.popUp #popUpOk').on('click', function(){
	               var parTD = react.parent();
	               var parTR = react.parents('tr').index();     
			$.post('/reactivate-job', {
				job: $jobID
			}, function (data) {
				if (data == 'success'){
					var credits = $('#loggedInButtons a:eq(0)').html().match(/^(\d+)\sCredit.*$/); 
					log(credits);
					//if (typeof credits != 'undefined' && credits[1] > 0 && typeof credits != 'null' && typeof credits[1] != 'null'){
					//if (data == 'success')
					var creditHTML = (parseInt(credits[1], 10) - 1)+' Credits';
					$('#loggedInButtons a:eq(0)').html(creditHTML);
					$('.alert').removeClass('fail').addClass('success').html('<span></span>Joblink Re-activated Successfully. Your account was debited one (1) credit');				
						
					location.reload();
					//}else{
					//	alertBox("fail", "Job not re-published. " + data + ". <a href='buy-job-credits'>Buy Credits?</a>");
					//}
	
				}else{
					//$('.alert').removeClass('success').addClass('fail').html('<span></span>Job not Re-published. '+data);
					alertBox("fail", "Joblink not re-activated. " + data + ". <a href='buy-job-credits'>Buy Credits?</a>");	
					
				}
	
	/* 			clearPopUp(); */
					
			}); 
	     
	       });
       }
       
       /* No credits left */
       else {
	       $('.popUp h2').html("No Credits Remaining");
           linkString = 'To activate the link you must purchase credits, click <a href="buy-job-credits?redirect=' + $(this).data('job') + '">here</a> to buy credits.';
	       $('.popUp p').html(linkString);
	       $('.popUp #popUpNo').hide();
	       $('#confirm').fadeIn();
	       
	       $('.popUp #popUpOk').click(function () {
		       clearPopUp();
	       })
       }
    });
    
    var clearPopUp = function(){
        $('#confirm').fadeOut('fast', function() {
	    	$('.popUp h2').empty();
	        $('.popUp p').empty();
	        $('.popUp #popUpOk').off('click');
	        $('.popUp #popUpNo').off('click'); 
	        $('.popUp').removeClass('success');
	        $('.popUp').removeClass('fail');
	        $('.popUp #popUpNo').show();
        });
    }
    
    var confirmAction = function(title, message){
        $('.popUp h2').html(title);
        $('.popUp p').html(message);
        $('#confirm').fadeIn();
    }

    
    $('.delete').click(function (e) {
        e.preventDefault();
        var $jobID = $(this).data('job');
        var $this = $(this);
        confirmAction("Delete Job?", "Once this job is deleted, you will no longer be able to edit it, or view applications");
        $('.popUp #popUpNo').on('click', clearPopUp);
        $('.popUp #popUpOk').on('click', function(){
            $.post('/delete-job', {
                job: $jobID
            }, function() {
                $this.parent().parent().remove();
                $('.alert').removeClass('fail').addClass('success').html('<span></span>Job Deleted Successfully');
            });
            clearPopUp();
        });
    }); 
    
    var successPopUp = function() {
        var $jobID = $(this).data('job');
        var $this = $(this);
        var link = $('.newJobAlert').text();
        $('.popUp').addClass('success');
        confirmAction("Your Job Link Has Been Created!", "Use this link in your job posting: <br /><strong>" + link + "</strong>");
        $('.jobAlertInput').focus();
        $('.jobAlertInput').select();
        $('.popUp #popUpNo').hide();
        $('.popUp #popUpOk').on('click', function() {
            clearPopUp();
        });
    }
    
    if ($('.newJobAlert').length) {
        successPopUp();
    }
    
    $('#jobManagerEdit #questionnaire').change(function(){
        $('#newQuestionnaire').val('');
        
        var selected = document.getElementById('questionnaire').options[document.getElementById('questionnaire').selectedIndex].value;
/*
        
        if ($('option:eq(1)',this).is(':selected')){
            $('#newQuestionnaire').attr('disabled',false);
            $('#rCreateNew').show();
        }
        else{
            $('#newQuestionnaire').attr('disabled',true);
            $('#rCreateNew').hide();
        }
        
*/
		console.log("new Questionnaire being called");
		
    }); 
    
    $('.reactivate').click(function(e) {
        e.preventDefault();
        var $jobID = $(this).data('job');
        var $expiry = $(this).data('expiry');
        var react = $(this);
        confirmAction("Re-activate Joblink?", "Re-activating this joblink will cost (1) credit");
        $('.popUp #popUpNo').on('click', clearPopUp);
        $('.popUp #popUpOk').on('click', function(){
            var parTD = react.parent();
            var parTR = react.parents('tr').index();
            $.post('/reactivate-job', {
                job: $jobID
            }, function(data) {
                if (data == 'success'){
                    //create active elements

                    var credits = $('#loggedInButtons a:eq(0)').html().match(/^(\d+)\sCredits$/);
                    if (typeof credits != 'undefined' && credits[1] > 0){
                        var creditHTML = (parseInt(credits[1], 10) - 1)+' Credits';
                        $('#loggedInButtons a:eq(0)').html(creditHTML);
                        
                        var domTR = document.getElementById('hrListJobs').getElementsByTagName('tr');
                        var actTD = domTR[parTR].getElementsByTagName('td');
                        
                       // domTR[parTR].
                        
                       // parTD.attr('colspan','1');
                        //$('a', parTD).removeClass('green').removeClass('reactivate').addClass('black').addClass('activate').html('Live - Un-Publish');
                       $('a', parTD).fadeOut("fast");
	                   $('a', parTD).replaceWith($expiry);
	                   $('a', parTD).fadeIn("slow");         

                        
                        var tdLink = document.createElement('td');
                        var linkText = document.createTextNode(location.host+"/apply/"+$jobID);
                        tdLink.appendChild(linkText);
                        
                        
                        //deit and delete buttons
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
                        
                        //remove columns under expiry and delete
                        
                        
                        $(actTD[1]).remove();
                        $(actTD[2]).remove();
                        $(actTD[2]).remove();
                       //$(actTD[3]).remove();
                        //$(actTD[2]).remove();
                       // $(actTD[5]).remove();
                        
                        
                        domTR[parTR].insertBefore(tdLink, actTD[1]); //add link to job
                        //domTR[parTR].remove(actTD[1]);
                    
                        domTR[parTR].appendChild(tdEdit); //add edit button
                        domTR[parTR].appendChild(tdDel); //add delete butotn
                        //actTD[1].child().replaceWith(tdLink);
                       // actTD[2].replaceWith(tdEdit);
                       // actTD[3].replaceWith(tdDel);
                       
                       // console.log(actTD[0]);
                       // console.log(actTD[1]);
                       // console.log(actTD[2]);
                       // console.log(actTD[3]);
                       //  console.log(actTD[4]);
                       //   console.log(actTD[5]);

                       
                        $('.alert').removeClass('fail').addClass('success').html('<span></span>Job Re-published Successfully. Your account was debited one (1) credit');
                        
                   
                    }else{
                    		$('.alert').removeClass('success').addClass('fail').html('<span></span>Job not Re-published. '+data);
                    }
                    clearPopUp();

                    location.reload();

                }
            });
            // clearPopUp();
        });
        
    });
    
    
    $('.reactivateLanding').click(function(e) {
        e.preventDefault();
        var $jobID = $(this).data('job');
        var react = $(this);
        confirmAction("Activate Joblink?", "Activating this joblink will cost (1) credit");
        $('.popUp #popUpNo').on('click', clearPopUp);
        $('.popUp #popUpOk').on('click', function(){
            var parTD = react.parent();
            var parTR = react.parents('tr').index();
            $.post('/reactivate-job', {
                job: $jobID
            }, function(data) {
                if (data == 'success'){
                    //create active elements
                    var credits = $('#loggedInButtons a:eq(0)').html().match(/^(\d+)\sCredits$/);
                    if (typeof credits != 'undefined' && credits[1] > 0){

	                 $('.reactivateLanding').remove();  
	                 $('.successAlert').html('Your job has been published!<br/>');    
                        $('.alert').removeClass('fail').addClass('success').html('<span></span>Job Re-activated Successfully. Your account was debited (1) credit');
                        
                   
                    }else{
                    		$('.alert').removeClass('success').addClass('fail').html('<span></span>Job not Re-activated. '+data);
                    }
                    clearPopUp();
                    
                }
            });
            clearPopUp();
        });
        
    });
    
    
    $('.buy').click(function(e){
        e.preventDefault();
        var $jobID = $(this).data('job');
        var react = $(this);
        confirmAction("Re-activate Joblink?", "You do not have enough credits. Re-activating this joblink will cost (1) credit.<br />Do you want to buy credits?");
        $('.popUp #popUpNo').on('click', function(){
            clearPopUp();
        });
        $('.popUp #popUpOk').on('click', function(){
            clearPopUp();
            window.location.href = react.attr('href');
        });
    });
   
    
    $('.grade').click(function () {
        var $application = $(this).data('application');
        var $grade = $(this).data('grade');
        
        if ($(this).attr("class") != 'grade btn black') {
	        $grade = 'none';
        }
        
        console.log($grade);
        
        var $this = $(this);
        
        $.post('/grade-applicant', {
            application: $application,
            grade: $grade
        }, function (response) {
	            $this.siblings().removeClass('green').removeClass('yellow').removeClass('red').addClass('black');
	            
	            if (response == 'black') {
		            $this.removeClass('green').removeClass('yellow').removeClass('red');
		            $this.addClass('black');
		        }
		        else {
			        $this.removeClass('black');
			        $this.addClass(response);
		        }

        });
    });
    
    
    var currentVideo = 1;
    var numberOfVideos = $('#video-answers .answer-box').length;
    
    $('#va-prev').click(function () {
    	
        $('#video-answers .answer-box:nth-child('+currentVideo+')').fadeOut(400, function() {
	        
	        if (currentVideo == 1) {
	    		currentVideo = numberOfVideos;
	        } else {
		        currentVideo--;
	        }
	        
	        $('#video-answers .answer-box:nth-child('+currentVideo+')').show();
        });
    });
    
    $('#va-next').click(function () {
        
        $('#video-answers .answer-box:nth-child('+currentVideo+')').fadeOut(400, function() {
	        
	        if (currentVideo == numberOfVideos) {
	    		currentVideo = 1;
	        } else {
		        currentVideo++;
	        }
	        
	        $('#video-answers .answer-box:nth-child('+currentVideo+')').show();
        });
    }); 
    
    $(".video-thumbnail").click(function() {
	   
	   var newVideo = parseInt($(this).data('vidnumber'));
	   
		$('#video-answers .answer-box:nth-child('+currentVideo+')').fadeOut(400, function() {
			
			currentVideo = newVideo;
			
			$('#video-answers .answer-box:nth-child('+currentVideo+')').show();
			
		});
	   
    });
      
});