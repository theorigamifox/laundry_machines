<?php

	if((isset($_POST['enquiry1'])) && ($_POST['enquiry1'] != ""))
	{
		exit;
	}
	include("static/includeBase_front.php");
	include("routines/emailOutput.php");
	include("routines/fieldValidation.php");
	
	$xSec=1;
	$thisTemplate = "contact.html";

	
	dbConnect($dbA);
	
	$xCmd = makeSafe(getFORM("xCmd"));
	if ($xCmd == "send") {
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='F' and visible=1");
		$contactform["error"] = "N";
		
		// RESET THE BLANK FIELD COUNTER
		$blankfields = 0;

		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$thisField = stripSlashes($thisField);
			$tempField = strtolower($thisField);

			// IF THIS FIELD IS BLANK, INCREASE THE BLANK FIELD COUNTER
			if($tempField == '')
			{
				$blankfields++;
			}

			if (strpos($tempField,"cc:") !== FALSE) {
				$dbA->close();
				doRedirect(configureURL("index.php"));
			}
			if ($fieldList[$f]["fieldname"] == "EmailAddress") {
				$thisField = ereg_replace("\n|\r|\r\n|\n\r", "", $thisField);
			}
			if ($fieldList[$f]["validation"] == 1) {
				$retVal = validateIndividual($thisField,$fieldList[$f]["validationType"],$fieldList[$f]["regex"]);
				if (!$retVal) {
					$contactform["error"] = "Y";
					$contactform[$fieldList[$f]["fieldname"]."_error"] = "Y";
				}
			}
			$contactform[$fieldList[$f]["fieldname"]] = $thisField;
		}

		// IF ALL FIELDS ARE BLANK, SET FOR ERROR
		if($blankfields == count($fieldList))
		{
			$contactform["error"] = "Y";
		}
		
		

		if (@$contactform["error"] == "N") {
			if (makeSafe(getFORM("xEmailDirect")) != "") {
				$emailDirect = makeSafe(getFORM("xEmailDirect"));
				$emailDirect = ereg_replace("\n|\r|\r\n|\n\r", "", $emailDirect);
				sendEmail(makeSafe($emailDirect),"","CONTACTFORM");
			} else {
				sendEmail("COMPANY","","CONTACTFORM");
			}
			$thisTemplate = "contactsent.html";
		}
	}
	
	include("routines/cartOutputData.php");
	
	$tpl->showPage();
	$dbA->close();
?>