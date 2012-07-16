<?php
/********************************************************************************************************
 * RESOLUTION INTERACTIVE MEDIA INC. (http://www.resolutionim.com)	    								*
 * 560 Wellington Street, London, Ontario, Canada														*
 *																										*
 * Jonathan Kochis (jonathan@resolutionim.com)															*
 * Brendan Farr-Gaynor (brendan@resolutionim.com)														*
 * Mike Almond (mike@resolutionim.com)																	*
 * Jon Rundle (jon@resolutionim.com)																	*
 ********************************************************************************************************

 SECTIONS
	sec001		Database Access
	sec006		File Access
	sec002		Text Manipulation
	sec003		Image Manipulation
	sec004		Display
	sec005		List Creation
	sec007		Validation
	sec008		Encryption

 ********************************************************************************************************/




/********************************************************************************************************
	sec001	Database Access	Section 
********************************************************************************************************/


/****** End Of SET SYSTEM LOG ******/

/****** End Of SET SYSTEM LOG ******/





/*
PAGINATION RESULT
how should the system query the db
*/
function resPageMyQuery($query, $numOfRecordsPerPage, $requestTag = "", $debug = false) {

	$taggednumOfRecords = "numOfRecords" . $requestTag;
	$taggednumPages  = "numPages" . $requestTag;
	$taggedstart  = "start" . $requestTag;
	$taggednumOfRecordsPerPage = "numOfRecordsPerPage" . $requestTag;
	$$taggednumOfRecordsPerPage = $numOfRecordsPerPage;
	global $_REQUEST, $DRAGGIN, $$taggednumOfRecords, $$taggednumPages, $$taggedstart;

	$query = stripslashes($query);

	if (!empty($_REQUEST['pgnop' . $requestTag])) { //pagination
		$$taggednumPages = $_REQUEST['pgnop' . $requestTag];
		$$taggednumOfRecords = $_REQUEST['pgnor' . $requestTag];
	} else {
		if (!$debug) { $Result = draggin_query($query); }
		if (!$debug) { $$taggednumOfRecords = @draggin_num_rows($Result); }
		else { $$taggednumOfRecords = 500; }


		if ($$taggednumOfRecords > $$taggednumOfRecordsPerPage) {
			$$taggednumPages = ceil($$taggednumOfRecords/$$taggednumOfRecordsPerPage);
		} else {
			$$taggednumPages = 1;
		}

	}



	//where to start limit
	if (!empty($_REQUEST['pgs' . $requestTag])) { //pagenation
		$$taggedstart = $_REQUEST['pgs' . $requestTag];
	} else {
		$$taggedstart = 0;
	}



	//add the mod to the end of the query and then run it again
	if ($DRAGGIN['dbgen']['dbtype'] == "mysql") {
		//MYSQL
		$query .= " LIMIT " . $$taggedstart . ", " . $$taggednumOfRecordsPerPage . ";";

	} elseif ($DRAGGIN['dbgen']['dbtype'] == "mssql") {

		//MSSQL
		$recordCount = $$taggedstart / $$taggednumOfRecordsPerPage;
		$recordCount = $recordCount * $$taggednumOfRecordsPerPage;

		$bQuery = preg_split("/SELECT/", $query);
		$bufferQueryA = "SELECT TOP NUM_PER_PAGE_A " . $bQuery[1] . $bQuery[2] . $bQuery[3];

		$afterFROM = preg_split("/FROM/", $bufferQueryA);

		$tableSets = preg_split("/WHERE/", $afterFROM[1]);
		$tables = preg_split("/ /", trim(trim(trim($tableSets[0]))));

		if (strstr($tables[1], "AS")) {
			$setTables = preg_split("/ /", trim(trim(trim($tables[2]))));
			$tables[0] = $setTables[0];
			//$removeL = preg_split("/ /", trim(trim(trim($tables[0]))));
			if (strlen($tables[0]) > 1) {
				$tables[0] = substr($tables[0], 0, 1);
			}
		}

		$bufferQueryB = $tables[0] . ".itemID NOT IN (SELECT TOP NUM_PER_PAGE_A " . $tables[0] . ".itemID FROM " . $afterFROM[1] . ") AND ";

		$bufferQueryA = str_replace("NUM_PER_PAGE_A", $$taggednumOfRecordsPerPage, $bufferQueryA);
		$bufferQueryB = str_replace("NUM_PER_PAGE_A", $recordCount, $bufferQueryB);

		$bQuery = preg_split("/WHERE/", $bufferQueryA);

		$finalBuffer = $bQuery[0] . " WHERE " . $bufferQueryB . $bQuery[1] . $bQuery[2] . $bQuery[3];
		$bQuery = $finalBuffer;

		//
		//print debug($bQuery);
		$query = str_replace("SELECIT", "SELECT", $bQuery);
		$query = str_replace("FROIM", "FROM", $query);
		$query = str_replace("WHERIE", "WHERE", $query);

	}
	/*
	 SELECT TOP $records_per_page * FROM main
WHERE published = 1 and id NOT IN ( SELECT TOP $next_page id
FROM main ORDER BY id ASC )
ORDER BY id ASC
*/

	// $debug = true;

	//if($debug) { return debug($bQuery); }
	return draggin_query($query); // RETURN the $Result of the db query
}
/****** End Of PAGINATION RESULT ******/





/********************************************************************************************************
	End	Database Access	Section
********************************************************************************************************/

/********************************************************************************************************
	sec006	File Access	Section
********************************************************************************************************/
/*
ADMIN RSS FILE CREATOR
This fuction returns an RSS Feed.  It is designed to be dynamic so you make a function call and you
have a RSS Box. IMPORTANT NOTE: you must use "&amp;" instead of "&" in links.
*/
function resolutionRSS($fileNameArg, $descripArg, $tableArg, $linkArg,
	$headingArg, $contentArg, $dateArg, $docsArg = "http://www.resolutionim.com/rss/",
	$managingEditorArg = "jonathan@resolutionim.com",
	$webmasterArg = "webmaster@resolutionim.com", $specialOrder = false) 
{
	
	global $DRAGGIN, $_SERVER;

	if (!stristr($fileNameArg, ":/") && !stristr($fileNameArg, ":\\")) {
		$FileName = $DRAGGIN['settings']['docroot'] . $fileNameArg;
	} else {
		$FileName = $fileNameArg;
	}

	$FileName = str_replace("/", "\\", $FileName);

	$FilePointer = fopen($FileName, "w+"); //create new file, overwrite existing

	$titleArg = split(" - ", $descripArg);

	$rssInsert = "<?xml version=\"1.0\"?>\n" .
		"\t<rss version=\"2.0\">\n" .
		"\t\t<channel>\n" .
		"\t\t\t<title>" . $titleArg[0] . "</title>\n" .
		"\t\t\t<link>http://" . $_SERVER['SERVER_NAME'] . "</link>\n" .
		"\t\t\t<description>$descripArg</description>\n" .
		"\t\t\t<language>en-us</language>\n" .
		"\t\t\t<pubDate>" . date("r", strtotime("now")) . "</pubDate>\n" .
		"\t\t\t<lastBuildDate>" . date("r", strtotime("now")) . "</lastBuildDate>\n" .
		"\t\t\t<docs>$docsArg</docs>\n" .
		"\t\t\t<generator>Resolution Interactive Media Inc.</generator>\n" .
		"\t\t\t<managingEditor>$managingEditorArg</managingEditor>\n" .
		"\t\t\t<webMaster>$webmasterArg</webMaster>\n" .
		"\t\t\t<image>\n" .
		"\t\t\t\t<url>http://" . $_SERVER['HTTP_HOST'] . "/images/resolutionim.gif</url>\n" .
		"\t\t\t\t<title>" . $titleArg[0] . "</title>\n" .
		"\t\t\t\t<link>http://" . $_SERVER['HTTP_HOST'] . "/</link>\n" .
		"\t\t\t</image>\n";

	if (!$specialOrder) { $specialOrder = "$dateArg DESC"; }
	$Result = resultPlease("1", $tableArg, "", "sysOpen = '1' AND sysActive = '1'", $specialOrder);

	// $cmdy, $table, $customSelect = false, $customWhere = false, $customOrder = false, $debug = false

	if ($Result != false) {
		while ($HeadRS = draggin_fetch_array($Result, true)) {
			if (!isset($HeadRS[$dateArg])) { $HeadRS[$dateArg] = false; }
			if (!isset($HeadRS[$headingArg])) { $HeadRS[$headingArg] = false; }
			if (!isset($HeadRS[$contentArg])) { $HeadRS[$contentArg] = false; }
			if (!isset($HeadRS['itemID'])) { $HeadRS['itemID'] = false; }

			$pubDate = date("r", strtotime($HeadRS[$dateArg]));
			// print $pubDate;

			//cleanThis(content, stripHTML?, cleanUpMSWord?, runHTMLEntities?, skipOldSchoolStripHTMLStuff?)
			//$HeadRS[$headingArg] = cleanThis($HeadRS[$headingArg], true, true, true, true);
			//$HeadRS[$contentArg] = cleanThis($HeadRS[$contentArg], true, true, true, true);
			//   /*MagicQuotes = false update*/if(is_array($HeadRS)) { foreach($HeadRS as $k => $v ) { if($k != "isWYSIWYG") { $HeadRS[$k] = cleanThis($v, true, true, true, true); } $HeadRS[$k] = stripslashes($v); } }

			$rssInsert.= "\t\t\t<item>\n" .
				"\t\t\t\t<title> ". substr($HeadRS[$headingArg], 0, 100) ." </title>\n" .
				"\t\t\t\t<link> http://" . $_SERVER['HTTP_HOST'] . $linkArg . "=$HeadRS[itemID] </link>\n";
			$rssInsert.= "\t\t\t\t<description><![CDATA[<p>" . $HeadRS[$contentArg] . "</p>" .
				/*********************************************************************************************************************************************************************************************************************************************************************/
			"<p><a name=\"fb_share\" type=\"icon_link\" href=\"http://www.facebook.com/sharer.php\">Share On Facebook</a><script src=\"http://static.ak.fbcdn.net/connect.php/js/FB.Share\" type=\"text/javascript\"></script>" .
				"&nbsp;&nbsp;&nbsp;<a href=\"http://twitter.com/home?status=Currently reading http://" . $_SERVER['HTTP_HOST'] . "/SocialMedia.php?iArticle=" . $Recordset['itemID'] . "\" title=\"Share this post on Twitter\">Share On Twitter</a></p>" .
				/*********************************************************************************************************************************************************************************************************************************************************************/
			"]]></description>\n";
			//$rssInsert.= " <description> $HeadRS['articleContent'] </description>";
			$rssInsert.= "\t\t\t\t<pubDate> $pubDate </pubDate>\n" .
				"\t\t\t\t<guid> http://" . $_SERVER['HTTP_HOST'] . $linkArg . "=$HeadRS[itemID] </guid>\n" .
				"\t\t\t</item>\n";
		}
	}
	$rssInsert.= "\t\t</channel>\n\t</rss>";

	fwrite($FilePointer, $rssInsert);

	fclose($FilePointer);

	@chmod($FileName, 0644);
}
/****** End Of RSS FILE CREATOR ******/

