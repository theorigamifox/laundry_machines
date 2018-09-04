<?php

	include("routines/cartOperations.php");
	include("routines/productOperations.php");
	dbConnect($dbA);
	
	grabAllOptions();
	
	if (retrieveOption("affiliatesActivated") == 1) {
		include("routines/affiliateTracking.php");
	}
	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");
	
	if (retrieveOption("currencyLiveRates") != "off") {
		$currencyService = retrieveOption("currencyLiveRates");
		$currencyLastCheck = retrieveOption("currencyLastCheck");
		$serviceInfo = explode("|",retrieveOption("currencyLiveRatesInfo"));
		switch ($currencyService) {
			case "Worldpay":
				if (substr($currencyLastCheck,0,8) != date("Ymd")) {
					//we need to check for currencies
					$ch = curl_init("https://select.worldpay.com/wcc/info");
					curl_setopt($ch, CURLOPT_HEADER, 1);
					curl_setopt($ch,CURLOPT_GET,1);
					curl_setopt($ch,CURLOPT_TIMEOUT,15);
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
					$data = "op=rates" . rawurlencode($this->key) . "&" . "instId=" . @$serviceInfo[0] . "&" . "infoPW=" . @$serviceInfo[1];
					curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
					$result=curl_exec ($ch);
					curl_close ($ch);				
					$bufferLines = explode("\n",$result);
					if (count($currArray) > 0) {
						$baseCurrency = $currArray[0]["code"];
						$oneUpdated = false;
						for ($f = 0; $f < count($bufferLines); $f++) {
							if (substr($bufferLines[$f],0,4) == $baseCurrency."_") {
								$thisLine = $bufferLines[$f];
								$splitOne = explode("=",$thisLine);
								$currSplit = explode("_",@$splitOne[0]);
								for ($g = 1; $g < count($currArray); $g++) {
									if ($currArray[$g]["code"] == @$currSplit[1]) {
										$oneUpdated = true;
										$dbA->query("update $tableCurrencies set exchangerate=".@$splitOne[1]." where currencyID=".$currArray[$g]["currencyID"]);
									}
								}
							}
						}
						if ($oneUpdated) {
							updateOption("currencyLastCheck",date("Ymd"));
						}
					}
				}
				break;
		}
	}
	
	if (spiderDetect()) {
		$cartCookie = true;
		$cartMain["cartID"] = "";
		$cartMain["currencyID"] = 1;
		$cartMain["country"] = retrieveOption("defaultCountry");
		$cartMain["county"] = "";
		if (@$forceDefaultTemplates != "") {
			$cartMain["templateSet"] = $forceDefaultTemplates;
		} else {
			$cartMain["templateSet"] = "templates/";
		}
		$cartMain["accTypeID"] = 1;
		$cartMain["rID"] = "";
		$cartMain["orderInfo"] = "";
		$cartMain["templateForceCompile"] = 0;
		$cartMain["createtime"] = 0;
		$cartMain["languageID"] = 1;
		$cartMain["products"] = null;
		$customerMain["loggedin"] = "N"; 
		$cartContents = null;
		$cartID="";
		$jssCustomer = "";
		for ($f = 0; $f < count($currArray); $f++) {
			if ($cartMain["currencyID"] == $currArray[$f]["currencyID"]) {
				$cartMain["currency"]["currencyID"] = $currArray[$f]["currencyID"];
				$cartMain["currency"]["code"] = $currArray[$f]["code"];
				$cartMain["currency"]["name"] = $currArray[$f]["name"];
				$cartMain["currency"]["decimals"] = $currArray[$f]["decimals"];
				$cartMain["currency"]["pretext"] = $currArray[$f]["pretext"];
				$cartMain["currency"]["middletext"] = $currArray[$f]["middletext"];
				$cartMain["currency"]["posttext"] = $currArray[$f]["posttext"];
				$cartMain["currency"]["useexchangerate"] = $currArray[$f]["useexchangerate"];
				$cartMain["currency"]["exchangerate"] = $currArray[$f]["exchangerate"];
				$cartMain["currency"]["checkout"] = $currArray[$f]["checkout"];
			}
		}
	} else {
		$cartCookie = false;
		$cartID = "";
		$jssCart = "";
		$customerMain = null;
		$jssCustomer = @$_COOKIE["jssCustomer"];
		if (getFORM("xForce") == "Y") {
			@$_COOKIE[retrieveOption("cookieName")] = "";
		} else {
			$cartCookie = false;
			$xBits = explode(".php/",@$_SERVER["REQUEST_URI"]);
			$xOptions = explode("/",@$xBits[1]);
			if (count($xOptions) > 0) {
				if ($xOptions[count($xOptions)-1] != "" and strlen($xOptions[count($xOptions)-1]) == 32) {
					$cartID = $xOptions[count($xOptions)-1];
					//$cartCookie = true;
				}
			}
			if (makeSafe(getFORM("jssCart")) != "") {
				$cartID = makeSafe(getFORM("jssCart"));
				//$cartCookie = true;
			}
			//if ($cartID == "") {
				if (array_key_exists(retrieveOption("cookieName"),@$_COOKIE) && @$_COOKIE[retrieveOption("cookieName")] != "") {
					$cookieCart = makeSafe($_COOKIE[retrieveOption("cookieName")]);
					if ($cookieCart != $cartID && $cartID != "") {
						//The cookie is different to the URL cart ID but we have to be careful because of switching to the SSL
					}
					if ($cartID != "" && $cartID == $cookieCart) {
						$cartCookie = true;
					}
					if ($cartID == "" & $cookieCart != "") {
						$cartID = $cookieCart;
						$cartCookie = true;
					}
				} else {
					$cartCookie = false;
				}
			//}
		}
		$newCart = FALSE;
		if ($cartID == "") {	
			$cartID = createNewCart();
			$newCart = TRUE;
		}
		if (makeSafe(getFORM("xTemplates")) != "") {
			$dbA->query("update $tableCarts set templateSet='".makeSafe(getFORM("xTemplates"))."' where cartID=\"$cartID\"");
			//$rArray[] = array("templateSet",makeSafe(getFORM("xTemplates")),"S");	
		} 
		$xCmd = getFORM("xCmd");
		switch ($xCmd) {
			case "cc":	//change currency
				$xCur = makeInteger(getFORM("xCur"));
				for ($f = 0; $f < count($currArray); $f++) {
					if ($currArray[$f]["currencyID"] == $xCur && $currArray[$f]["visible"] == "Y") {
						$dbA->query("update $tableCarts set currencyID=$xCur where cartID=\"$cartID\"");
						break;
					}
				}
				break;
			case "cl":	//change language
				$xLang = makeInteger(getFORM("xLang"));
				for ($f = 0; $f < count($langArray); $f++) {
					if ($langArray[$f]["languageID"] == $xLang && $langArray[$f]["visible"] == "Y") {
						$dbA->query("update $tableCarts set languageID=$xLang where cartID=\"$cartID\"");
						break;
					}
				}
				break;				
		}
		if (getFORM("language") != "") {
			$xCL = makeInteger(getFORM("language"));
			for ($f = 0; $f < count($langArray); $f++) {
				if ($langArray[$f]["languageID"] == $xCL && $langArray[$f]["visible"] == "Y") {
					$dbA->query("update $tableCarts set languageID=$xCL where cartID=\"$cartID\"");
					break;
				}
			}
		}
		if (getFORM("currency") != "") {
			$xCur = makeInteger(getFORM("currency"));
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["currencyID"] == $xCur && $currArray[$f]["visible"] == "Y") {
					$dbA->query("update $tableCarts set currencyID=$xCur where cartID=\"$cartID\"");
					break;
				}
			}
		}
		
		$cresult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
		if ($dbA->count($cresult) == 0) {
			$cartID = createNewCart();
			$newCart = TRUE;
			$cresult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
			$cartMain = $dbA->fetch($cresult);
		} else {
			$cartMain = $dbA->fetch($cresult);
			$thisRefer = @$_SERVER["HTTP_REFERER"];
			if (!$newCart) {
				//check that the referrer is actually from this site, otherwise it's probably come from elsewhere
				//if (substr($thisRefer,0,strlen($jssStoreWebDirHTTP)) != $jssStoreWebDirHTTP && substr($thisRefer,0,strlen($jssStoreWebDirHTTPS)) != $jssStoreWebDirHTTPS && $cartCookie == FALSE) {
					//the referrer is not valid, so we need to set a new cart
				//	$cartID = createNewCart();
				//	$cresult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
				//	$cartMain = $dbA->fetch($cresult);
				//}
			}
			if ($cartCookie == false) {
				if (time() - $cartMain["createtime"] > 18000) {
					//this is sombody else's cart i think? well it's 5 hours old and there was no cookie, so it looks likely.
					$cartID = createNewCart();
					$newCart = TRUE;
					$cresult = $dbA->query("select * from $tableCarts where cartID=\"$cartID\"");
					$cartMain = $dbA->fetch($cresult);
				}
			} else {
				$thisTime = time();
				$dbA->query("update $tableCarts set createtime=$thisTime where cartID=\"$cartID\"");
			}
		}
		$jssCustomer = $cartMain["customerID"];
		$cresult = $dbA->query("select * from $tableCustomers where customerID=\"$jssCustomer\"");
		if ($dbA->count($cresult) > 0) {
			$customerMain = $dbA->fetch($cresult);
			$customerMain["loggedin"] = ($customerMain["rID"] == $cartMain["rID"] && $cartMain["rID"] != "") ? "Y" : "N";
		} else {
			$customerMain["loggedin"] = "N";
		}
		if (retrieveOption("affiliatesActivated") == 1) {
			$jssAffiliateLogin = $cartMain["affiliateLoginID"];
			$aresult = $dbA->query("select * from $tableAffiliates where affiliateID=$jssAffiliateLogin");
			if ($dbA->count($aresult) > 0) {
				$affiliateMain = $dbA->fetch($aresult);
				$affiliateMain["loggedin"] = ($affiliateMain["arID"] == $cartMain["arID"] && $cartMain["arID"] != "") ? "Y" : "N";
			} else {
				$affiliateMain["loggedin"] = "N";
			}
		}
		$xTFC = makeInteger(getFORM("xTFC"));
		if ($xTFC != $cartMain["templateForceCompile"] && ($xTFC == 0 || $xTFC == 1)) {
			$dbA->query("update $tableCarts set templateForceCompile=$xTFC where cartID=\"$cartID\"");
			$cartMain["templateForceCompile"]=$xTFC;
		}
		$xRTU = makeInteger(getFORM("xRTU"));
		if ($xRTU == 1) { $xRTU = 2; }
		if ($xRTU != $cartMain["templateForceCompile"] && ($xRTU == 0 || $xRTU == 2)) {
			$dbA->query("update $tableCarts set templateForceCompile=$xRTU where cartID=\"$cartID\"");
			$cartMain["templateForceCompile"]=$xRTU;
		}
		$langBit = "";
		for ($f = 0; $f < count($langArray); $f++) {
			if ($langArray[$f]["languageID"] != 1) {
				$langBit .=", $tableProducts.name".$langArray[$f]["languageID"];
			}
		}
		cartRetrieveCart();

		
		setcookie(retrieveOption("cookieName"),$cartID,time()+(makeDecimal(retrieveOption("cookieTime"))*60*60),"/");	
	}
	$dbA->close();
			
	function configureURL($theURL) {
		global $cartID,$cartCookie,$jssStoreWebDirHTTP,$jssStoreWebDirHTTPS;
		$onSecure = false;
		if (substr($theURL,0,8) == "customer" || substr($theURL,0,8) == "checkout" || substr($theURL,0,8) == "giftcert" || substr($theURL,0,10) == "affiliates" || substr($theURL,0,7) == "process") {
			$onSecure = true;
		}
		if ($cartCookie != true || $onSecure == true) {
			$questionPos = strpos($theURL,"?");
			if ($cartID != "") {
				if (retrieveOption("useRewriteURLs") == 0 || (substr($theURL,0,7) != "product" && substr($theURL,0,7) != "section")) {
					if ($questionPos === false) {
						$theURL .= "?jssCart=$cartID";
					} else {
						$theURL .= "&jssCart=$cartID";
					}
				} else {
					$theURL .= "/$cartID";
				}
			}
		}
		if ($onSecure) {
			$theURL = $jssStoreWebDirHTTPS.$theURL;
		} else {
			$theURL = $jssStoreWebDirHTTP.$theURL;
		}
		return $theURL;
	}
	
	function findBaseDir($thePage) {
		global $jssStoreWebDirHTTP,$jssStoreWebDirHTTPS;
		if (substr($thePage,0,8) == "customer" || substr($thePage,0,8) == "checkout" || substr($thePage,0,8) == "giftcert" || substr($thePage,0,10) == "affiliates" || substr($thePage,0,7) == "process") {
			return $jssStoreWebDirHTTPS;
		} else {
			return $jssStoreWebDirHTTP;
		}
	}
	
	function createSectionLink($theSection,$xPage = 0) {
		if (retrieveOption("useRewriteURLs") == 0) {
			$theURL = "section.php?xSec=$theSection";
			if ($xPage != 0) {
				$theURL .= "&xPage=$xPage";
			}
			return configureURL($theURL);
		} else {
			return configureURL("section.php/$theSection/$xPage");
		}
	}
	
	function createProductLink($theProduct,$theSection=0,$xCmd = "") {
		if (retrieveOption("useRewriteURLs") == 0) {
			$theURL = "product.php?xProd=$theProduct";
			if ($theSection != 0) {
				$theURL .= "&xSec=$theSection";
			}
			if ($xCmd != "") {
				$theURL .= "&xCmd=$xCmd";
			}
			return configureURL($theURL);
		} else {
			return configureURL("product.php/$theProduct/$theSection/$xCmd");
		}
	}	
	
	function addToRecentlyViewed($recType,$theID) {
		global $dbA,$tableCarts,$tableCustomers,$cartMain,$customerMain;
		if (retrieveOption("recentViewActivated") == 0) { return false; }
		switch ($recType) {
			case "product":
				$recentMax = makeInteger(retrieveOption("recentViewProducts"));
				if ($recentMax == 0) { return false; }
				$currentList = explode(";",@$cartMain["productHistory"]);
				$newList[] = $theID;
				$foundMe = false;
				for ($f = 0; $f < count($currentList); $f++) {
					if (count($newList) == $recentMax) { break; }
					if ($currentList[$f] != $theID && makeInteger($currentList[$f]) > 1) {
						$newList[] = $currentList[$f];
					}
				}
				$newListShow = "";
				for ($f = 0; $f < count($newList); $f++) {
					$newListShow .= $newList[$f].";";
				}
				$dbA->query("update $tableCarts set productHistory = '$newListShow' where cartID='".$cartMain["cartID"]."'");
				/*if ($customerMain["loggedin"] == "Y") {
					$dbA->query("update $tableCustomers set productHistory = '$newListShow' where cartID='".$cartMain["cartID"]."'");
				}*/
				break;
			case "section":
				$recentMax = makeInteger(retrieveOption("recentViewSections"));
				if ($recentMax == 0) { return false; }
				$currentList = explode(";",@$cartMain["sectionHistory"]);
				$newList[] = $theID;
				$foundMe = false;
				for ($f = 0; $f < count($currentList); $f++) {
					if (count($newList) == $recentMax) { break; }
					if ($currentList[$f] != $theID && makeInteger($currentList[$f]) > 1) {
						$newList[] = $currentList[$f];
					}
				}
				$newListShow = "";
				for ($f = 0; $f < count($newList); $f++) {
					$newListShow .= $newList[$f].";";
				}
				$dbA->query("update $tableCarts set sectionHistory = '$newListShow' where cartID='".$cartMain["cartID"]."'");
				/*if ($customerMain["loggedin"] == "Y") {
					$dbA->query("update $tableCustomers set sectionHistory = '$newListShow' where cartID='".$cartMain["cartID"]."'");
				}*/
				break;				
		}
	}	
?>