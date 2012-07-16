<?php
/**
*	Display rss feed for any table that has news fields. RSS feeds are dynamic and can be filtered
*/
class newsFeeds{
	/**
	* @var object $db
	* @access protected
	*/
	protected $_db;
	protected $_siteID;
	/**
	* Sort an mutltidimensional array by a specific subkey
	* Can be used to sort multiple rss feeds by date
	* @access public
	* @param array $list
	* @param string $keySort
	* @return array
	*/
	public function multi_array_subval_sort($list,$keySort){
		foreach($list as $key=>$val) {
			$sorted[$key] = strtolower($val[$keySort]);
		}
		asort($sorted);
		foreach($sorted as $key=>$val) {
			$final[] = $list[$key];
		}
		return $final;
	}
	/**
	* Replace entities with decimal format for xml parsing (rss)
	* @access protected
	* @param string $string
	* @return string
	*/
	protected function replace_entities($string){
		$entities = array("&trade"=>"&#8482;","&ldquo;"=>"&#34;","&rdquo;"=>"&#34;","&nbsp;"=>"&#160;","&rsquo;"=>"&#8217;","&lsquo;"=>"&#8216;");
		foreach ($entities as $search => $replace){
			$string = str_replace($search,$replace,$string);
		}
		return $string;
	}
	/**
	* Output valid RSS feed based on items, title and description
	* @access protected
	* @param string $title
	* @param string $description
	* @param array $items
	* @see multi_array_subval_sort()
	*/
	protected function create_rss_feed($title,$description,$items){
		$output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		$output .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">";
		$output .= "<channel>";
		$output .= "<title>".$title."</title>";
		$output .= "<description>".$description."</description>";
		$output .= "<link>http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."</link>";
		$output .= "<atom:link href=\"http://" .$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."\" rel=\"self\" type=\"application/rss+xml\" />";

		$output .= "<copyright>Copyright (C) ".date("Y")." ".basename($_SERVER['SERVER_NAME'])."</copyright>";
		if (count($items) > 0){
			$items = $this->multi_array_subval_sort($items,"sortBy");
			foreach ($items as $item){
			$output .= "<item>";
			$output .= "<title><![CDATA[".$this->replace_entities($item["title"])."]]></title>";
			$output .= "<description><![CDATA[".$this->replace_entities($item["description"])."]]></description>";
			$output .= "<link>".htmlspecialchars($this->replace_entities($item["link"]))."</link>";
			$output .= "<guid>".htmlspecialchars($this->replace_entities($item["link"]))."</guid>";
			$output .= '<source url="'.$item["source"]."\"><![CDATA[".$this->replace_entities($item["title"])."]]></source>";
			$output .= "<pubDate>".$item["pubDate"]."</pubDate>";
			$output .= "</item>";
			}
		}
		$output .= "</channel>";
		$output .= "</rss>";
		echo($output);
	}
	/**
	* Method to retrieve news items for rss feed based on parameters submitted
	* @access public
	* @param string $table
	* @param string $link
	* @param string $source
	* @param string $title
	* @param string $description
	* @param string|false $filter
	* @see DB_MySQL::result_please()
	* @see DB_MySQL::valid()
	* @see create_rss_feed()
	*/
	public function create_rss_items($table,$link,$source,$title,$description,$filter = false){
		$items = array();
		$filter = ($filter == false)?"`sysStatus` = 'active' AND `sysOpen` = '1' AND `approvalStatus` = '1' AND `siteID` = ".$this->_siteID:stripslashes($this->_db->escape($filter,true));
		$mysql_sel = "itemID,title,author,lead_in,UNIX_TIMESTAMP(displayDate) as dateInserted,slug";
		$res = $this->_db->result_please(false,$table,$mysql_sel,$filter,"dateInserted desc, itemID desc");
		$j = 0;
		if ($this->_db->valid($res) != false){
			while ($row = $this->_db->fetch_assoc($res)) {
				$itemLink = "http://".$_SERVER['SERVER_NAME']."/".$link."/".trim($row["slug"]);
				array_push($items, array("title"=>trim($row["title"]),
							"description"=>trim($row["lead_in"]),
							"link"=>$itemLink,
							"pubDate"=>date("D, d M Y H:i:s O", trim($row['dateInserted'])),
							"source"=>"http://".$_SERVER['SERVER_NAME']."/".$source,
							"sortBy"=>$j
				));
				$j++;
			}
		}
		if (isset($items)){
			$this->create_rss_feed($title,$description,$items);
		}
	}
	/**
	* Set class property
	* @access public
	* @param object $db
	*/
	public function __construct($db,$siteID){
		if (is_object($db) && $db INSTANCEOF DB_MySQL){
			$this->_db = $db;
			$this->_siteID = (int)$siteID;
		}
	}
}
?>