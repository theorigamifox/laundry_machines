<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
	
	$recordType = "Pricing";
	$linkBackLink = "products_pricechange.php";
	
	$xAction = getFORM("xAction");

	if ($xAction == "pricechange") {
		$xSelection = getFORM("xSelection");
		$xCategoryID = getFORM("xCategoryID");
		$xSectionID = getFORM("xSectionID");
		$xPercent = makeDecimal(getFORM("xPercent"));
		$xPercent2 = makeDecimal(getFORM("xPercent"));
		$rArray = "";
		if ($xPercent >= 0) {
			$xPercent = 1 + ($xPercent / 100);
		} else {
			$xPercent = 1 - (abs($xPercent) / 100);
		}
		$pricePart = "";
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["useexchangerate"] != "Y") {
				if ($pricePart == "") {
					$pricePart = "price".$currArray[$f]["currencyID"]."=price".$currArray[$f]["currencyID"]."*$xPercent";
				} else {
					$pricePart .= ", price".$currArray[$f]["currencyID"]."=price".$currArray[$f]["currencyID"]."*$xPercent";
				}
			}
		}
		switch ($xSelection) {
			case "ALL":
				$theQuery = "update $tableProducts set $pricePart";
				break;
			case "CATEGORY":
				$theQuery = "update $tableProducts set $pricePart where categories=$xCategoryID";
				break;
		}
		$result = $dbA->query($theQuery);
		userLogActionUpdate($recordType,"All");
		createProcessMessage("Prices Updated!",
		"Selected Products Have Been Updated!",
		"The products you selected on the previous screen have now had their<br>prices updated by $xPercent2"."%",
		"&lt; Back",
		"self.location.href='products_pricechange.php?".userSessionGET()."';");			
	}
	doRedirect($linkBackLink."?".userSessionGET());
?>
