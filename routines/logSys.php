<?php
	dbConnect($dbA);
	$xIP = $_SERVER["REMOTE_ADDR"];
	$ignoreArray = split(",",retrieveOption("ignoreIPLogs"));
	$ignoreThis = false;
	for ($f = 0; $f < count($ignoreArray); $f++) {
		if (trim($ignoreArray[$f]) != "") {
			if (trim($ignoreArray[$f]) == $xIP) {
				$ignoreThis = true;
				break;
			}
		}
	}
	
	$browserArray = array (
			array("MSIE 6.0","Internet Explorer 6","IE","IE6"),
			array("MSIE 5.5","Internet Explorer 5.5","IE","IE5.5"),
			array("MSIE 5.0","Internet Explorer 5","IE","IE5"),
			array("MSIE 4.0","Internet Explorer 4","IE","IE4"),
			array("MSIE","Internet Explorer","IE","IE"),
			array("Opera","Opera","OPERA","OPERA"),
			array("Konqueror","Konqueror","KONQUEROR","KONQUEROR"),
			array("Firefox","Firefox","FIREFOX","FIREFOX"),
			array("Safari","Safari","SAFARI","SAFARI"),
			array("Mozilla/6","Netscape 7.x","NS","NS7"),
			array("Mozilla/5","Netscape 6.x","NS","NS6"),
			array("Mozilla/4","Netscape 4.x","NS","NS4"),
			array("Mozilla","Netscape","NS","NS3")
			);
	$found = false;
	for ($f = 0; $f < count($browserArray); $f++) {
		if (strpos(" ".$_SERVER["HTTP_USER_AGENT"],$browserArray[$f][0]) != false) {
			$xBrowser = $browserArray[$f][1];
			$xBrowserShort = $browserArray[$f][2];
			$xBrowserLong = $browserArray[$f][3];
			$found = true;
			break;
		}
	}
	if ($found == false) { $xBrowser = $_SERVER["HTTP_USER_AGENT"]; }			
			
	if (retrieveOption("enableLogging") == "1" && $ignoreThis == false) {
		//logging is enabled;
		$osArray = array(
						array("Win95","Windows 95"),
						array("Windows 95","Windows 95"),
						array("Win98","Windows 98"),
						array("Windows 98","Windows 98"),
						array("Windows 2000","Windows 2000"),
						array("Windows NT 4.0","Windows NT"),
						array("Windows NT 5.0","Windows 2000"),
						array("Windows NT 5.1","Windows XP"),
						array("Windows XP","Windows XP"),
						array("Windows ME","Windows ME"),
						array("WinNT","Windows NT"),
						array("Windows NT","Windows NT"),
						array("Macintosh","Macintosh"),
						array("Mac_PowerPC","Macintosh"),
						array("SunOS","SunOS"),
						array("Linux","Linux")
						);
									
		$searchEngineArray = array(
						array("216.239.39.100","Google","q"),
						array("google","Google","q"),
						array("search.msn","MSN","q"),
						array("search.aol","AOL","query"),
						array("hotbot.lycos","HotBot","query"),
						array("search.lycos","Lycos","query"),
						array("msxml.excite","Excite","qkw"),
						array("srch.overture.com/d/search/p/go","Go/Infoseek","Keywords"),
						array("search.netscape","Netscape","query"),
						array("search.yahoo","Yahoo","p"),
						array("altavista","Altavista","q"),
						array("dpxml.webcrawler","Web Crawler","qkw"),
						array("search.dmoz","DMOZ","search"),
						array("nlsearch","Northern Light","qr"),
						array("euroseek","Euroseek","string"),
						array("search.dogpile","Dogpile","q"),
						array("www.search.com","Search","q"),
						array("www.ask.","Ask Jeeves","ask"),
						array("www.bbc.co","BBC","q"),
						array("www.overture.com/d/search","Overture","Keywords")
						);
						
		$found = false;
		for ($f = 0; $f < count($osArray); $f++) {
			if (strpos(" ".$_SERVER["HTTP_USER_AGENT"],$osArray[$f][0]) != false) {
				$xOperatingSystem = $osArray[$f][1];
				$found = true;
				break;
			}
		}
		if ($found == false) { $xOperatingSystem = "Other"; }
		
		/*$found = false;
		for ($f = 0; $f < count($browserArray); $f++) {
			if (strpos(" ".$_SERVER["HTTP_USER_AGENT"],$browserArray[$f][0]) != false) {
				$xBrowser = $browserArray[$f][1];
				$found = true;
				break;
			}
		}
		if ($found == false) { $xBrowser = "Other"; }*/

		if (retrieveOption("logsReverseDNS") == 1) {
			$xHostMask = @gethostbyaddr(@$_SERVER['REMOTE_ADDR']);
			$xMaskSplit = split("\.",$xHostMask);
			$xDomainTopLevel = @$xMaskSplit[count($xMaskSplit)-1];
			if ($xHostMask == $_SERVER["REMOTE_ADDR"]) {
				//this is still an IP address
				$xDomainTopLevel = "Unknown";
			}			
		} else {
			$xHostMask = "DNS Lookup Off";
			$xDomainTopLevel = "n/a";
		}
		$xIP = $_SERVER["REMOTE_ADDR"];
		if (array_key_exists("REQUEST_URI",$_SERVER)) {
			$xPageName = @$_SERVER["REQUEST_URI"];
			$xBits =  explode("?",$xPageName);
			$xPage = @$xBits[0];
		} else {
			$xPage = @$_SERVER["SCRIPT_NAME"];
		}
		$xPageSplit = split("/",$xPage);
		$xPage = $xPageSplit[count($xPageSplit)-1];
		
		$xPageSubmit = $xPage;
		
		/*if (@$_SERVER["QUERY_STRING"] != "") {
			$xPageSubmit = $xPage."?".$_SERVER["QUERY_STRING"];
		} else {
			$xPageSubmit = $xPage;
		}*/
		
		$theTime = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
		
		$xHour = strftime("%H",$theTime);
		$xMinutes = strftime("%M",$theTime);
		$xSeconds = strftime("%S",$theTime);
		$xYear = strftime("%Y",$theTime);
		$xMonth = strftime("%m",$theTime);
		$xDay = strftime("%d",$theTime);
		$xDayOfYear = strftime("%j",$theTime);
		$xDayOfWeek = strftime("%w",$theTime);
		$xConcDate = strftime("%Y%m%d",$theTime);
		
		$xQuery=@$_SERVER["QUERY_STRING"];
		
		$xReferrer = @$_SERVER["HTTP_REFERER"];
		if ($xReferrer == "") {
			$xReferrer = "Direct/Unknown";
		}
		$xRefPage = explode("?", $xReferrer);
		$xRefQuery = explode("&",@$xRefPage[1]);

		$found = false;
		for ($f = 0; $f < count($searchEngineArray); $f++) {
			if (strpos($xRefPage[0],$searchEngineArray[$f][0]) != false) {
				$xSearchEngine = $searchEngineArray[$f][1];
				$xQueryElement = $searchEngineArray[$f][2];
				$found = true;
				break;
			}
		}
		if ($found == false) { $xSearchEngine = ""; }


		$newQueryString = "";
		if (@$xRefPage[1] != "") {
			for ($f = 0; $f < count($xRefQuery); $f++) {
				if ($f == 0) { $newQueryString = "?"; }
				$thisQuery = split("=",$xRefQuery[$f]);
				if ($thisQuery[0] != "jssCart") {
					$newQueryString = $thisQuery[0]."=".@$thisQuery[1];
				}
				if ($thisQuery[0] == @$xQueryElement) {
					$xSearchQuery = urldecode(@$thisQuery[1]);
				}
			}
		}
		$xReferrer = $xRefPage[0].$newQueryString;

		$rArray[] = array("concdate",$xConcDate,"S");	
		$rArray[] = array("year",$xYear,"N");			
		$rArray[] = array("month",$xMonth,"N");
		$rArray[] = array("day",$xDay,"N");	
		$rArray[] = array("hour",$xHour,"N");	
		$rArray[] = array("minute",$xMinutes,"N");	
		$rArray[] = array("second",$xSeconds,"N");	
		$rArray[] = array("dayofyear",$xDayOfYear,"N");	
		$rArray[] = array("dayofweek",$xDayOfWeek,"N");	
		$rArray[] = array("browser",$xBrowser,"S");	
		$rArray[] = array("ip",$xIP,"S");	
		$rArray[] = array("hostmask",$xHostMask,"S");	
		$rArray[] = array("domaintoplevel",$xDomainTopLevel,"S");	
		$rArray[] = array("page",$xPageSubmit,"S");	
		$rArray[] = array("referrer",$xReferrer,"S");	
		$rArray[] = array("os",$xOperatingSystem,"S");	
		$rArray[] = array("searchengine",@$xSearchEngine,"S");	
		$rArray[] = array("searchstring",@$xSearchQuery,"S");
		$dbA->insertRecord($tableLogs,$rArray,0);

	}
	$dbA->close();
	
	function addReportsPopularityRecord($xType,$xID) {
		global $dbA,$tableReportsPopular;
		if ($xType=="P") {
			if (retrieveOption("reportsPopularProducts") == 0) { return; }
		}
		if ($xType=="S") {
			if (retrieveOption("reportsPopularSections") == 0) { return; }
		}
		$theDate = date("Ymd");
		$dbA->query("insert into $tableReportsPopular (date,type,theID) VALUES('$theDate','$xType','$xID')");
	}

?>