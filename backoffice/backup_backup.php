<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	$xCommand = getFORM("xCommand");
	if ($xCommand == "") {
		$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Backup mySQL Database
		</td>
	</tr>
</table>
<p>
<form name="detailsForm2" action="backup_backup.php?xCommand=createbackup&<?php print userSessionGET(); ?>" method="POST">
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" colspan="2"><font class="boldtext">Output Method</td>
	</tr>
	<?php
		$outputArray[] = array("value"=>"D","text"=>"Download File In Browser");
		$outputArray[] = array("value"=>"O","text"=>"Output To Screen (Save with 'View Source')");
		$outputArray[] = array("value"=>"S","text"=>"Save To Server");
	?>
	<tr>
		<td class="table-list-title" colspan="1">Select Output Method</td>
		<td colspan="1" class="table-list-entry1">
			<?php $myForm->createSelect("xOutputMethod","D","BOTH",$outputArray); ?>
		</td>
	</tr>	
	<?php
		$myPath = realpath("index.php");
		$myPath = str_replace("index.php","",$myPath);
		$myPath = str_replace("\\","/",$myPath);
	?>
	<tr>
		<td class="table-list-title" colspan="1">Server File Name<br><font color="#ff0000">(Save To Server only)</font></td>
		<td colspan="1" class="table-list-entry1">
			<center>
			<input type="text" name="xFileLocal" value="<?php print $myPath; ?>" size="60" class="form-inputbox" onFocusIn="this.style.borderColor='#FF0000'" onFocusOut="this.style.borderColor='#000000'">
			</center>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="table-list-title" align="right"><?php $myForm->createSubmit("submit","Backup Now"); ?></td>
	</tr>
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	}
	if ($xCommand == "createbackup") {
		$xOutputMethod = getFORM("xOutputMethod");
		outputHeader();
		outputData("#JSHOP SERVER BACKUP");
		dbConnect($dbA);
		$tableList = $dbA->getTableList();
		for ($f = 0; $f < count($tableList); $f++) {
			if (function_exists('set_time_limit')) { @set_time_limit(30); }
			$currentTable = $tableList[$f];
			$thisLine = "";
			$thisLine .="CREATE TABLE $currentTable (";
			$tableContents = $dbA->query("select * from $currentTable");
			$numberFields =  $dbA->tableFields($tableContents);
			$allFields = $dbA->retrieveFieldInformation($currentTable);
			$keysBit = "";
			for ($g = 0; $g < count($allFields); $g++) {	
				if (function_exists('set_time_limit')) { @set_time_limit(30); }
				$defaultBit = "";
				if ($allFields[$g]["null"] == "YES") {
					if ($allFields[$g]["default"] == "") {
						$defaultBit = "default NULL";
					} else {
						$defaultBit = "NULL default \"".$allFields[$g]["default"]."\"";
					}
				} else {
					$defaultBit = "NOT NULL";
					if ($allFields[$g]["default"] == "") {
						$defaultBit = "NOT NULL";
					} else {
						$defaultBit = "NOT NULL default \"".$allFields[$g]["default"]."\"";
					}					
				}
				$thisLine .="  ".$allFields[$g]["name"]." ".$allFields[$g]["type"]." ".$defaultBit." ".$allFields[$g]["extra"]."";
				if ($g == count($allFields)-1) {
					$thisLine .="";
				} else {
					$thisLine .=",";
				}
			}
			$keyArray = $dbA->retrieveIndexInformation($currentTable);
			$keyArray[] = array("key"=>"","seq"=>"","column"=>"","nonunique"=>"");
			$keysBit = "";
			$cKey = "";
			$cBit = "";
			$cUnique = "0";
			for ($g = 0; $g < count($keyArray); $g++) {
				if ($cKey != $keyArray[$g]["key"]) {
					if ($cKey != "") {
						if ($cKey == "PRIMARY") {
							$thisKey = "PRIMARY KEY ($cBit)";
						}
						if ($cKey != "PRIMARY") {
							if ($cUnique == "0") {
								$thisKey = "UNIQUE KEY $cKey ($cBit)";
							} else {
								$thisKey = "KEY $cKey ($cBit)";
							}
						}
						if ($keysBit == "") {
							$keysBit = $thisKey;
						} else {
							$keysBit = $keysBit.",".$thisKey;
						}
					}
					$cKey = $keyArray[$g]["key"];
					$cBit = $keyArray[$g]["column"];
					$cUnique = $keyArray[$g]["nonunique"];
				} else {
					$cBit .= ",".$keyArray[$g]["column"];
				}
			}
				
			if ($keysBit != "") {
				$thisLine .="  ,$keysBit";
			}
			$thisLine .=");";
			outputData($thisLine);
			
			$thisLine = "";
			
			//Now for the data
			$recordCount = $dbA->count($tableContents);
			for ($h = 0; $h < $recordCount; $h++) {
				if (function_exists('set_time_limit')) { @set_time_limit(30); }
				$currentRecord = $dbA->fetch($tableContents);
				$recordInsert = "INSERT INTO $currentTable VALUES(";
				for ($g = 0; $g < count($allFields); $g++) {
					if ($g != 0) { $recordInsert .= ","; }
					if (substr($allFields[$g]["type"],0,7) == "varchar" || substr($allFields[$g]["type"],0,4) == "char" || substr($allFields[$g]["type"],0,4) == "text" || substr($allFields[$g]["type"],0,10) == "mediumtext") {
						$currentRecord[$allFields[$g]["name"]] = addSlashes($currentRecord[$allFields[$g]["name"]]);
						$currentRecord[$allFields[$g]["name"]] = eregi_replace("\r\n","\\r\\n",$currentRecord[$allFields[$g]["name"]]);
						$recordInsert .= "\"".$currentRecord[$allFields[$g]["name"]]."\"";
					} else {
						$recordInsert .= $currentRecord[$allFields[$g]["name"]];
					}
				}
				$recordInsert .= ");";
				outputData($recordInsert);
			}			
		}
		outputFooter();
		$dbA->close();
		exit;
	}

	function outputHeader() {
		global $xOutputMethod,$fp,$fileTmpName,$xType;
		switch ($xOutputMethod) {
			case "D":
				header("Content-type: text/plain");
				header("Content-Disposition: attachment; filename=export.txt");
				break;
			case "O":
				break;
			case "S":
				$fileTmpName = getFORM("xFileLocal");
				$fp = @fopen($fileTmpName,"w");
				if (!$fp) {
					createProcessMessage("Backup File Cannot be Created!",
					"JShop Server Could Not Create Your Backup File!",
					"Your export file ($fileTmpName) could not be opened. You should ensure that the directory exists,<br>that the correct permissions are set and that you<br>entered a valid file name.",
					"&lt; Back",
					"self.location.href='export.php?xType=$xType&".userSessionGET()."';");		
					exit;
				}
				break;
		}
	}
	
	function outputData($data) {
		global $xOutputMethod;
		global $fp;
		switch ($xOutputMethod) {
			case "D":
				echo $data."\r\n";
				break;
			case "O":
				echo $data."\r\n";	
				break;
			case "S":
				fwrite($fp, $data."\r\n");	
				break;
		}
	}
	
	function outputFooter() {
		global $xOutputMethod,$fp,$fileTmpName,$xType;
		switch ($xOutputMethod) {
			case "D":
				break;
			case "O":	
				break;
			case "S":
				fclose($fp);
				createProcessMessage("Export File Created!",
					"JShop Server Has Created Your Export File!",
					"Your export file ($fileTmpName) has been created.",
					"&lt; Back",
					"self.location.href='backup_backup.php?".userSessionGET()."';");	
				exit;
				break;
		}
	}
?>
