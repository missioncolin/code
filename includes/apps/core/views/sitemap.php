<?php global $nav; ?>


<?php 
$theMap =  $nav->build_nav($nav->get_nav_items_under_bucket('primary')); 
$theNodes = simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?><nav>".$theMap."</nav>");

$plainMap = strip_tags($theMap ,"<a>");
$mapEls = explode("</a>",$plainMap);
for ($i = 0; $i < count($mapEls); $i++){
    $link = str_replace("</a>","",$mapEls[$i]);
    if (preg_match("%(<a href=[\"\'])(.*)([\"\']>)(.*)%",$link,$matchl)){
        $liEls[] = $matchl[2];
    }
    
}
echo '<div class="left"><h2>Sitemap</h2>';
$n = 0;
$pos = "left";
foreach($theNodes->{"ul"}->{"li"} as $item){
    if ($n > 0){
        $pos = ($pos == "left")?"right":"left";
        echo '</div><div class="'.$pos.'">';
    }
    $sEl = (isset($liEls[$n]))?$liEls[$n]:"";
    echo '<h4><a href="'.$sEl.'">'.(string)$item->{"a"}.'</a></h4>';
    $n++;
    if (isset($item->{"ul"})){
        foreach($item->{"ul"}->{"li"} as $subItem){
            $chEl = (isset($liEls[$n]))?$liEls[$n]:"";
            echo '<p><a href="'.$chEl.'">'.(string)$subItem->{"a"}.'</a></p>';
            $n++;
            if (isset($subItem->{"ul"})){
                echo '<ul>';
                foreach($subItem->{"ul"}->{"li"} as $third){
                    $thEl = (isset($liEls[$n]))?$liEls[$n]:"";
                    echo '<li><a href="'.$thEl.'">'.(string)$third->{"a"}.'</a></li>';
                    $n++;
                }
                echo '</ul>';
            }
        }
    }
}
echo '</div>';
?>