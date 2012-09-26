<?php

    global $quipp, $user;
    
    
    include dirname(__DIR__) . '/Credits.php';
    $credits = new Credits($db);
    
    
    if (!isset($_GET['id'])) {
        echo '<strong>No invoice found</strong>';
        
    } else {
        
        $invoice = $credits->getInvoice($_GET['id'], $user->id);
        
        if ($invoice === false) {
            echo '<strong>No invoice found</strong>';
        } else {
        
            $invoice['chargedAmount'] = ((int) $invoice['chargedAmount'] > 0) ? $invoice['chargedAmount'] : $invoice['amount'];
?>


<table class="simpleTable">
<thead>
    <tr>
        <th colspan="2">Invoice Item</th>
        <th style="width:200px">Price</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td colspan="2"><?php echo $invoice['description']; ?></td>
        <td><?php echo money_format('%n', $invoice['amount'] / 100); ?></td>
    </tr>
    <tr class="subTotal">
        <td>&nbsp;</td>
        <td class="total">Subtotal</td>
        <td><?php echo money_format('%n', $invoice['amount'] / 100); ?></td>
    </tr>
    <?php
    if (is_array(json_decode($invoice['taxes'], true))) {
        foreach(json_decode($invoice['taxes']) as $label => $tax) {
    ?>
    <tr class="tax">
        <td>&nbsp;</td>
        <td class="total"><?php echo $label; ?></td>
        <td><?php echo money_format('%n', $tax / 100); ?></td>
    </tr>
    <?php 
        } 
    }
    ?>
    <tr class="finalTotal">
        <td>&nbsp;</td>
        <td class="total"><strong>Total</strong></td>
        <td><strong><?php echo money_format('%n', $invoice['chargedAmount'] / 100); ?></strong></td>
    </tr>
</tbody>
</table>
<a href="javascript:window.print();" class="btn green">Print</a>
<?php
        }
    }