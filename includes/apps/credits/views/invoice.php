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

        /* Get credits */
        switch ($invoice['creditID']) {

            case 1:
                $creditNum = 1;
                break;
            case 2:
                $creditNum = 3;
                break;
            case 3:
                $creditNum = 10;
                break;
            default:
                $creditNum = 'Unlimited Package';
                break;
        }

        /* Determine province and country */
        $billingProv = $db->return_specific_item((int)$invoice['billingProvince'], 'sysProvince', 'provName', 'No Province Provided');
        $billingCountry = $db->return_specific_item((int)$invoice['billingCountry'], 'sysCountry', 'countryName', 'No Country Provided');


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
	<dd><?php echo (isset($invoice['billingFirstName'])) ? $invoice['billingFirstName'] : 'No First Name Provided'; ?>
        <?php echo (isset($invoice['billingLastName'])) ? ' ' . $invoice['billingLastName'] : ' No Family Name Provided'; ?></dd>
	<dd><?php echo (isset($invoice['billingAddress'])) ? $invoice['billingAddress'] : 'No Address Provided'; ?></dd>
	<dd><?php echo (isset($invoice['billingCity'])) ? $invoice['billingCity'] : 'No City Provided'; ?>, <?php echo $billingProv; ?></dd>
    <dd><?php echo $billingCountry; ?></dd>
	<dd><?php echo (isset($invoice['billingPostal'])) ? $invoice['billingPostal'] : 'No Postal Code Provided'; ?></dd>
	<dd><?php echo (isset($invoice['billingEmail'])) ? $invoice['billingEmail'] : 'No Email Provided'; ?></dd>
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
        <td colspan="2"><div><?php echo $invoice['description']; ?> (on <?php echo date("Y-m-d g:i a", strtotime($invoice['sysDateCreated']));?>), <strong>Credits:</strong> <?php echo $creditNum; ?></div></td>
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
                echo '<a class="btn reactivate" style="margin-right:10px;" href="/applications?req='.str_replace(' ','+',$_GET['req']).'" data-job="'.$qryData[1].'">Re-Publish Job</a>&nbsp;';
                break;

            case "createnew":
                echo '<a class="btn" style="margin-right:10px;" href="/create-job?step=1">Create New Job</a>&nbsp;';
                break;

            default:
                echo '<a class="btn" style="margin-right:10px;" href="/create-job?step=1">Create New Job</a>&nbsp;';
                break;
        } 
        
    }

    else if (!isset($_GET['req']) && !isset($_GET['redirect'])) {
         echo '<a class="btn" href="/create-job?step=1">Create New Job</a>&nbsp;';
    }
        }
    }