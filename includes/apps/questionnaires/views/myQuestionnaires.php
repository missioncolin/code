<?php

if (!class_exists('Questionnaire')) {
    require dirname(__DIR__) . '/Questionnaire.php';
}
$q = new Questionnaire($db);
$questionnaires = $q->getQuestionnaires($_SESSION['userID']);


?>
<div id="card" class="box smallHeader">
    <div class="heading">
        <h2>My Questionnaires</h2>
    </div>

<?php
//fini_set('display_errors', 'off');
if ($this instanceof Quipp) {
    
    if (empty($questionnaires)) {
       echo "You haven't created any questionnaires";
    
    } else {
        
        echo '<ul>';
        foreach ($questionnaires as $questionnaire) {
            echo '<li><a href="/questionnaires?qnrID=' . $questionnaire['itemID'] . '">' . $questionnaire['label'] . '</a></li>';        
        }
        echo '</ul>';
    }
}
?>
</div>
<a href="/questionnaires" class="btn green">Create New</a>