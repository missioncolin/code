/**
* Admin methods for fullCalendar
* This file requires /includes/js/calendar/calendar.js which contains the full calendar
* created by Karen Laansoo November 10, 2010
*/
var sysTags;
/**
* reset form fields when a new event or day is clicked
* Force events to use the existing methods in tag-it
* @param string eventTags
*/
var update_tag_it = function(eventTags){
	//clear select element
	//clear and reset initialTags
	if ($('.tagit-close').length > 0){
		$('.tagit-close').each(function(){
			$(this).click();//force tags to be removed when dialog is opened for add/edit event
		});
	}
	if (eventTags != false && eventTags != ""){
		var eTags = eventTags.split(",");
		if (eTags.length > 0){
			for (var i = 0; i < eTags.length; i++){
				$('.tagit-input').val(eTags[i]);
				$('.tagit-input').blur();
			}
		}
	}
}
/**
* Initialize auto completer with pre-defined set of values
*/
var build_tag_it = function(){
	sysTags = new Array();
	var key = Math.round((Math.random() + Math.random()) * 100);
	$.get('../../ajax/apps/calendar/adminCalendar.php?ran='+key,({'isAjax':'y','request':'tags'}),function(data){
		if (data != false && data != ""){
			sysTags = data.split(",");
		}
		else{
			sysTags = new Array(0);
		}
		$('#editTags').tagit({
			tagSource: sysTags, 
			triggerKeys: ['enter', 'comma', 'tab'],
			select: true
		});
	});
}
/**
*jQuery gets css property in RGB. this converts to hex for the edit calendar form
*@param string rgb
*/
var rgb2hex = function(rgb){
	if (!rgb.match(/^#[A-Fa-f0-9]{3,6}$/)){
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    	function hex(x) {
        	return ("0" + parseInt(x).toString(16)).slice(-2);
    	}
    	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    }
    else{
    	return rgb;
    }
}
/**
* hides the calendar name element and the highlight colour element, but displays save button (for delete)
*/
var show_hide_edit_calendar_elements = function(){
	$('#calEditElements').hide();
	$('#calEditElements :input[name=calendarName]').val('');
	$('#calEditButtons').show();
}

/**
* displays the notice or message element based on id
* @param string message
* @param string id
*/
var display_complete_message = function(message,id){
	$('p.message',id).html(message).show();
}
/**
* set events for calendar list on lhs menu
* one click function for the text
* one click function for the checkbox
*/
var set_calendar_list_events = function(){
	if ($('#leftColAdmin ul').length == 1){
		$('#leftColAdmin ul li').each(function(){
			//should already have cursor pointer
			$('span',this).css({'cursor':'pointer'});
			$('span',this).click(function(){
				//open edit dialog
				var wineryID = $(this).parent().attr('id').replace("winery","");
				var wineryName = "None";
				$('#wineryEdit option').each(function(){
					if ($(this).val() == wineryID && wineryID != "0"){
						wineryName = $(this).text();
						$(this).attr("selected",true);
					}
				});
				$('p.message','#dlgEditCalendar').empty();
				$('#btnEditCalendar').show();
				$('#btnDeleteCalendar').show();
				$('#calEditID').val($(this).attr('id').replace('cal',''));
				$('#currentCalendarName').text($(this).text());
				$('#currentWinery').html(wineryName);
				$('#currentCalendarColor').css({'backgroundColor':$(this).css('color')}).html('&nbsp;&nbsp;&nbsp;&nbsp;');
				$('#calEditElements').hide();
				$('#calEditButtons').hide();
				$(':input[name=backgroundColorEdit]','#frmEditCalendar').val(rgb2hex($(this).css('color'))).css({'backgroundColor':$(this).css('color')});
				$('#frmEditCalendar').show();
				$('#dlgEditCalendar').dialog('open');
			});
			$(':input',this).click(function(){
				//filter calendar - start tuesday
				($(this).attr('checked') == true)?add_remove_event_sources('addEventSource',$(this).val()):add_remove_event_sources('removeEventSource',$(this).val());				
			});
		});
	}
}
/**
* Load css into body styles tag when user updates a main calendar
* Events are re-fetched when a calendar is updated to apply new css styles
*/
var update_cal_css = function(){
	var key = Math.round((Math.random() + Math.random()) * 100);
	$.get('../../ajax/apps/calendar/adminCalendar.php?ran='+key,({'isAjax':'y','request':'style'}),function(data){
		if (data != 'false'){
			$('style:eq(0)').empty();
			$('style:eq(0)').html(data);
		}
	});
}
/**
* Update the calendar list when a user adds or edits a new event when a calendar is added or updated
*/
var update_add_edit_select_cal = function(){
	if ($('#leftColAdmin ul').length == 1){
		$(':input[name=calendarID] option:gt(0)','#frmEditEvents').remove();
		$('#leftColAdmin ul li').each(function(index){
			$(':input[name=calendarID]','#frmEditEvents').append('<option value="'+$('span',this).attr('id').replace("cal","")+'>'+$('span',this).html()+'</option>');
			$(':input[name=calendarID] option:eq('+(1+index)+')','#frmEditEvents').css('color',$('span',this).css('color'));
		});
	}
}
/**
* Remove and replace calendars list on lhs menu with data returned from ajax request
* @see set_calendar_list_events()
*/
var load_cals_list = function(){
	var key = Math.round((Math.random() + Math.random()) * 100);
	$.get('../../ajax/apps/calendar/adminCalendar.php?ran='+key,({'isAjax':'y','request':'list'}),function(data){
		$('#calsList').remove();
		$('#leftColAdmin p:eq(0)').after(data);
		//add events here for checkboxes and text-data (all should have cursor:pointer)
		set_calendar_list_events();
		update_add_edit_select_cal();
	});
}
/**
* Method called once response data is received from add new calendar request
* set up a new event source to fullcalendar
* reload the calendars list
* @param string data
* @param object form
* @see load_cals_list()
* @see display_complete_message()
*/
var complete_add_new_calendar = function(data,form){
	$('#imgNewCalLoad').hide();
	var message = "Your calendar was saved!<br />You can now start adding events.";
	if (isNaN(parseInt(data,10)) == true){
		message = "There was an error adding your data: "+data;
	}
	else{
		update_cal_css();
		load_cals_list();
		add_remove_event_sources('addEventSource',data);
	}
	display_complete_message(message,'#dlgNewCalendar');
}
/**
* Method called once response data is received from the edit main calendar details request
* @param string data
* @param object form
* @see load_cals_list()
* @see display_complete_message()
*/
var complete_edit_calendar = function(data,form,refetch){
	$('#imgEditCalLoad').hide();
	var message = 'Your changes were updated successfully.';
	if (data != "true"){
		message = "There was an error updating your calendar: "+data;
	}
	update_cal_css();
	load_cals_list();
	//update css for event items
	var calID = $(':input[name=calendarID]',form).val();
	if (refetch == true){
		$('#calendar').fullCalendar('refetchEvents');
	}
	display_complete_message(message,'#dlgEditCalendar');

}
/**
* Method called once response data is received from delete (disable) main calendar request
* remove the fullCalendar feed
* @param string data
* @param object form
* @see complete_edit_calendar()
*/
var complete_delete_calendar = function(data,form){
	//delete source of disabled calendar from fullcalendar if successfully disabled
	if (data == 'true'){
		add_remove_event_sources('removeEventSource',$(':input[name=calendarID]',form).val());
	}
	complete_edit_calendar(data,form,false);
}
/**
* Send an ajax request to update main calendar information. 
* This includes delete, which is really disable
* @param object form
* @param string action
* @see complete_edit_calendar()
* @see complete_delete_calendar()
*/
var update_calendar = function(form,action){
	$(form).hide();
	$('#imgEditCalLoad').show();
	if (action == 'u'){
		var calendarName = ($(':input[name=calendarName]',form).val() != "")?$(':input[name=calendarName]',form).val():$('#currentCalendarName').text();
		var params = {'itemID':$(':input[name=calendarID]',form).val(),'eventBackgroundColor':$(':input[name=backgroundColorEdit]',form).val(),'calendarName':calendarName,'wineryID':$(':input[name=wineryID]',form).val()};
	}
	else{
		params = {'itemID':$(':input[name=calendarID]',form).val()};
	}
	var key = Math.round((Math.random() + Math.random()) * 100);
	$.post('../../ajax/apps/calendar/adminCalendar.php?isAjax=y&request=calendar&action='+action+'&ran='+key,(params),function(data){
		(action == 'u')?complete_edit_calendar(data,form,true):complete_delete_calendar(data,form);
	});
}
/**
* Common method to update form date and time fields in the edit events form 
* This is used for drag/drop, event resize (time) and for day click
* Drag/drop submits the edit form blindly
* start and end parameters are date objects set by fullCalendar
* @param object start
* @param object end
* @param object form
*/
var set_add_edit_date_vals = function(start,end,form){
	var startDay = (start.getDate() < 10)?"0"+start.getDate():start.getDate();
	var endDay = (end.getDate() < 10)?"0"+end.getDate():end.getDate();
	var startHours = (parseInt(start.getHours(),10) > 11)?parseInt(start.getHours(),10) - 12:parseInt(start.getHours(),10);
	var startMM = (parseInt(start.getMinutes(),10) < 10)?"0"+parseInt(start.getMinutes(),10):parseInt(start.getMinutes(),10);
	var startTOD = (parseInt(start.getHours(),10) > 11)?'12':'0';
	startHours = (startHours < 10)?"0"+startHours:startHours;
	var endHours = (parseInt(end.getHours(),10) > 11)?parseInt(end.getHours(),10) - 12:parseInt(end.getHours(),10);
	endHours = (endHours < 10)?"0"+endHours:endHours;
	var endMM = (parseInt(end.getMinutes(),10) < 10)?"0"+parseInt(end.getMinutes(),10):parseInt(end.getMinutes(),10);
	var endTOD = (parseInt(end.getHours(),10) > 11)?'12':'0';
	var startMth = ((start.getMonth()+1) < 10)?"0"+(start.getMonth()+1):(start.getMonth()+1);
	var endMth = ((end.getMonth()+1) < 10)?"0"+(end.getMonth()+1):(end.getMonth()+1);
	var startDate = start.getFullYear()+'-'+startMth+'-'+startDay;
	var endDate = end.getFullYear()+'-'+endMth+'-'+endDay;
	$(':input[name=startDate]',form).val(startDate);
	$(':input[name=endDate]',form).val(endDate);	
	$(':input[name=startHH]',form).val(startHours);
	$(':input[name=startMM]',form).val(startMM);
	$(':input[name=startTOD]',form).val(startTOD);
	$(':input[name=endHH]',form).val(endHours);
	$(':input[name=endMM]',form).val(endMM);
	$(':input[name=endTOD]',form).val(endTOD);
}
/**
* Show hide start and end date for editing recurring events.
* Dates can only be modified if single instance is selected, else only times can be modified
* @param true|false disable
*/
var toggle_edit_recur_options = function(disable){
	$(':input[name=startDate]','#frmEditRecurring').attr('disabled',disable);
	$(':input[name=endDate]','#frmEditRecurring').attr('disabled',disable);
}
/**
* Set the field values for edit a recurring event dialog before it is opened
* The form is reset prior to being re-opened
* eventDetails is the original object set and passed from fullCalendar
* @param object eventDetails
* @see toggle_edit_recur_options()
* @see set_add_edit_date_vals()
*/
var open_edit_recurring_dialog = function(eventDetails){
	$('p.message','#dlgAddEditEvent').empty();
	document.getElementById('frmEditRecurring').reset();
	toggle_edit_recur_options(false);
	var form = $('#frmEditRecurring');
	$(':input[name=recurrenceID]',form).val(eventDetails.recurrenceID);
	$(':input[name=eventID]',form).val(eventDetails.id);
	set_add_edit_date_vals(eventDetails.start,eventDetails.end,form);
	form.show();
	$('#dlgEditRecurring').dialog({title:'Edit Recurring Event: '+eventDetails.title});
	$('#dlgEditRecurring').dialog('open');
}
/**
* Sets values in the edit event form. This is used for both drag/drop editing (blind) and event click editing
* eventDetails is the original object set and passed from fullCalendar
* @param object eventDetails
* @param object form
* @see set_add_edit_date_vals()
*/
var set_edit_form_element_vals = function(eventDetails,form){
	var calendarInfo = eventDetails.className.toString().split("_");
	var calendarID = calendarInfo[calendarInfo.length-1];
	$(':input[name=eventTitle]',form).val(eventDetails.title);
	$(':input[name=calendarID]',form).val(calendarID);
	$(':input[name=winery]',form).val(eventDetails.winery);
	$(':input[name=description]',form).val(eventDetails.description);
	$(':input[name=location]',form).val(eventDetails.location);
	set_add_edit_date_vals(eventDetails.start,eventDetails.end,form);
	if (eventDetails.allDay == true){
		$(':input[name=allDayEvent]',form).attr('checked',true);
	}
	$(':input[name=recurrence]',form).val(eventDetails.recurrence);
	if (eventDetails.recurrence !== 'None'){
		var recurrenceDetails = eventDetails.recurrenceDescription.toString().split(" ");
		var recurrenceInterval = (recurrenceDetails.length > 1)?recurrenceDetails:"1";
		$(':input[name=recurrenceInterval]',form).val(recurrenceInterval);
		$(':input[name=recurrenceEnd]',form).val(eventDetails.recurrenceEnd);
	}
	if (eventDetails.altUrl){
		$(':input[name=detailPage]',form).attr('checked',true);
		$(':input[name=detailsAlternateURL]',form).val(eventDetails.altUrl);
	}
	$(':input[name=eventSaveType]',form).val('u');
	$(':input[name=eventID]',form).val(eventDetails.id);
}
/**
* Reset edit event form based on event clicked, and open dialog
* eventDetails is the original object set and passed from fullCalendar
* @param object eventDetails
* @see set_edit_form_element_vals()
*/
var open_edit_event_dlg = function(eventDetails){
	var form = $('#frmEditEvents');
	$('p.message','#dlgAddEditEvent').empty();
	document.getElementById('frmEditEvents').reset();
	update_tag_it(eventDetails.tags);
	set_edit_form_element_vals(eventDetails,form);
	form.show();
	$('#dlgAddEditEvent').dialog({title:'Edit Event'});
	$('#dlgAddEditEvent').dialog('open');
}
/**
* Reset delete event form based on event clicked, and open dialog
* eventDetails is the original object set and passed from fullCalendar
* @param object eventDetails
*/
var open_delete_event_dlg = function(eventDetails){
	$('p.message','#dlgDeleteEvent').empty();
	var form = $('#frm_delete_event');
	document.getElementById('frm_delete_event').reset();
	$(':input[name=recurrenceID]',form).val('');
	$('#tbRecurring','#dlgDeleteEvent').hide();
	if (eventDetails.recurrence != 'None'){
		if (eventDetails.recurrenceID){
			$(':input[name=recurrenceID]',form).val(eventDetails.recurrenceID);
			$('#tbRecurring','#dlgDeleteEvent').show();
		}
	}
	$(':input[name=eventID]',form).val(eventDetails.id);
	form.show();
	$('#dlgDeleteEvent').dialog({title:'Delete: '+eventDetails.title});
	$('#dlgDeleteEvent').dialog('open');
}
/**
* Reset add event form based on day clicked, and open dialog
* jsEvent can be used to move dialog to specific position
* parameters are fullCalendar objects - multiDay is false if only one day is selected. Else, it's the last day in the drag select
* @param object date
* @param true|false allDay
* @param object jsEvent
* @param object view
* @param false|object multiDay
* @see set_edit_form_element_vals()
*/
var open_add_new_event = function(date,allDay,jsEvent,view,multiDay){
	update_tag_it(false);
	$('p.message','#dlgAddEditEvent').empty();
	var form = $('#frmEditEvents');
	document.getElementById('frmEditEvents').reset();
	var endDate = (multiDay != false)?multiDay:date;
	set_add_edit_date_vals(date,endDate,form);
	$(':input[name=eventSaveType]',form).val('a');
	if ((allDay == true && $(':input[name=startDate]',form).val() != $(':input[name=endDate]',form).val()) || (view.name != 'month' && allDay == true)){
		$(':input[name=allDayEvent]',form).attr('checked',true);
	}
	form.show();
	$('#dlgAddEditEvent').dialog({title:'Add a New Event'});
	$('#dlgAddEditEvent').dialog('open');
}
/**
* Submits an edit request when a user drags an event time, or drops an event to a new day
* This is a blind request so the user doesn't see it
* eventDetails fullCalendar objects, revertFunc is a callback that can be used if ajax fails, however, all events are refetched, so not used currently
* @param object eventDetails
* @param object reverFunc
* @see set_edit_form_element_vals()
*/
var send_drop_event = function(eventDetails,revertFunc){
	var form = $('#frmEditEvents');
	set_edit_form_element_vals(eventDetails,form);
	var params = form.serialize();
	var key = Math.round((Math.random() + Math.random()) * 100);
	$.post('../../ajax/apps/calendar/adminCalendar.php?isAjax=y&ran='+key+'&request=event&action='+$(':input[name=eventSaveType]',form).val(),(params),function(data){
		$('#calendar').fullCalendar('refetchEvents');
	});
}
/**
* this global function allows the fullcalendar defaults to be overridden by the admin functions so the same calendar code 
* can be used on the public page
*/
var adminDefaults = function(){
	var defaults = {
		editable: true,
		dayClick: function(data,allDay,jsEvent,view){
			$('#calendar td').each(function(){
				if ($(this).css('backgroundColor') != 'rgb(255, 255, 204)'){
					$(this).css('backgroundColor','transparent');
				}
			});
			if ($(this).css('backgroundColor') != 'rgb(255, 255, 204)'){
				$(this).css('backgroundColor','#99cccc');
			}
			//open_add_new_event(data,allDay,jsEvent,view,false);
		},
		selectable: true,
		selectHelper: true,
		select: function(start,end,allDay){
			//send end date or recurrence number to open_add_new_event
			open_add_new_event(start,allDay,{},$('#calendar').fullCalendar('getView'),end);
			$('#calendar').fullCalendar('unselect');
		},
		dragOpacity: {
        	month: 2,
        	''   : .5
    	},
    	eventClick: function(calEvent, jsEvent, view){
			show_event_details(calEvent,jsEvent,view);
			var dlg = '#dlgEventDetails';
			$('#editEvent',dlg).unbind('click');
			$('#deleteEvent',dlg).unbind('click');
			$('#editEvent',dlg).click(function(){
				close_dialog(dlg);
				(calEvent.recurrenceID && calEvent.recurrenceID.match(/^\d{1,6}$/))?open_edit_recurring_dialog(calEvent):open_edit_event_dlg(calEvent);
			});
			$('#deleteEvent',dlg).click(function(){
				close_dialog(dlg);
				open_delete_event_dlg(calEvent);
			});
		},
		eventDrop: function(calEvent,dayDelta,minuteDelta,allDay,revertFunc){
			send_drop_event(calEvent,revertFunc);
		},
		eventResize: function(calEvent,dayDelta,minuteDelta,allDay,revertFunc){
			send_drop_event(calEvent,revertFunc);
		}
	}
	return defaults;
}
/**
* Submit ajax request to add a new event or edit a current one
* @param object form
* @param string dialog
* @see display_complete_message()
*/
var save_add_edit_events = function(form,dialog){
	$(form).hide();
	$('#imgEditImgLoad').show();
	var params = $(form).serialize();
	var message = ($(':input[name=eventSaveType]',form).val() == 'd')?"Your event was deleted successfully":"Your events were saved successfully";
	var key = Math.round((Math.random() + Math.random()) * 100);
	$.post('../../ajax/apps/calendar/adminCalendar.php?isAjax=y&ran='+key+'&request=event&action='+$(':input[name=eventSaveType]',form).val(),(params),function(data){
		if (data != 'true'){message = "There was an error saving your events: "+data;}
		$('#imgEditImgLoad').hide();
		display_complete_message(message,dialog);
		if (data == 'true'){$('#calendar').fullCalendar('refetchEvents');}
	});
}
$(document).ready(function(){
	//admin methods start here
	//js events: new Calendar
	if ($('#calendar').length == 1){		
		set_dialog('#dlgNewCalendar');
		set_dialog('#dlgEditCalendar');
		set_dialog('#dlgAddEditEvent');
		set_dialog('#dlgDeleteEvent');
		set_dialog('#dlgEditRecurring');
		$('#dlgAddEditEvent').dialog("option","height",700);
		$('#dlgAddEditEvent').dialog("option","width",550);
		var set_date_picker_events = function(){
			$(':input[name=startDate]').datepicker({
				dateFormat: 'yy-mm-dd'
			});
			$(':input[name=endDate]').datepicker({
				dateFormat: 'yy-mm-dd'
			});
			$(':input[name=recurrenceEnd]','#frmEditEvents').datepicker({
				dateFormat: 'yy-mm-dd'
			});
			$('#imgCalStartDate').click(function(){
				$(':input[name=startDate]','#frmEditEvents').focus();
			});
			$('#imgCalEndDate').click(function(){
				$(':input[name=endDate]','#frmEditEvents').focus();
			});
			$('#imgCalRecurEndDate').click(function(){
				$(':input[name=recurrenceEnd]','#frmEditEvents').focus();
			});
			$('#recur_imgCalStartDate').click(function(){
				$(':input[name=startDate]','#frmEditRecurring').focus();
			});
			$('#recur_imgCalEndDate').click(function(){
				$(':input[name=endDate]','#frmEditRecurring').focus();
			});
		}
		var set_form_button_events = function(){
			$(':input[name=cancelEditEvent]','#frmEditEvents').click(function(){
				$('#dlgAddEditEvent').dialog('close');
				$('p.message','#dlgAddEditEvent').empty();
				document.getElementById('frmEditEvents').reset();
			});
			$('#btnNewCalendar').click(function(){
				$('#dlgNewCalendar p.message').empty();
				$('#dlgNewCalendar :input[name=calendarName]').val('');
				$('#dlgNewCalendar :input[name=backgroundColorNew]').val('#cccccc').css({'backgroundColor':'#ccc'});
				$('#dlgNewCalendar form:eq(0)').show();
				$('#dlgNewCalendar').dialog('open');
			});
			$('#btnCancelNewCalendar').click(function(){
				close_dialog('#dlgNewCalendar');
			});
			$('#btnEditCalendar').click(function(){
				$('#dlgEditCalendar p.message').empty();
				show_hide_edit_calendar_elements();
				$('#calEditElements').show();
				$('#calEditAction').val('edit');
				$(this).hide();
				$('#btnDeleteCalendar').hide();
			});
			$('#btnDeleteCalendar').click(function(){
				show_hide_edit_calendar_elements();
				$('#calEditAction').val('delete');
				$(this).hide();
				$('#btnEditCalendar').hide();
				display_complete_message('Are you sure you want to delete the following calendar?<hr />','#dlgEditCalendar');
			});
			$('#btnCancelEditCalendar').click(function(){
				show_hide_edit_calendar_elements();
				$('#calEditButtons').hide();
				close_dialog('#dlgEditCalendar');
				$('#calEditAction').val('');
				$('#calEditID').val('');
			});
			$('#btnCancelDeleteEvent').click(function(){
				$('#tbRecurring','#dlgDeleteEvent').hide();
				close_dialog('#dlgDeleteEvent');
			});
			$('#cancelEditRecurEvent').click(function(){
				close_dialog('#dlgEditRecurring');
			});
			$('#recurEdit_all','#frmEditRecurring').click(function(){
				toggle_edit_recur_options(true);
			});
			$('#recurEdit_this','#frmEditRecurring').click(function(){
				toggle_edit_recur_options(false);
			});
		}
		set_calendar_list_events();
		set_date_picker_events();
		set_form_button_events();
		//submit add new calendar
		$('#frmNewCalendar').submit(function(){
			//save calendar.
			//if calendar is saved, show saved message, add new calendar link to all lists with colour sel
			var params = {'calendarName':$(':input[name=calendarName]',this).val(),'eventBackgroundColor':$(':input[name=backgroundColorNew]',this).val(),'wineryID':$(':input[name=wineryID]',this).val()};
			$(this).hide();
			$('#imgNewCalLoad').show();
			var key = Math.round((Math.random() + Math.random()) * 100);
			$.post('../../ajax/apps/calendar/adminCalendar.php?isAjax=y&request=calendar&action=i&ran='+key,(params),function(data){
				complete_add_new_calendar(data,this);
			});
			return false;
		});
		//submit edit main calendar
		$('#frmEditCalendar').submit(function(){
			if ($(':input[name=calendarID]',this).val() != ''){
				if ($(':input[name=editAction]',this).val() == 'edit'){
					if($(':input[name=calendarName]',this).val() == "" && $(':input[name=backgroundColorEdit]',this).val() == rgb2hex($('#currentCalendarColor').css('backgroundColor'))){
						$('p.message','#dlgEditCalendar').html('Change the Calendar Name or <br />change the Highlight Colour to continue<hr />').show();
					}
					else{
						update_calendar($(this),'u');
					}
				}
				else if ($(':input[name=editAction]',this).val() == 'delete'){
					update_calendar($(this),'d');
				}
			}
			return false;
		});
		/**
		* Create/edit calendar events for calendars
		*/
		$('#frmEditEvents').submit(function(){
			$('p.message','#dlgAddEditEvent').empty();
			if ($(':input[name=calendarID]',this).val() != ""){
				var message = "";
				if ($(':input[name=eventTitle]',this).val() == ""){
					message += "Provide a title for your event<br />";
				}
				if (!$(':input[name=startDate]',this).val().match(/^20[0-9]{2}([-])(0[1-9]|1[012])([-])([012][0-9]|3[01])$/)){
					message += "Provide a valid starting date (YYYY-MM-DD)<br />";
				}
				if (!$(':input[name=endDate]',this).val().match(/^20[0-9]{2}([-])(0[1-9]|1[012])([-])([012][0-9]|3[01])$/)){
					message += "Provide a valid ending date (YYYY-MM-DD)<br />";
				}
				if(message == ""){
					save_add_edit_events(this,'#dlgAddEditEvent');
				}
				else{
					display_complete_message(message+'<hr />','#dlgAddEditEvent');
				}
			}
			else{
				display_complete_message('Select a calendar for this event.<hr />','#dlgAddEditEvent');
			}
			return false;
		});
		/**
		* submit edit recurring events form
		*/
		$('#frmEditRecurring').submit(function(){
			$('p.message','#dlgEditRecurring').empty();
			var message = "";
			if ($(':input[name=eventTitle]',this).val() == ""){
				message += "Provide a title for your event<br />";
			}
			if (!$(':input[name=startDate]',this).val().match(/^20[0-9]{2}([-])(0[1-9]|1[012])([-])([012][0-9]|3[01])$/)){
				message += "Provide a valid starting date (YYYY-MM-DD)<br />";
			}
			if (!$(':input[name=endDate]',this).val().match(/^20[0-9]{2}([-])(0[1-9]|1[012])([-])([012][0-9]|3[01])$/)){
				message += "Provide a valid ending date (YYYY-MM-DD)<br />";
			}
			if(message == ""){
				toggle_edit_recur_options(false);
				save_add_edit_events(this,'#dlgEditRecurring');
			}
			else{
				display_complete_message(message+'<hr />','#dlgEditRecurring');
			}
			return false;
		});
		/**
		* delete events functions
		*/
		$('#frm_delete_event').submit(function(){
			$('p.message','#dlgDeleteEvent').empty();
			if (isNaN($(':input[name=eventID]',this).val()) == true){
				$(this).hide();
				$('p.message','#dlgDeleteEvent').html('An event was not selected. Please retry');
			}
			else if (isNaN($(':input[name=eventID]',this).val()) == false && $(':input[name=recurDelete]',this).val() == ""){
				$('p.message','#dlgDeleteEvent').html('Select which instance you want to delete.');
			}
			else{
				save_add_edit_events(this,'#dlgDeleteEvent');
			}
			return false;
		});
		//initialize tag-it
		build_tag_it();
	}
});