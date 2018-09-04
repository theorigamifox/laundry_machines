<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Countries";
	$tableName = $tableCountries;
	$linkBackButton = "COUNTRIES";
	$linkBackLink = "countries_listing.php";

	$xAction=getFORM("xAction");
		
	if ($xAction == "reorder") {
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tableCountries set position=$g where countryID=$newOrderBits[$f]");
		}
		userLogAction("Sorted","Countries","All");
		doRedirect("reorder.php?xType=countries&".userSessionGET());
	}	
	
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",$xName,"S");
			$rArray[] = array("isocode",getFORM("xIsocode"),"S");
			$rArray[] = array("isonumber",getFORM("xIsonumber"),"S");
			$rArray[] = array("visible",getFORM("xVisible"),"YN");
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);		
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xCountryID = getFORM("xCountryID");
		if (!$dbA->doesIDExist($tableName,"countryID",$xCountryID,$uRecord)) {
			setupProcessMessage($recordType,$xCountryID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"countryID",$xCountryID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}	
	if ($xAction == "update") {
		$xCountryID = getFORM("xCountryID");
		$xName = chop(getFORM("xName"));
		if (!$dbA->doesIDExist($tableName,"countryID",$xCountryID,$uRecord)) {
			setupProcessMessage($recordType,$xName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"countryID",$xCountryID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("name",$xName,"S");
			$rArray[] = array("isocode",getFORM("xIsocode"),"S");
			$rArray[] = array("isonumber",getFORM("xIsonumber"),"S");
			$rArray[] = array("visible",getFORM("xVisible"),"YN");
			$dbA->updateRecord($tableName,"countryID=$xCountryID",$rArray);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}		
?>
