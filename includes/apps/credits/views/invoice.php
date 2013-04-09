<?php

    global $quipp, $user;
    
    
    include dirname(__DIR__) . '/Credits.php';
    array_push($quipp->js['footer'], '/includes/apps/credits/js/buy-credits.js'); 
    $credits = new Credits($db);
    
    
    if (!isset($_GET['id'])) {
        echo '<strong>No invoice found</strong>';
        
    } else {
        
        $invoice = $credits->getInvoice($_GET['id'], $user->id);
        
        if ($invoice === false) {
            echo '<strong>No invoice found</strong>';
        } else {
        
            $invoice['chargedAmount'] = ((int) $invoice['chargedAmount'] > 0) ? $invoice['chargedAmount'] : $invoice['amount'];
        
        /* Get user's province name */
        $prov = $db->query("SELECT `provName` FROM `sysProvince` WHERE itemID = '" . $user->info['Company Province'] . "'");
        
        if ($db->valid($prov)){
            $row = $db->fetch_assoc($prov);
        }
        else {
	        $row['provName'] = 'None specified.';
        }

?>

<div id="seller-details">
	<dd>Intervue Inc.</dd>
	<dd>216 Parkmount Rd.</dd>
	<dd>Toronto, Ontario</dd>
	<dd>M4J 4V6</dd>
	<dd>6473486784</dd>
	<dd><a href="mailto:info@intervue.ca">info@intervue.ca</a></dd>
</div>

<div id="buyer-details">
	<dd>Purchaser Information</dd>
	<dd><?php echo (isset($user->info['First Name']) && isset($user->info['Last Name'])) ? $user->info['Last Name'] . ' ' . $user->info['Last Name'] : 'No Name Provided'; ?></dd>
	<dd><?php echo (isset($user->info['Company Address'])) ? $user->info['Company Address'] : 'No Address Provided'; ?></dd>
	<dd><?php echo (isset($user->info['Company City']) && isset($row['provName'])) ? $user->info['Company City'] . ', ' . $row['provName'] : 'No City Provided'; ?></dd>
	<dd><?php echo (isset($user->info['Company Postal Code'])) ? $user->info['Company Postal Code'] : 'No Postal Code Provided'; ?></dd>
	<dd><?php echo (isset($user->info['Email'])) ? $user->info['Email'] : 'No Email Provided'; ?></dd>
	<dd>Payment type: </dd>
<!-- 	<dd><?php echo $user->info['Phone Number']; ?></dd> -->
</div>
<table class="simpleTable">
<thead>
    <tr>
        <th colspan="2">Invoice Item</th>
        <th style="width:200px">Price</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td colspan="2"><div><?php echo $invoice['description']; ?> (on <?php echo date("Y-m-d g:i a", strtotime($invoice['sysDateCreated']));?>)</div></td>
        <td><div><?php echo money_format('%n', $invoice['amount'] / 100); ?></div></td>
    </tr>
</tbody>
</table>
<table class="simpleTable invoiceTotals">
<tbody>
    <tr class="subTotal">
        <td class="total"><div>Subtotal</div></td>
        <td><div><?php echo money_format('%n', $invoice['amount'] / 100); ?></div></td>
    </tr>
    <?php
    if (is_array(json_decode($invoice['taxes'], true))) {
        foreach(json_decode($invoice['taxes']) as $label => $tax) {
    ?>
    <tr class="tax">
        <td class="total"><div><?php echo $label; ?></div></td>
        <td><div><?php echo money_format('%n', $tax / 100); ?></div></td>
    </tr>
    <?php 
        } 
    }
    ?>
    <tr class="finalTotal">
        <td class="total"><div><strong>Total</strong></div></td>
        <td><div><strong><?php echo money_format('%n', $invoice['chargedAmount'] / 100); ?></strong></div></td>
    </tr>
</tbody>
</table>
<div class="clearfix"></div>
<a href="javascript:window.print();" class="btn blue">Print</a>
<?php
if(isset($_GET['redirect']) && is_numeric($_GET['redirect'])){
	print "<a href=\"/new-job-info?jobID=".$_GET['redirect']."\" class=\"btn\" style=\"margin-right:10px;\">Return to Job Activation Page</a>";
} 

?>
<?php
    if (isset($_GET['req'])){
        $qryData = explode(" ",$_GET['req']);
        switch($qryData[0]){
            case "reactivate":
                echo '<a class="btn reactivate" href="/applications?req='.str_replace(' ','+',$_GET['req']).'" data-job="'.$qryData[1].'">Re-Publish Job</a>&nbsp;';
                break;
            case "createnew":
                echo '<a class="btn" href="/create-job?step=1">Create New Job</a>&nbsp;';
                break;
            
        }
        
    }
        }
    }