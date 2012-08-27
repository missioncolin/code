<?php


/**
 * returns a string with a formatted query string with some values removed
 */
 
function clean_query_string($val, $key)
{
	global $qs;

	// an array of paramaters to ignore
	$ignore = array(
		'null' => true,
		'chgC' => true,
		'p' => true,
		'c' => true,
		'carta' => true,
		'add' => true,
		't'   => true,
		'mode' => true,
		'qty' => true
	);
	

	
	
	
	if(!array_key_exists($key, $ignore)) {
		$qs .= $key . '=' . urlencode($val) . '&';
	}
}

/**
 * returns a nicely formatted block
 */
function yell()
{
	$total = func_num_args();
	$arrays = func_get_args();

	if($arrays[0] == 'print') {
		print '<pre style="text-align:left;margin:10px auto;text-align:left;background-color:#fff;color:#333;overflow:auto;width:940px;padding:10px;-moz-border-radius:5px;-webkit-border-radius:5px;border:1px solid #333;">';
		print '<h3>Testing Data</h3>';
		for ($i = 1; $i < $total; $i++) {
			print_r($arrays[$i]);
		}
		print '</pre>';
	
	} elseif($arrays[0] == 'var_dump') {
		print '<pre style="text-align:left;margin:10px auto;text-align:left;background-color:#fff;color:#333;overflow:auto;width:940px;padding:10px;-moz-border-radius:5px;-webkit-border-radius:5px;border:1px solid #333;">';
		print '<h3>Testing Data</h3>';

		for ($i = 1; $i < $total; $i++) {
			var_dump($arrays[$i]);
		}
		print '</pre>';
	} else {
		/*$date = date('D M d H:i:s Y');
		error_log("\n\n----------------------------------------------------------------------------------------------  \n", 3, "/resolutionDevSiteRoot/dev.log");

		error_log("[" . $date . "] [log] [client " . $_SERVER['REMOTE_ADDR'] . "]\n", 3, '/resolutionDevSiteRoot/dev.log');
		for ($i = 0; $i < $total; $i++) {
			error_log(print_r($arrays[$i], true) . " \n", 3, "/resolutionDevSiteRoot/dev.log");
		
		}*/
	}
		
	
}


/**
 * displays an error message
 * 1: good (check)
 * 2: bad (bad symbol)
 * 3: warn (ex point)
 */
function alert_box($message, $alertType, $otherIcon = "")
{

	switch ($alertType) {
	case 1:
		$img   = "/images/alert1.gif";
		$class = "alertBoxFunctionGood";
		break;
	case 2:
		$img   = "/images/alert2.gif";
		$class = "alertBoxFunctionBad";
		break;
	case 3:
	default:
		$img   = "/images/alert3.gif";
		$class = "alertBoxFunctionWarn";
		break;
	case 4:
		$img   = false;
		$class = "alertBoxFunctionEmpty";
		break;
	}

	if($otherIcon != '') {
		$img = $otherIcon;
	}

	if($img) {
		$background = "background-image: url('$img');";
	}
	return sprintf('<div class="%s" style="%sdisplay:block;">%s</div>',
		$class,
		$img,
		$message);
}


/**
 * sanitizes a string based on several paramaters
 */
function clean($string, $cleanHTML = false, $runHTMLentities = false)
{

	if(!is_string($string)) {
		return $string;
	}


	//remove odd non-standard Microsoft Word characters and change special characters to HTML supported ones
	$badwordchars = array(
		chr(145),
		chr(146),
		chr(96),
		chr(132),
		chr(147),
		chr(148),
		chr(133),
		chr(150),
		chr(151)
	);
	$fixedwordchars = array(
		"'",
		"'",
		"'",
		'&quot;',
		'&quot;',
		'&quot;',
		'...',
		'&mdash;',
		'&mdash;'
	);




	if($cleanHTML) {//remove all HTML formatting

		$string = strip_tags($string); //strip any HTML tags

		if($runHTMLentities) {
			$string = htmlentities($string);
		}

		$string = str_replace($badwordchars, $fixedwordchars, $string);

	} else {

		if($runHTMLentities) {
			$string = htmlentities($string);
		}
		$string = str_replace($badwordchars, $fixedwordchars, $string);
	}

	return $string;
}



