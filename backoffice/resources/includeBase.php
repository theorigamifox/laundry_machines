<?php
	set_magic_quotes_runtime(0);

	//if (get_magic_quotes_gpc()) {
		if(isset($HTTP_GET_VARS))
		{
			foreach($HTTP_GET_VARS as $k=>$v) {
				$HTTP_GET_VARS[$k] = stripslashes($v);
			}
		}

		if(isset($HTTP_POST_VARS))
		{
			foreach($HTTP_POST_VARS as $k=>$v) {
				$HTTP_POST_VARS[$k] = stripslashes($v);
			}
		}

		if(isset($HTTP_COOKIE_VARS))
		{
			foreach($HTTP_COOKIE_VARS as $k=>$v) {
				$HTTP_COOKIE_VARS[$k] = stripslashes($v);
			}
		}
	//}
	
	$browserArray = array (
			array("MSIE 6.0","Internet Explorer 6","IE","IE6"),
			array("MSIE 5.5","Internet Explorer 5.5","IE","IE5.5"),
			array("MSIE 5.0","Internet Explorer 5","IE","IE5"),
			array("MSIE 4.0","Internet Explorer 4","IE","IE4"),
			array("MSIE","Internet Explorer","IE","IE"),
			array("Firefox","Mozilla Firefox","Firefox","Firefox"),
			array("Safari","Safari","SAFARI","SAFARI")
			);
			
	$found = false;
	$xBrowserShort = "";
	$xBrowserLong = "";
	for ($g = 0; $g < count($browserArray); $g++) {
		if (strpos(" ".$_SERVER["HTTP_USER_AGENT"],$browserArray[$g][0]) != false) {
			$xBrowser = $browserArray[$g][1];
			$xBrowserShort = $browserArray[$g][2];
			$xBrowserLong = $browserArray[$g][3];
			$found = true;
			break;
		}
	}		
	
	include("../static/config.php");
	include("../routines/dbAccess_mysql.php");
	include("./routines/formelements.php");
	
	$trans_tbl = get_html_translation_table (HTML_ENTITIES); 
   	$trans_tbl = array_flip ($trans_tbl); 
	
	function unhtmlentities ($string)  {
		global $trans_tbl;
	    $ret = strtr ($string, $trans_tbl);
	    return preg_replace('/&#(\d+);/me',
	      "chr('\\1')",$ret);
	}
	
	function formatDate($xDate) {
		$dateFormat = retrieveOption("dateFormat");
		$theYear = substr($xDate,0,4);
		$theMonth = substr($xDate,4,2);
		$theDay = substr($xDate,6,2);
		$returnDate = @date($dateFormat,mktime(0,0,0,$theMonth,$theDay,$theYear));
		return $returnDate;
	}
	
	function formatTime($xTime) {
		$timeFormat = retrieveOption("timeFormat");
		$theHours = substr($xTime,0,2);
		$theMinutes = substr($xTime,2,2);
		$theSeconds = substr($xTime,4,2);
		$returnTime = @date($timeFormat,mktime($theHours,$theMinutes,$theSeconds,date("m"),date("d"),date("Y")));
		return $returnTime;
	}
	
	function implodeQueryString($theArray) {
		$queryString = "";
		foreach($theArray as $k=>$v) {
			if ($queryString != "") {
				$queryString .="&";
			}
			$queryString .= $k."=".$v;
		}
		return $queryString;
	}
	
	function explodeQueryString($theQueryString) {
		$queryBits = explode("&",$theQueryString);
		$newArray = null;
		for ($f = 0; $f < count($queryBits); $f++) {
			if ($queryBits[$f] != "") {
				$thisPair = explode("=",$queryBits[$f],2);
				$newArray[$thisPair[0]] = @$thisPair[1];
			}
		}
		return $newArray;
	}

	function getGET($vName) {
		if (array_key_exists($vName,$_GET)) {
			return $_GET[$vName];
		} else {
			return "";
		}
	}

	function getPOST($vName) {
		if (array_key_exists($vName,$_POST)) {
			return $_POST[$vName];
		} else {
			return "";
		}
	}

	function getGENERIC($vName,$vArray) {
		if (array_key_exists($vName,$vArray)) {
			return $vArray[$vName];
		} else {
			return "";
		}
	}

	function getFORM($vName) {
		if (array_key_exists($vName,$_GET)) {
			return $_GET[$vName];
		} else {
			if (array_key_exists($vName,$_POST)) {
				return $_POST[$vName];
			}
			return "";
		}
	}

	function doRedirect($theURL) {
		global $dbA;
		@$dbA->close();
?>
<HTML>
<HEAD>
<TITLE></TITLE>
</HEAD>
<BODY>
<script>
	document.location.replace("<?php print $theURL; ?>")
</script>
</body>
</html>
<?php
	}
	
	function doRedirectTop($theURL) {
		global $dbA;
		@$dbA->close();
?>
<HTML>
<HEAD>
<TITLE></TITLE>
</HEAD>
<BODY>
<script>
	top.location.replace("<?php print $theURL; ?>")
</script>
</body>
</html>
<?php
	}	

	function exitError($errorTitle,$errorText) {
?>
<HTML>
<HEAD>
<TITLE>JShop Server: <?php print $errorTitle; ?></TITLE>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<font class="normaltext"><B>JShop Server: <?php print $errorTitle; ?></b><p>
<b><?php print $errorText; ?></b>
</body>
</html>
<?php
		exit;
	}

	function formatDates($datestring) {
		global $userRecord;
		$timeDiff = $userRecord["timeDiff"];
		$dateDay = substr($datestring,6,2);
		$dateMonth = substr($datestring,4,2);
		$dateYear = substr($datestring,0,4);
		$dateHour = substr($datestring,8,2);
		$dateMinutes = substr($datestring,10,2);
		$datestring = date("YmdHi",mktime(($dateHour+$timeDiff),$dateMinutes,0,$dateMonth,$dateDay,$dateYear));
		if ($userRecord["USDates"] == "N") {
			return substr($datestring,6,2)."/".substr($datestring,4,2)."/".substr($datestring,0,4);
		} else {
			return substr($datestring,4,2)."/".substr($datestring,6,2)."/".substr($datestring,0,4);
		}
	}

	function formatDatesFront($datestring) {
		global $aRecord;
		if ($aRecord["USDates"] == "N") {
			return substr($datestring,6,2)."/".substr($datestring,4,2)."/".substr($datestring,0,4);
		} else {
			return substr($datestring,4,2)."/".substr($datestring,6,2)."/".substr($datestring,0,4);
		}
	}
	
	function formatTimes($datestring) {
		global $userRecord;
		$timeDiff = $userRecord["timeDiff"];
		$dateDay = substr($datestring,6,2);
		$dateMonth = substr($datestring,4,2);
		$dateYear = substr($datestring,0,4);
		$dateHour = substr($datestring,8,2);
		$dateMinutes = substr($datestring,10,2);
		$datestring = date("YmdHi",mktime(($dateHour+$timeDiff),$dateMinutes,0,$dateMonth,$dateDay,$dateYear));
		return substr($datestring,8,2).":".substr($datestring,10,2);	
	}
	
	function formatTimesFront($datestring) {
		global $aRecord;
		$timeDiff = $aRecord["timeDiff"];
		$dateDay = substr($datestring,6,2);
		$dateMonth = substr($datestring,4,2);
		$dateYear = substr($datestring,0,4);
		$dateHour = substr($datestring,8,2);
		$dateMinutes = substr($datestring,10,2);
		$datestring = date("YmdHi",mktime(($dateHour+$timeDiff),$dateMinutes,0,$dateMonth,$dateDay,$dateYear));
		return substr($datestring,8,2).":".substr($datestring,10,2);		
	}

		function makeInteger($theValue) {
			return (int)$theValue;
		}
		
		function makeYesNo($theValue) {
			if ($theValue == "") { $theValue = "N"; }
			return $theValue;
		}
		
		function make01($theValue) {
			if ($theValue != "0" && $theValue != "1") { $theValue = "0"; }
			return $theValue;
		}

		function makeDecimal($theValue) {
			return (double)$theValue;
		}		
		
		function makeSafe($theValue) {
			global $cartMain,$langArray;
			$excludeSafe = false;
			$theValue = str_replace(";","",$theValue);	
			$theValue = str_replace("<?","",$theValue);
			$theValue = str_replace("?>","",$theValue);
			$theValue = htmlentities($theValue);
			return $theValue;	
		}
		
	function priceFormat($thisPrice,$currencyID) {
		global $currArray;
		$additionalSign = ($thisPrice >= 0) ? "" : "-";
		$thisPrice = abs($thisPrice);
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $currArray[$f]["decimals"];
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.$cPre.number_format($thisPrice,$cDec,$cMiddle,"").$cPost;
	}			

	function calculatePriceFormat($theBasePrice,$thisPrice,$currencyID) {
		global $currArray;
		$additionalSign = ($theBasePrice > 0) ? "" : "-";
		$theBasePrice = abs($theBasePrice);
		$thisPrice = abs($thisPrice);
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $currArray[$f]["decimals"];
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.$cPre.number_format($thisPrice,$cDec,$cMiddle,"").$cPost;
	}

	function calculatePriceFormat4($theBasePrice,$thisPrice,$currencyID) {
		global $currArray;
		$additionalSign = ($theBasePrice > 0) ? "" : "-";
		$theBasePrice = abs($theBasePrice);
		$thisPrice = abs($thisPrice);
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $currArray[$f]["decimals"];
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.$cPre.number_format($thisPrice,4,$cMiddle,"").$cPost;
	}

	function calculatePriceFormatDecs($theBasePrice,$thisPrice,$currencyID,$decs) {
		global $currArray;
		$additionalSign = ($theBasePrice > 0) ? "" : "-";
		$theBasePrice = abs($theBasePrice);
		$thisPrice = abs($thisPrice);
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $decs;
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.$cPre.number_format($thisPrice,$cDec,$cMiddle,"").$cPost;
	}
	
	/*function calculatePrice($theBasePrice,$thisPrice,$currencyID) {
		global $currArray;
		$additionalSign = ($theBasePrice > 0) ? "" : "-";
		$theBasePrice = abs($theBasePrice);
		$thisPrice = abs($thisPrice);
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $currArray[$f]["decimals"];
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.number_format($thisPrice,$cDec,".","");
	}*/

	function calculatePrice($theBasePrice,$thisPrice,$currencyID) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($theBasePrice >= 0) ? "" : "-";
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
		}
		return $additionalSign.number_format(abs($thisPrice),4,".","");
	}	

	function calculatePrice4($theBasePrice,$thisPrice,$currencyID) {
		global $currArray;
		$additionalSign = ($theBasePrice > 0) ? "" : "-";
		$theBasePrice = abs($theBasePrice);
		$thisPrice = abs($thisPrice);
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $currArray[$f]["decimals"];
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.number_format($thisPrice,4,".","");
	}

	function formatWithoutCalcPriceInCurrency($thePrice,$currencyID) {
		global $currArray,$cartID,$cartMain;
		$currRecord = 0;
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$currRecord = $f;
				break;
			}
		}
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		return $additionalSign.$currArray[$currRecord]["pretext"].number_format(abs($thePrice),$currArray[$currRecord]["decimals"],$currArray[$currRecord]["middletext"],"").$currArray[$currRecord]["posttext"];
	}		

	function roundWithoutCalcPrice($thePrice) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		return $additionalSign.number_format(abs($thePrice),$cartMain["currency"]["decimals"],".","").$cartMain["currency"]["posttext"];
	}

	function roundWithoutCalcDisplay($thePrice,$currencyID) {
		global $currArray;
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["currencyID"] == $currencyID) {
				$cDec = $currArray[$f]["decimals"];
				$cPre = $currArray[$f]["pretext"];
				$cMiddle = $currArray[$f]["middletext"];
				$cPost = $currArray[$f]["posttext"];
			}
		}
		return $additionalSign.number_format(abs($thePrice),$cDec,".","").$cPost;
	}



	function calculatePriceNoFormat($theBasePrice,$thisPrice,$currencyID) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($theBasePrice >= 0) ? "" : "-";
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
		}
		return $additionalSign.abs($thisPrice);
	}
	
	function addPadding($theField) {
		if ($theField == "") {
			return "&nbsp;";
		} else {
			return $theField;
		}
	}	

	function getSectionsList($start,&$sectionArray,$prefix = "") {
		global $dbA,$tableSections;
		$sResult = $dbA->query("select * from $tableSections where parent=$start order by title");
		$sCount = $dbA->count($sResult);
		for ($f = 0; $f < $sCount; $f++) {
			$sRecord = $dbA->fetch($sResult);
			if ($start != 0 ) {
				$thisTitle = $prefix." > ".$sRecord["title"];
			} else {
				$thisTitle = "Main";
			}
			$sectionArray[] = array($sRecord["sectionID"],$thisTitle);
			$s2Result = $dbA->query("select * from $tableSections where parent=".$sRecord["sectionID"]." order by title");
			$s2Count = $dbA->count($s2Result);
			if ($s2Count > 0) {
				getSectionsList($sRecord["sectionID"],$sectionArray,$thisTitle);
			}
		}
	}	

	function isValidCard($ccField) {
		$checkingString="01234567890 ";
		$invalidChar = false;
		$charFound = false;
		for ($g = 0; $g < strlen($ccField); $g++) {
			$charFound = false;
			for ($h = 0; $h < strlen($checkingString); $h++) {
				if (substr($ccField,$g,1) == substr($checkingString,$h,1)) {
					$charFound = true;
				}
			}
			if ($charFound == false) {
				return true;
			}
		}
		return false;
	}	
	
	dbConnect($dbA);
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$dbA->close();
?>