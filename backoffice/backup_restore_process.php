<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	$xAction=getFORM("xAction");
	if ($xAction=="gorestore") {
		dbConnect($dbA);
		if (@$_FILES["xSQLFile"]["name"] != "") {
			$fileName = @$_FILES["xSQLFile"]["name"];
			$fileType = @$_FILES["xSQLFile"]["type"];
			$fileSize = @$_FILES["xSQLFile"]["size"];
			$fileTmpName = @$_FILES["xSQLFile"]["tmp_name"];
		} else {
			$fileTmpName = getFORM("xSQLFileLocal");
			$fileName = getFORM("xSQLFileLocal");
		}
		if ($fileTmpName == "") {
			$dbA->close();
			createProcessMessage("No File Specified!",
			"Restore File Not Specified!",
			"You have not specified either a file from your computer or a file on<br>your server to restore from!",
			"&lt; Back",
			"self.location.href='backup_restore.php?".userSessionGET()."';");	
			exit;
		}
		$fp = @fopen($fileTmpName,"r");
		if (!$fp) {
			$dbA->close();
			createProcessMessage("Restore File Cannot be Opened!",
			"JShop Server Could Not Open Your Restore File!",
			"Your restore file ($fileName) could not be opened. If you<br>uploaded the file from your computer it is possible<br>that your server's PHP settings do not allow files of that size to be uploaded.<br>If you gave a path to file on the server,<br>this could not be found or opened by JShop Server",
			"&lt; Back",
			"self.location.href='backup_restore.php?".userSessionGET()."';");	
			exit;
		}
		$lineOne = true;
		while (!feof($fp)) {
			$buffer = chop(fgets($fp, 100000));
			if ($lineOne) {
				if ($buffer != "#JSHOP SERVER BACKUP") {
					fclose($fp);
					$dbA->close();
					//error, not recognised restore file
					createProcessMessage("Invalid Restore File!",
					"File to restore from is invalid!",
					"The restore file you uploaded is not a valid JShop Server backup.<br>Please click the button below to try again.",
					"Restore Database",
					"self.location.href='backup_restore.php?".userSessionGET()."';");	
					exit;
				} else {
					$lineOne = false;
					$tableList = $dbA->getTableList();
					for ($f = 0; $f < count($tableList); $f++) {
						@$dbA->query("drop table ".$tableList[$f]);
					}
				}
			}
			$dbA->query($buffer);
			set_time_limit(30);
		}
		fclose($fp);
		$dbA->close();
		createProcessMessage("Data Restored!",
		"Data from file $fileName has now been restored!",
		"The database has now been restored to the status in $fileName.",
		"&lt; Back",
		"self.location.href='backup_restore.php?".userSessionGET()."';");	
		exit;
	}
?>
