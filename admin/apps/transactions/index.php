<?php
 
$root = dirname(dirname(dirname(__DIR__)));
require $root . '/includes/init.php';

$meta['title'] = 'Job Credit Transactions';
$meta['title_append'] = ' &bull; Quipp CMS';

if ($auth->has_permission("viewTransactions")){
    
    include $root . "/admin/templates/header.php";

?>
<h1>Job Credit Transactions</h1>
<div class="boxStyle">
	<div class="boxStyleContent">
		<div class="boxStyleHeading">
			<h2>Transactions</h2>

			<div class="boxStyleHeadingRight"></div>
		</div>
		<div class="clearfix">&nbsp;</div>
		<p>To view a detailed report. Please visit your <a href="https://manage.stripe.com/login" style="color:blue;" target="_blank">Stripe account</a></p>
		<div class="clearfix">&nbsp;</div>

		<div id="template">

	<?php
    //display logic

    //view = view state, these standard views will do for most single table interactions, you may need to replace with your own
        
    $listqry = "SELECT * FROM tblTransactions WHERE sysOpen='1' ORDER BY sysDateCreated DESC";
    $resQry = $db->query($listqry);
    
    if ($db->valid($resQry) !== false){
    ?>
    <table id="adminTableList" class="adminTableList tablesorter" width="100%" cellpadding="5" cellspacing="0" border="1">
        <thead>
            <tr>
                <th>User</th>
                <th>Stripe ID</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Taxes (&cent)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
    <?php
    while ($row = $db->fetch_assoc($resQry)) {
        $user = new User($db, $row['userID']);
        if(!isset($user->info['First Name']) || !isset($user->info['Last Name'])) { 
            $name = "Unknown"; 
        } else {
             $name = $user->info['First Name'] . ' ' . $user->info['Last Name'];     
        }
        
        ?>
            <tr>
                <td><?php echo $name; ?></td>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo money_format('%n', ($row['amount'] / 100)); ?></td>
                <td><?php echo $row['taxes']; ?></td>
                <td><?php echo $row['sysDateCreated']; ?></td>
            </tr>
        <?php
    }    
    ?>
        </tbody>
    </table>

    <?php

    } else {
        echo 'no data present';
    }

?>
        </div><!-- end template -->
        <div class="clearfix">&nbsp;</div>
    </div><!-- boxStyleContent -->
</div><!-- boxStyle -->
<?php

//end of display logic


include $root. "/admin/templates/footer.php";

} else {
    echo 'no permission';

}
?>