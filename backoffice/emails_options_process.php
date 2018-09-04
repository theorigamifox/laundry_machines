<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Emails";
	$tableName = $tableGeneral;
	$linkBackButton = "EMAIL OPTIONS";
	$linkBackLink = "emails_options.php";

	$xAction=getFORM("xAction");
	if ($xAction == "update") {
		updateOption("emailMerchFromCustomer",make01(getFORM("xEmailMerchFromCustomer")));							
		updateOption("emailMerchTo",getFORM("xEmailMerchTo"));	
		updateOption("emailCustomerFrom",getFORM("xEmailCustomerFrom"));	
		updateOption("sendMerchPaymentEmail",getFORM("xSendMerchPaymentEmail"));
		userLogActionUpdate($recordType,"Options");
		doRedirect("$linkBackLink?xSectionID=".getFORM("xParent")."&".userSessionGET());
	}
?>
