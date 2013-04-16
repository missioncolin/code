<?php


class Credits {

    var $db;
    var $credits = array();
    var $taxes = array(
        '01' => array( //Alberta
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        '02' => array( //BC
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        '03' => array( //MB
            array(
                'label' => "GST",
                'rate'  => 0.05
            ),
            array(
                'label' => 'PST',
                'rate'  => 0.07
            )
        ),
        '04' => array( //NB
            array(
                'label' => "HST",
                'rate'  => 0.13
            )
        ),
        '05' => array( //NL
            array(
                'label' => "HST",
                'rate'  => 0.13
            )
        ),
        '07' => array( //NS
            array(
                'label' => "HST",
                'rate'  => 0.15
            )
        ),
        '08' => array( //ON
            array(
                'label' => "HST",
                'rate'  => 0.13
            )
        ),
        '09' => array( //PE
            array(
                'label' => "GST",
                'rate'  => 0.05
            ),
            array(
                'label' => 'HST',
                'rate'  => 0.09,
                'groupGST' => true
            )
        ),
        '10' => array( //QC
            array(
                'label' => "GST",
                'rate'  => 0.05
            ),
            array(
                'label' => 'QST',
                'rate'  => 0.095,
                'groupGST' => true
            )
        ),
        '11' => array( //SK
            array(
                'label' => "GST",
                'rate'  => 0.05
            ),
            array(
                'label' => 'PST',
                'rate'  => 0.05
            )
        ),
        '12' => array( //YK
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        
        '13' => array( //NW
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        '14' => array( //NU
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        )
    );
    
    
    public function __construct($db) {

        $this->db = $db;
        $this->getCreditPrices();
    }

    /**
     * Get an associative array of job credit pricing
     * @return array
     */
    public function getCreditPrices() {
        $qry = "SELECT * FROM  `tblJobCreditsPricing` ORDER BY price DESC";
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res)) {
            while ($c = $this->db->fetch_assoc($res)) {
                $this->credits[$c['itemID']] = $c;
            }     
        }
        
        return $this->credits;
    }

    public function charge($creditID, $token, $user) {
        
        $price = (int)($this->credits[$creditID]['price'] * 100);
        try {
            $response = Stripe_Charge::create(array(
                "amount"      => round($price + array_sum($this->calculateTax($price, $user))),
                "currency"    => "cad",
                "card"        => $token,
                "description" => $this->credits[$creditID]['packageName'] . " for {$user->username}"
            ));
            
            if ($response->paid == true) {
                
                $qry = sprintf("INSERT INTO tblTransactions (userID, creditID, id, chargedAmount, amount, taxes, currency, description, paid, sysDateCreated) VALUES ('%d', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%d', NOW())",
                    (int)$user->id,
                    (int)$creditID,
                    $this->db->escape($response->id),
                    (int)$response->amount,
                    (int)$price,
                    json_encode($this->calculateTax($price, $user)),
                    $this->db->escape($response->currency),
                    $this->db->escape($response->description),
                    (int)$response->paid);
                $this->db->query($qry);
                $invoiceID = $this->db->insert_id();
                
                $totalCredits = $this->assignCredits($user, $this->credits[$creditID]['credits']);

                return $invoiceID;

                
            } else {
                throw new Exception('There was an unexpected error.');
            }
        
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
       
    }
    
    
    public function calculateTax($amount, $user) {
        
        $fipsCode = $this->db->return_specific_item($user->get_meta('Company Province'), 'sysProvince', 'fipsCode', '00');

        $taxes = array();
        foreach($this->taxes[$fipsCode] as $tax) {
            
            if (!isset($tax['groupGST']) || isset($tax['groupGST']) && $tax['groupGST'] !== true) {
                $taxes[$tax['label']] = $amount * $tax['rate'];
            } else {
                $taxes[$tax['label']] = ($amount + array_sum($taxes)) * $tax['rate'];
            }
        }
        
        return $taxes;
    }
    
    
    /**
     * Assigns credits to a user
     * @param Quipp\User
     * @param int credits
     * @return bool
     */
    static public function assignCredits($user, $credits) {
    
        if ($user->set_meta('Job Credits', ((int)$user->get_meta('Job Credits') + (int)$credits))) {
            
            return $user->get_meta('Job Credits');
        } else {
            return 0;
        }
    }
    
    
    
    public function getInvoice($id, $userID) {
        
        $qry = sprintf("SELECT * FROM tblTransactions WHERE itemID='%d' AND userID='%d'",
            (int)$id,
            (int)$userID);
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res)) {
        
            return $this->db->fetch_assoc($res);
        }
        
        return false;        
    }
}