/**
 * SUB ME
 * takes a string, if that sting is over a provided max length it is cut down and
 * a hellip (three dots as one character) is added to the end
 */
function str_shorten($content, $maxCharacters, $showDots = "&hellip;", $stopAtSpace = false)
{

	if(strlen($content) > $maxCharacters) {
		$toReturn = substr($content, 0, $maxCharacters) . $showDots;
		if(!$stopAtSpace) {
			return $toReturn;
		} else {
			$toReturn = substr($content, 0, $maxCharacters);
			$pos = strrpos($toReturn, " ");

			$toReturn = substr($toReturn, 0, $pos) . $showDots;
			return $toReturn;
		}
	} else {
		return $content;
	}
}



/**
 * Prepare a string for output as
 * an HTML link or filename.
 * ex. "FRID'EH UPDATE #9" becomes "frideh-update-9"
 */
function slug($string)
{
	$string = strtolower($string);
	$string = preg_replace("/[^[:space:]\.a-zA-Z0-9_-]/", "", $string);
	$string = str_replace('  ', ' ', $string);
	$string = str_replace(' ', '-', $string);
	return str_replace('---', '-', $string);
}


/**
 * string_format
 *
 * string_format("(###) ###-####", "4015551212");
 * will print out:
 * (401)555-1212
 */

function str_format($format, $string, $placeHolder = "#")
{
	$numMatches = preg_match_all("/($placeHolder+)/", $format, $matches);
	foreach ($matches[0] as $match) {
		$matchLen = strlen($match);
		$format = preg_replace("/$placeHolder+/", substr($string, 0, $matchLen), $format, 1);
		$string = substr($string, $matchLen);
	}
	return $format;
}


/**
 * Strips out all characters that aren’t numeric.
 * INCLUDING DECIMALS!
 */
function make_numeric($foo)
{
	$numfoo = preg_replace("/[^[:digit:]]/", "", $foo);
	return $numfoo;
}



/**
 * pagination(total, current, url)
 *
 * Displays pagination based on
 * input paramaters
 */
function pagination($total, $display, $current = 1, $url = '/?page=', $showall = true)
{

	$toReturn = '';
	if($total > 0) {

		$pages   = ceil($total/$display);

		if($pages > 1) {
			$toReturn .= '<div class="clearBox"></div><div class="pagination">';
			if($current == 1) {
				$toReturn .= '<span class="previous disabled">Previous</span>';
			} else {
				$toReturn .= '<a class="previous" href="' . $url . ($current - 1) . '">Previous</a>';
			}

			//Checks to see if there are less than 12 Pages
			if($pages <= 12) {


				$page = 1;
				while ($page <= $pages) {

					if($page == $current) {
						$toReturn .= '<a class="current" href="'. $url . $page . '">' . $page . '</a>';
					} else {
						$toReturn .= ' <a href="'. $url . $page . '">' . $page . '</a>';
					}
					$page++;
				}

				//Checks to see if the current page is too close to the start for normal processing
			} else if($current < 8) {

					$page = 1;
					while ($page < 11) {

						if($page == $current) {
							$toReturn .= '<a class="current" href="'. $url . $page . '">' . $page . '</a>';
						} else {
							$toReturn .= ' <a href="'. $url . $page . '">' . $page . '</a>';
						}
						$page++;
					}

					$toReturn .= '<span class="spacing"> ... </span><a href="' . $url . ($pages - 1) . '">' . ($pages - 1) . '</a>  <a href="' . $url . $pages . '">' . $pages . "</a>";

					//Checks to see if te current page is too close to the end for nomrmal processing

				} else if($current > ($pages - 7)) {

					$toReturn .= '<a href="' . $url . '1">1</a><a href="' . $url . '2">2</a><span class="spacing"> ... </span>';

					$page = $pages - 10;
					while ($page <= $pages) {
						if($page == $current) {
							$toReturn .= '<a class="current" href="'. $url . $page . '">' . $page . '</a>';
						} else {
							$toReturn .= ' <a href="'. $url . $page . '">' . $page . '</a>';
						}
						$page++;
					}


					//Runs normal processing if other checks are false
				} else {

				$toReturn .= '<a href="' . $url . '1">1</a><a href="' . $url . '2">2</a><span class="spacing"> ... </span>';

				$page = $current - 4;

				while ($page <= $current + 4) {
					if($page == $current) {
						$toReturn .= '<a class="current" href="'. $url . $page . '">' . $page . '</a>';
					} else {
						$toReturn .= ' <a href="'. $url . $page . '">' . $page . '</a>';
					}
					$page++;
				}



				$toReturn .= "<span class='spacing'> ... </span><a href='". $url . ($pages - 1) . "'>" . ($pages - 1) . "</a>  <a href='". $url . $pages . "'>" . $pages . "</a>";
			}


			if($current == $pages) {
				$toReturn .= '<span class="next disabled">Next</span>';
			} else {
				$toReturn .= '<a class="next" href="' . $url . ($current + 1) . '">Next</a>';
			}
			if ($showall === true){
			    $toReturn .= '<a class="showAll" href="' . $url . '1&amp;show=all">Show all</a>';
			}
			$toReturn .= '</div>';
		}
	}
	return $toReturn;
}



