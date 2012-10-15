<?php

if (!class_exists('Questionnaire')) {
    require dirname(__DIR__) . '/Questionnaire.php';
}
$q = new Questionnaire($db);
$questionnaires = $q->getProfileQuestionnaires($_SESSION['userID'], 3);


?>
<div id="card" class="box smallHeader">
    <div class="heading">
        <h2>My Questionnaires</h2>
    </div>

<?php
//fini_set('display_errors', 'off');
if ($this instanceof Quipp) {
    
    if (empty($questionnaires)) {
       echo "<ul><li>You haven't created any questionnaires</li></ul>";
    
    } else {
        
        echo '<ul>';
        foreach ($questionnaires as $questionnaire) {
            echo '<li><a href="/questionnaires?qnrID=' . $questionnaire['itemID'] . '">' . $questionnaire['label'] . '</a></li>';        
        }
        echo "<li><a href=\"/questionnaires\">View All</a></li>";
        echo '</ul>';
    }
}
?>
</div>

