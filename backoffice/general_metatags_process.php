<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Meta Tags";
	$tableName = $tableGeneral;
	$linkBackButton = "META TAG DETAILS";
	$linkBackLink = "general_metatags.php";

	$xAction=getFORM("xAction");
	if ($xAction == "update") {
		updateOption("overrideAllMeta",make01(getFORM("xOverrideAllMeta")));
		$rArray[] = array("metaAuthor",getFORM("xMetaAuthor"),"S");			
		$rArray[] = array("metaDescription",getFORM("xMetaDescription"),"S");	
		$rArray[] = array("metaKeywords",getFORM("xMetaKeywords"),"S");										
		$dbA->updateRecord($tableName,"",$rArray);
		userLogActionUpdate($recordType,"Details");
		doRedirect("$linkBackLink?xSectionID=".getFORM("xParent")."&".userSessionGET());
	}
?>
