
</div> <!-- end of #inhalt -->
</div> <!-- end of #container -->

<footer><div id="footerTagline">quipp engine v.0.1</div></footer>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script>!window.jQuery && document.write(unescape('%3Cscript src="/js/jquery-1.4.4.min.js"%3E%3C/script%3E'))</script>

<script src="/min/?f=js/plugins.js,js/script.js"></script>

<!--[if lt IE 7 ]>
<script src="/min/?f=js/dd_belatedpng.js"></script>
<script> DD_belatedPNG.fix('img, .png_bg'); </script> 
<![endif]-->


<script type="text/javascript" src="/js/jquery.cookie.js"></script>
<script type="text/javascript" src="/js/jquery.hotkeys.js"></script>
<script type="text/javascript" src="/js/jquery.jstree/jquery.jstree.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.6.min.js"></script>

<script type="text/javascript" src="/js/jquery.easing-1.3.pack.js"></script>
<script type="text/javascript" src="/js/jquery.fancybox-1.3.4.pack.js"></script>

<script type="text/javascript" src="/admin/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="/admin/js/jquery.jscrollpane.min.js"></script>
<script type="text/javascript" src="/admin/js/jquery.serialize-list.js"></script>

<!-- /* <script type="text/javascript" src="/js/ckeditor/ckeditor.js"></script> */ -->
<?php 
//print out any scripts that are needed for the page calling in this header file, 
//this is set in that particular file using array_push($quipp->js['header'],"/path/to/script.js", "/path/to/another/script.js");

if(isset($quipp->js['footer'])) {
	if(is_array($quipp->js['footer'])) {
		foreach($quipp->js['footer'] as $val) {
			if ($val != '') {
				print "<script type=\"text/javascript\" src=\"$val\"></script>\n"; 
			}
		}
	}
}
?>
<script type="text/javascript">

$('#structureControl').jScrollPane({autoReinitialise: true});

/* Page Property Utility */
function updateFields(userID) {
    $('#metaFields').load('/admin/ajax/buildUserEditor.php', {
        id: userID,
        fields: $('#metaFields input, #metaFields select').serialize(),
        groups: $("input:checkbox.groupForm").serialize()
    });
}

function updateBundles() {
    $('#metaFields').load('/admin/ajax/buildBundles.php', {
        exchangeID: $('#RQvalALPHExchange').val(),
        bundleID: gup('id')
    });
}

<?php

if (!isset($pageRS)) { ?>

$("#outboundLinkSaveChanges").click(function() { updateOutboundLink(); });

function updateOutboundLink() {
	//console.log("updating outbound link");
	$.post("/admin/ajax/navUtility.php", {
        "operation": "update_outbound_link",
        "linkLabel": $("#linkLabel").val(),
        "linkURL": $("#linkURL").val(),
        "linkBehaviour": $("#linkBehaviour").val(),
        "navID": $("#currentlyEditingNavID").val()
    }, function (r) {
        if (!r.status) {
            feedback(r.message, "Outbound Link Could Not Be Saved", 2);
        } else {
        $("#structureList").jstree("refresh");
           feedback(r.message, "Outbound Link Saved", 1);
        }
    });
	
	
	//
}



<?php } ?>

//Functions for the 'Stream' 
function denyApprovalTicket(callingItem) {

	//determine the ticket ID based on the button calling this function for UI purposes
	//all buttons should have an ID prefix, in the case of delete it's apTicket_Deny_XX
	window.ticketID = callingItem.id.substr(14);
	//alert(window.ticketID);
	$.post("/admin/ajax/pageUtility.php", {
        "operation": "deny_approval_request",
        "ticketID": window.ticketID
    }, function (r) {        
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Not Updated", 2);
        } else {
            //node
            //set the editor pageID to the newly created draft version whose id will be returned back
            $("#currentlyEditingPageID").val(r.draftPageID);
            //reloadTemplate();
            feedback("The content change has been denied.", "Approval Denied", 1);
        	ticketToHide = "#apTicket_" + window.ticketID;
        	$(ticketToHide).fadeOut();
        	
        }
    });

}



