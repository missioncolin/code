<?php

class Search
{

	var $userID;
	var $success = true;

	function __construct()
	{
		if (isset($_SESSION['myId'])) {
			$this->userID = (int) $_SESSION['myId'];
		} else if (isset($_SESSION['user_ID'])) {
				$this->userID = (int) $_SESSION['user_id'];
			}
	}



	function run_content_search($queryString)
	{
		global $quipp, $db, $jsFooter;
		
		$outputBuffer = ""; //init
		$howManyFoundTotal = 0;
		if (!empty($queryString)) {

			//move the words into an array so they can be looped and referenced later
			//$searchWords = explode(" ". $queryString);
			$searchWords = addslashes($queryString);

			// The words are checked against the search index table (sysSearchIndex)
			// A search is preformed 4 times for each word:
			// [SBOOL] Boolean
			// [SNAT] Natural Language
			// [SQE] Query Expansion

			// [SLIT] and then a simple literal 'like' match


			//... the results of these 4 searches are then stored in a scratch table and compared, items will then be returned to the user sorting by the highest hit count across the searches

			$searchResults = array();


			$searchType = array(
				"PAGETITLE" => true, 
				//"WINERY-SUB-APP"=>true, 
				"SBOOL" => "IN BOOLEAN MODE", 
				"SNAT" => "", 
				"SQE" => " WITH QUERY EXPANSION", 
				"SLIT" => true, 
				"TITLE" => true//,  
				//"EVENTS" => "TAGS"
			);
			//$searchType = array("SNAT" => "", "SLIT" => true, "TITLE" => true);

			foreach ($searchType as $searchKey => $searchValue) {
			
				if ($searchKey == "WINERY-SUB-APP"){
					$Query = "SELECT concat('tag_', itemID) as itemID, concat('/', winerySystemName) as pathToContent, sysDateCreated as indexTime, 
					wineryName as contentTitle, description as contentDumpToScan, winerySystemName as indexSource
					FROM tblWineries  
					WHERE (subAppellationHuman LIKE '%$searchWords%' OR winemaker LIKE '%$searchWords%')  AND sysOpen = '1'";
					
					$bonus = 4;		
				
				}elseif ($searchKey == "PAGETITLE"){
					$Query = "SELECT concat('tag_', itemID) as itemID, concat('/', systemName) as pathToContent, sysDateCreated as indexTime, 
					label as contentTitle, label as contentDumpToScan, systemName as indexSource
					FROM sysPage  
					WHERE label LIKE '%$searchWords%' AND sysOpen = '1'";
					
					$bonus = 4;		
				
				}elseif ($searchKey == "SLIT") {

					$Query = "SELECT *
							 			FROM sysSearchIndex
									   WHERE
									   contentDumpToScan LIKE '%$searchWords%' OR  contentTitle LIKE '%$searchWords%'
									   AND sysOpen = '1' AND sysStatus = 'active'";

					$bonus = 2;

				} elseif ($searchKey == "TITLE") {



					$Query = "SELECT *
							 			FROM sysSearchIndex
									   WHERE
									   contentTitle LIKE '%$searchWords%'
									   AND sysOpen = '1' AND sysStatus = 'active'";

					$bonus = 4;


				} else if ($searchKey == "EVENTS"){
					$Query = "SELECT concat('tag_',e.itemID) as itemID, concat('/',w.winerySystemName,'#event',e.itemID) as pathToContent, e.eventStartDate as indexTime, 
					concat('Event: ',e.eventTitle) as contentTitle,e.description as contentDumpToScan,t.tag as indexSource 
					FROM tblWineries as w INNER JOIN tblCalendarEvents as e ON w.itemID = e.wineryID 
					INNER JOIN tblEventsTags as et ON e.itemID = et.eventID 
					INNER JOIN tblTags as t ON et.tagID = t.itemID 
					WHERE t.tag LIKE '%$searchWords%' AND UNIX_TIMESTAMP(e.eventStartDate) >= ".mktime(0,0,0,date('m'),1,date('Y'));
					
					$bonus = 4;		
				
				} else {
					$Query = "SELECT *,
											(MATCH(contentDumpToScan) AGAINST ('$searchWords' $searchValue) +
											MATCH(contentTitle) AGAINST ('$searchWords' $searchValue))

											 AS searchScore
									   FROM sysSearchIndex
									   WHERE
									   MATCH(contentDumpToScan)
									   AGAINST ('$searchWords' $searchValue) OR
									   MATCH(contentTitle)
									   AGAINST ('$searchWords' $searchValue)
									   AND sysOpen = '1' AND sysStatus = 'active' ORDER BY searchScore";

					$bonus = 1;
				}

				//yell("print", $Query);

				//  $Query .= " LIMIT 1";

				$qResult = $db->query($Query);


				

				if ($db->valid($qResult)) {
					$howManyFoundTotal += $db->num_rows($qResult);
					//add these items to the result array (this array may already have items in it from the last iteration, in that case add to it)
					while ($qRS = $db->fetch_array($qResult)) {
						$myContentRank = 1 + $bonus; //everything gets defaulted to one
						$addAnItem = true;

						//check first to see if this item id has come up before
						if (isset($searchResults[$qRS['itemID']])) {
							//if it has, it means that this item was matched in 2 or more search result lists, bump up it's content rank by 1 each time you get here
							$myContentRank = ($searchResults[$qRS['itemID']]['contentRank']+=(3 + $bonus));
							$addAnItem = false;

						}


						//check to make sure this item has a title, users are somehow getting pages through without page names
						if (empty($qRS['contentTitle'])) {
							$qRS['contentTitle'] = substr($qRS['pathToContent'], 0, 40);
						}


						//check to see if this item's pathToContent has already been listed somewhere previously (we don't want duplicate links to the same place)
						foreach ($searchResults as $searchItem => $searchItemArray) { //loop through all the previously entered items
							if (array_search($qRS['pathToContent'], $searchItemArray)) {
								//yes this content path has been listed before, however it could be the same itemID as me in a different search, so let's check
								if ($searchItem != $qRS['itemID']) { //if this is pointing to the same content as another index item, let's ignore this but add a point to the other item
									//no this is a different index item that is pointing to the same content, give the old item a boost, but make sure it's stuff doesn't get added
									//as this would cause multiple links to the same thing to show up in the results
									$searchResults[$searchItem]['contentRank']+=1;

								}

								$addAnItem = false;
							} elseif (array_search($qRS['contentTitle'], $searchItemArray)) { //this could be an old PDF version, check to make sure the title is not repeated, if it is, ignore it
								$addAnItem = false;

							}
						}




						if ($addAnItem) {

							$searchResults[$qRS['itemID']]['contentRank'] = $myContentRank;
							$searchResults[$qRS['itemID']]['indexTime'] = $qRS['indexTime'];
							$searchResults[$qRS['itemID']]['indexSource'] = $qRS['indexSource'];
							$searchResults[$qRS['itemID']]['contentTitle'] = $qRS['contentTitle'];
							$searchResults[$qRS['itemID']]['pathToContent'] = $qRS['pathToContent'];
							$searchResults[$qRS['itemID']]['contentDumpToScan'] = $qRS['contentDumpToScan'];

						}

						//$outputBuffer .= "Returned: " . $qRS['itemID'] . " -> " . $qRS['searchScore'] . $qRS['contentDumpToScan'] . "<br />";

					}

				}
			}

			//$outputBuffer .= out the stuff
			// $outputBuffer .= "<h3>Returned To User</h3>";
			if (!empty($searchResults)) {

				$outputBuffer .= "<span class=\"searchResultMessageBlock\">Found <span class=\"searchResultsNum\">" . count($searchResults) . "</span> result";
				if (count($searchResults) > 1) { $outputBuffer .= "s"; }
				$outputBuffer .= " for your search on <span class=\"searchResultsOnWhat\"> " . stripslashes(htmlentities($searchWords)) . " </span></span>

						<span id=\"searchResults\">
						";

				//sorting function
				function cmp($x, $y)
				{
					if ($x["contentRank"] == $y["contentRank"]) {
						return 0;
					} else if ( $x["contentRank"] > $y["contentRank"] ) {
							return -1;
						} else {
						return 1;
					}

				}

				usort($searchResults, "cmp");
				//end of sorting function

				$numOfResultsPerPage = 10;
				$p = 0; //pages
				$i = 0; //item on this page
				$ti = 0; //total items
				$first = true;

				foreach ($searchResults as $searchItem => $searchItemArray) {

					$i++;
					$ti++;



					if ($i > $numOfResultsPerPage || ($first)) {

						if (!$first) {
							$i = 0;
							$outputBuffer .= "</div>";
							$display = "style=\"display:none;\"";
						} else {
							$first = false;
							$display = "";
						}

						$outputBuffer .= "<div id=\"searchResultPage_" . $p . "\" class=\"searchResultPages\"" . $display . ">";
						$p++;
					}
					

					$outputBuffer .= "<span class=\"searchResultItem\">
									<a class=\"searchResultItemLink\" href=\"" . $searchItemArray['pathToContent'] . "\">".stripslashes($searchItemArray['contentTitle'])."</a>
									<span class=\"searchResultDescrip\">" . substr(stripslashes($searchItemArray['contentDumpToScan']), 0, 250) . "...</span>
									<span class=\"searchResultPath\">" . $_SERVER['HTTP_HOST'] . $searchItemArray['pathToContent'] . "</span> -
									<span class=\"searchResultRank\">" . $searchItemArray['indexTime'] . " (" . $searchItemArray['contentRank'] . ")</span>
							</span>";

					//$outputBuffer .= $ti . "THISISATEST" . count($searchResults);

					if ($ti == count($searchResults)) {
						$outputBuffer .= "</div>";
					}

				}

				$outputBuffer .= "</span>";

				$outputBuffer .= "<div id=\"searchPagination\" class=\"pagination\">&nbsp;</div>";

				array_push($quipp->js['footer'],"/js/jquery.pagination.js", "/js/jquery.scrollTo.js");
				
				
				$jsFooter .= "
						
								function adjustSearchResults(pageID, container) {
											thisPage = \"#searchResultPage_\" + pageID;
											$(\"#searchResults\").children(\".searchResultPages\").hide();
											$(thisPage).show();
											$.scrollTo(0);
								}
		
								$(\"#searchPagination\").pagination(" . $ti . ", {
										items_per_page:" . $numOfResultsPerPage . ",
										callback:adjustSearchResults
								});
	
						";

				$searchWordsArray = explode(" ", $searchWords);




			} else {

				$outputBuffer .= "Sorry, no search results found.";

			}



		} else {

			$outputBuffer .= "Please provide a search query.";

		}

		return $outputBuffer;

	}

