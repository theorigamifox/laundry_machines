<?php
	include("static/includeBase_front.php");
	include("routines/fieldValidation.php");
	
	$xSec = 1;
	$thisTemplate = "index.html";
	
	dbConnect($dbA);
	
	$xCmd = getFORM("xCmd");
	
	if ($xCmd == "subscribe") {
		$addError = "";
		
		$xEmail = getFORM("xEmailAddress");
		$xEmail = str_replace("'","",$xEmail);
		$xEmail = str_replace('"',"",$xEmail);
		$xEmail = makeSafe($xEmail);
		if (!validateIndividual($xEmail,"Email Address","")) {
			$addError = "NOTVALID";
		}
		
		if ($addError == "") {
			$xEmail = strtolower($xEmail);
			$result = $dbA->query("select * from $tableNewsletter where emailaddress=\"$xEmail\"");
			if ($dbA->count($result) > 0) {
				$addError = "DUPLICATE";
			} else {
				$dbA->query("insert into $tableNewsletter (emailaddress) VALUES(\"$xEmail\")");
				$pageType = "newsletter";
				$thisTemplate = "newslettersubscribe.html";
				include("routines/emailOutput.php");
				$newsletter["emailaddress"] = $xEmail;
				$newsletter["removelink"] = $jssStoreWebDirHTTP."newsletter.php?xCmd=unsubscribe&xEmailAddress=".$xEmail;
				@sendEmail($xEmail,"","CUSTNEWSLETTER");
				@sendEmail("COMPANY","","MERCHNEWSLETTER");
				
				mail("daniel@laundrymachines.co.uk", "Laundry Machines subscriber", $xEmail . " has subscribed to the Laundry Machines mailing list");
			}
			
		}
		$newsletter["emailaddress"] = $xEmail;
		if ($addError != "") {
			$newsletter["error"] = $addError;
			$thisTemplate = "newslettererror.html";
		}
	}
	
	if ($xCmd == "unsubscribe") {
		$xEmail = chop(getFORM("xEmailAddress"));
		$xEmail = str_replace("'","",$xEmail);
		$xEmail = str_replace('"',"",$xEmail);	
		$xEmail = makeSafe($xEmail);
		$dbA->query("delete from $tableNewsletter where emailaddress=\"$xEmail\"");
		$newsletter["emailaddress"] = $xEmail;
		$thisTemplate = "newsletterunsubscribe.html";
		$pageType = "newsletter";
	}
	
	include("routines/cartOutputData.php");
	
	$tpl->showPage();
	$dbA->close();
?>