function approvePageVersion(pageID, callingItem) {
	
	//determine the ticket ID based on the button calling this function
	//all buttons should have an ID prefix, in the case of delete it's apTicket_Approve_XX
	window.ticketID = callingItem.id.substr(17);
	
	$.post("/admin/ajax/pageUtility.php", {
        "operation": "approve_draft_and_make_live",
        "pageID": pageID
    }, function (r) {        
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Not Made Live", 2);
        } else {
            //node
            //set the editor pageID to the newly created draft version whose id will be returned back
            $("#currentlyEditingPageID").val(r.draftPageID);
            feedback("The file has been approved.", "Pushed Live", 1);
        	//console.log(r.message);
        	ticketToHide = "#apTicket_" + window.ticketID;
        	$(ticketToHide).fadeOut();
        	        	
        }
    });
}
//End of functions for the stream.

<?php if (isset($pageRS)) { ?>

$("#descriptionForSE").change(function() { updatePageProperty("Description","pageDescription", $(this).val()); });
$("#keywordsForSE").change(function() { updatePageProperty("Keywords","pageKeywords", $(this).val()); });
$("#pagePropertyLabel").change(function() { updatePageProperty("Name","label", $(this).val()); });
$("#pageSystemName").change(function() { updatePageProperty("URL/Address","systemName", $(this).val()); });
$("#makeHomepageCheck").change(function() { updatePageProperty("Set As Homepage","isHomepage", $(this).val()); });
//$("").change(function() { updatePageProperty("Set As Homepage","isHomepage", $(this).val()); });
$("#makeThisLive").click(function() { approveDraftAndMakeLive() });
$("#submitForReview").click(function() { submitForReview() });
$("#startOverFromLive").click(function() { startOverFromLive() });



$("#btnAddToApp").click(function() { insertAppWidget($('#appToAdd option:selected').val()); });


$("input.pageTemplateOption").click(function() { changeTemplate("Template","templateID", $(this).val()); });



$("#propertiesGroupsForm input").change(function () { adjust_password_protect($("#propertiesGroupsForm input").serialize()); });

function insertAppWidget(contentID) {
	//console.log("Trying to place: " + contentID);
	//reload(); 
	//console.log('reloading structure');
	$.post("/admin/ajax/pageUtility.php", {
        "operation": "insert_app_widget",
        "pageID": $("#currentlyEditingPageID").val(),
        "contentID": contentID
    }, function (r) {
        
        
        if (!r.status) {
            //console.log(r.message);
        } else {
            reloadTemplate();
           	//console.log(r.message);
        }
    });
	
	
	//
}



function startOverFromLive() {

	$.post("/admin/ajax/pageUtility.php", {
        "operation": "start_over_from_live",
        "pageID": $("#currentlyEditingPageID").val()
    }, function (r) {        
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Could Not Be Rolled Back", 2);
        } else {
            //node
            //set the editor pageID to the newly created draft version whose id will be returned back
            $("#currentlyEditingPageID").val(r.draftPageID);
            reloadTemplate();
            feedback(r.message, "Rolled Back", 1);
        	//console.log(r.message);
        	
        }
    });
}

function submitForReview() {

	$.post("/admin/ajax/pageUtility.php", {
        "operation": "submit_for_review",
        "pageID": $("#currentlyEditingPageID").val(),
        "navID": $("#currentlyEditingNavID").val()
    }, function (r) {        
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Not Submitted", 2);
        } else {
            //node
            //set the editor pageID to the newly created draft version whose id will be returned back
            //$("#currentlyEditingPageID").val(r.draftPageID);
            //reloadTemplate();
            feedback(r.message, "Review Request Sent", 1);
        	//console.log(r.message);
        	
        }
    });
}


function approveDraftAndMakeLive() {

	$.post("/admin/ajax/pageUtility.php", {
        "operation": "approve_draft_and_make_live",
        "pageID": $("#currentlyEditingPageID").val()
    }, function (r) {        
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Not Saved", 2);
        } else {
            //node
            //set the editor pageID to the newly created draft version whose id will be returned back
            $("#currentlyEditingPageID").val(r.draftPageID);
            reloadTemplate();
            feedback(r.message, "Pushed Live", 1);
        	//console.log(r.message);
        	
        }
    });
}


