<?php
	function affiliatesCart() {
		$thisAffiliate = "";
		$setAffiliateCookie = false;
		if (@$_COOKIE["jssAffiliate"] != "") {
			$thisAffiliate = makeSafe(@$_COOKIE["jssAffiliate"]);
		} else {
			if (getFORM("a") != "") {
				$thisAffiliate = makeSafe(getFORM("a"));
				$setAffiliateCookie = true;
			}
		}
		if ($thisAffiliate == "") { return 0; }
		$affiliateID = affiliatesValidate($thisAffiliate);
		if ($setAffiliateCookie) {
			if (retrieveOption("affiliatesCookieDays") != 0) {
				setCookie("jssAffiliate",$thisAffiliate,time()+(makeInteger(retrieveOption("affiliatesCookieDays"))*86400));
			}
		}
		return $affiliateID;
	}
	
	function affiliatesValidate($affiliateUsername) {
		global $dbA,$tableAffiliates;
		$result = $dbA->query("select * from $tableAffiliates where username='$affiliateUsername'");
		if ($dbA->count($result) != 1) { return 0; }
		$affiliateRecord = $dbA->fetch($result);
		if ($affiliateRecord["status"] != "L") { return 0; }
		affiliatesCreateStats($affiliateRecord["affiliateID"]);
		return $affiliateRecord["affiliateID"];
	}
	
	function affiliatesCreateStats($affiliateID) {
		global $dbA,$tableAffiliatesStats;
		$rArray = "";
		$rArray[] = array("affiliateID",$affiliateID,"N");
		$rArray[] = array("datetime",date("YmdHis"),"S");
		$theURL = @$_SERVER["HTTP_REFERER"];
		if ($theURL == "") { $theURL = "Unknown"; }
		$rArray[] = array("url",$theURL,"S");
		$dbA->insertRecord($tableAffiliatesStats,$rArray,0);
	}
	
	function affiliatesCreatePayment($theOrder) {
		global $dbA,$tableAffiliatesTrans,$tableAffiliates,$tableAffiliatesGroups,$tableCurrencies;
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$total4Commission = $theOrder["goodsTotal"] - $theOrder["discountTotal"] - $theOrder["giftCertTotal"];
		if (retrieveOption("affiliatesPaymentShipping") == 1) { $total4Commission = $total4Commission + $theOrder["shippingTotal"]; }
		if (retrieveOption("affiliatesPaymentTax") == 1) { $total4Commission = $total4Commission + $theOrder["taxTotal"]; }
		$forceNotAuth = false;
		if ($theOrder["currencyID"] != 1) {
			for ($f = 0; $f < count($currArray); $f++) {
				if ($theOrder["currencyID"] == $currArray[$f]["currencyID"]) {
					if ($currArray[$f]["useexchangerate"] == "N") {
						$forceNotAuth = true;
					} else {
						$total4Commission = $total4Commission / $currArray[$f]["exchangerate"];
					}
				}
			}
		}
		$theStatus = "1";
		$result = $dbA->query("select * from $tableAffiliates where affiliateID=".$theOrder["affiliateID"]);
		if ($dbA->count($result) != 1) { return false; }
		$afRecord = $dbA->fetch($result);
		$result = $dbA->query("select * from $tableAffiliatesGroups where groupID=".$afRecord["groupID"]);
		if ($dbA->count($result) != 1) { return false; }
		$agRecord = $dbA->fetch($result);
		switch ($agRecord["commissionType"]) {
			case "P":
				$theCommission = $total4Commission * ($agRecord["commission"] / 100);
				break;
			case "F":
				$theCommission = $agRecord["commission"];
				$forceNotAuth = false;
				break;
		}
		$theCommission = number_format($theCommission,$currArray[0]["decimals"],".","");
		if (retrieveOption("affiliatesCreatePaymentStatus") == "NOTAUTH" || $forceNotAuth) {
			$theStatus = "0";
		}
		$tArray = null;
		$tArray[] = array("affiliateID",$theOrder["affiliateID"],"N");
		$tArray[] = array("datetime",date("YmdHis"),"S");
		$tArray[] = array("status",$theStatus,"S");
		$tArray[] = array("type","C","S");
		$tArray[] = array("reference",$theOrder["orderID"]+retrieveOption("orderNumberOffset"),"S");
		$tArray[] = array("amount",$theCommission,"D");
		$tArray[] = array("secondtier","N","S");
		$dbA->insertRecord($tableAffiliatesTrans,$tArray,0);
		if ($afRecord["parentID"] != 0 && retrieveOption("affiliatesAllow2Tier") == 1) {
			$secondAff = $afRecord["parentID"];
			//this maybe a 2nd tier commission here
			$result = $dbA->query("select * from $tableAffiliates where affiliateID=".$secondAff);
			if ($dbA->count($result) != 1) { return false; }
			$afRecord = $dbA->fetch($result);
			$result = $dbA->query("select * from $tableAffiliatesGroups where groupID=".$afRecord["groupID"]);
			if ($dbA->count($result) != 1) { return false; }
			$agRecord = $dbA->fetch($result);
			switch ($agRecord["commissionType2"]) {
				case "P":
					$theCommission = $total4Commission * ($agRecord["commission2"] / 100);
					break;
				case "F":
					$theCommission = $agRecord["commission2"];
					$forceNotAuth = false;
					break;
			}
			$theCommission = number_format($theCommission,$currArray[0]["decimals"],".","");
			if (retrieveOption("affiliatesCreatePaymentStatus") == "NOTAUTH" || $forceNotAuth) {
				$theStatus = "0";
			}
			$tArray = null;
			$tArray[] = array("affiliateID",$secondAff,"N");
			$tArray[] = array("datetime",date("YmdHis"),"S");
			$tArray[] = array("status",$theStatus,"S");
			$tArray[] = array("type","C","S");
			$tArray[] = array("reference",$theOrder["orderID"]+retrieveOption("orderNumberOffset"),"S");
			$tArray[] = array("amount",$theCommission,"D");
			$tArray[] = array("secondtier","Y","S");
			$dbA->insertRecord($tableAffiliatesTrans,$tArray,0);
		}
	}
?>