/*
	Function createthumb($name,$filename,$new_w,$new_h)
	creates a resized image
	variables:
	$name		Original filename
	$filename	Filename of the resized image
	$new_w		width of resized image
	$new_h		height of resized image
*/	
function createthumb($name,$filename,$new_w,$new_h, $new_x = false, $new_y = false)
{
	$system=explode(".",$name);
	if (preg_match("/jpg|jpeg/",end($system))){$src_img=imagecreatefromjpeg($name);}
	if (preg_match("/png/",end($system))){$src_img=imagecreatefrompng($name);}
	
	//print debug("ONE -> W:" . $new_w . "  H:" . $new_h . "  X:" . $new_x . "  Y:" . $new_y);

	$dontSkipCorrections = true;
	$point_x = 0;
	$point_y = 0;
	$old_x=imageSX($src_img);
	$old_y=imageSY($src_img);
	
	
	//custom assignment of old_x and _y if new is set (auto crop)
	if($new_x || $new_y) { 
	
			$dontSkipCorrections = false;  
			
			//determine the percentage difference between the src graphic and the pageScale version to determine how much we have to scale the crop dimensions
			$modifier = addPrefixToFileNameInPath($name, 'pageScale');
			$modifierE=explode(".",$modifier);
			if (preg_match("/jpg|jpeg/",end($modifierE))){$page_img=imagecreatefromjpeg($modifier);}
			if (preg_match("/png/",end($modifierE))){$page_img=imagecreatefrompng($modifier);}
			
			//we only need one in this age
			$modifier_x=imageSX($page_img);

			$modifier = $old_x/$modifier_x;
			
	
			$old_x = ($new_w * $modifier);  $point_x = ($new_x * $modifier);  $thumb_w = ($new_w * $modifier); 			
			$old_y = ($new_h * $modifier);  $point_y = ($new_y * $modifier);  $thumb_h = ($new_h * $modifier);
			
			
	}
	
	
	//print debug("TWO -> OldX:" . $old_x . "  OldY:" . $old_y);

	
	//check to see if the params provided are larger than what we are working with, if so, set the image to it's original size
	/*
if($old_x < $new_w) { //WIDTH: 
		$new_w = $old_x;
	}
	
	if($old_y < $new_h) { //HEIGHT: if I'm already less than the limit passed in, just set me to the max of my source image
		$new_h = $old_y; 
		//but of course we must adjust the width now, because the scale will be off
		$new_h += $old_x - $new_x;
	}
*/
	//
	//print debug("W: " . $new_w . " H:" . $new_h);
	if($dontSkipCorrections) {
		if ($old_x > $old_y) { // if the width is greater than the height, set the width to what the user has provided, and the height to the height*(providedHeight/imageWidth) 
			$thumb_w=$new_w;
			$thumb_h=$old_y*($new_w/$old_x);
		}
		if ($old_x < $old_y) 
		{
			$thumb_w=$old_x*($new_h/$old_y);
			$thumb_h=$new_h;
		}
		if ($old_x == $old_y) 
		{
			$thumb_w=$new_w;
			$thumb_h=$new_h;
		}
	}
	$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
	//print debug("RESAMPLE: ->" . $dst_img . ", " . $src_img . ",0,0,0,0, " .$thumb_w . ", " .$thumb_h . ", " .$old_x . ", " .$old_y);
	imagecopyresampled($dst_img,$src_img,0,0,$point_x,$point_y,$thumb_w,$thumb_h,$old_x,$old_y); 
	if (preg_match("/png/",end($system)))
	{
		imagepng($dst_img,$filename); 
		setSystemLog("Generating thumbnail in DSTIMG:" . $dst_img . " FILENAME: ".$filename . "");
		$gotIt = true;
	} elseif(preg_match("/jpg/",end($system))) {
		imagejpeg($dst_img,$filename); 
		setSystemLog("Generating thumbnail in DSTIMG:" . $dst_img . " FILENAME: ".$filename . "");
		$gotIt = true;
	}
	imagedestroy($dst_img); 
	imagedestroy($src_img); 
	
	if($gotIt) {
		return true;
	} else {
		return false;
	}
	
	
}


