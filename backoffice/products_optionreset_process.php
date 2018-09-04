<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");
	
	$recordType = "Product Options";
	$linkBackLink = "products_optionreset.php";
	
	$xAction = getFORM("xAction");

	if ($xAction == "optionreset") {
		$xSelectSpecial = getFORM("xSelectSpecial");
		$xSelectNew = getFORM("xSelectNew");
		$xSelectTop = getFORM("xSelectTop");

		$optionShow = "";
		if ($xSelectSpecial == "ALL") {
			resetOption("S");
			$optionShow .= "Special Offer";
		}		
		
		if ($xSelectNew == "ALL") {
			resetOption("N");
			if ($optionShow == "") {
				$optionShow .= "New Product";
			} else {
				$optionShow .= ", New Product";
			}
		}
		
		if ($xSelectTop == "ALL") {
			resetOption("T");
			if ($optionShow == "") {
				$optionShow .= "Top Product";
			} else {
				$optionShow .= ", Top Product";
			}
		}
		if (is_array($flagArray)) {
			for ($f = 0; $f < count($flagArray); $f++) {
				if (getFORM("xFlag".$flagArray[$f]["flagID"]) == "ALL") {
					$dbA->query("update $tableProducts set flag".$flagArray[$f]["flagID"]."='N'");
					if ($optionShow == "") {
						$optionShow .= $flagArray[$f]["description"];
					} else {
						$optionShow .= ", ".$flagArray[$f]["description"];
					}
				}
			}
		}
		
		userLogActionUpdate($recordType,$optionShow);
		createProcessMessage("Options/Flags Updated!",
		"Selected Options/Flags Have Been Updated!",
		"All $optionShow options/flags have been set back to NO",
		"&lt; Back",
		"self.location.href='products_optionreset.php?".userSessionGET()."';");			
	}

	doRedirect($linkBackLink."?".userSessionGET());
	
	function resetOption($theOption) {
		global $dbA,$tableProducts,$tableProductsOptions;
		$theField = "";
		switch ($theOption) {
			case "S":
				$theField = "specialoffer";
				break;
			case "N":
				$theField = "newproduct";
				break;
			case "T":
				$theField = "topproduct";
				break;
		}
		$result = $dbA->query("select * from $tableProductsOptions where type='$theOption'");
		$rCount = $dbA->count($result);
		for ($f = 0; $f < $rCount; $f++) {
			$record = $dbA->fetch($result);
			$dbA->query("update $tableProducts set $theField='N' where productID=".$record["productID"]);
		}
		$dbA->query("delete from $tableProductsOptions where type='$theOption'");
	}	
?>