/*
GET PROV LIST
returns a list of Provinces with the provided being selected as default
*/
function get_prov_list($selectName, $whichSection = false, $default = 9, $extraParam = "")
{
	if(empty($whichSection)) { $whichSection = $default; }
	return get_list($selectName, "sysProvince", "provName", "WHERE sysOpen = '1'", $whichSection, $extraParam, "$selectName", "itemID", false, false, false, "itemID ASC");
}
/****** End Of GET PROV LIST ******/

/*
GET COUNTRY LIST
returns a list of countries with the provided being selected as default
*/
function get_country_list($selectName, $whichSection = false, $default = 38, $extraParam = "")
{
	if(empty($whichSection)) { $whichSection = $default; }
	return get_list($selectName, "sysCountry", "countryName", "WHERE sysOpen = '1'", $whichSection, $extraParam, "$selectName", "itemID", false, false, false, "itemID ASC");
}
/****** End Of GET COUNTRY LIST ******/


/*
GET ME A LIST
returns a list of items with the provided being selected as default.
can make list from array elements or database table
*/

function get_list($selectName, $whatTable, $whatItem, $whereClause = "WHERE sysOpen = '1'", $whichSection = false, $extraSelectParam = "", $selectID = "", $whatValue = "itemID", $isOrder = false, $debug = false, $allOption = false, $argNewOrder = false, $groupItems = false)
{
	global $db;

	if(!$argNewOrder) { 
		$argNewOrder = "$whatItem"; 
	}
	
	if($groupItems) {
		$groupItems = "GROUP BY";
		$whatValue = "MIN(".$whatValue.") AS ".$whatValue."";
	} else { 
		$groupItems = "ORDER BY"; 
	}
	
	
	$orderCount = 1;
	
	if(empty($selectID)) {
		$selectID = $selectName; 
	}
	$whatToReturn = " <select  class='uniform' " . $extraSelectParam . " id=\"$selectID\" name=\"$selectName\"> ";
	
	if($allOption) { 
		 $whatToReturn .= "<option value=\"0\">" . $allOption . "</option>"; 
	}
	
	if(!is_array($whatTable)) {
		$getItems = "SELECT $whatValue, $whatItem FROM $whatTable $whereClause " . $groupItems . " " . $argNewOrder . ";";
		if($debug) {
			print yell($getItems);
		}
		$Result = $db->query($getItems);

		if($argNewOrder) {
			$whatValue = 0;
			$whatItem = 1;

		}
		while ($secRS = $db->fetch_array($Result)) {
			//print substr($whatValue, strpos($whatValue,".") + 1,strlen($whatValue)) . "<br />";
			if(strpos($whatValue, ".") > 0) {
				$whatValue = substr($whatValue, strpos($whatValue, ".") + 1, strlen($whatValue));
			}
			if(strpos($whatItem, ".") > 0) {
				$whatItem = substr($whatItem, strpos($whatItem, ".") + 1, strlen($whatItem));
			}
			if(!$isOrder) {
				//    $valueToReturn = $secRS[$whatValue];
				$valueToReturn = $secRS[$whatValue];
				$valueToDisplay = $secRS[$whatItem];
			}
			else {
				$valueToReturn = $orderCount;
				$valueToDisplay = $orderCount;
				$orderCount++;
			}
			if($valueToReturn == $whichSection) {
				$amISelected = "selected=\"selected\""; $b = "*";
			}
			else {
				$amISelected = ""; $b="";
			}
			if($debug) { $valueToDisplay .= ":" . $whichSection; }

			$whatToReturn .= "<option value=\"$valueToReturn\" $amISelected> $valueToDisplay $b </option>";
		}

	}/*end is not array*/ else {
		/*	$selectName, $whatTable, "X", "X", $whichSection = false, $extraSelectParam = "", $selectID = "", "X", $isOrder = false, "X" */
		foreach ($whatTable as $key => $value) {

			//print substr($whatValue, strpos($whatValue,".") + 1,strlen($whatValue)) . "<br />";
			if(!$isOrder) {
				$valueToReturn = $key;
				$valueToDisplay = $value;
			}
			else {
				$valueToReturn = $orderCount;
				$valueToDisplay = $orderCount;
				$orderCount++;
			}
			if($valueToReturn == $whichSection) {
				$amISelected = "selected=\"selected\""; $b = "*";
			}
			else {
				$amISelected = ""; $b="";
			}


			$whatToReturn .= "<option value=\"$valueToReturn\" $amISelected> $valueToDisplay $b </option>";
		}

		$whatToReturn .= " </select>";
		return $whatToReturn;

	}


	$whatToReturn .= " </select>";
	if($Result != false || !empty($whatTable)) {
		return $whatToReturn;

	}
	else {
		return "<span style=\"font-size:10px; color:#CCCCCC;\">[there are no items]</span>";
	}

}