/*
PHOTO UPLOADER W/ SCALING
This fuction returns the path to the image you uploaded.  It is designed to save an
uploaded GIF/JPG/PNG file to the specified location.
*/
function uploaderScalePhoto($WYName = "", $fileLocation, $height, $width, $isPDF = false) {

	global $DRAGGIN, $_REQUEST, $_SESSION, $_FILES;
	$isValid = true;
	$picDirGoodToGo = false;

	$ext = substr(strrchr($_FILES['uploadFile' . $WYName]['name'], "."), 1);
	$randName = substr($_FILES['uploadFile' . $WYName]['name'], 0, strpos($_FILES['uploadFile' . $WYName]['name'], "."));
	$randName = md5(rand(1, 999999999) . date("R"));
	$uploaddir = $DRAGGIN['settings']['docroot'] . $fileLocation;
	$uploadfile = $uploaddir . $randName . "." . $ext;
	$uploadType = $_FILES['uploadFile' . $WYName]['type'];
	if (($uploadType == "application/pdf") || ($uploadType == "application/msword") || ($uploadType == "application/rtf") || ($uploadType == "application/x-rtf") || ($uploadType == "text/richtext") ||
		($uploadType == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") || ($uploadType == "application/octet-stream") ||
		($uploadType == "image/gif") || ($uploadType == "image/png") || ($uploadType == "image/jpeg") || ($uploadType == "image/x-png") || ($uploadType == "image/pjpeg")  ||
		($uploadType == "application/x-shockwave-flash") || ($uploadType == "video/x-flv")
	) {
		move_uploaded_file($_FILES['uploadFile' . $WYName]['tmp_name'], $uploadfile);
		chmod($uploadfile, 0644);
		$picDirDirForDB = $fileLocation . $randName . "." . $ext;
		$picDirGoodToGo = true;
	} else {
		$isValid = false;
		$message .= "<li>Photo must be GIF/JPG/PNG and no greater then 30Mb in file size. </li>";
	}
	$picDirGoodToGo = true;
	if ($isValid) {
		if (!$isPDF || !($uploadType == "application/pdf" && $uploadType == "application/msword" && $uploadType == "application/rtf" && $uploadType == "application/x-rtf" && $uploadType == "text/richtext" &&
				$uploadType == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" && $uploadType == "application/octet-stream" &&
				$uploadType == "application/x-shockwave-flash" && $uploadType == "video/x-flv")
		) {
			resResizePhoto($uploadfile, $width, $height, $uploadfile);
		}
		return $picDirDirForDB;
	}
	else {
		//  return alertBox("Unable to Upload. Photo must be GIF/JPG/PNG and no greater then 30Mb in file size.", $validTriggerType);
		if ($uploadType != "application/pdf" &&  $uploadType != "application/msword" && $uploadType == "application/rtf" && $uploadType == "application/x-rtf" && $uploadType == "text/richtext" &&
			$uploadType == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" && $uploadType == "application/octet-stream" &&
			$uploadType == "application/x-shockwave-flash" && $uploadType == "video/x-flv") {
			return "ERROR|Unable to upload. Photo must be GIF/JPG/PNG and no greater then 30MB in file size.";
		} else {
			return "ERROR|Unable to upload. File must be a PDF/DOC/DOCX/RTF/SFW/FLV and no greater then 30MB in file size.";
		}
	}
}
/****** End Of PHOTO UPLOADER W/ SCALING ******/

/*
ADMIN XLS FILE CREATOR
This fuction returns an XLS file.  It is designed to be dynamic so you make a function call and you
have a RSS Box. IMPORTANT NOTE: you must use "&amp;" instead of "&" in links.
*/
function getXLSFile($fileToPut, $tblToGet, $whereToUse = "WHERE sysOpen = '1'", $selectToUse = "*", $orderToUse = false, $fullQuery = false) {

	global $DRAGGIN, $_REQUEST, $_SESSION, $_FILES;

	if ($_REQUEST['getterDone']) {

		if (!$fullQuery) {
			$getData = "SELECT $selectToUse FROM $tblToGet $whereToUse $OrderToUse";
		}
		else {
			$getData = $fullQuery;
		}

		$Result = draggin_query($getData, $DRAGGIN['dbgen']['link']);

		if ($Result != false) {
			//if(draggin_num_rows($Result)) {
			//generate excel XLS
			$FileName = $_SERVER['DOCUMENT_ROOT'] . $fileToPut;
			$FilePointer = fopen($FileName, "w+"); //create new file, overwrite existing



			$firstRun = true;

			while ($RS = draggin_fetch_array($Result)) {
				$userData = "";
				$headerData = "";


				//write header
				if ($firstRun) {
					$firstRun = false;
					$i=1;
					foreach ($RS as $headerItem => $value) {
						$i++;
						if ($i%2) {
							$headerData .= "$headerItem\t";
							$userData .= "$value\t";
						}
					}
					$headerData .= "\n";

					$writeMe = $headerData . $userData . "\n";
					//print "<br /> <br /> " . $writeMe;
					fwrite($FilePointer, $writeMe);
				}
				else {

					//write body
					$i=1;
					foreach ($RS as $headerItem => $value) {
						$i++;
						if ($i%2) {
							$userData .= "$value\t";
						}
					}
					$userData .= "\n";
					fwrite($FilePointer, $userData);
				}
			}

			fclose($FilePointer);


			$messageToSend = "<div style=\"padding-bottom:5px;\"><strong>Success</strong>, an excel XLS has been generated, you may download it " .
				"<a href=\"$fileToPut\"><strong>here</strong></a>.</div>";
			return alertBox($messageToSend, 1, "/images/icons/generateExcel.gif");
		}
	}
	else {
		return "<input type=\"button\" onclick=\"javascript:window.location.href = '" . $_SERVER['PHP_SELF'] . "?getterDone=1';\"
					value=\"Generate Excel List\" />";
	}
}
/****** End Of XLS FILE CREATOR ******/

/*
ADMIN XLS ON DEMAND CREATOR
This fuction returns an XLS file.  This function returns a header excel file.  it MUST BE CALLED before header and it will kill itself.
*/
function getXLSFileOnDemand($fullQuery, $fileName = "fileName", $ReturnLink = false) {

	global $DRAGGIN, $_REQUEST, $_SESSION, $_FILES;
	/*********************************************************************/

	if ($ReturnLink == false) {
		$ReturnLink = $_SERVER['PHP_SELF'] . "?gxlsod=" . md5("gxlsod");
	} elseif (stristr($ReturnLink, "?")) {
		$ReturnLink .= "&gxlsod=" . md5("gxlsod");
	} else {
		$ReturnLink .= "?gxlsod=" . md5("gxlsod");
	}

	if ($_REQUEST['gxlsod'] == md5("gxlsod")) {

		$Result = draggin_query($fullQuery, $DRAGGIN['dbgen']['link']);

		if ($Result != false) {
			//if(draggin_num_rows($Result)) {
			$firstRun = true;

			while ($RS = draggin_fetch_array($Result)) {
				$userData = "";
				$headerData = "";

				//write header
				if ($firstRun) {
					$firstRun = false;
					$i=1;
					foreach ($RS as $headerItem => $value) {
						$i++;
						if ($i%2) {
							$headerData .= "\t$headerItem";
							$userData .= "\t$value";
						}
					}

					$writeMe .= substr($headerData, 1) . "\n" . substr($userData, 1) . "\n";
					$headerData = ""; $userData = "";
				} else {
					$i=1;
					foreach ($RS as $headerItem => $value) {
						$i++;
						if ($i%2) {
							$userData .= "\t$value";
						}
					}
					$writeMe .= substr($userData, 1) . "\n";
					$headerData = ""; $userData = "";
				}

			}

			header("Content-Type: application/excel");
			header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
			print $writeMe;

			die();

			$messageToSend = "<div style=\"padding-bottom:5px;\"><strong>Success</strong>, an excel XLS has been generated, you may download it <a href=\"$fileToPut\"><strong>here</strong></a>.</div>";

			return alertBox($messageToSend, 1, "/images/icons/generateExcel.gif");
		}
	} else {
		return "<input type=\"button\" onclick=\"javascript:window.location.href = '" . $ReturnLink . "';\" value=\"Generate Excel List\" />";
	}
	/*********************************************************************/
}
/****** End Of XLS FILE CREATOR ******/


function XML2Array($xml,$recursive = false) {
 
    if (!$recursive ) { $array = simplexml_load_string ($xml); }
    else { $array = $xml ; }

    $newArray = array();
    $array = $array ;

    foreach ($array as $key => $value) {
        $value = (array) $value;

        if (isset($value[0])) { $newArray[$key] = trim($value[0]); }
        else { $newArray[$key][] = XML2Array($value,true) ; }
    }

    return $newArray;
}

//this version uses the built in DOM extension
function parseXMLDataIntoArray($data) {
		  $p = xml_parser_create();
		 
		  xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		  xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		 
		  xml_parse_into_struct($p, $data, $vals, $index);
		  xml_parser_free($p);
		
		  $levels = array(null);
		 
		  foreach ($vals as $val) {
			if ($val['type'] == 'sysOpen' || $val['type'] == 'complete') {
			  if (!array_key_exists($val['level'], $levels)) {
				$levels[$val['level']] = array();
			  }
			}
			
		   
			$prevLevel =& $levels[$val['level'] - 1]; 
			$parent = $prevLevel[sizeof($prevLevel)-1];
		   
			if ($val['type'] == 'sysOpen') {
			
			  $val['children'] = array();
			  array_push($levels[$val['level']], $val);
			  continue;
			
			} else if ($val['type'] == 'complete') {
			
			  $parent['children'][$val['tag']] = $val['value'];
			
			} else if ($val['type'] == 'close') {
			  
			  $pop = array_pop($levels[$val['level']]);
			  $tag = $pop['tag'];
			 
			 if ($parent) {
			
				if (!array_key_exists($tag, $parent['children'])) {
				 
				  $parent['children'][$tag] = $pop['children'];
			
				} else if (is_array($parent['children'][$tag])) {

					if(!isset($parent['children'][$tag][0])) {
						$oldSingle = $parent['children'][$tag];
						$parent['children'][$tag] = null;
						$parent['children'][$tag][] = $oldSingle;
					   
					}
					  $parent['children'][$tag][] = $pop['children'];
		        }
			  
			  } else {
				return(array($pop['tag'] => $pop['children']));
			  }
			}
		   
			$prevLevel[sizeof($prevLevel)-1] = $parent;
		  }
							
}

/********************************************************************************************************
	End	File Access	Section
********************************************************************************************************/


/********************************************************************************************************
	sec002	Text Manipulation	Section
********************************************************************************************************/






/****** End Of SUB ME ******/


/*
SWEAR FILTER
this will parse a file and compare to words in a file.
Then replace then with the the new string from the file
*/
function swearFilter($whatToFilter, $dictionaryFile = "/inc/swear.dict", $pattern = " - ") {

	global $DRAGGIN;
	$FileName = $DRAGGIN['settings']['docroot'] . $dictionaryFile;

	$toFilterArray = @split(" ", $whatToFilter);

	if (!file_exists($FileName)) {
		return "Cannot find swear dictionary.  Comment removed.  Please contact system administrator.";
	} else {

		$FilePointer = fopen($FileName, "r"); //create new file, overwrite existing


		$SwearFileContents = @fread($FilePointer, filesize($FileName));

		$SwearArray = split("\n", $SwearFileContents);

		foreach ($SwearArray as $swearK => $swearV) {
			$word = split($pattern, $swearV);

			foreach ($toFilterArray as $filterK => $filterV) {
				//   if(strtolower($filterV) == strtolower(addslashes(cleanThis($word[0], true)))) {
				if (@stristr($filterV, strtolower(addslashes(cleanThis($word[0], true)))) ) {
					$toFilterArray[$filterK] = addslashes(cleanThis($word[1], true));
				}
			}

		}

		// $toReturn = "";
		foreach ($toFilterArray as $returnK => $returnV) { $toReturn .= " " . $returnV; }

		fclose($FilePointer);

		return substr($toReturn, 1);
	}// } else { if(!file_exists($FileName) {

}
/****** End Of SWEAR FILTER ******/



/********************************************************************************************************
	END	Text Manipulation	Section
********************************************************************************************************/



/********************************************************************************************************
	sec003	Image Manipulation	Section
********************************************************************************************************/
/*
ADMIN PICTURE SELECTOR
looks for files in the given directory and presents them with specified params
developed specifically for resolution admin
*/
function resPictureSet($dir, $imgDir, $listWidth, $width, $height, $leadIn, $id, $fieldName = "hasPic") {

	global $alreadyPrintedSet;

	if (!$alreadyPrintedSet) {
		$printBuff = "

			<script language=\"javascript\" type=\"text/javascript\">
				function checkMyToggle(whoAmI) {
						if (document.getElementById(whoAmI).style.display == 'none') {
						document.getElementById(whoAmI).style.display = 'block';
						} else {
								document.getElementById(whoAmI).style.display = 'none';
						}
				}

			</script>

			";

		$alreadyPrintedSet = true;

	}

	$printBuff .= "<div id=\"picBox$id\" style=\"border: 1px solid #CCCCCC; display:none;\">";

	// sysOpen a known directory, and proceed to read its contents
	$i=0;

	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			$printBuff .=  "<table cellspacing=\"4\ cellpadding=\"0\"> <tr>";
			while (($file = readdir($dh)) !== false && $i <= 7) {


				if ($file != "." && $file != ".." && (substr($file, 0, 1) != ".")) {
					$i++;
					$printBuff .= " <td align=\"center\" style=\"border: 1px solid #CCCCCC;\">
					 <div align=\"center\" style=\"margin:0px; width:100%; background-color:#EAEAEA;\">  <input type=\"radio\" id=\"checkbox_". $id. "_pic_" . $i . "\" name=\"$fieldName\" value=\"$imgDir" .  "$file\" /> </div>
					 ";



					$printBuff .= scalePic($width, $height, $imgDir .  $file, "Image", "border:1px solid #7A7A7A;", "onmousedown=\"document.getElementById('checkbox_". $id. "_pic_" . $i . "').checked = true;\"");





					$printBuff .= "</td> ";

					if ($i%$listWidth==0) {
						$printBuff .= " </tr> <tr> ";
					}
				}



			}

			if ($i < $listWidth) {
				while ($i < $listWidth) {
					$i++;
					$printBuff .= " <td> &nbsp; </td> ";
				}
				$printBuff .= " </tr> ";
			}

			$printBuff .= " </table> </div>";

			closedir($dh);
			return "<a href=\"javascript:checkMyToggle('picBox$id');\">$leadIn</a> <br /> <br />" .  $printBuff;
		}
	}
}
/****** End Of ADMIN PICTURE SELECTOR ******/

/*
SCALE PIC
takes a user provided picture and scales it so that it fits within a provided dimension
to prevent large pictures from breaking layouts
*/
function scalePic($maxH, $maxW, $picToCheck, $altTag = '', $styles ='', $extraItems = '') {

	global $DRAGGIN;

	if (!empty($picToCheck)) {

		//get the size of this image and stick in in the array $imgSpecs
		$imgSpecs = @getimagesize($DRAGGIN['settings']['docroot'] . $picToCheck);

		//set starting w/h
		$imgW = $imgSpecs[0];
		$imgH = $imgSpecs[1];

		$sW = $imgSpecs[0];
		$sH = $imgSpecs[1];


		if ($imgW > $maxW) {
			//scale down this image in w untill it fits in width
			while ($imgW > $maxW) {
				$imgW--;
			}

			//get percentage difference
			$imgH = $imgH * ($imgW / $sW);
			$sH = $imgH; // <--- note the re-assignment used later in 'B"
		}

		if ($imgH > $maxH) {
			//scale down this image in h just in case it is still to long in height
			while ($imgH > $maxH) {
				$imgH--;
			}

			//get percentage difference
			$imgW = $imgW * ($imgH / $sH);  // <--- B ($sH)
		}

	} else {
		//no user picture supplied, spit out default

		//      return "NO PICTURE";
		return  "<img $extraItems style=\"$styles\" " .
			"src=\"/images/blankLocation.gif\" alt=\"NO PICTURE\" " .
			"width=\"$maxW\" height=\"$maxH\" />";

	}

	return  "<img $extraItems style=\"$styles\" src=\"$picToCheck\" alt=\"$altTag\" width=\"$imgW\" height=\"$imgH\" />";


}
/****** End Of SCALE PIC ******/

/*
RESIZE PIC
takes a user provided picture and resizes it so that it fits within a provided dimension
proportionatly to prevent large pictures from breaking layouts
*/
function resResizePhoto($FileName, $MaxWidth, $MaxHeight, $SourceFileName = false) {

	global $DRAGGIN;
	/****vv Thumbnail vv****/

	if (!$DRAGGIN['settings']['gdlibrarysupport']) { return false; }
	// The file
	if ($SourceFileName) {
		@copy($SourceFileName, $FileName);
		@chmod($FileName, 0644);
	}
	switch (substr($FileName, (strlen($FileName) - 3))) {
	case "jpg":
		$source = @imagecreatefromjpeg($FileName);
		break;
	case "gif":
		$source = @imagecreatefromgif($FileName);
		break;
	case "png":
		$source = @imagecreatefrompng($FileName);
		break;
	default:
		$source = @imagecreatefromjpeg($FileName);
		break;
	}


	$width = @imagesx($source);
	$height = @imagesy($source);

	$imgW = @imagesx($source);
	$imgH = @imagesy($source);

	$sW = @imagesx($source);
	$sH = @imagesy($source);

	if ($imgW > $MaxWidth) {
		//scale down this image in w untill it fits in width
		while ($imgW > $MaxWidth) {
			$imgW--;
		}

		//get percentage difference
		$imgH = $imgH * ($imgW / $sW);
		$sH = $imgH; // <--- note the re-assignment used later in 'B"
	}

	if ($imgH > $MaxHeight) {
		//scale down this image in h just in case it is still to long in height
		while ($imgH > $MaxHeight) {
			$imgH--;
		}

		//get percentage difference
		$imgW = $imgW * ($imgH / $sH);  // <--- B ($sH)
	}

	$imgW = round($imgW);
	$imgH = round($imgH);
	// Load
	$thumb = @imagecreatetruecolor($imgW, $imgH);
	// Resize
	@imagecopyresampled($thumb, $source, 0, 0, 0, 0, $imgW, $imgH, $width, $height);


	// Output
	switch (substr($FileName, (strlen($FileName) - 3))) {
	case "jpg":
		@imagejpeg($thumb, $FileName, 100);
		break;
	case "gif":
		@imagegif($thumb, $FileName);
		break;
	case "png":
		@imagepng($thumb, $FileName);
		break;
	default:
		@imagejpeg($thumb, $FileName, 100);
		break;
	}
	@imagedestroy($source);
	@imagedestroy($thumb);

	/****^^ Thumbnail ^^****/
}
/****** End Of RESIZE PIC ******/

