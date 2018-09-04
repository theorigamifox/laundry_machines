<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Company";
	$tableName = $tableGeneral;
	$linkBackButton = "COMPANY DETAILS";
	$linkBackLink = "general_company.php";

	$xAction=getFORM("xAction");
	if ($xAction == "update") {
		//updateOption("jssRegCompanyName",getFORM("xJssRegCompanyName"));
		//updateOption("jssRegCode",getFORM("xJssRegCode"));
		$rArray[] = array("companyName",getFORM("xCompanyName"),"S");			
		$rArray[] = array("addressLine1",getFORM("xAddressLine1"),"S");	
		$rArray[] = array("addressLine2",getFORM("xAddressLine2"),"S");	
		$rArray[] = array("city",getFORM("xCity"),"S");	
		$rArray[] = array("county",getFORM("xCounty"),"S");	
		$rArray[] = array("postcode",getFORM("xPostcode"),"S");
		$rArray[] = array("country",getFORM("xCountry"),"S");
		$rArray[] = array("telephone",getFORM("xTelephone"),"S");
		$rArray[] = array("fax",getFORM("xFax"),"S");
		$rArray[] = array("generalemail",getFORM("xGeneralemail"),"S");
		$rArray[] = array("storeurl",getFORM("xStoreurl"),"S");										
		$dbA->updateRecord($tableName,"",$rArray);
		userLogActionUpdate($recordType,"Details");
		doRedirect("$linkBackLink?xSectionID=".getFORM("xParent")."&".userSessionGET());
	}
?>