/*
VALIDATE FORM
Checks form elements that are formatted distinctly

You'll need the following code for the element coloring

To do: ADD JQUERY targeting

if(!empty($badFields)) {
	$sendMeIn .= "<script language=\"javascript\" type=\"text/javascript\">
	var bKeeper = new Array(".implode("," , $badFields) . ");
	for(i = 0; i < bKeeper.length; i++) {
		bKeeper[i] = bKeeper[i] + \"\";
		document.getElementById(bKeeper[i]).style.backgroundColor = \"#FFFFCC\";
	}</script> ";
}

*/



function validate_form($formData = false)
{

	global $message, $cleanNames, $badFields;


	$is_valid = true;

	/********************************************************************************************/
	//All required fields should start with 'RQ'
	//Create an array to store the required fields seperate from the $_REQUEST

	$requiredFields = array(); //base array (holds both but all are required)
	$finalRequired  = array(); //after parsing (will hold non-pattern only but still required)
	$badFields   = array(); //hold an array of bad fields for javascript colouring
	$cleanNames  = array(); //holds a clean name for display in validation and excel file headers

	/******************************************************************************************/
	$validationClasses = array(
		'valDATE' => array(
			'myItem' 	=> array(), 
			'myPattern' => '^([0-9]{4})(-[0-9]{2})(-[0-9]{2})?$', 
			'myTitle' 	=> 'Date (YYYY-DD-MM)'
		),
		'valNUMB' => array(
			'myItem' 	=> array(), 
			'myPattern' => '\d', 
			'myTitle' 	=> 'Number'
		),
		'valALPH' => array(
			'myItem' 	=> array(), 
			'myPattern' => '[[:alnum:]]', 
			'myTitle' 	=> 'Text'
		),
		'valPHON' => array(
			'myItem' 	=> array(), 
			'myPattern' => '^(?:\+?1[-. ]?)?\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$', 
			'myTitle' 	=> 'Phone Number ([1-]555-555-5555)'
		),
		'valPOST' => array(
			'myItem' 	=> array(), 
			'myPattern' => '^([abceghjklmnprstvxy][0-9][a-z][\s-]*[0-9][a-z][0-9])?$|^([0-9]{5})?$|^([0-9]{5}-[0-9]{4})?$', 
			'myTitle' 	=> 'Postal Code (A1A 1A1) or Zip Code (99999) or (99999-2222)'
		),
		'valMAIL' => array(
			'myItem' 	=> array(), 
			'myPattern' => '^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$', 
			'myTitle' 	=> 'E-Mail Address (user@domain.com)'
		),
		'valWEBS' =>array(
			'myItem' 	=> array(), 
			'myPattern' =>'^((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?([a-z0-9-.]*)\.([a-z]{2,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?', 
			'myTitle' 	=> 'Website Address ([http or https:// or ftp]://[www.]domain.[com or ca])'
		),
		'valCCNM' => array(
			'myItem' 	=> array(), 
			'myPattern' => '^(5[1-5][0-9]{14}|6011[0-9]{12})|(4[0-9]{12}([0-9]{3})?)|(3[47][0-9]{13})$', 
			'myTitle' 	=> 'Mastercard, Discover, Visa, American Express are currently accepted'
		)
	);

	//'valCCNM' => array('myItem' => array(),'myPattern' => '^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$', 'myTitle' => 'Credit Card (XXXX-XXXX-XXXX-XXXX)')

	//generate clean names

	foreach ($formData as $key => $value) {

		$myCleanedName = $key;

		//clean out RQ
		if(substr($key, 0, 2) == "RQ") {
			$myCleanedName = substr($key, 2, strlen($key));

			//clean out val (as they can only exist if RQ is present anyway)
			if(substr($key, 2, 3) == "val") {
				$myCleanedName = substr($key, 9, strlen($key));
			}
		}
		elseif(substr($key, 0, 2) == "OP") {
			//clean out val (as they can only exist if RQ is present anyway)
			if(substr($key, 2, 3) == "val") {
				$myCleanedName = substr($key, 9, strlen($key));
			}
		}


		//replace '_' with spaces
		$myCleanedName = str_replace("_", " ", $myCleanedName);

		$cleanNames[$key] = $myCleanedName;
	}


	//store the required fields (both for base testing)
	foreach ($formData as $key => $value) {
		if(is_string($formData[$key])) {
			if(substr($key, 0, 2) == "RQ") {
				$requiredFields[$key] = $value;
			}
			elseif(substr($value, 0, 5) == "OPval" && !empty($value)) {
				$requiredFields[$key] = $value;
			}
		}

	}

	//pull out the items that require further validation and stick them in the proper array as listed above
	foreach ($requiredFields as $key => $value) {
		if(substr($key, 2, 3) == "val") {
			//needs pattern matching
			$whereTo = substr($key, 2, 7);
			$validationClasses[$whereTo]['myItem'][count($validationClasses[$whereTo]['myItem'])] = $key;

		} else {
			//just plain old empty() validation
			$finalRequired[$key] = $value;
		}
	}


	//ALL DATA ORGANIZED, NOW RUN THE CHECKS ON THE REQUIRED STUFF WITHOUT FORMATTING
	foreach ($finalRequired as $key => $value) {
		if(!valid_form_check(trim($value), false, "", "Required", $key)) {
			$is_valid = false;
			$badFields[count($badFields)] = "\"" . $key . "\""; //this field is empty add it to the highlight list for the js

		}
	}


	foreach ($validationClasses as $key => $value) {
		for ($j=0; $j < count($validationClasses[$key]['myItem']); $j++) {

			if(!valid_form_check($requiredFields[$validationClasses[$key]['myItem'][$j]],
					true, $validationClasses[$key]['myPattern'],
					$validationClasses[$key]['myTitle'],
					$validationClasses[$key]['myItem'][$j])) {
				$is_valid = false;
				$badFields[count($badFields)] = "\"" . $validationClasses[$key]['myItem'][$j] . "\"";
				//this field is either empty or not in the proper format add it to the highlight list for the js
			}
		}
	}

	return $is_valid;
}


