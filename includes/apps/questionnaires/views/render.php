<?php

global $quipp;

require dirname(dirname(__DIR__)) . '/jobs-manager/JobManager.php';
require dirname(__DIR__) . '/Questionnaire.php';


$j = new JobManager($db, $_SESSION['userID']);

list($title, $link, $dateExpires, $datePosted, $questionnaireID, $status) = $j->getJob($_GET['job']);


if (time() < strtotime($datePosted) || $status == 'inactive') {
    echo alert_box('<strong>Error</strong>. No job found', 2);

} else if (time() > strtotime($dateExpires)) {
    echo alert_box('<strong>Warning</strong>. We\'re sorry, this job posting has expred.', 3);

} else {
    $q = new Questionnaire($db, $questionnaireID);
    $quipp->js['footer'][] = "/includes/apps/questionnaires/js/questionnaires.js";
    
    
    var_dump($_POST);

?>

<form id="job-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<table class="simpleTable">
	<tr><th><?php echo $title; ?></th></tr>
	<?php

    if (is_array($q->questions) && !empty($q->questions)) {
        foreach($q->questions as $questionID => $question) {
            echo "<tr>";
            echo "<td>";
            echo $question['label'];
            echo "</td>";
            echo "</tr>";
            
            
            echo "<tr>";
            echo "<td>";
            switch($question['type']){
                case 1://radio
                case 2://checkbox

                    if (isset($question['options']) && !empty($question['options'])) {
                        echo '<ul>';
                        foreach ($question['options'] as $optionID => $opt) {
    
                            $id   = $questionID . '_' . $optionID;
                            $name = $question['itemID'];
                            
                            echo '<li>';
                            if ($question['type'] == '1') {
                                echo '<input type="radio" id="' . $id . '"  name="' . $name . '"  value="' . $opt['itemID'] . '" />';
                            } else {
                                echo '<input type="checkbox" id="' . $id . '"  name="' . $name . '[]"  value="' . $opt['itemID'] . '" />';
                            }
                            echo '<label for="' . $id . '">' . $opt['label'] . '</label>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo "No options available currently.";
                    }
                
                break;

                case 3://slider
                    $name = $question['itemID'];
                    $id = $name;
                    $val = 0;
                    echo "<div class=\"slider\" rel=\"$name\" alt='".$val."'></div><input type=\"hidden\" name=\"$name\" id=\"$id\" value=\"".$val."\" /><div class='sliderValueHolder' rel='$id'>".$val."/20</div>";

                break;
                
                case 4://video

                case 5://file

                break;

            }
            echo "</td>";
            echo "</tr>";
            
            
        }
    } else {
        $feedback = "This questionnaire has no questions.";
    }


?>
	</table>
	<input type="submit" class="btn green" value="Submit" />
</form>
<?php
}
?>