/********************************************************************************************************
	END	Image Manipulation	Section
********************************************************************************************************/

/********************************************************************************************************
	sec004	Display	Section
********************************************************************************************************/


/****** End Of ALERT BOX ******/

/*/****** End Of GIVE ME A BOX ******/

/*
PAGINATION LIST
what number list should the system display
*/

function resReturnMeAPageList($numOfRecords, $numPages, $start, $numOfRecordsPerPage, $sendQueryCap = true, $noLabel = false , $requestTag = "") {

	global $_POST, $_GET, $_REQUEST;

	$queryCap = "";

	if ($sendQueryCap) {
		/*****************************************************
			foreach($_POST as $key => $value) {
				if(substr($key,0,2) == "RQ" && substr($key,0,2) == "OP" &&
				$key == "hasFileDir" && $key == "MAX_FILE_SIZE" && $key == "hasPicDir" &&
				($key != "pgnop" . $requestTag) && ($key != "pgs" . $requestTag)  &&
				($key != "pgnor" . $requestTag)) {
				$queryCap .= "&" . $key . "=" . $value;
				}
			}

			foreach($_GET as $key => $value) {
				if(substr($key,0,2) == "RQ" && substr($key,0,2) == "OP" &&
				$key == "hasFileDir" && $key == "MAX_FILE_SIZE" && $key == "hasPicDir" &&
				($key != "pgnop" . $requestTag) && ($key != "pgs" . $requestTag)  &&
				($key != "pgnor" . $requestTag)) {
				$queryCap .= "&" . $key . "=" . $value;
				}
			}
 *****************************************************/
		foreach ($_REQUEST as $key => $value) {
			if (substr($key, 0, 2) != "RQ" && substr($key, 0, 2) != "OP" && $key != "hasFileDir" && $key != "MAX_FILE_SIZE" && $key != "hasPicDir" &&
				$key != "__utmz" && $key != "__utma" && $key != "PHPSESSID" &&
				$key != "pgnop" . $requestTag && $key != "pgs" . $requestTag && $key != "pgnor" . $requestTag) {
				$queryCap .= "&" . $key . "=" . $value;
			}
		}
	}
	if ($numPages > 1) {
		$firstDisplay = ($start < 1) ? '1' : $start;
		if (($firstDisplay + $numOfRecordsPerPage) > $numOfRecords) { $numOfRecordsShowingDisplay = ($numOfRecords - $start); } else { $numOfRecordsShowingDisplay = $numOfRecordsPerPage; }
		if (!$noLabel) { $forReturn = "<p> <strong>Showing:</strong> $firstDisplay to " . ($start + $numOfRecordsShowingDisplay) . " of $numOfRecords <br />"; }
		$currentPage = ($start/$numOfRecordsPerPage) + 1;

		//if this is not the first page then create a previous button
		if ($currentPage != 1) {
			$forReturn .= " <a href=\"/?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". ($start - $numOfRecordsPerPage)."&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">&laquo; Prev</a> ";
		}

		//generate numbered page links
		for ($i=1; $i<= $numPages; $i++) {
			if ($i != $currentPage) {
				$forReturn .= " <a href=\"/?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". (($numOfRecordsPerPage) * ($i - 1)) . "&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">$i</a> ";
			} else {
				$forReturn .= "<strong>" . $i . "</strong>";
			}
		}

		//if this is not the last page create a next button
		if ($currentPage != $numPages) {
			$forReturn .= " <a href=\"/?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". ($start + $numOfRecordsPerPage)."&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">Next &raquo;</a> ";
		}

		if (!$noLabel) { $forReturn .= "</p> <br />"; }
		return $forReturn;
	}

}
/****** End Of PAGINATION LIST ******/