function adjust_password_protect(serial) {
	//groups_list
	$.post("/admin/ajax/pageUtility.php", {
        "operation": "adjust_password_protect",
        "pageID": $("#currentlyEditingPageID").val(),
        "serial": serial
    }, function (r) {
        
        $("#structureList").jstree("refresh");
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Not Saved", 2);
        } else {
            //node
            feedback(r.message, "Auto-Saved", 1);
            
           //console.log(r.message);
        	
        }
    });


}

function changeTemplate(humanReadableName, fieldName, value) {
	$.post("/admin/ajax/pageUtility.php", {
        "operation": "update_page_property",
        "pageID": $("#currentlyEditingPageID").val(),
        "humanReadableName": humanReadableName,
        "fieldName": fieldName,
        "value": value
    }, function (r) {
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Problem", 2);
        } else {
            //node
            feedback(r.message, "Template Changed", 1);
            
            //run some JS to change the template used in Mike's stuff...
            reloadTemplate();
        	//console.log(r.message);
        	
        }
    });

}


function updatePageProperty(humanReadableName, fieldName, value) {
	$.post("/admin/ajax/pageUtility.php", {
        "operation": "update_page_property",
        "pageID": $("#currentlyEditingPageID").val(),
        "humanReadableName": humanReadableName,
        "fieldName": fieldName,
        "value": value
    }, function (r) {
        
        $("#structureList").jstree("refresh");
        
        if (!r.status) {
            //console.log(r.message);
            feedback(r.message, "Not Saved", 2);
        } else {
            //node
            feedback(r.message, "Auto-Saved", 1);
            
            //set the preview link (we're doing this everytime for now), even if the systemName hasn't changed
            $("#previewButton").attr("href", "/?p=" + $("#pageSystemName").val() + "&draft=preview");
            
            
        	//console.log(r.message);
        	
        }
    });

}

/* End Of Page Property Utility */

<?php } ?>
function update_order(elem) {
	
	<?php if (isset($pageRS)) { ?>
	$.post('ajax/update-box-order.php',  'regionID='+ $(elem).attr('id').substring(6) + '&' + $(elem).sortable('serialize') + '&pageID=' + $('#currentlyEditingPageID').val() + '&templateID=<?php print $pageRS['templateID']; ?>', function(data, textStatus) { 
		$('#region' + $(elem).attr('id').substring(6)).html(data);
		$('.fancybox').fancybox({'width':825, 'height': 550 });
	});
	
	<?php } ?>
	
}
function gup( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}
function confirmDelete(loc) {
    if (confirm("Are you sure you would like to delete this listing?")) {
        window.location = loc;
    }
}
$(function () {

	<?php if(isset($quipp->js['footer'])) { print $quipp->js['onload']; } ?>
	var width = $(window).width() - 290;
	$('#inhalt').css('width', +width);
	$('.pageTitle').css('width', +width - 16);
	
	
	$(window).resize(function() {
		var width = $(window).width() - 290;
		$('#inhalt').css('width', width);
		$('.pageTitle').css('width', width - 16);
	});
	
	var width2 = $(window).width() - 24;
	$('header').css('width', width2);
	
	$(window).resize(function() {
		var width2 = $(window).width() - 24;
		$('header').css('width', width2);
	});
	
	var height = $(window).height();
	$('#container').css('height', height);
	
	$(window).resize(function() {
		var height = $(window).height();
		$('#container').css('height', height);
	});
	
	
	
	$(".hideSection").click(function() {
		
		var targetID = $(this).attr('rel');
		
		if($("#" + targetID).css('display') == "none"){
			$(this).html("Hide");
			$(this).css("background", "transparent url(/images/icons/upArrow.png) no-repeat scroll right center");
			
		}else{
			$(this).html("Show");
			$(this).css("background", "transparent url(/images/icons/downArrow.png) no-repeat scroll right center");
		}
		$("#" + targetID).slideToggle();
		
		return false;
	});
	
	// clear input on focus
	$('.cInput').click(
		function() {
			if (this.value == this.defaultValue) {
				this.value = '';
			}
		}
	);
		
	$('.cInput').blur(
		function() {
			if (this.value == '') {
				this.value = this.defaultValue;
			}
		}
	);

	$('.fancybox').fancybox({'width':850, 'height': 550 });
	 
	$(".adminTableList tr:nth-child(even)") .addClass("striped");
	//$("#tableEditorForm table tr:nth-child(even)") .addClass("striped");

	 
	
	$('.edit-pages').click(function() {
		$('#applications').slideUp('fast');
		$('#stream').slideUp('fast');
		$('#users').slideUp('fast');
		$('#structureList').slideDown('fast');
		$('.edit-apps, .edit-users, .edit-stream').parent().removeClass('current');
		$('.edit-pages').parent().addClass('current');
		return false;
	});
	
	$('.edit-apps').click(function() {
		$('#structureList').slideUp('fast');
		$('#stream').slideUp('fast');
		$('#users').slideUp('fast');
		$('#applications').slideDown('fast');
		$('.edit-pages, .edit-users, .edit-stream').parent().removeClass('current');
		$('.edit-apps').parent().addClass('current');
		return false;
	});
	
	$('.edit-stream').click(function() {
		$('#structureList').slideUp('fast');
		$('#applications').slideUp('fast');
		$('#users').slideUp('fast');
		$('#stream').slideDown('fast');
		$('.edit-apps, .edit-users, .edit-pages').parent().removeClass('current');
		$('.edit-stream').parent().addClass('current');
		
		$.ajax({
			url: "/admin/stream.php",
			cache: false,
			success: function(html){
				$("#stream").html("<p style=\"margin-left:10px;\">This is a feed of all approval requests submitted by users for your review before being approved for go-live.</p><p>&nbsp;</p>" + html);
			}
		});

		
		
		return false;
	});
	
	
	$('.edit-users').click(function() {
		$('#applications').slideUp('fast');
		$('#structureList').slideUp('fast');
		$('#stream').slideUp('fast');
		$('#users').slideDown('fast');
		$('.edit-apps, .edit-stream, .edit-pages').parent().removeClass('current');
		$('.edit-users').parent().addClass('current');
		return false;
	});
	
	//this will auto select whichever item has the current flag on it
	$(".current").children(".edit-apps, .edit-stream, .edit-pages, .edit-users").click();
    $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});

});