/*
AM I A VALID FORM
Checks form elements for validation used with ValidateForm()
*/

function valid_form_check($myContent, $formatting, $pattern, $classType, $myName)
{

	global $message, $cleanNames;

	//print debug($formatting);

	//always check to see if I'm empty
	if(_empty($myContent)) {
		$message .= "<li> <strong> " . $cleanNames[$myName]. " </strong> is required and was left empty, please fill it in before submission. <em> $classType </em> </li>";
		return false;
	}
	if($formatting) {
		if(!preg_match("/" . $pattern . "/i", $myContent)) {
			$message .= "<li> <strong>". $cleanNames[$myName]." </strong> field was not in the proper format, please check it before submission. <em> $classType </em> </li>";
			return false;
		}
	}
	return true;
}

/****** End Of AM I A VALID FORM ******/


function _empty($string)
{

	$string = trim($string);
	if(!is_numeric($string)) {
		return empty($string);
	}
	return false;
}





function upload_file($file, $location, $allowed_mime_types, $thumbnails = false, $randomizeName = false, $overwrite = false)
{

	$uploadErrors = array(
		0 => "There is no error, the file uploaded with success",
		1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
		2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
		3 => "The uploaded file was only partially uploaded",
		4 => "No file was uploaded",
		6 => "Missing a temporary folder"
	);

	// Validate the upload
	if(!isset($_FILES[$file])) {
		return "<strong>Error</strong>, No upload found in \$_FILES for " . $file;
	} else if(isset($_FILES[$file]["error"]) && $_FILES[$file]["error"] != 0) {
		return "<strong>Error</strong>, " . $uploadErrors[$_FILES[$file]["error"]] . ': ' . $file;;
	} else if(!isset($_FILES[$file]["tmp_name"]) || !is_uploaded_file($_FILES[$file]["tmp_name"])) {
		return "<strong>Error</strong>, Upload failed is_uploaded_file test.";
	} else if(!isset($_FILES[$file]['name'])) {
		return "<strong>Error</strong>, File has no name.";
	}


	if(!array_key_exists($_FILES[$file]['type'], $allowed_mime_types)) {
		return "<strong>Error</strong>, the system could not be updated, because the file must be one of the following: " . implode(', ', array_unique($allowed_mime_types)) . " - You uploaded a file with the mime type " . $_FILES[$file]['type'];
	}


	// Validate file name (for our purposes we'll just remove invalid characters)
	$fileInfo  = pathinfo($_FILES[$file]['name']);
	$extension = strtolower($fileInfo["extension"]);

	$extension = ($extension == 'jpeg') ? 'jpg' : $extension;
	$fileName  = $fileInfo['filename'];

	$fileName  = slug($fileName);
	if($randomizeName === true) {
		$fileName = substr(md5($fileName . time()), 0, 10);
	}
	if(file_exists($location . $fileName . '.' . $extension) && $overwrite === false) {
		$i = 1;
		while (file_exists($location . $fileName . $i . '.' . $extension)) {
			$i++;
		}
		$fileName =  $fileName . $i;
	}


	/*
	$thumbnails = array (
		'med' 	=> array(
			'l' 	   => 120,
			'w' 	   => 120,
			'adaptive' => true
		),
		'large' => array(
			'l' 	   => 300,
			'w' 	   => 300,
			'adaptive' => false
		)
	);
	*/

	if(is_array($thumbnails)) {



		foreach ($thumbnails as $name => $sizes ) {

			try {
				$thumb = PhpThumbFactory::create($_FILES[$file]["tmp_name"]);
			}
			catch (Exception $e) {
				return "<strong>Error</strong>, Cannot create thumbnails: " . $e;
			}

			if(isset($sizes['w'], $sizes['l'])) {
				if(isset($sizes['adaptive']) && $sizes['adaptive'] === true) {

					$thumb->adaptiveResize($sizes['w'], $sizes['l']);
				} else {
					$thumb->resize($sizes['w'], $sizes['l']);
				}

				if(!is_dir($location . $name)) {
					mkdir($location . $name);
				}
				$thumb->save($location . $name . '/' . $fileName . '.' . $extension, strtolower($extension));
			}

		}
	}

	move_uploaded_file($_FILES[$file]['tmp_name'], $location . $fileName . '.' . $extension);
	return $fileName . '.' . $extension;

}
function tinyMCE($id, $theme)
{

	if($theme == "simple") {
		$buttons = 'theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",';

	}
	if($theme == "standard") {
		$buttons = 'theme_advanced_buttons1 : "undo,redo,code,|,link,unlink,anchor, image,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,styleselect",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",';

	}
	if($theme == "advanced") {
		$buttons = 'theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",';

         
 	}
 

	$return = '$("#'. $id .'").tinymce({';
	
	// position the toolbars
 	$return .=  'theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left", theme_advanced_resizing : true, ';

	
	$return .=  ' script_url : "/js/tinymce/jscripts/tiny_mce/tiny_mce.js",

            // General options
            theme : "advanced",
            plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",';

	$return .=  $buttons;
	$return .=  'file_browser_callback : "tinyBrowser",';

	$return .=  '// Example content CSS (should be your site CSS)
	            content_css : "/css/tiny_mce.css",

	            // Drop lists for link/image/media/template dialogs
	            template_external_list_url : "lists/template_list.js",
	            external_link_list_url : "lists/link_list.js",
	            external_image_list_url : "lists/image_list.js",
	            media_external_list_url : "lists/media_list.js"';


	$return .=  ' });';

 
	
	return $return;
}


