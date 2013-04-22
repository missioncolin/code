<?php


class Credits {

    var $db;
    var $credits = array();
    var $taxes = array(
        '1' => array( //Alberta
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        '2' => array( //BC
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        '3' => array( //MB
            array(
                'label' => "GST",
                'rate'  => 0.05
            ),
            array(
                'label' => 'PST',
                'rate'  => 0.07
            )
        ),
        '4' => array( //NB
            array(
                'label' => "HST",
                'rate'  => 0.13
            )
        ),
        '5' => array( //NL
            array(
                'label' => "HST",
                'rate'  => 0.13
            )
        ),
        '7' => array( //NS
            array(
                'label' => "HST",
                'rate'  => 0.15
            )
        ),
        '9' => array( //ON
            array(
                'label' => "HST",
                'rate'  => 0.13
            )
        ),
        '10' => array( //PE
            array(
                'label' => 'HST',
                'rate'  => 0.14,
            )
        ),
        '11' => array( //QC
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
        '12' => array( //SK
            array(
                'label' => "GST",
                'rate'  => 0.05
            ),
            array(
                'label' => 'PST',
                'rate'  => 0.05
            )
        ),
        '13' => array( //YK
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        
        '6' => array( //NW
            array(
                'label' => "GST",
                'rate'  => 0.05
            )
        ),
        '8' => array( //NU
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

    // /* Updates billing information for the current transaction */
    // public function setBillingInfo($transID, $firstName, $lastName, $address, $city, $email, $postal, $province, $country) {

    //     $qry = sprintf("UPDATE tblTransactions SET billingFirstName = '%s', billingLastName = '%s', billingAddress = '%s',
    //                     billingCity = '%s', billingEmail = '%s', billingPostal = '%s', billingProvince = '%s', 
    //                     billingCountry = '%s' WHERE itemID = '%d'", $firstname, $lastName, $address, $city, $email, $postal, $province,
    //                     $country, $transID);

    //     $res = $this->db->query($qry);

    //     if ($this->db->num_rows($res) > 0) {
    //         return true;
    //     }

    //     return false;

    // }

    public function charge($creditID, $token, $user, $firstName, $lastName, $address, $city, $email, $postal, $province, $country) {

        $price = (int)($this->credits[$creditID]['price'] * 100);
        try {
            $response = Stripe_Charge::create(array(
                "amount"      => round($price + array_sum($this->calculateTax($price, (int)$province))),
                "currency"    => "cad",
                "card"        => $token,
                "description" => $this->credits[$creditID]['packageName'] . " for {$user->username}"
            ));
            
            if ($response->paid == true) {
                
                $qry = sprintf("INSERT INTO tblTransactions (userID, creditID, id, chargedAmount, amount, taxes, currency, description, paid, billingFirstName, 
                                billingLastName, billingAddress, billingCity, billingEmail, billingPostal, billingProvince,
                                billingCountry, sysDateCreated) VALUES ('%d', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s',
                                '%s', '%s', '%s', '%s', '%s', NOW())",
                    (int)$user->id,
                    (int)$creditID,
                    $this->db->escape($response->id),
                    (int)$response->amount,
                    (int)$price,
                    json_encode($this->calculateTax($price, (int)$province)),
                    $this->db->escape($response->currency),
                    $this->db->escape($response->description),
                    (int)$response->paid,
                    $firstName,
                    $lastName,
                    $address,
                    $city,
                    $email,
                    $postal,
                    $province,
                    $country);

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
    
    
    public function calculateTax($amount, $billingProv) {
        
        $taxes = array();
        if (isset($this->taxes[(int)$billingProv])) {
            foreach($this->taxes[(int)$billingProv] as $tax) {
                
                if (!isset($tax['groupGST']) || isset($tax['groupGST']) && $tax['groupGST'] !== true) {
                    $taxes[$tax['label']] = $amount * $tax['rate'];
                } else {
                    $taxes[$tax['label']] = ($amount + array_sum($taxes)) * $tax['rate'];
                }
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