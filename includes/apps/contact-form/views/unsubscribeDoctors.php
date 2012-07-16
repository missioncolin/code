<?php
if (isset($_GET["req"]) && trim($_GET["req"]) == md5("onthefence") && isset($_GET["user"]) && strstr($_GET["user"],"@") !== false){
    
    $unsub = urldecode($_GET["user"]);
    
    $codes = array(
        "231"   => "Already Unsubscribed",
        "232"   => "Not a Registered User",
        "233"   => "Not a Subscribed User",
        "215"   => "Not a Subscribed User"
    );
    
    require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCAPI.class.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/includes/lib/MCconfig.inc.php");
    $mc = new MCAPI($mcAPI);
    $lists = $mc->lists(array("list_name"=>"Kindersmiles Subscribers"));
    $mcLID = $lists["data"][0]["id"];
    if ($mc->listUnsubscribe($mcLID,$unsub,false,false)){
        echo alert_box("Success! Your email address was removed.", 1);
    }
    else{
        $message = "";
        if (isset($codes[$mc->errorCode])){
            $message = " You are ".$codes[$mc->errorCode];
        }
        echo alert_box("Error: Your email address was not removed.".$message, 2);
    }
}
else{
    echo alert_box("The page you are looking for was not found", 0);
}