	function build_index()
	{

		global $quipp, $db;

		
		$lastSystemName = "";

		// USER GENERATED PLAIN DATA
		//run a query to pull out all of the pages in the navigation and the content (content box data)

		$getPages = "SELECT page.systemName, page.label, page.pageKeywords, pageContent.htmlContent										  
										  FROM sysNav as nav 
										  LEFT OUTER JOIN sysPage as page ON (nav.pageSystemName = page.systemName) 
										  LEFT OUTER JOIN sysPageTemplateRegionContent as pageLink ON (page.itemID = pageLink.pageID)
										  LEFT OUTER JOIN sysPageContent as pageContent ON (pageLink.contentID = pageContent.itemID)
										  WHERE page.sysStatus = 'active' AND page.sysVersion = 'live' AND page.sysOpen = '1' AND  pageContent.sysOpen = '1' AND pageLink.sysOpen = '1'; 
										  
										  ";
						yell($getPages);
						$Result = $db->query($getPages);

						
						if($db->valid($Result)) {
						
							$clearOutOldIndexItems = "DELETE FROM sysSearchIndex WHERE indexSource = 'master';";
							$db->query($clearOutOldIndexItems);
						

							while($indexRS = $db->fetch_array($Result)) {
								
								$pageSystemName = $indexRS['systemName'];
								$pageContent = strip_tags($indexRS['htmlContent']);
								$pageContentMeta = strip_tags($indexRS['pageKeywords']);
								
								if(empty($indexRS['label'])) {
									$indexRS['label'] = substr($pageSystemName, 0, 40);
								}
								
								if(!empty($pageContent)) {
									//print "<li><strong>$pageSystemName</strong> : $pageContent </li>";
									$insertNewIndexItem = "
										INSERT INTO sysSearchIndex (indexTime, indexSource, pathToContent, contentTitle, contentDumpToScan) 
										VALUES
										(NOW(), 'master','/" .$pageSystemName . "','" . $indexRS['label'] . "','" . addslashes($pageContent) . "');
									";
									yell($insertNewIndexItem);
									$db->query($insertNewIndexItem);
								}
								
								
								if(!empty($pageContentMeta) && $pageSystemName != $lastSystemName) {
									//print "<li><strong>$pageSystemName</strong> : $pageContentMeta </li>";
									$insertNewIndexItem = "
										INSERT INTO sysSearchIndex (indexTime, indexSource, pathToContent, contentTitle, contentDumpToScan) 
										VALUES
										(NOW(), 'master','/" .$pageSystemName . "','" . $indexRS['label'] . "','" . addslashes($pageContentMeta) . "');
									";
									
										$lastSystemName = $pageSystemName;
									yell($insertNewIndexItem);
									$db->query($insertNewIndexItem);
								}
							}
						
						}
						
						
						/*

						$webDir = "/fileBin/library/";
						$dir = $DRAGGIN['settings']['docroot'] . $webDir;
						
						searchIndexThisDirForPDF($dir, $webDir, "masterPDF");
						
						*/
						//searchIndexRunNews();

	}







}

?>