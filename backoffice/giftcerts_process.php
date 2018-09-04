<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	include("../routines/emailOutput.php");
	include("../routines/tSys.php");	

	dbConnect($dbA);

	$recordType = "Gift Certificate";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' order by position");
	
	$tableName = $tableGiftCertificates;
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "insert") {
		$xCertSerial = getCertID();
		$rArray[] = array("certSerial",$xCertSerial,"S");
		$rArray[] = array("status",getFORM("xStatus"),"S");
		$rArray[] = array("fromname",getFORM("xFromname"),"S");
		$rArray[] = array("toname",getFORM("xToname"),"S");
		$rArray[] = array("message",getFORM("xMessage"),"S");
		$rArray[] = array("certValue",makeDecimal(getFORM("xAmount")),"D");
		$rArray[] = array("currencyID",makeDecimal(getFORM("xCurrencyID")),"N");
		if (getFORM("xExpires") == "N") {
			$rArray[] = array("expiryDate","N","S");
		} else {
			$rArray[] = array("expiryDate",getFORM("xYear").getFORM("xMonth").getFORM("xDay"),"S");
		}
		$rArray[] = array("type",getFORM("xGType"),"S");
		if (getFORM("xGType") == "E") {
			$rArray[] = array("emailaddress",getFORM("xEmailaddress"),"S");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],"","S");
			}
		} else {
			$rArray[] = array("emailaddress","","S");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
		}
		$dbA->insertRecord($tableName,$rArray);
		userLogActionAdd($recordType,$xCertSerial);
		doRedirect("giftcerts_listing.php?xStatus=X&".userSessionGET());		
	}
	if ($xAction == "delete") {
		$xCertSerial = getFORM("xCertSerial");
		$dbA->query("delete from $tableGiftCertificates where certSerial='$xCertSerial'");;
		userLogActionDelete($recordType,$xCertSerial);
		doRedirect("$linkBackLink&".userSessionGET());
	}	
	if ($xAction == "email") {
		$xCertSerial = getFORM("xCertSerial");
		$result = $dbA->query("select * from $tableGiftCertificates where certSerial='$xCertSerial'");
		if ($dbA->count($result) > 0) {
			$record = $dbA->fetch($result);
			if ($record["type"] == "E") {
				//this is an email one!
				$giftCert = $record;
				@sendEmail($giftCert["emailaddress"],"","GIFTCERT");
			}
		}
		doRedirect("$linkBackLink&".userSessionGET());	
	}
	if ($xAction == "update") {
		$xCertSerial = getFORM("xCertSerial");
		$rArray[] = array("status",getFORM("xStatus"),"S");
		$rArray[] = array("fromname",getFORM("xFromname"),"S");
		$rArray[] = array("toname",getFORM("xToname"),"S");
		$rArray[] = array("message",getFORM("xMessage"),"S");
		$rArray[] = array("certValue",makeDecimal(getFORM("xAmount")),"D");
		if (getFORM("xExpires") == "N") {
			$rArray[] = array("expiryDate","N","S");
		} else {
			$rArray[] = array("expiryDate",getFORM("xYear").getFORM("xMonth").getFORM("xDay"),"S");
		}
		$rArray[] = array("type",getFORM("xGType"),"S");
		if (getFORM("xGType") == "E") {
			$rArray[] = array("emailaddress",getFORM("xEmailaddress"),"S");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],"","S");
			}
		} else {
			$rArray[] = array("emailaddress","","S");
			for ($f = 0; $f < count($fieldList); $f++) {
				$rArray[] = array($fieldList[$f]["fieldname"],getFORM($fieldList[$f]["fieldname"]),"S");
			}
		}
		$dbA->updateRecord($tableName,"certSerial='$xCertSerial'",$rArray);
		userLogActionUpdate($recordType,$xCertSerial);
		doRedirect("$linkBackLink&".userSessionGET());
	}	

	function getCertID() {
		global $dbA,$tableGiftCertificates;
		srand((double)microtime()*1000000);
		$rID = rand();
		$rID = strtoupper(md5($rID));
		$result = $dbA->query("select certSerial from $tableGiftCertificates where certSerial='$rID'");
		while ($dbA->count($result) > 0) {
			$rID = rand();
			$rID = md5($rID);
			$result = $dbA->query("select certSerial from $tableGiftCertificates where certSerial='$rID'");
		}
		return $rID;
	}	
?>
