
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
  <script>!window.jQuery && document.write(unescape('%3Cscript src="/js/jquery-1.4.4.min.js"%3E%3C/script%3E'))</script>
 
  <script src="/min/?f=js/plugins.js,js/script.js"></script> 

  <!--[if lt IE 7 ]>
    <script src="/min/?f=js/dd_belatedpng.js"></script>
    <script> DD_belatedPNG.fix('img, .png_bg'); </script>
  <![endif]-->
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

	
	

	$(function(){ 
		
	
		<?php if(isset($quipp->js['footer'])) { print $quipp->js['onload']; } ?>
		
		$("#saveBtn").click(function(){

			//$("#boxContentForm").submit();
			//console.log('submitting form data');

			$("#loader").show();

			var cID = $('#contentID').val();
			var pID = $('#pageID').val();
			var rID = $('#regionID').val();
			var boxStyle = $('#boxStyle').val();
			
			var boxTitle = $('#boxTitle').val();
			var boxContent = $('#boxBodyContent').val();
			var hideTitle = $('#hideTitle').attr("checked") ? $('#hideTitle').val() : 0;
			
			
			
			
			//var boxContent = CKEDITOR.instances.boxBodyContent.getData();

			var pb = $('#pb').val();

			//console.log(boxContent);
			$.ajax({
						url: "<?php print $_SERVER['PHP_SELF']; ?>",
						type: 'POST',
						data: "&pb=" + pb + "&contentID=" + cID + "&pageID=" + pID + "&regionID=" + rID + "&boxStyle=" + boxStyle + "&boxTitle=" + escape(boxTitle) + "&hideTitle=" + escape(hideTitle) + "&boxBodyContent=" + escape(boxContent),
						context: document.body,
						success: function(result){
							//console.log(result);
							//console.log("ajax returned");
							$("#loader").hide();
							parent.reload(cID, boxTitle);
     					}
			});
		});
	});
</script>
  <!-- there were <?php print count($db->queries); ?> queries on this page that took <?php print round($db->query_time, 4); ?> seconds to finish - Quipp -->
</body>
</html>
<?php $db->close(); ?>