/*
GET COMMENTS
returns a Comment tool.  Used for Blogs,Photos,and Videos
*/
function getComments($app, $appItem, $parent = false, $showReply = false, $replyingTo = false, $artReqVar = false) {

	global $DRAGGIN, $message, $_REQUEST, $_POST;


	$randIdentifier  = md5(rand(1, 10) . $app . $appItem);
	$formReturn   = "";
	$whatToReturn   = "";

	/********** vv Post Form vv **********/
	if (!$parent) {
		/********** vv Comment Postback vv **********/

		if ($_POST['pb' . $app . $appItem] == "commentpost") {
			$isValid = validateForm();
			/********** vv MagicQuotes = false update vv **********/
			foreach ($_REQUEST as $k => $v) {
				$_REQUEST[$k] = ereg_replace("<script(.+)</script>", "", $_REQUEST[$k]); $_REQUEST[$k] = ereg_replace("&lt;script(.+)&lt;/script&gt;", "", $_REQUEST[$k]);
				if ($k != "isWYSIWYG") { $_REQUEST[$k] = cleanThis($v, true); } $_REQUEST[$k] = addslashes($_REQUEST[$k]);
			}
			foreach ($_POST as $k => $v) {
				$_POST[$k] = ereg_replace("<script(.+)script>", "", $_POST[$k]); $_POST[$k] = ereg_replace("&lt;script(.+)script&gt;", "", $_POST[$k]);
				if ($k != "isWYSIWYG") { $_POST[$k] = cleanThis($v, true); } $_POST[$k] = addslashes($_POST[$k]);
			}
			/********** ^^ MagicQuotes = false update ^^ **********/

			/****vv Internal Validation vv****/
			$getIV = "SELECT validCode FROM sysTBLCaptchaPhotos WHERE sysOpen = '1' AND sysActive = '1' AND picDir = '$_POST[RQCID]';";
			$ivResult = draggin_query($getIV);

			if ($Result != false) {
				//if(draggin_num_rows($ivResult)) {
				$ivRS = draggin_fetch_array($ivResult);
				if (strtolower($ivRS['validCode']) != strtolower($_POST['RQImage_Code'])) {
					$isValid = false;
					$message .= "<li><strong>Image Validation Failed</strong>, are you sure you&#039;re human? Please re-enter the text you see in the image.</li>";
				}
			} else {
				$isValid = false;
				$message .= "<li><strong>Image Validation Failed</strong>, are you sure you&#039;re human? Please re-enter the text you see in the image.</li>";
			}
			if (strlen($_POST['RQComment']) > 1000) {
				$isValid = false;
				$message .= "<li><strong>Your Comment</strong>, is too long.&nbsp; Please shorten it to less then 1000 characters.</li>";
			}
			/****^^ Internal Validation ^^****/

			if ($isValid) {
				if (!$_POST['OPParentID' . $randIdentifier]) {
					$setParent = "NULL";
				} else {
					$setParent = "'" . $_POST['OPParentID' . $randIdentifier] . "'";
				}

				$_POST['RQvalALPHName']    = swearFilter($_POST['RQvalALPHName'], "/fileBin/swear.dict");
				$_POST['RQvalMAILEmail_Address']  = swearFilter($_POST['RQvalMAILEmail_Address'], "/fileBin/swear.dict");
				$_POST['RQComment']     = swearFilter($_POST['RQComment'], "/fileBin/swear.dict");

				$setMeIn = "INSERT INTO sysTBLComments (parentID, appID, appItemID, dateSubmitted, timeSubmitted, visitor, email, comment, sysActive, sysOpen) VALUES
				(
					'" . $setParent . "', '" . $app . "', '" . $appItem . "',
					" . $DRAGGIN['dbabs']['now'] . ", " . $DRAGGIN['dbabs']['now'] . ",
					'$_POST[RQvalALPHName]', '$_POST[RQvalMAILEmail_Address]', '$_POST[RQComment]',
					'1', '1'
				);" . $DRAGGIN['dbabs']['lastinsert'];
				draggin_query($setMeIn);
				////////////print "<div class=\"clearBox\" style=\"height:150px;\">" . debug($setMeIn) . "</div>";
				$myValiddescrip = "<strong>Thanks!</strong> Your comment has been added.";
				$validTriggerType = 1;

				$_POST['RQvalMAILEmail_Address'] = "";
				$_POST['RQvalALPHName'] = "";
				$_POST['RQComment'] = "";

				$jsToRun = "";

				$blankThem = false;
				/*Clear*/ foreach ($_POST as $key => $value) { if (substr($key, 0, 2) == "pb") { $blankThem = true; }}
				if ($blankThem) {
					/*Clear*/ foreach ($_POST as $key => $value) { if (substr($key, 0, 2) == "OP" || substr($key, 0, 2) == "RQ") { $_POST[$key] = ""; }}
				}

			} else {
				$myValiddescrip = "<strong>Whoops</strong>, your comment could not be added! Here's why:<ul>$message</ul>";
				$validTriggerType = 2;

				$jsToRun = "<script language=\"javascript\" type=\"text/javascript\">document.getElementById('getCommentFormBox" . $randIdentifier . "').style.display = 'block'; document.getElementById('hideCommentPostBox" . $randIdentifier . "').style.display = 'none';</script>";
			}

			$formReturn .= "<a name=\"newpost\"></a>" . alertBox($myValiddescrip,  $validTriggerType);

		} elseif (!empty($_REQUEST['cid']) && is_numeric($_REQUEST['cid'])) { //if($_POST['pb' . $app . $appItem] == "commentpost") {
			$reportResult = draggin_query($reportQuery = "UPDATE	sysTBLComments SET sysActive = '3' WHERE itemID = '" . $_REQUEST['cid'] . "';");
			////////print debug($reportQuery);
			if ($reportResult) {// && draggin_num_rows($reportResult) > 0) {
				$myValiddescrip = "<strong>Thanks!</strong> Your report of this comment will be reviewed.";
				$validTriggerType = 1;
			} else {//if($reportResult) {
				$myValiddescrip = "<strong>Whoops</strong>, your report of this comment could not be made!";
				$validTriggerType = 2;
			}//if($reportResult) {

			$formReturn .= "<a name=\"newpost\"></a>" . alertBox($myValiddescrip,  $validTriggerType);

		} //if($_POST['pb' . $app . $appItem] == "commentpost") {

		/********** ^^ Comment Postback ^^ **********/


		/********** vv Comment Captcha vv **********/
		$getImageValidation = "SELECT itemID, picDir FROM sysTBLCaptchaPhotos WHERE sysOpen = '1' AND sysActive = '1'
		ORDER BY RAND() LIMIT 0, 1;";
		//print debug($getImageValidation);
		$imageValidationResult = draggin_query($getImageValidation);
		$ivRS = draggin_fetch_array($imageValidationResult);
		/********** ^^ Comment Captcha ^^ **********/


		$formReturn .= "<div id=\"hideCommentPostBox" . $randIdentifier . "\">" .
			"<input type=\"button\" class=\"prettyButton\" name=\"hideCommentPost\" id=\"hideCommentPost\" style=\"margin:0px 0px 10px 0px;\" value=\"Post A Comment\" " .
			"onclick=\"document.getElementById('hideCommentPostBox" . $randIdentifier . "').style.display = 'none'; " .
			"document.getElementById('getCommentFormBox" . $randIdentifier . "').style.display = 'block'; ";
		if ($replyingTo) { $formReturn .= "document.getElementById('commentOn" . $randIdentifier . "').innerHTML = '<label>Reply To:</label> " . stripslashes(cleanthis($replyingTo, true)) . "'; "; }
		$formReturn .= "document.getElementById('getCommentFormBox" . $randIdentifier . "').style.display = 'block';\"></div>" .
			"<div id=\"getCommentFormBox" . $randIdentifier . "\" style=\"display:none;\">";

		if ($replyingTo) { $formReturn .= "<div id=\"commentOn" . $randIdentifier . "\"><label>Reply To:</label> " . $replyingTo . "</div>"; }
		/*MagicQuotes = false update*/if (is_array($_POST)) { foreach ($_POST as $k => $v ) { if ($k != "isWYSIWYG") { $_POST[$k] = cleanThis($v, true, true, true, true); } $_POST[$k] = stripslashes($v); } }
		$formReturn .=
			"<form enctype=\"multipart/form-data\" name=\"getCommentForm\" id=\"getCommentForm\" method=\"post\" action=\"" . $_SERVER['REQUEST_URI'] . "#newpost\"><dl>" .

			"<dt>Your Name:</dt>" .
			"<dd><input type=\"text\" class=\"specialInputField\" style=\"width:90%;\" id=\"RQvalALPHName\" name=\"RQvalALPHName\" value=\"" . htmlentities($_POST['RQvalALPHName']) . "\" /></dd>" .

			"<dt>Your Email Address:</dt>" .
			"<dd><input type=\"text\" class=\"specialInputField\" style=\"width:90%;\" id=\"RQvalMAILEmail_Address\" name=\"RQvalMAILEmail_Address\" value=\"" . htmlentities($_POST['RQvalMAILEmail_Address']) . "\" /></dd>" .

			"<dt>Your Comment: - Max input: 1000 characters.&nbsp; </dt>" .
			"<dd><textarea style=\"width:90%; height:60px;\" class=\"specialInputField\" id=\"RQComment\" name=\"RQComment\">" . cleanThis(nl2br($_POST['RQComment']), true) . "</textarea></dd>" .

			/********** vv Comment Captcha vv **********/
		"<dt class=\"catcha\"><strong>Are you human?</strong> Enter the text you see in the image</dt>" .
			"<dd class=\"catcha\">
				<img src=\"" . $ivRS['picDir'] . "\" name=\"imageCaptcha\" id=\"imageCaptcha\" alt=\"Are you human? Enter the text you see in the image\" />
				<input type=\"hidden\" name=\"RQCID\" value=\"" . $ivRS['picDir'] . "\" />
			</dd>" .

			"<dt class=\"catcha\">Enter Code</dt>" .
			"<dd class=\"catcha\"><input style=\"width:80px;\"  type=\"text\" name=\"RQImage_Code\" id=\"RQImage_Code\" value=\"\" /><dd>" .
			/********** ^^ Comment Captcha ^^ **********/

		"<dd class=\"formButtons\"><input type=\"hidden\" name=\"pb" . $app . $appItem . "\" id=\"pb" . $app . $appItem . "\" value=\"commentpost\" />
		<input type=\"hidden\" name=\"OPParentID" . $randIdentifier . "\" id=\"OPParentID" . $randIdentifier . "\" value=\"\" />";

		foreach ($_POST as $k => $v) {
			if (
				$k != "PHPSESSID" && $k != "interactivePoll" && substr($k, 0, 2) != "RQ" && substr($k, 0, 2) != "OP" && !strchr($k, "_") &&
				$k != "pb" && $k != "pb" && $k != "cf" && $k != "logMeIn" && !strpos($k, "submit")
			) {

				$formReturn .= "\n<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
			}
		}
		foreach ($_REQUEST as $k => $v) {
			if (
				$k != "PHPSESSID" && $k != "interactivePoll" &&  substr($k, 0, 2) != "RQ" && substr($k, 0, 2) != "OP" && !strchr($k, "_") &&
				$k != "pb" && $k != "pb" && $k != "cf" && $k != "logMeIn" && !strpos($k, "submit")
			) {

				$formReturn .= "\n<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
			}
		}

		$formReturn .= "
		<input type=\"submit\" name=\"submitBlogPost\" id=\"submitBlogPost\"  class=\"prettyButton\" value=\"Post Your Comment!\" />
		<input type=\"button\" name=\"cancelCommentPost\" id=\"cancelCommentPost\" class=\"prettyButton\"  value=\"Cancel\" onclick=\"document.getElementById('getCommentFormBox" . $randIdentifier . "').style.display = 'none'; " .
			"document.getElementById('hideCommentPostBox" . $randIdentifier . "').style.display = 'block';\"></dd>
		</dl>
		</form></div>
		<div class=\"clear\">&nbsp;</div>";
	} //if(!$parent) {
	/********** ^^ Post Form ^^ **********/

	if ($parent) {
		$myParent = "AND parentID = '$parent'";
	} else {
		$myParent = "AND (parentID IS NULL OR parentID = '')";
	}

	$NewsItemQuery = "SELECT * FROM sysTBLComments WHERE appID = '$app' AND (appItemID = '$appItem' AND (appItemID != '' OR appItemID != '0')) $myParent AND sysOpen = '1' AND sysActive != '3';";
	// $whatToReturn .= "<div class=\"clearBox\" style=\"height:150px;\">" . debug($NewsItemQuery) . "</div>";
	$Result = draggin_query($NewsItemQuery);

	if ($Result != false) {
		//if(@draggin_num_rows($Result) > 0) {
		while ($Recordset = draggin_fetch_array($Result)) {
			/*MagicQuotes = false update*/if (is_array($Recordset)) { foreach ($Recordset as $k => $v ) { if ($k != "isWYSIWYG") { $Recordset[$k] = cleanThis($v, true, true, true, true); } $Recordset[$k] = stripslashes($v); } }
			$whatToReturn .= "<div class=\"getCommentBox\">";

			if ($showReply) {
				$whatToReturn .=
					"<div class=\"replayCommentPost\">" .
					"<input type=\"button\" name=\"replayCommentPost_$Recordset[itemID]\" id=\"replayCommentPost_$Recordset[itemID]\" value=\"Reply to this Comment\" " .
					"onclick=\"document.getElementById('hideCommentPostBox" . $randIdentifier . "').style.display = 'none'; " .
					"document.getElementById('getCommentFormBox" . $randIdentifier . "').style.display = 'block'; " .
					"document.getElementById('commentOn" . $randIdentifier . "').innerHTML = '<label>Reply To:</label> " . stripslashes(cleanthis($Recordset['visitor'], true)) . "'; " .
					"document.getElementById('OPParentID" . $randIdentifier . "').value='$Recordset[itemID]';\" /></div>";
			}
			$whatToReturn .= "<a class=\"anchorDrag\" name=\"com" . $Recordset['itemID'] . "\" >&nbsp;</a>";


			$whatToReturn .=
				"<h4 class=\"getCommentsVisitor\">" . stripslashes(cleanthis($Recordset['visitor'], true)) . "</h4>" .
				"\n<span class=\"getCommentsDate\">" . date("l, F jS, Y", strtotime($Recordset['dateSubmitted'])) . "\n</span>" .
				"<div class=\"getCommentsComment\">" . stripslashes($Recordset['comment']) . "</div>";

			$addComments = getComments($app, $Recordset['appItemID'], $Recordset['itemID']);
			if ($addComments) { $whatToReturn .= $addComments; }

			if ($Recordset['sysActive'] != "2") {
				if ($artReqVar) { $SendMeTo = "&" . $artReqVar . "=" . $appItem; } else { $SendMeTo = ""; }
				$whatToReturn .= "<a href=\"/?p=" . $_REQUEST['p'] . "&cid=" . $Recordset['itemID'] . $SendMeTo . "\" class=\"getCommentsReport\">Report This Comment</a>";
			} else {
				$whatToReturn .= "<span class=\"getCommentsReport\">Reported As Ok</span>";
			}
			$whatToReturn .= "</div>";
		}


	} else {

		if ($_REQUEST['action'] != 3 && $Result != false) {
			$whatToReturn = alertBox("<strong> Sorry</strong>, invalid comments.", 2, "", "pubComments");
		} elseif ($Result === false) {
			$whatToReturn = alertBox("No posts in this thread.", 4, "", "pubComments");
		}

		if ($parent) {
			return false;
		}
	}

	return "<div class=\"getComments\">" . $formReturn . $whatToReturn . $jsToRun . "</div>";

}
/****** End Of GET COMMENTS ******/


/********************************************************************************************************
	End	Display	Section
********************************************************************************************************/

/********************************************************************************************************
	sec005	List Creation	Section
********************************************************************************************************/


/*
GET DATE LIST
Returns a year month day group of select lists
WARNING:  IF you copy this code back to an old project you will Break it!!!!!
DO NOT MOVE TO OLD PROJECTS!!!!
 */
