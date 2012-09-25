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
            
            if ($response->paid == true) {
                
                $qry = sprintf("INSERT INTO tblTransactions (userID, creditID, id, amount, currency, description, paid, sysDateCreated) VALUES ('%d', '%d', '%s', '%d', '%s', '%s', '%d', NOW())",
                    (int)$user->id,
                    (int)$creditID,
                    $this->db->escape($response->id),
                    (int)$response->amount,
                    $this->db->escape($response->currency),
                    $this->db->escape($response->description),
                    (int)$response->paid);
                $this->db->query($qry);
                
                $totalCredits = $this->assignCredits($user, $this->credits[$creditID]['credits']);
                
            } else {
                throw new Exception('There was an unexpected error.');
            }
        
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return true;

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
}