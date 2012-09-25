<?php

    global $quipp, $user;
    
    
    include dirname(__DIR__) . '/Credits.php';
    $credits = new Credits($db);
    
    
    array_push($quipp->js['footer'], 'https://js.stripe.com/v1/');
    array_push($quipp->js['footer'], '/includes/apps/credits/js/buy-credits.js');    

    $quipp->js['onload'] .= 'Stripe.setPublishableKey(\'pk_0i8Mtrri9uQxBMMOsrlXGQCSPlguJ\');';

    if (!empty($_POST)) {
        $charge = $credits->charge((int)$_POST['credits'], $_POST['stripeToken'], $user);
    }

?>

<div class="payment-errors"><?php if (isset($charge) && $charge !== true) { echo $charge; } ?></div>

<form action="" method="POST" id="payment-form">
    <div class="credits">
        <?php     
        $i = 1;
        foreach ($credits->credits as $creditID => $credit) {
            $checked = ($i == 1) ? ' checked="checked"' : '';
            echo "<input type=\"radio\" name=\"credits\" id=\"credit_{$credit['itemID']}\" value=\"{$credit['itemID']}\"{$checked}\"> <label for=\"credit_{$credit['itemID']}\">\${$credit['price']}<br /><span>{$credit['packageName']}</span></label>\n";
            $i++;
        }
        ?>
    </div>
    
    <div id="creditCardForm">
    
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
            <label>Expiration (MM/YYYY)</label><br />
            <input type="text" size="2" class="card-expiry-month"/>
            <span> / </span>
            <input type="text" size="4" class="card-expiry-year"/>
        </div>
    
    </div>

    <input type="submit" class="submit-button btn green" value="Submit Payment" />
</form>
