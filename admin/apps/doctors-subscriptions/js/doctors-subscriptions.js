$(document).ready(function(){
    
    $('#selectAll').change(function(){
        var selectAll = ($(this).attr('checked') == 'checked' || $(this).attr('checked') === true)?true:false;
        
        $('input','#adminTableList tbody:eq(1)').each(function(){
            $(this).attr('checked',selectAll);
        });
            
    });
});