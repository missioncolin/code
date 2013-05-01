<?php

require '../includes/init.php';

$meta['title'] = 'Administrative Panel';
$meta['title_append'] = ' &bull; Quipp CMS';

require 'templates/header.php';
?>

<!-- <p><a href="/admin/content.php" class="btnStyle">Content Editing</a></p> -->
<!--

<a href="#" class="btnStyle">Gray Button</a> 
<a href="#" class="btnStyle blue">Blue Button</a> 
<a href="#" class="btnStyle red">Red Button</a> 
<a href="#" class="btnStyle green">Green Button</a> 
-->
<?php

require '../includes/lib/gapi-1.3/gapi.class.php';

$ga = new gapi($quipp->google["ga_email"],$quipp->google["ga_password"]);

$ga->requestReportData($quipp->google["ga_profile_id"],array('pagePath','pageTitle', 'visitCount'),array('pageviews','visits', 'visitors', 'visitBounceRate'), null, null, date("Y-m-d", strtotime("-30 days")), date("Y-m-d", strtotime("yesterday")));


?>

<style type="text/css">
	#dashboardReporting {
		
	}
	
	#dashboardReporting h2 {  
		font-size:14px;
		color:black;
		font-weight:bold;
	}
	
	#dashboardReporting .chartingSummary {  
		font-size:14px;
		color:white;
		font-weight:bold;
		border-top: 2px solid #999999;
		
		padding:0px;
		margin:10px 0px 0px 0px;
	}
	
	#dashboardReporting .chartingSummary tr td {
	
		padding:5px 0px 5px 0px;
	
	}
	
	#dashboardReporting .chartMain {  
		font-size:14px;
		color:#666666;
		font-weight:bold;
		display:block;
		padding:0px;
		margin:0px;
		
	}
	
	#dashboardReporting .chartSub {  
		font-size:12px;
		display:block;
		padding:0px;
		margin:0px;
		color:#999999;
		font-weight:bold;
	}
	
	#dashboardReporting .chartValue {  

		font-size:24px;
		color:black;
		font-weight:bold;
	}
	
	
</style>

<div id="dashboardReporting">
<h2>Activity (Last 30 Days) <?php print date("Y-m-d", strtotime("-30 days")); ?> to <?php print date("Y-m-d"); ?></h2>
<table class="chartingSummary" width="100%">

 <tr>
  <td><span class="chartMain">Total Visits</span><span class="chartSub">30 days</span></td> <td><span class="chartValue"><?php echo number_format($ga->getVisits()); ?></span></td>
  <td><span class="chartMain">Total Pageviews</span><span class="chartSub">30 days</span></td> <td><span class="chartValue"><?php echo number_format($ga->getPageviews()); ?></span></td>
  <td><span class="chartMain">Bounce Rate</span><span class="chartSub">30 days</span></td> <td><span class="chartValue"><?php echo number_format($ga->getVisitBounceRate(), 2); ?>%</span></td>


</tr>

</table>


<?php

	$ga->requestReportData($quipp->google["ga_profile_id"],array('date'),array('visits', 'visitors'), array("date"), null, date("Y-m-d", strtotime("-31 days")), date("Y-m-d", strtotime("today")), 1, 31);
	$params = "";
	foreach($ga->getResults() as $result) {
	//yell($result);
		$params .= "&visits[".substr(date("M", strtotime($result->getDate())),0,1) . date("d", strtotime($result->getDate()))."]=" . $result->getVisits();
		$params .= "&visitors[".substr(date("M", strtotime($result->getDate())),0,1) . date("d", strtotime($result->getDate()))."]=" . $result->getVisitors();
	}
?>



<div style="text-align:center; margin:0px auto;">
	<img src="http://maple.resolutionim.com/charting/quipp/analytics.php?<?php print $params; ?>" />
</div>
<h2>Top Content</h2>
<table class="adminTableList" width="100%">
<tr>
  <th>Page</th>

  <th>Pageviews</th>
 
</tr>

<?php

	$ga->requestReportData($quipp->google["ga_profile_id"],array('pagePath'),array('pageviews'), array("-pageviews"), null, date("Y-m-d", strtotime("-30 days")), date("Y-m-d", strtotime("yesterday")), 1, 10);

	foreach($ga->getResults() as $result) {
	//yell($result);
?>

<tr>
  <td><a href="<?php echo $result->getPagePath(); ?>" target="_blank"><?php echo $_SERVER['HTTP_HOST'] . $result->getPagePath(); ?></a></td>
   
  <td><?php echo $result->getPageviews(); ?></td>

</tr>
<?php
	}
?>
</table>


</div>
<?php

require 'templates/footer.php';
?>