/*
SET APPROVAL LOG
	Accepts a message and links.  Used to set records in the Content Streem Manager.
*/
function set_approval_log($appName, $appItemID, $logMessage, $viewLink, $editLink, $denyLink, $approveLink, $userID = "USESESSIONMYID", $instanceID = false, $pageRS)
{
	global $db, $user;


	if($userID == "USESESSIONMYID") {
		$userID = $user->id;
	}

	$qry = sprintf("DELETE FROM sysApprovalLog WHERE sysOpen = '1' AND appName = '%s' AND appItemID = '%s';",
		$db->escape($appName),
		$db->escape($appItemID));
	$db->query($qry);

	$qry = sprintf("INSERT INTO sysApprovalLog (instanceID, appName, appItemID, dragUserID, dateTime, description, viewLink, editLink, denyLink, approveLink, sysStatus, sysOpen) VALUES ('%d', '%s', '%s', '%d', %s,  '%s', '%s', '%s', '%s', '%s', 'active', '1');%s",
		(int) $instanceID,
		$db->escape($appName),
		$db->escape($appItemID),
		(int) $userID,
		$db->now,
		$db->escape($logMessage),
		$db->escape($viewLink),
		$db->escape($editLink),
		$db->escape($denyLink),
		$db->escape($approveLink),
		$db->last_insert);
		
	$res = $db->query($qry);
	$approvalID = $db->insert_id();
	
	
	$approvalGroupCandidates = array();

	//////////////// SEND OUT AN E-MAIL TO ANY APPROVERS //////////////////////
	$lastSendBlastTo =  null;
	
	
	if($pageRS['editPrivID']) {
		$approvalGroupCandidates = array();
		//determine if this page is limited to editing by cetain groups
		$getMyGroupsWhoCanEdit = sprintf("SELECT DISTINCT g.itemID, g.nameFull, l.privID 
			FROM sysUGroups AS g
			LEFT OUTER JOIN sysUGPLinks AS l ON (g.itemID = l.groupID AND l.privID = '%d') 
			WHERE l.privID IS NOT NULL 
			AND g.sysOpen = '1' 
			ORDER BY g.nameFull ASC;",
				(int) $pageRS['editPrivID']);

		$getMyGroupsWhoCanEditResult = $db->query($getMyGroupsWhoCanEdit);

		while ($grpwceRS = $db->fetch_assoc($getMyGroupsWhoCanEditResult)) {
			//of those groups, determine which of them has approval capabilities
			//pile each one of those into an array
			array_push($approvalGroupCandidates, $grpwceRS['itemID']);
		}



		$getAllGroupsWhoCanApprove = "SELECT DISTINCT g.itemID 
				FROM sysUGroups AS g
				LEFT OUTER JOIN sysUGPLinks AS l ON (g.itemID = l.groupID)
				LEFT OUTER JOIN sysPrivileges AS p ON (l.privID = p.itemID)
				WHERE g.sysOpen = '1' AND p.systemName = 'approvepage';";
		$getAllGroupsWhoCanApproveResult = $db->query($getAllGroupsWhoCanApprove );

		while ($grpwcaRS = $db->fetch_assoc($getAllGroupsWhoCanApproveResult)) {
			if(in_array($grpwcaRS['itemID'], $approvalGroupCandidates)) {
			
				//we've found a group who can approve, now for each user in that group, blast an e-mail
				$getUsersForMailBlast = sprintf("SELECT DISTINCT u.itemID, u.userIDField 
					FROM sysUsers AS u
					LEFT OUTER JOIN sysUGLinks AS l ON (u.itemID = l.userID AND l.groupID = '%d') 
					WHERE u.sysOpen = '1'",
						(int) $grpwcaRS['itemID']);
				$getUsersForMailBlastResult = $db->query($getUsersForMailBlast);
				while ($blastUsersRS = $db->fetch_assoc($getUsersForMailBlastResult)) {
				
					$sendBlastTo = User::get_meta("E-Mail", $blastUsersRS['itemID']);
					
					$subject = "Approval Request [" . $pageRS['label'] . "]";
					$body = "Hello,

A change has been made to a page on the web site which requires your approval.

Please use the following links below to access the CMS administration area to review the changes:

View: 				http://" . $_SERVER['SERVER_NAME'] . "/webAdmin/approvalLog.php?action=view&approvID=" . $approvalID . "
Edit: 				http://" . $_SERVER['SERVER_NAME'] . "/webAdmin/approvalLog.php?action=edit&approvID=" . $approvalID . "
Deny Content: 		http://" . $_SERVER['SERVER_NAME'] . "/webAdmin/approvalLog.php?action=deny&approvID=" . $approvalID . "
Approve Content: 	http://" . $_SERVER['SERVER_NAME'] . "/webAdmin/approvalLog.php?action=approve&approvID=" . $approvalID . "

Thanks!";
					if(User::get_meta("Send Notification Emails", $blastUsersRS['itemID']) == '1') {
						$quipp->system_log("Approval request e-mail for [" . $pageRS['label'] . "] was sent out to: $sendBlastTo");
						if($sendBlastTo != $lastSendBlastTo) {
							$lastSendBlastTo = $sendBlastTo;
							mail($sendBlastTo, $subject, $body, "From:no-reply@" . str_replace("www.", "", $_SERVER['SERVER_NAME']));
						}
					} else {
						$quipp->system_log("Skipped [" . $blastUsersRS['userIDField'] . "] in approval e-mail blast for [" . $pageRS['label'] . "] as their settings specify they do not want to receive notifications. ");
					}
				}
			}
		}
	}
	return true;
}
//to sort a multi dimenstional array by sub key
function multi_array_subval_sort($list,$keySort){
	foreach($list as $key=>$val) {
		$sorted[$key] = strtolower($val[$keySort]);
	}
	asort($sorted);
	foreach($sorted as $key=>$val) {
		$final[] = $list[$key];
	}
	return $final;
}