function reloadTemplate() {

    $('#template').html('<img src="/images/admin/ajax-loader.gif" />');
    var config = {
        "operation": "reload_template",
        "pageID": $("#currentlyEditingPageID").val(),
        "templateID": $('.pageTemplateOption:checked').val()
    };
    $.post("/admin/ajax/pageUtility.php", config, function (r) {
        $('#template').html(r);
        $("#template .regionbox").sortable({
            connectWith: ".regionbox",
            update: function (event, ui) {
                update_order(this);
            }
        }).disableSelection();
        $('.fancybox').fancybox({
            'width': 825,
            'height': 550
        });
    });
}

function reload(contentID, boxTitle) {
    $.fancybox.close();
    $('#content_' + contentID + ' a').text(boxTitle);
    reloadTemplate();
}

function UpdateDelete(contentID, regionID) {
    if (confirm('Are you sure you want to remove this content area?')) {
        $.post("/admin/ajax/pageUtility.php", {
            "operation": "delete_content",
            "pageID": $("#currentlyEditingPageID").val(),
            "contentID": contentID,
            "regionID": regionID
        }, function (r) {
            if (!r.status) {
                console.log(r.message);
                feedback(r.message, "Not Removed", 2);
            } else {
                feedback(r.message, "Removed", 1);
                reloadTemplate();
                console.log(r.message);
            }
        });
    } else {
        feedback("Delete canceled by user", "Not Removed", 2);
    }
}
</script>
<script src="/admin/js/navTree.js"></script>
<script type="text/javascript" src="/admin/js/growl/jquery.gritter.js"></script>
<!-- there were <?php print count($db->queries); ?> queries on this page that took <?php print round($db->query_time, 4); ?> seconds to finish - Quipp -->

<?php 
	//end-user 'pretty' error reporting for in-app errors that we want the user to see (ask Brendan about this if you are confused), do not remove!
	$feedback->display_messages(true);  
?>
</body>
</html>
<?php $db->close(); ?>