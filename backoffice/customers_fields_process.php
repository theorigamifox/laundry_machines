<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$xType = getFORM("xType");
	$xFieldType = getFORM("xFieldType");
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	switch ($xType) {
		case "C":
			$recordType = "Customer Fields";
			$linkBackLink = "customers_fields.php?xType=C&";
			break;
		case "D":
			$recordType = "Address/Delivery Fields";
			$linkBackLink = "customers_fields.php?xType=D&";
			break;
		case "O":
			$recordType = "Extra Order Fields";
			$linkBackLink = "customers_fields.php?xType=O&";
			break;			
		case "F":
			$recordType = "Contact Form Fields";
			$linkBackLink = "customers_fields.php?xType=F&";
			break;		
		case "CC":
			$recordType = "Credit Card Fields";
			$linkBackLink = "customers_fields.php?xType=CC&";
			break;		
		case "G":
			$recordType = "Gift Certificate Fields";
			$linkBackLink = "customers_fields.php?xType=G&";
			break;			
		case "AF":
			$recordType = "Affiliate Account Fields";
			$linkBackLink = "customers_fields.php?xType=AF&";
			break;	
		case "SU":
			$recordType = "Supplier Fields";
			$linkBackLink = "customers_fields.php?xType=SU&";
			break;	
	}
	$tableName = $tableCustomerFields;

	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$xFieldName = getFORM("xFieldName");
		$xType = getFORM("xType");
		if ($xType != "F") {
			$xFieldName = "e_".$xFieldName;
		}
		if ($xType == "AF") {
			$xFieldName = "aff_".getFORM("xFieldName");
		}
		if ($xType == "SU") {
			$xFieldName = "sup_".getFORM("xFieldName");
		}
		if ($dbA->doesRecordExist($tableName,"fieldname",$xFieldName)) {
			setupProcessMessage($recordType,$xFieldName,"error_duplicate_add","BACK","");
		} else {
			$rArray[] = array("fieldname",$xFieldName,"S");
			$rArray[] = array("titleText",getFORM("xTitleText"),"S");
			
			$rArray[] = array("type",$xType,"S");
			$rArray[] = array("fieldtype",getFORM("xFieldType"),"S");
			
			$rArray[] = array("size",makeInteger(getFORM("xSize")),"N");
			
			$maxlength = makeInteger(getFORM("xMaxLength"));
			if ($maxlength > 250) { $maxlength = 250; }
			$rArray[] = array("maxlength",$maxlength,"N");
			$rArray[] = array("cols",makeInteger(getFORM("xCols")),"N");
			$rArray[] = array("rows",makeInteger(getFORM("xRows")),"N");
			
			$rArray[] = array("contentvalues",getFORM("xContentValues"),"S");
					
			$rArray[] = array("validation",getFORM("xValidation"),"N");
			$rArray[] = array("validationType",getFORM("xValidationType"),"S");
			$rArray[] = array("regex",getFORM("xRegex"),"S");
			$rArray[] = array("validationmessage",getFORM("xValidationMessage"),"S");
			
			$rArray[] = array("incOrdering",getFORM("xIncOrdering"),"N");
			$rArray[] = array("visible",getFORM("xVisible"),"N");
			$rArray[] = array("internalOnly",getFORM("xInternalOnly"),"N");
			$rArray[] = array("deletable",1,"N");
			$rArray[] = array("editable",1,"N");
			
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("titleText".$thisLanguage,getFORM("xTitleText".$thisLanguage),"S");			
					$rArray[] = array("validationmessage".$thisLanguage,getFORM("xValidationMessage".$thisLanguage),"S");	
				}
			}
			
			$dbA->insertRecord($tableName,$rArray,0);	
			$fieldID = $dbA->lastID();
			switch (getFORM("xFieldType")) {
				case "TEXTAREA":
					switch ($xType) {
						case "C":
							$dbA->query("alter table $tableCustomers add column $xFieldName text not null");
							$dbA->query("alter table $tableOrdersHeaders add column $xFieldName text not null");
							break;
						case "D":
							$dbA->query("alter table $tableCustomers add column $xFieldName text not null");
							$dbA->query("alter table $tableCustomersAddresses add column $xFieldName text not null");
							$dbA->query("alter table $tableOrdersHeaders add column $xFieldName text not null");
							$dbA->query("alter table $tableGiftCertificates add column $xFieldName text not null");
							break;
						case "O":
							$dbA->query("alter table $tableOrdersHeaders add column $xFieldName text not null");
							break;	
						case "AF":
							$dbA->query("alter table $tableAffiliates add column $xFieldName text not null");
							break;	
						case "SU":
							$dbA->query("alter table $tableSuppliers add column $xFieldName text not null");
							break;	
					}
					break;
				default:
					switch ($xType) {
						case "C":
							$dbA->query("alter table $tableCustomers add column $xFieldName char(250) not null");
							$dbA->query("alter table $tableOrdersHeaders add column $xFieldName char(250) not null");
							break;
						case "D":
							$dbA->query("alter table $tableCustomers add column $xFieldName char(250) not null");
							$dbA->query("alter table $tableCustomersAddresses add column $xFieldName char(250) not null");
							$dbA->query("alter table $tableOrdersHeaders add column $xFieldName char(250) not null");
							$dbA->query("alter table $tableGiftCertificates add column $xFieldName char(250) not null");
							break;
						case "O":
							$dbA->query("alter table $tableOrdersHeaders add column $xFieldName char(250) not null");
							break;		
						case "AF":
							$dbA->query("alter table $tableAffiliates add column $xFieldName char(250) not null");
							break;		
						case "SU":
							$dbA->query("alter table $tableSuppliers add column $xFieldName char(250) not null");
							break;		
					}
					break;
			}
			userLogActionAdd($recordType,$xFieldName);
			doRedirect("$linkBackLink".userSessionGET());
		}
	}
	if ($xAction == "delete") {
		$xFieldID = getFORM("xFieldID");
		$xType = getFORM("xType");
		if (!$dbA->doesIDExist($tableName,"fieldID",$xFieldID,$uRecord)) {
			setupProcessMessage($recordType,$xFieldID,"error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"fieldID",$xFieldID);
			switch ($xType) {
				case "C":
					$dbA->query("alter table $tableCustomers drop column ".$uRecord["fieldname"]);
					$dbA->query("alter table $tableOrdersHeaders drop column ".$uRecord["fieldname"]);
					break;
				case "D":
					$dbA->query("alter table $tableCustomersAddresses drop column ".$uRecord["fieldname"]);
					$dbA->query("alter table $tableCustomers drop column ".$uRecord["fieldname"]);
					$dbA->query("alter table $tableOrdersHeaders drop column ".$uRecord["fieldname"]);
					$dbA->query("alter table $tableGiftCertificates drop column ".$uRecord["fieldname"]);
					break;
				case "O":
					$dbA->query("alter table $tableOrdersHeaders drop column ".$uRecord["fieldname"]);
					break;		
				case "AF":
					$dbA->query("alter table $tableAffiliates drop column ".$uRecord["fieldname"]);
					break;		
				case "SU":
					$dbA->query("alter table $tableSuppliers drop column ".$uRecord["fieldname"]);
					break;
			}

			userLogActionDelete($recordType,$uRecord["fieldname"]);
			doRedirect("$linkBackLink".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xFieldID = getFORM("xFieldID");
		$xFieldName = getFORM("xFieldName");
		if (!$dbA->doesIDExist($tableName,"fieldID",$xFieldID,$uRecord)) {
			setupProcessMessage($recordType,$xFieldName,"error_existance","BACK","");	
		} else {
			if (!$dbA->isUnique($tableName,"fieldID",$xFieldID,"fieldname",$xFieldName)) {
				setupProcessMessage($recordType,$xFieldName,"error_duplicate_update","BACK","");					
			}
			$rArray[] = array("titleText",getFORM("xTitleText"),"S");
			
			$rArray[] = array("size",makeInteger(getFORM("xSize")),"N");
			$maxlength = makeInteger(getFORM("xMaxLength"));
			if ($maxlength > 250) { $maxlength = 250; }
			$rArray[] = array("maxlength",$maxlength,"N");
			$rArray[] = array("cols",makeInteger(getFORM("xCols")),"N");
			$rArray[] = array("rows",makeInteger(getFORM("xRows")),"N");
			
			$rArray[] = array("contentvalues",getFORM("xContentValues"),"S");
			$rArray[] = array("validation",getFORM("xValidation"),"N");
			$rArray[] = array("validationType",getFORM("xValidationType"),"S");
			$rArray[] = array("regex",getFORM("xRegex"),"S");
			$rArray[] = array("validationmessage",getFORM("xValidationMessage"),"S");
			
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("titleText".$thisLanguage,getFORM("xTitleText".$thisLanguage),"S");			
					$rArray[] = array("validationmessage".$thisLanguage,getFORM("xValidationMessage".$thisLanguage),"S");	
				}
			}

			if ($uRecord["deletable"] == 1 && ($uRecord["type"] == 'C' || $uRecord["type"] == 'D')) {
				$rArray[] = array("incOrdering",getFORM("xIncOrdering"),"N");
			}
			if ($uRecord["deletable"] == 1 && ($uRecord["type"] == 'C' || $uRecord["type"] == 'O')) {
				$rArray[] = array("internalOnly",getFORM("xInternalOnly"),"N");
			}
			if ($uRecord["deletable"] == 1 || $uRecord["fieldname"] == "company" || getFORM("xVisible") != "") {
				$rArray[] = array("visible",getFORM("xVisible"),"N");
			}
			
			$dbA->updateRecord($tableName,"fieldID=$xFieldID",$rArray,0);
			
			if ($xFieldName == "county") {
				$zArray = "";
				$zArray = array("contentvalues",getFORM("xContentValues"),"S");
				$dbA->updateRecord($tableName,"fieldname='deliveryCounty'",$rArray,0);
			}
			if ($xFieldName == "deliveryCounty") {
				$zArray = "";
				$zArray = array("contentvalues",getFORM("xContentValues"),"S");
				$dbA->updateRecord($tableName,"fieldname='county'",$rArray,0);
			}
				
			userLogActionUpdate($recordType,$xFieldName);
			doRedirect("$linkBackLink".userSessionGET());
		}		
	}
	if ($xAction == "reorder") {
		$xType = getFORM("xExtra");
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tableCustomerFields set position=$g where fieldID=$newOrderBits[$f]");
		}
		switch ($xType) {
			case "C":
				userLogAction("Sorted","Customer Fields","All");
				break;
			case "D":
				userLogAction("Sorted","Address/Delivery Fields","All");
				break;
			case "O":
				userLogAction("Sorted","Extra Order Fields","All");
				break;	
			case "F":
				userLogAction("Sorted","Contact Form Fields","All");
				break;		
			case "CC":
				userLogAction("Sorted","Contact Form Fields","All");
				break;		
			case "G":
				userLogAction("Sorted","Gift Certificate Fields","All");
				break;		
			case "AF":
				userLogAction("Sorted","Affiliate Account Fields","All");
				break;	
			case "SU":
				userLogAction("Sorted","Supplier Fields","All");
				break;	
		}
		$linkBackLink = "customers_fields.php?xType=$xType&".userSessionGET();
		doRedirect("$linkBackLink?".userSessionGET());
	}
?>
