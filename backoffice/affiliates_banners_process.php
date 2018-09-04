<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Affiliate Banner";
	$tableName = $tableAffiliatesBanners;
	$linkBackLink = "affiliates_banners.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",$xName,"S");			
			$rArray[] = array("width",getFORM("xWidth"),"N");
			$rArray[] = array("height",getFORM("xHeight"),"N");
			$rArray[] = array("description",getFORM("xDescription"),"S");
			$rArray[] = array("groups",getFORM("xGroups"),"S");
			addImageUpdate("xFilename","filename","banners/",$rArray);	
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);		
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xBannerID = getFORM("xBannerID");
		if (!$dbA->doesIDExist($tableName,"bannerID",$xBannerID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"bannerID",$xBannerID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xBannerID = getFORM("xBannerID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"bannerID",$xBannerID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"bannerID",$xBannerID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("name",$xName,"S");			
			$rArray[] = array("width",getFORM("xWidth"),"N");
			$rArray[] = array("height",getFORM("xHeight"),"N");
			$rArray[] = array("description",getFORM("xDescription"),"S");
			$rArray[] = array("groups",getFORM("xGroups"),"S");
			addImageUpdate("xFilename","filename","banners/",$rArray);	
			$dbA->updateRecord($tableName,"bannerID=$xBannerID",$rArray);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	
	}
?>
