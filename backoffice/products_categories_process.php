<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Product Category";
	$tableName = $tableProductsCategories;
	$linkBackButton = "PRODUCT CATEGORIES";
	$linkBackLink = "products_categories.php";

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xName = getFORM("xName");
		if ($dbA->doesRecordExist($tableName,"name",$xName)) {
			setupProcessMessage($recordType,$xName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("name",$xName,"S");			
			$dbA->insertRecord($tableName,$rArray);
			userLogActionAdd($recordType,$xName);		
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xCategoryID = getFORM("xCategoryID");
		if (!$dbA->doesIDExist($tableName,"categoryID",$xCategoryID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"categoryID",$xCategoryID);
			userLogActionDelete($recordType,$uRecord["name"]);
			doRedirect("$linkBackLink?".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xCategoryID = getFORM("xCategoryID");
		$xName = getFORM("xName");
		if (!$dbA->doesIDExist($tableName,"categoryID",$xCategoryID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xName"),"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"categoryID",$xCategoryID,"name",$xName)) {
				setupProcessMessage($recordType,getFORM("xName"),"error_duplicate_update","BACK","");				
			}
			$rArray[] = array("name",getFORM("xName"),"S");
			$dbA->updateRecord($tableName,"categoryID=$xCategoryID",$rArray);
			userLogActionUpdate($recordType,$xName);
			doRedirect("$linkBackLink?".userSessionGET());
		}		
	
	}
?>