function getDateList($selectName, $startYear = 0, $endYear = 70, $selectedVal = "", $returnNums = false, $donTShowDay = false, $extraSelectParam = false) {

	$whatToReturn = "
	<script language=\"javascript\" type=\"text/javascript\">
		function changeDay$selectName()
		{ ";
	if (!$donTShowDay) {
		$whatToReturn .=
			"switch(document.getElementById('itemMonth$selectName').selectedIndex)
			{
				case 0: //jan
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 1: //feb
				document.getElementById('itemDay$selectName').options[30].style.display = 'none';
				document.getElementById('itemDay$selectName').options[29].style.display = 'none';
				document.getElementById('itemDay$selectName').options[28].style.display = 'none';
				break;
				case 2: //mar
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 3: //apr
				document.getElementById('itemDay$selectName').options[30].style.display = 'none';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 4: //may
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 5: //june
				document.getElementById('itemDay$selectName').options[30].style.display = 'none';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 6: //july
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 7: //aug
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 8: //sept
				document.getElementById('itemDay$selectName').options[30].style.display = 'none';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 9: //oct
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 10: //nov
				document.getElementById('itemDay$selectName').options[30].style.display = 'none';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;
				case 11: //dec
				document.getElementById('itemDay$selectName').options[30].style.display = 'block';
				document.getElementById('itemDay$selectName').options[29].style.display = 'block';
				document.getElementById('itemDay$selectName').options[28].style.display = 'block';
				break;

			} ";
	}
	$whatToReturn .=
		"
		}
		function updateHidden$selectName() {
			var year = document.getElementById('itemYear$selectName')." .
		"options[document.getElementById('itemYear$selectName').selectedIndex].value;
			var month = document.getElementById('itemMonth$selectName')." .
		"options[document.getElementById('itemMonth$selectName').selectedIndex].value; ";
	if (!$donTShowDay) {
		$whatToReturn .=
			"var day = document.getElementById('itemDay$selectName')." .
			"options[document.getElementById('itemDay$selectName').selectedIndex].value; ";
	} else {
		$whatToReturn .=
			"var day = '01';";
	}
	$whatToReturn .=
		"document.getElementById('$selectName').value = year + '-' + month + '-' + day;
		}
	</script>";


	$whatToReturn .= " <select name=\"itemYear$selectName\" id=\"itemYear$selectName\"
			onchange=\"javascript:updateHidden$selectName();\" " . $extraSelectParam . "> ";
	for ($countYear = date("Y", strtotime("now")) + $startYear; $countYear >= date("Y", strtotime("now")) - $endYear;$countYear--) {
		if (!empty($selectedVal) && $countYear == date("Y", strtotime($selectedVal))) {
			$amISelected = "selected=\"selected\"";
			$b = "*";
		}
		elseif (empty($selectedVal) && $countYear == date("Y", strtotime("now"))) {
			$amISelected = "selected=\"selected\"";
			$b = "*";
		}
		else {
			$amISelected = "";
			$b = "";
		}

		$whatToReturn .= "<option value=\"$countYear\" $amISelected> $countYear $b </option>";
	}

	$whatToReturn .= " </select>";

	$whatToReturn .= " <select name=\"itemMonth$selectName\" id=\"itemMonth$selectName\"
								onchange=\"javascript:changeDay$selectName(); javascript:updateHidden$selectName();\" " . $extraSelectParam . "> ";
	for ($countMonth = 1; $countMonth <= 12;$countMonth++) {
		$displayMonth = mktime(0, 0, 0, $countMonth, 1, date("Y"));

		if ($returnNums) {
			if (!empty($selectedVal)) {
				if ($countMonth == date("m", strtotime($selectedVal))) {
					$selectedCheck = date("m", strtotime($selectedVal));
					$returnValue = date("m", $displayMonth);
				} else {
					$selectedCheck = date("m", strtotime($selectedVal));
					$returnValue = date("m", $displayMonth);
				}
			} else {
				if ($countMonth == date("m")) {
					$selectedCheck = date("m");
					$returnValue = date("m", $displayMonth);
				} else {
					$selectedCheck = date("m");
					$returnValue = date("m", $displayMonth);
				}
			}
			if (date("m", $displayMonth) == $selectedCheck) {
				$amISelected = "selected=\"selected\"";
				$b = "*";
			}
			else {
				$amISelected = "";
				$b = "";
			}

		} else {
			$selectedCheck = date("m");
			$returnValue = date("m", $displayMonth);
			if (date("m", $displayMonth) == $selectedCheck) {
				$amISelected = "selected=\"selected\"";
				$b = "*";
			}
			else {
				$amISelected = "";
				$b = "";
			}
		}


		$whatToReturn .= "<option value=\"". $returnValue . "\" $amISelected> " .
			date("M", $displayMonth) . " $b </option>";
	}

	$whatToReturn .= " </select>";
	if (!$donTShowDay) {
		$whatToReturn .= " <select name=\"itemDay$selectName\" id=\"itemDay$selectName\"
			onchange=\"javascript:updateHidden$selectName();\" " . $extraSelectParam . "> ";
		for ($countDay = 1; $countDay <= 31;$countDay++) {
			$displayDay = mktime(0, 0, 0, 1, $countDay, date("Y"));

			if ($returnNums) {
				if (!empty($selectedVal)) {
					if ($countDay == date("d", strtotime($selectedVal))) {
						$selectedCheck = date("d", strtotime($selectedVal));
						$returnValue = date("d", $displayDay);
					} else {
						$selectedCheck = date("d", strtotime($selectedVal));
						$returnValue = date("d", $displayDay);
					}
				} else {
					if ($countDay == date("d")) {
						$selectedCheck = date("d");
						$returnValue = date("d", $displayDay);
					} else {
						$selectedCheck = date("d");
						$returnValue = date("d", $displayDay);
					}
				}

				if ($returnValue == $selectedCheck) {
					$amISelected = "selected=\"selected\"";
					$b = "*";
				}
				else {
					$amISelected = "";
					$b = "";
				}
			} else {
				$selectedCheck = date("j");
				$returnValue = date("j", $displayDay);

				if ($countDay == $selectedCheck) {
					$amISelected = "selected=\"selected\"";
					$b = "*";
				}
				else {
					$amISelected = "";
					$b = "";
				}
			}


			$whatToReturn .= "<option value=\"" . $returnValue . "\" $amISelected> $countDay $b </option>";
		}

		$whatToReturn .= " </select>";
	}
	if (empty($selectedVal)) { $selectedVal = date("Y-m-d"); }
	else { $selectedVal = date("Y-m-d", strtotime($selectedVal)); }

	$whatToReturn .= "
		<input type=\"hidden\" name=\"$selectName\" id=\"$selectName\" value=\"\" />
		<script language=\"javascript\" type=\"text/javascript\"> updateHidden$selectName(); </script>";


	return $whatToReturn;
}
/****** End Of GET DATE LIST ******/

/*
GET TIME LIST
Returns a hour:minute AM/PM or hour:minute:second group of select lists depending if twoFourTime is false or true
 */
function getTimeList($selectName, $selectedVal, $minGap, $twoFourTime = false) {
	$whatToReturn = "
	<script language=\"javascript\" type=\"text/javascript\">
function updateTimeHidden$selectName(selectName) {
	var hour = document.getElementById('itemHour'+selectName).options[document.getElementById('itemHour'+selectName).selectedIndex].value;
	var minute = document.getElementById('itemMinute'+selectName).options[document.getElementById('itemMinute'+selectName).selectedIndex].value;

	try { var second = document.getElementById('itemSecond'+selectName).options[document.getElementById('itemSecond'+selectName).selectedIndex].value; }
	catch(e) { var second = 00; }

	try { var ap = document.getElementById('itemAMPM'+selectName).options[document.getElementById('itemAMPM'+selectName).selectedIndex].value; }
	catch(e) { var ap = 1; }

	try {
		if(ap == 2 && hour == 12) { hour = 00; }
		else if(ap == 2) { hour = 12 + Number(hour); }
	} catch(e) {

	}

	document.getElementById(selectName).value = hour + ':' + minute + ':' + second;

}
	</script>";

	/*****vv Hours vv*****/
	$whatToReturn .= " <select name=\"itemHour$selectName\" id=\"itemHour$selectName\"
			onchange=\"javascript:updateTimeHidden$selectName('$selectName');\"> ";
	if ($twoFourTime) {
		$hourStart = 0;
		$hourEnd = 23;
		$hourFormat = "H";
	} else {
		$hourStart = 1;
		$hourEnd = 12;
		$hourFormat = "h";
	}

	for ($countHour = $hourStart; $countHour <= $hourEnd; $countHour++) {
		$hourDate = mktime($countHour, 0, 0, date("m", strtotime("now")), date("d", strtotime("now")), date("Y", strtotime("now")));

		if (!empty($selectedVal) && date($hourFormat, $hourDate) == date($hourFormat, strtotime($selectedVal)) ) {
			$amISelected = "selected=\"selected\"";
			$b = "*";
		} elseif (empty($selectedVal) && date($hourFormat, $hourDate) == date($hourFormat, strtotime("now"))) {
			$amISelected = "selected=\"selected\"";
			$b = "*";
		} else {
			$amISelected = "";
			$b = "";
		}

		$whatToReturn .= "<option value=\"" . date($hourFormat, $hourDate) . "\" $amISelected> " .
			date("G", $hourDate) . " $b </option>";
	}

	$whatToReturn .= " </select>";
	/*****^^ Hours ^^*****/

	$whatToReturn .= ":";

	/*****vv Minutes vv*****/
	$whatToReturn .= " <select name=\"itemMinute$selectName\" id=\"itemMinute$selectName\"
			onchange=\"javascript:updateTimeHidden$selectName('$selectName');\"> ";

	for ($countMinute = 0; $countMinute <= 59; $countMinute += $minGap) {
		$minDate = mktime(12, $countMinute, 0, date("m", strtotime("now")), date("d", strtotime("now")), date("Y", strtotime("now")));

		if (!empty($selectedVal) && (
				date("i", $minDate) <= date("i", strtotime($selectedVal)) &&
				date("i", strtotime($selectedVal)) <  date("i", strtotime("+" . $minGap . " minutes", $minDate))
			) ) {
			$amISelected = "selected=\"selected\"";
			$b = "*";
		} elseif (empty($selectedVal) && date("i", $minDate) == date("i", strtotime("now"))) {
			$amISelected = "selected=\"selected\"";
			$b = "*";
		} else {
			$amISelected = "";
			$b = "";
		}

		$whatToReturn .= "<option value=\"" . date("i", $minDate) . "\" $amISelected> " .
			date("i", $minDate) . " $b </option>";

	}

	$whatToReturn .= " </select>";
	/*****^^ Minutes ^^*****/

	if ($twoFourTime) {

		$whatToReturn .= " : ";

		/*****vv Seconds vv*****/
		$whatToReturn .= " <select name=\"itemSecond$selectName\" id=\"itemSecond$selectName\"
			onchange=\"javascript:updateTimeHidden$selectName('$selectName');\"> ";

		for ($countSecond = 0; $countSecond <= 59; $countSecond++) {
			$secDate = mktime(12, 0, $countSecond, date("m", strtotime("now")), date("d", strtotime("now")), date("Y", strtotime("now")));

			if (!empty($selectedVal) && date("s", $secDate) == date("s", strtotime($selectedVal)) ) {
				$amISelected = "selected=\"selected\"";
				$b = "*";
			} elseif (empty($selectedVal) && date("s", $secDate) == date("s", strtotime("now"))) {
				$amISelected = "selected=\"selected\"";
				$b = "*";
			} else {
				$amISelected = "";
				$b = "";
			}

			$whatToReturn .= "<option value=\"" . date("s", $secDate) . "\" $amISelected> " .
				date("s", $secDate) . " $b </option>";
		}

		$whatToReturn .= " </select>";
		/*****^^ Seconds ^^*****/
	} else {
		/*****vv AM/PM vv*****/
		$whatToReturn .= "&nbsp;<select name=\"itemAMPM$selectName\" id=\"itemAMPM$selectName\"
			onchange=\"javascript:updateTimeHidden$selectName('$selectName');\"> ";


		if (!empty($selectedVal) && date("H", strtotime($selectedVal)) > 12 ) {
			$amISelectedAM = "";
			$bAM = "";

			$amISelectedPM = "selected=\"selected\"";
			$bPM = "*";
		} else {
			$amISelectedAM = "selected=\"selected\"";
			$bAM = "*";

			$amISelectedPM = "";
			$bPM = "";
		}

		$whatToReturn .= "<option value=\"1\" $amISelectedAM> AM $bAM </option>";
		$whatToReturn .= "<option value=\"2\" $amISelectedPM> PM $bPM </option>";

		$whatToReturn .= " </select>";
		/*****^^ AM/PM ^^*****/
	}


	$whatToReturn .= "
		<input type=\"hidden\" name=\"$selectName\" id=\"$selectName\" value=\"\" />
		<script language=\"javascript\" type=\"text/javascript\"> updateTimeHidden$selectName('$selectName'); </script>";


	return $whatToReturn;
}
/****** End Of GET TIME LIST ******/

/********************************************************************************************************
	End	List Creation	Section
********************************************************************************************************/




/********************************************************************************************************
	End	Validaiton	Section
********************************************************************************************************/