function array_search_recursive($haystack, $needle, $index = null)
{
	$aIt = new RecursiveArrayIterator($haystack);
	$it  = new RecursiveIteratorIterator($aIt);

	while ($it->valid()) {
		if(((isset($index) && ($it->key() == $index)) || (!isset($index))) && ($it->current() == $needle)) {
			return $aIt->key();
		}

		$it->next();
	}

	return false;
}


/**
 * chunk an array into columns
 * keeps the column count proper
 */
function partition( $list, $p ) {
	if (!is_array($list)) {
		return false;
	}
    $listlen = count( $list );
    $partlen = floor( $listlen / $p );
    $partrem = $listlen % $p;
    $partition = array();
    $mark = 0;
    for ($px = 0; $px < $p; $px++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice( $list, $mark, $incr );
        $mark += $incr;
    }
    return $partition;
}
/**
* Convert decimal degrees to degrees - min - seconds
* Displays formatted GPS coordinates to 3 decimal places
*/
function gps_dec_to_dms($lat,$lon){
	$NS = ($lat > 0)?"N":"S";
	$EW = ($lon > 0)?"E":"W";
	list($latDeg,$latTime) = explode(".",abs($lat));
	$latTime = (abs($lat) - $latDeg)*60;
	list($lonDeg,$lonTime) = explode(".",abs($lon));
	$lonTime = (abs($lon) - $lonDeg)*60;
	$GPS = $NS.$latDeg."&deg; ".number_format($latTime,3)."' ".$EW.$lonDeg."&deg; ".number_format($lonTime,3)."'";
	return $GPS;
}
?>