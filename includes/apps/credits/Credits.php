<?php


class Credits {

    var $db;
    var $credits = array();

    public function __construct($db) {

        $this->db = $db;
        $this->getCreditPrices();
    }

    /**
     * Get an associative array of job credit pricing
     * @return array
     */
    public function getCreditPrices() {
        $qry = "SELECT * FROM  `tblJobCreditsPricing`";
        $res = $this->db->query($qry);
        
        if ($this->db->valid($res)) {
            while ($c = $this->db->fetch_assoc($res)) {
                $this->credits[$c['itemID']] = $c;
            }     
        }
        
        return $this->credits;
    }

    public function charge($creditID, $token, $user) {
        
        try {
            $response = Stripe_Charge::create(array(
                "amount"      => $this->credits[$creditID]['price']*100,
                "currency"    => "cad",
                "card"        => $token,
                "description" => $this->credits[$creditID]['packageName'] . " for {$user->username}"
            ));
            
            var_dump($response);
        
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return true;

    }
}