/********************************************************************************************************
	sec008	Encryption Section
********************************************************************************************************/


function getEncrypt($string, $key) {

	srand((double) microtime() * 1000000); //for sake of MCRYPT_RAND
	$key = md5($key); //to improve variance
	/* sysOpen module, and create IV */
	$td = mcrypt_module_open('des', '', 'cfb', '');
	$key = substr($key, 0, mcrypt_enc_get_key_size($td));
	$iv_size = mcrypt_enc_get_iv_size($td);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	/* Initialize encryption handle */
	if (mcrypt_generic_init($td, $key, $iv) != -1) {

		/* Encrypt data */
		$c_t = mcrypt_generic($td, $string);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$c_t = $iv.$c_t;
		return $c_t;
	} //end if
}

function getDecrypt($string, $key) {

	$key = md5($key); //to improve variance
	/* sysOpen module, and create IV */
	$td = mcrypt_module_open('des', '', 'cfb', '');
	$key = substr($key, 0, mcrypt_enc_get_key_size($td));
	$iv_size = mcrypt_enc_get_iv_size($td);
	$iv = substr($string, 0, $iv_size);
	$string = substr($string, $iv_size);
	/* Initialize encryption handle */
	if (mcrypt_generic_init($td, $key, $iv) != -1) {

		/* Encrypt data */
		$c_t = mdecrypt_generic($td, $string);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $c_t;
	} //end if
}


/********************************************************************************************************
	End Of Encryption Section
********************************************************************************************************/


/*
PAGINATION LIST
what number list should the system display
*/


function resReturnMeAPageListLink($WhereTo, $numOfRecords, $numPages, $start, $numOfRecordsPerPage, $sendQueryCap = true, $noLabel = false , $requestTag = "")
{
	global $_POST, $_GET, $_REQUEST;

	$queryCap = "";

	if (!$_REQUEST['p']) { $_REQUEST['p'] = "NONE"; }

	if ($sendQueryCap) {
		/*****************************************************
		foreach($_POST as $key => $value) {
			if(substr($key,0,2) == "RQ" && substr($key,0,2) == "OP" &&
			$key == "hasFileDir" && $key == "MAX_FILE_SIZE" && $key == "hasPicDir" &&
			($key != "pgnop" . $requestTag) && ($key != "pgs" . $requestTag)  &&
			($key != "pgnor" . $requestTag)) {
			$queryCap .= "&" . $key . "=" . $value;
			}
		}

		foreach($_GET as $key => $value) {
			if(substr($key,0,2) == "RQ" && substr($key,0,2) == "OP" &&
			$key == "hasFileDir" && $key == "MAX_FILE_SIZE" && $key == "hasPicDir" &&
			($key != "pgnop" . $requestTag) && ($key != "pgs" . $requestTag)  &&
			($key != "pgnor" . $requestTag)) {
			$queryCap .= "&" . $key . "=" . $value;
			}
		}
*****************************************************/
		foreach ($_REQUEST as $key => $value) {
			if (substr($key, 0, 2) != "RQ" && substr($key, 0, 2) != "OP" && $key != "hasFileDir" && $key != "MAX_FILE_SIZE" && $key != "hasPicDir" &&
				$key != "__utmz" && $key != "__utma" && $key != "PHPSESSID" &&
				$key != "pgnop" . $requestTag && $key != "pgs" . $requestTag && $key != "pgnor" . $requestTag) {
				$queryCap .= "&" . $key . "=" . $value;
			}
		} //foreach($_REQUEST as $key => $value) {
	} //if($sendQueryCap) {

	if ($numPages > 1) {

		$firstDisplay = ($start < 1) ? '1' : $start;
		if (($firstDisplay + $numOfRecordsPerPage) > $numOfRecords) { $numOfRecordsShowingDisplay = ($numOfRecords - $start); } else { $numOfRecordsShowingDisplay = $numOfRecordsPerPage; }
		if (!$noLabel) { $forReturn = "<p> <strong>Showing:</strong> $firstDisplay to " . ($start + $numOfRecordsShowingDisplay) . " of $numOfRecords <br />"; }
		$currentPage = ($start/$numOfRecordsPerPage) + 1;

		//if this is not the first page then create a previous button
		if ($currentPage != 1) {
			$forReturn .= " <a href=\"" . $WhereTo . "?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". ($start - $numOfRecordsPerPage)."&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">&laquo; Prev</a> ";
		}

		//generate numbered page links


		/********** vv Owain's Shorten page number list to 10 vv **********/
		$shortenStart  = 1;
		$shortenEnd  = $numPages;
		if ($numPages > 10) {
			$shortenStart  = ($currentPage - 5);
			//  $shortenEnd  = ($currentPage + 5);

			if ($shortenStart < 1) { $shortenStart = 1; }

			$shortenEnd = (4 + $currentPage);

			if ($currentPage <= 5) { $shortenEnd = $shortenEnd + (6 - $currentPage); }

			if ($shortenEnd > $numPages) { $shortenEnd = $numPages; }

			if ($currentPage > ($numPages - 4)) { $shortenStart = $shortenStart - (4 - ($numPages - $currentPage)); }

		} //if($numPages > 10) {


		/********** ^^ Owain's Shorten page number list to 10 ^^ **********/

		////////////////  for($i=1; $i<= $numPages; $i++) {
		for ($i = $shortenStart; $i <= $shortenEnd; $i++) {
			if ($i != $currentPage) {
				$forReturn .= " <a href=\"" . $WhereTo . "?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". (($numOfRecordsPerPage) * ($i - 1)) . "&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">$i</a> ";
			} else {
				$forReturn .= "<strong>" . $i . "</strong>";
			}
		} //for($i=1; $i<= $numPages; $i++) {

		//if this is not the last page create a next button
		if ($currentPage != $numPages) {
			$forReturn .= " <a href=\"" . $WhereTo . "?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". ($start + $numOfRecordsPerPage)."&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">Next &raquo;</a> ";
		}

		if (!$noLabel) { $forReturn .= "</p> <br />"; }
		return $forReturn;

	} //if($numPages > 1) {

} //function resReturnMeAPageListLink($WhereTo, $numOfRecords, $numPages, $start, $numOfRecordsPerPage, $sendQueryCap = true, $noLabel = false , $requestTag = "") {
/****** End Of PAGINATION LIST ******/


/*
PAGINATION LIST
what number list should the system display
*/

function getMeAPaginationList($WhereTo, $numOfRecords, $numPages, $start, $numOfRecordsPerPage, $sendQueryCap = true, $noLabel = false , $requestTag = "")
{

	$queryCap  = '';
	$forReturn = '';

	if (!isset($_REQUEST['p'])) {
		$_REQUEST['p'] = "NONE";
	} elseif (!$_REQUEST['p']) {
		$_REQUEST['p'] = "NONE";
	}

	if ($sendQueryCap) {
		foreach ($_GET as $key => $value) {
			if ($key != "p") {
				$queryCap .= "&" . $key . "=" . $value;
			}
		}
	}

	if ($numPages > 1) {
		$firstDisplay = ($start < 1) ? '1' : $start;
		if (($firstDisplay + $numOfRecordsPerPage) > $numOfRecords) {
			$numOfRecordsShowingDisplay = ($numOfRecords - $start);
		} else {
			$numOfRecordsShowingDisplay = $numOfRecordsPerPage;
		}

		if (!$noLabel) {
			$forReturn = "<p> <strong>Showing:</strong> $firstDisplay to " . ($start + $numOfRecordsShowingDisplay) . " of $numOfRecords <br />";
		}

		$currentPage = ($start/$numOfRecordsPerPage) + 1;

		//if this is not the first page then create a previous button
		if ($currentPage != 1) {
			$forReturn .= " <a href=\"" . $WhereTo . "?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". ($start - $numOfRecordsPerPage)."&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">&laquo; Prev</a> ";
		}


		//generate numbered page links
		$shortenStart  = 1;
		$shortenEnd  = $numPages;

		if ($numPages > 10) {
			$shortenStart  = ($currentPage - 5);

			if ($shortenStart < 1) {
				$shortenStart = 1;
			}

			$shortenEnd = (4 + $currentPage);

			if ($currentPage <= 5) {
				$shortenEnd = $shortenEnd + (6 - $currentPage);
			}

			if ($shortenEnd > $numPages) {
				$shortenEnd = $numPages;
			}

			if ($currentPage > ($numPages - 4)) {
				$shortenStart = $shortenStart - (4 - ($numPages - $currentPage));
			}

		}

		for ($i = $shortenStart; $i <= $shortenEnd; $i++) {
			if ($i != $currentPage) {
				$forReturn .= " <a href=\"" . $WhereTo . "?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". (($numOfRecordsPerPage) * ($i - 1)) . "&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">$i</a> ";
			} else {
				$forReturn .= "<strong>" . $i . "</strong>";
			}
		}

		if ($currentPage != $numPages) {
			$forReturn .= " <a href=\"" . $WhereTo . "?p=" . $_REQUEST['p'] . "&pgs" . $requestTag . "=". ($start + $numOfRecordsPerPage)."&pgnor" . $requestTag . "=$numOfRecords&pgnop" . $requestTag . "=" . $numPages . $queryCap . "\">Next &raquo;</a> ";
		}

		if (!$noLabel) {
			$forReturn .= "</p> <br />";
		}
	}
	return $forReturn;
}








/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
/********************************************************************************************************
 *RESOLUTION INTERACTIVE MEDIA INC.																		*
 *Brendan Farr-Gaynor (brendan@resolutionim.com)														*
 ********************************************************************************************************
 **this fuction returns a basic set of select lists in day/time format. 								*
 *It accepts a date/time in basic MySQL formatting '2006-04-04 12:00:00' writes out HTML 'selects'      *
 *, allows the user to change the data and then writes (though JS) to a single                          *
 *HTML input field for later postback handlers to write/update a field in a db table  					*
 ********************************************************************************************************/
