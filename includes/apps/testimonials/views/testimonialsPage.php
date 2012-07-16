<?php

if ($this INSTANCEOF Quipp && isset($this->siteID)){
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/apps/testimonials/Testimonials.php");

$tml = new Testimonials($db);
$isAdmin = (preg_match("%(draft=preview)%",$_SERVER["REQUEST_URI"],$matches))?true:false;
$testimonials = $tml->getTestimonialsList($this->siteID,$isAdmin);

if (is_array($testimonials)){
?>
<div class="testimonials">
<?php
    for ($i = 0; $i < count($testimonials); $i++){
        echo '<blockquote>'.nl2br($testimonials[$i]["comment"]).'</blockquote>';
        echo '<p class="submittedBy">'.nl2br($testimonials[$i]["name"]).'</p>';
    }
?>
</div>
<?php
}
}