<?php
require_once dirname(dirname(dirname(__DIR__))) ."/lib/facebookSDK/facebook.php";
?>
<div id="facebookWidget">
<h2>Facebook Update</h2>
<?php

$status = array();
try{
    $facebook = new Facebook(array(
        'appId' => "262874937160756",
        'secret' => "bce420b61a232eb883d1e54ceb701938",
        'cookie' => true,
        'oauth'  => true
    ));
    $fbKs = $facebook->api('/kindersmiles/feed');
    if (!empty($fbKs)){
        echo '<p>'.$fbKs["data"][0]["message"].'</p>';
        if ($fbKs["data"][0]["link"] != ""){
            echo '<p><a href="'.$fbKs["data"][0]["link"].'" target="_blank">Read More</a></p>';
        }
    }
    else{
        echo '<p>No recent facebook updates</p>';
    }
}
catch(\Exception $e){
    echo '<p>No recent facebook updates</p>';
}
?>
</div>