function generateASelect($type, $defaultSet = false, $prefix)
{
	$whatToCycleValue = array();
	$whatToCycleDisplay = array();
	$codeToCheck = false;
	//strtotime()

	switch ($type) {

	case "Day":
		$j=0;
		for ($i=1; $i<=31; $i++) {

			$whatToCycleDisplay[$j] = date("j", mktime(0, 0, 0, 1, $i, 2000));
			$whatToCycleValue[$j] = date("d", mktime(0, 0, 0, 1, $i, 2000));
			$j++;
		}

		$codeToCheck = "d";
		$whatToCountWith = count($whatToCycleValue);

		break;
	case "Month":
		$j=0;
		for ($i=1; $i<=12; $i++) {

			$whatToCycleDisplay[$j] = date("M", mktime(0, 0, 0, $i, 1, 2000));
			$whatToCycleValue[$j] = date("m", mktime(0, 0, 0, $i, 1, 2000));
			$j++;
		}
		$codeToCheck = "m";
		$whatToCountWith = count($whatToCycleValue);

		break;
	case "Year":
		$lastYear = date("Y") - 1;

		for ($i=0; $i<5; $i++) {
			$whatToCycleDisplay[$i] = $lastYear + $i;
			$whatToCycleValue[$i] = $lastYear + $i;

		}
		$codeToCheck = "Y";
		$whatToCountWith = 5;
		break;
	case "Hour":
		$j=0;
		for ($i=1; $i<=12; $i++) {



			$whatToCycleDisplay[$j] = date("h", mktime($i, 0, 0, 1, 1, 2000));
			$whatToCycleValue[$j] = date("h", mktime($i, 0, 0, 1, 1, 2000));
			$j++;
		}

		$codeToCheck = "h";
		$whatToCountWith = count($whatToCycleValue);
		break;
	case "Minute":

		$j=0;
		for ($i=0; $i<=45; $i+=15) {


			$whatToCycleDisplay[$j] = date("i", mktime(1, $i, 0, 1, 1, 2000));
			$whatToCycleValue[$j] = date("i", mktime(1, $i, 0, 1, 1, 2000));
			$j++;
		}

		$codeToCheck = "i";
		$whatToCountWith = count($whatToCycleValue);
		break;
	case "AMPM":

		$whatToCycleDisplay = array("AM", "PM");
		$whatToCycleValue = array("AM", "PM");

		$codeToCheck = "A";
		$whatToCountWith = count($whatToCycleValue);
		break;
	}






	$whatToReturn = " <select id=\"$prefix$type\" name=\"$prefix$type\" style=\"font-size:10px;\"> ";
	for ($i=0; $i<$whatToCountWith; $i++) {
		if ($defaultSet) {
			if ($defaultSet > 0) {
				if (date($codeToCheck, strtotime($defaultSet)) == $whatToCycleValue[$i]) {
					$amISelected = "selected=\"selected\"";
				}
				else {
					$amISelected = "";
				}
			}
			else {
				$amISelected = "";
			}
		}
		$whatToReturn .= "<option value=\"$whatToCycleValue[$i]\" $amISelected> $whatToCycleDisplay[$i] </option>";
	}

	$whatToReturn .= " </select>";
	return $whatToReturn;





}


function giveMeADateSelectGroup($defaultDate, $prefix, $whatSet)
{
	/***
 $whatToReturn  = generateASelect("Month", $defaultDate, $prefix);
 $whatToReturn .= generateASelect("Day", $defaultDate, $prefix);
 $whatToReturn .= generateASelect("Year", $defaultDate, $prefix);
 **/
	$whatToReturn .= "<div id=\"webCalGroup$whatSet\">" . generateASelect("Hour", $defaultDate, $prefix);
	$whatToReturn .= generateASelect("Minute", $defaultDate, $prefix);
	$whatToReturn .= generateASelect("AMPM", $defaultDate, $prefix);
	$whatToReturn .= "</div>";

	return $whatToReturn;

}



/*
GET FOLDER TREE STRUCTURE.
Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi semper, enim eu pellentesque pellentesque, ipsum lectus hendrerit nisl, non aliquet magna mauris a nisi.
*/

function getFilesInAFolder($whatFolder, $iFrameID = false)
{


	$getFilePath = $_SERVER['DOCUMENT_ROOT'] . $whatFolder;
	$path = $whatFolder;

	//////////////////////////////////////print debug("::".$getFilePath."::");
	//////////////////////////////////////print debug("::".$path."::");

	$getFilesInAFolderArray = array();
	$getFilesInAFolderContent = "";
	$getFilesInAFolderCounter = 0;

	// Open the folder
	$dir_handle = @opendir($getFilePath) or die("Cannot open the requested folder: $path");

	// Loop through the files
	while ($file = readdir($dir_handle)) {
		if ($file === "." || $file === ".." || substr($file, 0, 1) === "_") { continue; }
		$getFilesInAFolderCounter += 1;

		if ($iFrameID) {
			$whereToSendUser = " onclick=\"$('#" . $iFrameID . "').attr('src','http://web.perthcounty.ca" . $path . "/" . $file . "');\"";
		} else {
			$whereToSendUser = " href=\"" . $path . "/" . $file . "\" target=\"blank\"";
		}

		if (is_dir($getFilePath . "/" . $file)) {
			////////////   $getFilesInAFolderArray[$getFilesInAFolderCounter] = "<li><a" . $whereToSendUser . ">" . substr($file,0,44) . "</a>" . getFilesInAFolder($path . "/" . $file, $iFrameID) . "</li>";/////:::" .  $path . "/" . $file . ":::
			$getFilesInAFolderArray[$getFilesInAFolderCounter] = "<li><span class=\"expandable-hitarea folder-label\">" . substr($file, 0, 44) . "</span>" . getFilesInAFolder($path . "/" . $file, $iFrameID) . "</li>";/////:::" .  $path . "/" . $file . ":::
		} else {
			if ($file != "Thumbs.db" && $file != ".DS_Store") {
				$getFilesInAFolderArray[$getFilesInAFolderCounter] = "<li><a" . $whereToSendUser . ">" . substr($file, 0, 44) . "</a></li>";
			}
			///////////////////////////////////print debug("RAGE!");
		}
		///////////////////////////////////print debug($getFilesInAFolderArray[$getFilesInAFolderCounter]."::".$getFilesInAFolderCounter."::".$path . "/" . $file);
	}

	// Close
	closedir($dir_handle);

	$getFilesInAFolderArray1 = arsort($getFilesInAFolderArray);
	foreach ($getFilesInAFolderArray as $getFilesInAFolderItemKey => $getFilesInAFolderItemVal) {
		$getFilesInAFolderContent .= $getFilesInAFolderItemVal;
	}///foreach($getFilesInAFolderArray as $getFilesInAFolderItem) {

	return  "<ul class=\"folderTreeUL\">" . $getFilesInAFolderContent . "</ul>";
}///function getFilesInAFolder($whatFolder) {
/****** End Of PAGINATION LIST ******/




function breadcrumb()
{

	global $DRAGGIN, $pRS;
	
	$_SESSION['crumb'] = "";

	if (!isset($bcOverride)) {

		//Breadcrummer, this could probably be a function
		$bcBasket = array(); //little array to hold the crumbs as a buffer

		if (is_array(isset($bcBasketAdd))) {
			foreach ($bcBasketAdd as $bcBasketAddKey => $bcBasketAddValue) {
				$bcBasket[count($bcBasket)] = $bcBasketAddValue;
				$_SESSION['crumb'] .= $bcBasketAddValue;
			}
		}

		$globalHomePageInBreadCrumb = false;
		getBreadcrumb($pRS['itemID']);

	} else {


		foreach ($bcOverride as $key => $value) {
			print "<a href=\"" . $value['url'] . "\">" . $value['label'] . "</a>";
		}
	}
}


function getBreadcrumb($suppliedID, $whatKindOfIDAmI = false, $showLinks = true)
{

	//assumes $pRS, $tRS is already global
	global $DRAGGIN, $bcBasket, $globalHomePageInBreadCrumb, $bcBasketAdd;



	if ($whatKindOfIDAmI == "nav") {
		$thisPageID = returnSpecificItem($suppliedID, "sysTBLNav", "pageID");
	} else { //assumes "page"
		$thisPageID = $suppliedID;
	}

	$getMyNavInfo = "SELECT parentID, label FROM sysTBLNav WHERE (pageID = '$thisPageID' OR pageID IN (SELECT itemID FROM sysTBLPage WHERE systemName = '" . returnSpecificItem($thisPageID, "sysTBLPage", "systemName") . "')) AND sysOpen = '1';";

	$niResult = draggin_query($getMyNavInfo, $DRAGGIN['dbgen']['link']);

	if ($niResult != false) {

		$niRS = draggin_fetch_array($niResult);

		$isTheHP = returnSpecificItem($thisPageID, "sysTBLPage", "isHomepage");
		if ($isTheHP == "1") { $globalHomePageInBreadCrumb = true; }
		if ($globalHomePageInBreadCrumb == true || $isTheHP == "1") {
			$isHP = "id=\"homeBreadCrumb\"";
		} else {
			$isHP = "";
		}

		if (count($bcBasket) > 0) {
			if ($showLinks == true) {
				$bcBasket[count($bcBasket)] = " <a " . $isHP . " href=\"" . $DRAGGIN['settings']['urlpath'] . returnSpecificItem($thisPageID, "sysTBLPage", "systemName")."\">$niRS[label]</a>";
			} else {
				$bcBasket[count($bcBasket)] = " $niRS[label] &gt; ";
			}
		} else {
			//$bcBasket[count($bcBasket)] = " <span class=\"bcCurrentItem\">$niRS[label]</span>";

			$bcBasket[count($bcBasket)] = " <a  class=\"noArrow\" href=\"".$_SERVER['REQUEST_URI']."\">$niRS[label]</a>";
		}
		if ($niRS['parentID'] > 0) { //if I have a parent
			getBreadcrumb($niRS['parentID'], "nav", $showLinks);
		} else {

			/***************************** vv Homepage vv *****************************/
			if ($globalHomePageInBreadCrumb == false && !is_array($bcBasketAdd)) {





				$getHomepageQuery = sprintf("SELECT n.pageID, n.label
					FROM sysTBLNav n
					LEFT JOIN sysTBLPage p ON p.itemID=n.pageID
					LEFT JOIN sysTBLSitesInstanceDataLink i ON i.appItemID = p.systemName
					WHERE p.sysOpen='1'
					AND p.isHomepage='1'
					AND p.sysActive='1'
					AND p.systemName <> '%s'
					AND i.appID='page'
					AND i.instanceID='%d'
					LIMIT 1",
					draggin_escape($_GET['p'], $DRAGGIN['dbgen']['link']),
					(int) $_SESSION['instanceID']);

				$ghpqResult = draggin_query($getHomepageQuery, $DRAGGIN['dbgen']['link']);

				if ($ghpqResult != false) {
					$ghpqRS = draggin_fetch_array($ghpqResult);

					if ($showLinks == true) {
						//home link)

						print "<a href=\"" . $DRAGGIN['settings']['urlpath'] . returnSpecificItem($ghpqRS['pageID'], "sysTBLPage", "systemName") . "\">" . $ghpqRS['label'] . "</a>";
					} else {
						print $ghpqRS['label'];
					}
				}
			}
			/***************************** ^^ Homepage ^^ *****************************/

			$bcBasket = array_reverse($bcBasket);
			$_SESSION['crumb'] = $bcBasket;
			print implode(" ", $bcBasket); //spill the basket
		}
	}
}


function getParents($pageID, $whatKindOfIDAmI = false)
{

	global $DRAGGIN, $getParents;


	if ($whatKindOfIDAmI == "nav") {
		$thisPageID = returnSpecificItem($pageID, "sysTBLNav", "pageID");
	} else {
		$thisPageID = $pageID;
	}

	$getMyNavInfo = sprintf("SELECT parentID FROM sysTBLNav WHERE (pageID = '%d' OR pageID IN (SELECT itemID FROM sysTBLPage WHERE systemName = '" . returnSpecificItem($thisPageID, "sysTBLPage", "systemName") . "')) AND sysOpen = '1';",
		$thisPageID);

	$niResult = draggin_query($getMyNavInfo, $DRAGGIN['dbgen']['link']);

	if (is_resource($niResult) && draggin_num_rows($niResult) > 0) {

		$niRS = draggin_fetch_array($niResult);
		$getParents[count($getParents)] = returnSpecificItem($thisPageID, "sysTBLPage", "systemName");

		if ($niRS['parentID'] > 0) { //if I have a parent
			getParents($niRS['parentID'], "nav");
		}
	}
}

?>