<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	$xAction=getFORM("xAction");
	if ($xAction=="removeemails") {
		dbConnect($dbA);
		$fileName = @$_FILES["xEmailFile"]["name"];
		$fileType = @$_FILES["xEmailFile"]["type"];
		$fileSize = @$_FILES["xEmailFile"]["size"];
		$fileTmpName = @$_FILES["xEmailFile"]["tmp_name"];
		
		$fp = @fopen($fileTmpName,"r");
		if ($fp == false) {
			echo "Cannot open file $fileName to read";
			exit;
		}
		$lineOne = true;
		$lineCounter = 0;
		while (!feof($fp)) {
			$buffer = chop(fgets($fp, 100000));
			$lineCounter++;
			$buffer = str_replace('"','',$buffer);
			$dbA->query("delete from $tableNewsletter where emailaddress=\"$buffer\"");
			set_time_limit(30);
		}
		fclose($fp);
		$dbA->close();
		createProcessMessage("Bulk Email Remove Success!",
		"Email addresses in $fileName has now been removed!",
		"The $fileName contained $lineCounter email addresses which have been removed from the newsletter list.",
		"&lt; Back",
		"self.location.href='newsletter_emails_bulk_remove.php?".userSessionGET()."';");	
		exit;
	}
	echo "unrecognised command, exiting";
?>
