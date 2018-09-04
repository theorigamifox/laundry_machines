<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Shipping Zones";
	$tableName = $tableZones;
	$linkBackLink = "shipping_zones.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xCountryList = getFORM("xCountryList");
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("countyList",getFORM("xCountyList"),"S");
			$dbA->insertRecord($tableName,$rArray);
			$xZoneID = $dbA->lastID();
			$delList = split(";",$xCountryList);
			for ($f = 0; $f < count($delList); $f++) {
				if ($delList[$f] != "") {
					$dbA->query("update $tableCountries set zoneID=$xZoneID where countryID=".$delList[$f]);
				}
			}
			userLogActionAdd($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xZoneID = getFORM("xZoneID");
		if (!$dbA->doesIDExist($tableName,"zoneID",$xZoneID,$uRecord)) {
			setupProcessMessage($recordType,$xZoneID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"zoneID",$xZoneID);
			$dbA->query("update $tableCountries set zoneID=0 where zoneID=$xZoneID");
			$dbA->query("delete from $tableShippingRates where zoneID=$xZoneID");
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xCountryList = getFORM("xCountryList");
		$xCountryDeletedList = getFORM("xCountryDeletedList");
		$xZoneID = getFORM("xZoneID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"zoneID",$xZoneID,$uRecord)) {
			setupProcessMessage($recordType,$xName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"zoneID",$xZoneID,"name",$xName)) {
				setupProcessMessage($recordType,$xName,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("name",$xName,"S");
			$rArray[] = array("countyList",getFORM("xCountyList"),"S");
			$dbA->updateRecord($tableName,"zoneID=$xZoneID",$rArray,0);
			
			$delList = split(";",$xCountryDeletedList);
			for ($f = 0; $f < count($delList); $f++) {
				if ($delList[$f] != "") {
					$dbA->query("update $tableCountries set zoneID=0 where countryID=".$delList[$f]);
				}
			}
			$delList = split(";",$xCountryList);
			for ($f = 0; $f < count($delList); $f++) {
				if ($delList[$f] != "") {
					$dbA->query("update $tableCountries set zoneID=$xZoneID where countryID=".$delList[$f]);
				}
			}
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	}
?>
