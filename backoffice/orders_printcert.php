<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	include("../routines/emailOutput.php");
	include("../routines/tSys.php");
	
	dbConnect($dbA);
	
	$xAction = getFORM("xAction");
	$xOrderID = getFORM("xOrderID");

	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");

	if (getFORM("xCertSerial") != "") {
		$result =  $dbA->query("select * from $tableGiftCertificates where certSerial='".getFORM("xCertSerial")."'");
	} else {
		$result =  $dbA->query("select * from $tableGiftCertificates where orderID=$xOrderID");
	}
	$giftCert = $dbA->fetch($result);
	$giftCert["messageFormatted"] = eregi_replace("\r\n","<BR>",$giftCert["message"]);
	$giftCert["amount"] = formatWithoutCalcPriceInCurrency($giftCert["certValue"],$giftCert["currencyID"]);
	
	$result = $dbA->query("select * from $tableGeneral");
	$companyRecord = $dbA->fetch($result);		
		
	$tpl = new tSys("../templates/","giftcert_print.html",$requiredVars,0);				

	$tpl->addVariable("company",$companyRecord);
	$tpl->addVariable("giftcertificate",$giftCert);
	$tpl->showPage();
	$dbA->close();
	
?>
