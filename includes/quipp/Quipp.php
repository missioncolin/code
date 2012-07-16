<?php

class Quipp {
	
	public $instanceID = 1;
	public $siteLanguageRS = array();
    public $siteID = 1;
	
	function __construct()
	{
		$this->get_site_instance();
	
	}
	
		
	function get_site_instance()
	{
		global $db;
		
		$qry = sprintf("SELECT i.itemID AS instanceID, s.itemID AS siteID, s.title AS siteTitle, s.description, s.defaultLanguageID AS defaultLanguage, l.itemID AS languageID, l.title AS languageTitle 
			FROM sysSites AS s 
			LEFT OUTER JOIN sysSitesInstances AS i ON(s.itemID = i.siteID AND i.sysOpen = '1' AND i.sysStatus = 'active')
			LEFT OUTER JOIN sysSitesDomains AS d ON(s.itemID = d.siteID AND d.sysOpen = '1' AND d.sysStatus = 'active' AND d.domain = '%s') 
			LEFT OUTER JOIN sysSitesLanguages AS l ON(i.languageID = l.itemID AND l.sysOpen = '1' AND l.sysStatus = 'active')
			WHERE d.domain IS NOT NULL 
			AND s.sysOpen = '1' 
			AND s.sysStatus = 'active';",
				$_SERVER['HTTP_HOST']);

		$res = $db->query($qry);
		
		if ($db->valid($res)) {
			while ($slRS = $db->fetch_assoc($res)) {
				
				if (!isset($_SESSION['languageID'])) {
					$_SESSION['languageID'] = $slRS['defaultLanguage'];
				}
		
				if (trim($_SESSION['languageID']) == trim($slRS['languageID'])) {
					$_SESSION['instanceID'] = $slRS['instanceID'];
				}
		
				foreach ($slRS as $key => $val) {
					$this->siteLanguageRS[$slRS['instanceID']][$key] = $val;
				}
				$this->siteID = trim($slRS["siteID"]);
			}
		}
		if (isset($_SESSION['instanceID'])) {
			$this->instanceID = $_SESSION['instanceID'];
		}
		return $this->instanceID;
	}
	
	/**
	 * SET SYSTEM LOG
	 * Accepts a message and grabs various $_SERVER variables and Quipp variables.
	 */
	
	function system_log($message) 
	{
		global $db, $user;
	
		if (!isset($_SERVER['HTTP_REFERER'])) { 
			$_SERVER['HTTP_REFERER'] = false; 
		}
	
		
		$qry = sprintf("INSERT INTO sysLog (userID, message, userRemoteAddr, userAgent, userReferer, sysDateCreated) VALUES ('%d',  '%s', '%s', '%s', '%s', %s);%s",
			$user->id,
			$db->escape($message),
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_USER_AGENT'],
			$_SERVER['HTTP_REFERER'],
			$db->now,
			$db->last_insert);	
		$db->query($qry);
	
		return true;
	}

}

?>