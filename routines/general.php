<?php
	function findCorrectLanguage($theRecord,$theField) {
		global $dbA,$cartMain;
		if ($cartMain["languageID"] == 1) {
			return $theRecord[$theField];
		}
		if (array_key_exists($theField.$cartMain["languageID"],$theRecord)) {
			if (chop(@$theRecord[$theField.$cartMain["languageID"]]) == "") {
				return $theRecord[$theField];
			} else {
				return $theRecord[$theField.$cartMain["languageID"]];
			}
		} else {
			return $theRecord[$theField];
		}
	}

	function findCorrectLanguageExtraField($theRecord,$theField) {
		global $dbA,$cartMain;
		if ($cartMain["languageID"] == 1) {
			return $theRecord[$theField];
		}
		if (array_key_exists($theField."_".$cartMain["languageID"],$theRecord)) {
			if (chop(@$theRecord[$theField."_".$cartMain["languageID"]]) == "") {
				return $theRecord[$theField];
			} else {
				return $theRecord[$theField."_".$cartMain["languageID"]];
			}
		} else {
			return $theRecord[$theField];
		}
	}

	function changeKeysCase($an_array) {
		if(is_array($an_array)) {
      		foreach($an_array as $key => $value) {
        		$new_array[strtolower($key)] = $value;
        		$new_array[$key] = $value;
        	}
       		return $new_array;
   		} else {
      		return $an_array;
      	}
	}
	
	function getmicrotime($theTime){ 
	   list($usec, $sec) = explode(" ",$theTime); 
	   return ((float)$usec + (float)$sec); 
	} 
	
	function getRootSection() {
		global $dbA,$tableSections;
		$record = retrieveSections("select * from $tableSections where sectionID=1");
		if (is_array($record)) {
			return array("title"=>$record["title"],"link"=>configureURL("index.php"));
		} else {
			return array("title"=>"","link"=>"");
		}
	}

	function retrieveSections($theQuery,$type="s") {
		global $dbA;
		$result = $dbA->query($theQuery);
		$uCount = $dbA->count($result);
		if ($uCount > 0) {
			$secArray = null;
			for ($f = 0; $f < $uCount; $f++) {
				$secRecord = $dbA->fetch($result);
				$secRecord["title"] = findCorrectLanguage($secRecord,"title");
				$secRecord["shortDescription"] = findCorrectLanguage($secRecord,"shortDescription");
				$secRecord["fullDescription"] = findCorrectLanguage($secRecord,"fullDescription");
				if (retrieveOption("convertToBR") == 1) {
					$secRecord["fullDescription"] = str_replace("\r\n","<br>",$secRecord["fullDescription"]);
				}
				if ($secRecord["sectionID"] == 1) {
					$secRecord["link"] = configureURL("index.php");	
				} else {
					$secRecord["link"] = createSectionLink($secRecord["sectionID"]);
				}
				if ($type=="s") { return $secRecord; }
				$secArray[] = $secRecord;
			}
			return $secArray;
		} else {
			return false;
		}
	}
	
	function generateFullSectionPath($xSec,&$rootsectionID) {
		global $tableSections;
		$reachedRoot = false;
		$thisSectionID = $xSec;
		$pathReverse = "";
		$rootsectionID = $thisSectionID;
		while ($reachedRoot == false) {
			$sRecord = retrieveSections("select * from $tableSections where sectionID=$thisSectionID","s");
			$thisSectionID = $sRecord["parent"];
			if ($thisSectionID == 0) {
				$reachedRoot = true;
				$thisPath = array("title"=>$sRecord["title"],"link"=>configureURL("index.php"),"sectionID"=>$sRecord["sectionID"]);
			} else {
				$thisPath = array("title"=>$sRecord["title"],"link"=>createSectionLink($sRecord["sectionID"]),"sectionID"=>$sRecord["sectionID"]);
				if ($thisSectionID != 1) {
					$rootsectionID = $thisSectionID;
				}
			}	
			if ($sRecord["visible"]!="N") { $pathReverse[] = $thisPath; }
		}
		$pathCorrect = "";
		for ($f = count($pathReverse)-1; $f >= 0; $f--) {
			$thisPath = array("title"=>$pathReverse[$f]["title"],"link"=>$pathReverse[$f]["link"]);
			$pathCorrect[] = $thisPath;
		}
		return $pathCorrect;
	}	
		
	function formatPrice($thePrice) {
		global $currArray,$cartID,$cartMain;
		$thePrice = ($cartMain["currency"]["useexchangerate"] == "Y") ? $thePrice = $thePrice * $cartMain["currency"]["exchangerate"] : $thePrice;
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		return $additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"];
	}

	function formatPriceWithSpan($thePrice,$xProd,$spanName) {
		global $currArray,$cartID,$cartMain,$xBrowserLong,$xBrowserShort;
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		if ($xBrowserLong == "NS4") {
			return "<span id=\"".$spanName."Layer$xProd\" name=\"".$spanName."Layer$xProd\">".$additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"]."</span>";
		} else {
			if ($xBrowserShort == "FIREFOX" || $xBrowserShort == "SAFARI") {
				return "<LAYER id=\"".$spanName."Layer$xProd\" name=\"".$spanName."Layer$xProd\" style=\"position:relative;\"><span id=\"".$spanName."Span$xProd\">".$additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"]."</span></layer>";
			} else {
				return "<LAYER id=\"".$spanName."Layer$xProd\" name=\"".$spanName."Layer$xProd\" style=\"position:absolute;\"><span id=\"".$spanName."Span$xProd\">".$additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"]."</span></layer>";
			}
		}
	}	
	
	function calculatePrice($theBasePrice,$thisPrice,$currencyID) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($theBasePrice >= 0) ? "" : "-";
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
		}
		return $additionalSign.number_format(abs($thisPrice),4,".","");
	}	
	
	function calculatePriceNoFormat($theBasePrice,$thisPrice,$currencyID) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($theBasePrice >= 0) ? "" : "-";
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
		}
		return $additionalSign.abs($thisPrice);
	}
	
	function calculatePriceFormat($theBasePrice,$thisPrice,$currencyID) {
		global $currArray,$cartID,$cartMain;
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
		}
		$additionalSign = ($theBasePrice >= 0) ? "" : "-";
		return $additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thisPrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"];
	}	
	
	function calculatePriceFormatDecs($theBasePrice,$thisPrice,$currencyID,$decs) {
		global $currArray,$cartID,$cartMain;
		for ($f = 0; $f < count($currArray); $f++) {
			$thisPrice = ($currArray[$f]["currencyID"] == $currencyID && $currArray[$f]["useexchangerate"] == "Y") ? $thisPrice = $theBasePrice * $currArray[$f]["exchangerate"] : $thisPrice;
		}
		$additionalSign = ($theBasePrice >= 0) ? "" : "-";
		return $additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thisPrice),$decs,$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"];
	}
	
	function formatWithoutCalcPrice($thePrice) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($thePrice >= 0) ? "" : "-";
		return $additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"];
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
		return $additionalSign.number_format(abs($thePrice),$cDec,$cMiddle,"").$cPost;
	}

	function formatWithoutCalcDifference($thePrice) {
		global $currArray,$cartID,$cartMain;
		$additionalSign = ($thePrice >= 0) ? "+" : "";
		return $additionalSign.$cartMain["currency"]["pretext"].number_format(abs($thePrice),$cartMain["currency"]["decimals"],$cartMain["currency"]["middletext"],"").$cartMain["currency"]["posttext"];
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
	
	function calculatePriceInBase($thePrice) {
		global $currArray,$cartID,$cartMain;
		if ($thePrice > 0) {
			$thePrice = $thePrice / $cartMain["currency"]["exchangerate"];
		}
		return $thePrice;
	}
	
	function returnCurrencyDetails($theCurrencyID) {
		global $currArray;
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["currencyID"] == $theCurrencyID) {
				return $currArray[$f];
			}
		}
		return "";
	}

	function retrieveGatewayOptions($theGateway) {
		global $dbA,$tableGatewayConfigs;
		$result = $dbA->query("select * from $tableGatewayConfigs where gateway='$theGateway'");
		$count = $dbA->count($result);
		$gatewayOptions = "";
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			$gatewayOptions[$record["fieldname"]] = $record["fieldvalue"];
		}
		return $gatewayOptions;
	}
	
	function updateGatewayOption($theGateway,$theOption,$theValue) {
		global $dbA,$tableGatewayConfigs;
		$rArray[] = array("fieldvalue",$theValue,"S");
		$dbA->updateRecord($tableGatewayConfigs,"gateway=$theGateway and fieldname='$theOption'",$rArray,0);
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

	function doRedirect_JavaScript($theURL) {
		global $dbA;
		$dbA->close();
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<link rel="stylesheet" href="resources/shop.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<script>
	location.replace("<?php print $theURL; ?>")
	//self.location.href="<?php print $theURL; ?>";
</script>
</body>
</html>
<?php
	}

	function doRedirect($theURL) {
		global $dbA;
		$dbA->close();
		header("Location: $theURL"); 
		exit;
	}

	
	function moduleError($theModule) {
		global $dbA;
		@$dbA->close();
?>
<HTML>
<HEAD>
<TITLE>JShop Server: Module Error</TITLE>
<link rel="stylesheet" href="resources/shop.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<font class="normaltext"><B>JShop Server has encountered a configuration error. Please see below for details.</b><p>
<b>Module Error:</b> The needed module <font class="text-small-red"><b><?php print $theModule; ?></b></font> is not enabled. Please see General Settings to activate it or remove links to this module in the templates.
</body>
</html>
<?php
		exit;
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
			$currentLanguage = makeInteger($cartMain["languageID"]);
			for ($f = 0; $f < count($langArray); $f++) {
				if ($currentLanguage = $langArray[$f]["languageID"] && $langArray[$f]["doubleByte"] == "Y") {
					$excludeSafe = true;
					break;
				}
			}
			if ($excludeSafe == false) {
				$theValue = str_replace(";","",$theValue);	
				$theValue = str_replace("<?","",$theValue);
				$theValue = str_replace("?>","",$theValue);
			}
			$theValue = htmlentities($theValue);
			return $theValue;	
		}
?>
