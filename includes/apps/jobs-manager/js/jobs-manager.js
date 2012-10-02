$(function () {
    
    
    $('.activate').click(function () {
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
        }, function () {
            $this.parent().parent().remove();
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