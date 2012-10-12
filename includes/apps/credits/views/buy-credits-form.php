<?php

    global $quipp, $user;
    
    
    include dirname(__DIR__) . '/Credits.php';
    $credits = new Credits($db);
    
    require_once(dirname(__DIR__)."/../forms/Forms.php");
    if (!isset($frms)){
        $frms = new Forms($db);
    }
    
    array_push($quipp->js['footer'], 'https://js.stripe.com/v1/');
    array_push($quipp->js['footer'], '/includes/apps/credits/js/buy-credits.js');    

    $quipp->js['onload'] .= 'Stripe.setPublishableKey(\'pk_0i8Mtrri9uQxBMMOsrlXGQCSPlguJ\');';
    
     $provs  = $db->query("SELECT `itemID`, `provName` FROM `sysProvince` WHERE countryID IN (38, 213) ORDER BY `countryID`, `provName`");
     
     $meta   = $frms->getMetaFieldsByGroup('hr-managers');
    $post   = array();
    foreach($meta as $fields){
        $post[str_replace(" ","_",$fields["fieldLabel"])] = array("code" => $fields["validationCode"], "value" => $frms->get_meta($fields["fieldLabel"]), "label" => $fields["fieldLabel"]);
    }

    if (!empty($_POST)) {
        $charge = $credits->charge((int)$_POST['credits'], $_POST['stripeToken'], $user);
        if (is_int($charge)) {
            header('Location: /invoice?id=' . $charge.(!empty($_POST['referrer']) ? '&req='.str_replace(' ','+',$_POST['referrer']) : ''));
        }
    }
    

?>

<div class="payment-errors"<?php if (isset($charge) && $charge !== true) { echo ' style="display:block"'; } ?>><?php if (isset($charge) && $charge !== true) { echo $charge; } ?></div>



<form action="" method="post" id="payment-form">
    <div class="credits">
        <?php     
        $i = 1;
        foreach ($credits->credits as $creditID => $credit) {
            $checked = ($i == 1) ? ' checked="checked"' : '';
            $class   = ($i == 1) ? ' class="selected"' : '';
            echo "<input type=\"radio\" name=\"credits\" id=\"credit_{$credit['itemID']}\" value=\"{$credit['itemID']}\"{$checked}\"> <label for=\"credit_{$credit['itemID']}\"{$class}>\${$credit['price']}<span> + tax</span><br /><span class=\"creditNumber\">{$credit['packageName']}</span></label>\n";
            $i++;
        }
        ?>
    </div>
    
    <script type="text/javascript">
        <?php
        foreach ($credits->credits as $creditID => $credit) {
           // print_r($credit);
           echo "var ds_credit_{$credit['itemID']} = new Array('{$credit['price']}', '{$credit['credits']}'); \n";
           
        }
        
        foreach ($credits->taxes as $provID => $taxes) {
        
            echo "var ds_tax_{$provID} = new Array(); \n";
          // print_r($taxes);
           foreach($taxes as $taxID => $tax) {
                echo "ds_tax_{$provID}.push('{$tax['label']}'); \n";
                echo "ds_tax_{$provID}.push('{$tax['rate']}'); \n";
               
           }
        }
        
        ?>
    </script>
    
    <div id="whatAreYouBuying">&nbsp;</div>
   
    <div class="creditCardForm">
        
        <div class="form-row heading">
            <h4>Billing Details</h4>
        </div>
         <div class="form-row">
        <label for="First_Name">First Name</label>
        <input type="text" id="First_Name" name="First_Name" class="full" placeholder="First Name" value="<?php echo $post["First_Name"]["value"];?>" required="required"/>
        <label for="Last_Name">Last Name</label>
        <input type="text" id="Last_Name" name="Last_Name" class="full" placeholder="Last Name" value="<?php echo $post["Last_Name"]["value"];?>" required="required"/>
        <label for="Email">Email Address</label>
        <input type="text" id="Email" name="Email" class="full" placeholder="Email Address" value="<?php echo $post["Email"]["value"];?>" required="required"/>
         </div>
         
          <div class="form-row">
        <label for="Billing_Address">Address</label>
        <input type="text" id="Billing_Address" name="Billing_Address" class="half left" placeholder="Address" value="<?php echo $post["Billing_Address"]["value"];?>" required="required"/>
        <label for="Billing_City">City</label>
        <input type="text" id="Billing_City" name="Billing_City" class="half" placeholder="City" value="<?php echo $post["Billing_City"]["value"];?>" required="required"/>
        <label for="Billing_Postal_Code">Postal Code/Zip Code</label>
        <input type="text" id="Billing_Postal_Code" name="Billing_Postal_Code" class="half" placeholder="Postal Code" value="<?php echo $post["Billing_Postal_Code"]["value"];?>" required="required"/>
        
          </div>
           <div class="form-row">
        <label for="Billing_Province">Province/State</label>
        <div class="select half">
        <select id="Billing_Province" name="Billing_Province" required="required">
<?php
        if ($db->valid($provs)){
            while ($row = $db->fetch_assoc($provs)){
                echo '<option value="'.$row["itemID"].'"'.($post["Billing_Province"]["value"] == $row["itemID"] ? ' selected="selected"':'').'>'.$row["provName"].'</option>';
            }
        }
?>               
        </select>
        </div>
        <label for="Billing_Country">Country</label>
        <div class="half bottom left select">
        <select name="Billing_Country" id="Billing_Country" class="half bottom" required="required">
        <option value="38"<?php ($post["Billing_Country"]["value"] == 38 || empty($post["Billing_Country"]["value"]) ? ' selected="selected"':'')?>>Canada</option>
        <option value="213"<?php ($post["Billing_Country"]["value"] == 213 ? ' selected="selected"':'')?>>United States</option>
        </select>
        </div>
  
           </div>
           
    </div>
    <div class="creditCardForm">
        
        
        <div class="form-row heading">
            <h4>Payment Details <img src="/themes/Intervue/img/creditCardCompanies.png" alt="" /></h4>
        </div>
    
        <div class="form-row">
            <label for="name">Name on Card</label><br />
            <input id="name" type="text" size="50" autocomplete="off" class="card-name"/>
        </div>
    
        <div class="form-row">
            <label for="cardNum">Card Number</label><br />
            <input id="cardNum" type="text" size="20" autocomplete="off" class="card-number"/>
        </div>
        <div class="form-row">
            <label for="cvc">CVC</label><br />
            <input id="cvc" type="text" size="4" autocomplete="off" class="card-cvc"/>
        </div>
        <div class="form-row">
            <label for="exp_mon">Expiration (MM/YYYY)</label><br />
            <select id="exp_mon" class="card-expiry-month"/>
            <?php
            foreach (array_map(function($n) { return sprintf('%02d', $n); }, range(1, 12)) as $number) {
                $selected = ($number == date('m')) ? ' selected="selected"' : '';
                echo "<option value=\"{$number}\"{$selected}>{$number}</option>\n";
            }
            ?>
            </select>
        
            <span> / </span>
            <select class="card-expiry-year">
            <?php
            foreach (range(date('Y'), date('Y', strtotime('+10 years'))) as $number) {
                $selected = ($number == date('Y')) ? ' selected="selected"' : '';
                echo "<option value=\"{$number}\"{$selected}>{$number}</option>\n";
            }
            ?>
            </select>
        </div>
    
    </div>
    <input type="submit" class="submit-button btn green" value="Submit Payment" />
    <input type="hidden" name="referrer" value="<?php echo (isset($_GET['req']) ? $_GET['req'] : '');?>" />
</form>