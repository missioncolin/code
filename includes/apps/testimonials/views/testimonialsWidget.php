<?php
if ($this INSTANCEOF Quipp && isset($this->siteID)){
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/apps/testimonials/Testimonials.php");

$tml = new Testimonials($db);
$isAdmin = (preg_match("%(draft=preview)%",$_SERVER["REQUEST_URI"],$matches))?true:false;
$testimonials = $tml->getTestimonialsList($this->siteID,$isAdmin);
?>
<div id="testimonialsWidget" class="callout">
    <div id="testimonialQuotes">
<?php
if (is_array($testimonials)){
    $numItems = count($testimonials);
    for ($i = 0; $i < $numItems; $i++){
        $comment = $testimonials[$i]["comment"];
        if (strlen($comment) > 90){
            $space = strpos($comment, " ",85);
            $comment = substr($comment,0,$space).'&nbsp;<a href="/testimonials">read more</a>';
        }
        echo "<blockquote".($i > 0 ?' style="display:none"':'').">".$comment."</blockquote>";
    }
?>
    </div>
	<div id="testimonialsNav">
		<ul>
<?php
        if ($numItems > 1){
            for ($l = 1; $l <= $numItems; $l++){
                echo '<li><a'.($l == 1 ?' class="current"':'').' href="#">'.$l.'</a></li>';
            }
        }
        else{
            "<li>&nbsp;</li>";
        }
?>
		</ul>
	</div>
<?php
    if ($numItems > 1){
        global $quipp;
        $quipp->js['footer'][] = "/includes/apps/testimonials/js/testimonials.js";
    }
}
?>
</div>
<?php
}