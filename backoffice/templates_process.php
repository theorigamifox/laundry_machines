<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Template";
	
	$xCommand = getFORM("xCommand");
	
	if ($xCommand == "removecompiled") {
		if (!$safeMode) {
			$xStartDir = $jssShopFileSystem."templates/compiled/";
			$myDir = opendir($xStartDir);
			$dirFileArray = "";
			$dirDateArray = "";
			$dirSizeArray = "";
			while (false !== ($file = readdir($myDir))) {
				if (substr($file,strlen($file)-5,5) == ".html" || substr($file,strlen($file)-4,5) == ".req") {
					unlink("$xStartDir/$file");
				}
			}
		}
		userLog("Removed Compiled Templates");
		createProcessMessage("Compiled Templates Removed",
		"Compiled Templates Removed",
		"Compiled templates in <B>tempates/compiled/</b> has been removed.",
		"&lt; Back",
		"self.history.go(-1);");
	}
	
	$xFile = getFORM("xFile");
	$xContent = getFORM("xContent");
	$xStartDir = getFORM("xStartDir");
	$xSaveAs = getFORM("xSaveAs");
	$xExtend = getFORM("xExtend");
	$doRefresh = false;
	if (chop($xSaveAs) != "") {
		$xFile = $xStartDir.$xSaveAs.$xExtend;
		$doRefresh = true;
	}
	if (!$safeMode) {
		if (get_magic_quotes_gpc() == 1) {
			$xContent = stripslashes($xContent);
		}
		if (!$fd = @fopen ($xFile, "w")) { 
			createProcessMessage("Error: System does not have persmission to write to file!",
			"Template: $xFile Could not be written to.",
			"Please check the permissions on your template directory as the system<br>cannot currently write to files in that directory.",
			"&lt; Back",
			"self.history.go(-1);");
		}
		fwrite($fd, $xContent);
		fclose ($fd); 
		userLogActionAdd($recordType,$xFile);
	}
	if ($doRefresh) {
		doRedirect("templates_edit.php?xRefresh=YES&xFile=$xFile&xStartDir=$xStartDir&".userSessionGET());
	} else {
		doRedirect("templates_edit.php?xFile=$xFile&xStartDir=$xStartDir&".userSessionGET());
	}
?>
