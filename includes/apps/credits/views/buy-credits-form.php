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
            $class   = ($i == 1) ? ' class="selected"' : '';
            echo "<input type=\"radio\" name=\"credits\" id=\"credit_{$credit['itemID']}\" value=\"{$credit['itemID']}\"{$checked}\"> <label for=\"credit_{$credit['itemID']}\"{$class}>\${$credit['price']}<br /><span>{$credit['packageName']}</span></label>\n";
            $i++;
        }
        ?>
    </div>
    
    <div id="creditCardForm">
    
        <div class="form-row heading">
            <h4>Enter Payment Details <img src="/themes/Intervue/img/creditCardCompanies.png" alt="" /></h4>
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
</form>