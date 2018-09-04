<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	$xOrderID=getFORM("xOrderID");
	$orderID=$xOrderID;
	$orderIDShow = retrieveOption("orderNumberOffset")+$xOrderID;

	$myForm = new formElements;
	
	$courierArray = $dbA->retrieveAllRecordsFromQuery("select * from $tableCouriers order by name");
	$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
	
	$hiddenFields = "";

	$lResult = $dbA->query("select * from $tableOrdersLines where orderID=$orderID order by lineID");
	$dispatchLines = $dbA->retrieveAllRecordsFromQuery("select lineID,sum(qty) as qtytotal from $tableDispatchesTree where orderID=$orderID group by lineID order by lineID");
	$lCount = $dbA->count($lResult);
	$anyDigital = false;
	for ($f = 0; $f < $lCount; $f++) {
		$lRecord = $dbA->fetch($lResult);
		$qtyleft = $lRecord["qty"];
		for ($g = 0; $g < count($dispatchLines); $g++) {
			if ($lRecord["lineID"] == $dispatchLines[$g]["lineID"]) {
				$qtyleft = $qtyleft - $dispatchLines[$g]["qtytotal"];
			}
		}
		if ($qtyleft > 0) {
			if ($lRecord["isDigital"] == "Y" && $lRecord["digitalReg"] > 0) {
				$anyDigital = true;
			}
		}
	}	
	if (retrieveOption("orderAdminDispatchTracking") == 0 && retrieveOption("orderAdminDispatchPartial") == 0) {
		if ($anyDigital == false) {
			doRedirect("orders_process.php?xAction=setdispatchedsimple&".userSessionGET()."&xReturn=".getFORM("xReturn")."&xTrackingEnabled=N&xOrderID=".$xOrderID);
			//echo "orders_process.php?xAction=setdispatchedsimple&".userSessionGET()."&".getFORM("xReturn")."&xTrackingEnabled=N&xOrderID=".$xOrderID;
			//exit;
		}
	}
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<script>
	function checkFields() {
	}
</script>
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Dispatch Order <?php print $orderIDShow; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","orders_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print hiddenReturnPOST(); ?>
<input type="hidden" name="xAction" value="setdispatchedcomplex">
<input type="hidden" name="xOrderID" value="<?php print $xOrderID; ?>">
<table cellpadding="2" cellspacing="0" class="table-list">

<?php
	if (retrieveOption("orderAdminDispatchPartial") == 1 || $anyDigital) {
?>
	<tr>
		<td class="table-grey-nocenter" colspan="2" align="left">Products Left To Dispatch</td>
	</tr>
	<tr>
		<td class="table-list-title" colspan="2">

<table cellpadding="2" cellspacing="0" width="100%" class="table-outline-white">
<tr>
	<td class="table-list-title" align="left">Product Code</td>
	<td class="table-list-title" align="left">Product Details</td>
	<td class="table-list-title" align="right">Qty Left</td>
	<td class="table-list-title" align="right">Dispatch</td>
</tr>
<?php
	$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
	$lResult = $dbA->query("select * from $tableOrdersLines where orderID=$orderID order by lineID");
	$dispatchLines = $dbA->retrieveAllRecordsFromQuery("select lineID,sum(qty) as qtytotal from $tableDispatchesTree where orderID=$orderID group by lineID order by lineID");
	$lCount = $dbA->count($lResult);
	$allDigital = true;
	for ($f = 0; $f < $lCount; $f++) {
		$lRecord = $dbA->fetch($lResult);
		$qtyleft = $lRecord["qty"];
		for ($g = 0; $g < count($dispatchLines); $g++) {
			if ($lRecord["lineID"] == $dispatchLines[$g]["lineID"]) {
				$qtyleft = $qtyleft - $dispatchLines[$g]["qtytotal"];
			}
		}
		$extraFields = @$lRecord["extrafields"];
		if ($extraFields != "") {
			$extraFields = "<br>".$extraFields;
		}
		$allFields = "";
		for ($g = 0; $g < count($extraFieldsArray); $g++) {
			$thisField = "";
			switch ($extraFieldsArray[$g]["type"]) {
					case "SELECT":
					case "RADIOBUTTONS":
						$theContent = "";
						for ($i = 0; $i < count($extraFieldList); $i++) {
							if ($lRecord["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
								$theContent = $extraFieldList[$i]["content"];
								break;
							}
						}
						if ($theContent != "") {
							$thisField = $extraFieldsArray[$g]["title"].": ".$theContent;
						}
						break;								
					case "CHECKBOXES":
						$optionArray = "";
						$theContent = "";
						for ($i = 0; $i < count($extraFieldList); $i++) {
							if ($lRecord["lineID"] == $extraFieldList[$i]["lineID"] && $extraFieldsArray[$g]["extraFieldID"] == $extraFieldList[$i]["extraFieldID"]) {
								if ($extraFieldList[$i]["content"] != "") {
									if ($theContent == "") {
										$theContent = $extraFieldList[$i]["content"];
									} else {
										$theContent .= ", ".$extraFieldList[$i]["content"];
									}
								}
							}
						}
						if ($theContent != "") {
							$thisField = $extraFieldsArray[$g]["title"].": ".$theContent;
						}
						break;
			}	
			if 	($thisField != "") {
				$allFields .= $thisField . "<BR>";
			}
		}
		if ($allFields != "") {
			$allFields = "<BR>".$allFields;
		}
		if ($qtyleft > 0 && $lRecord["isDigital"] == "N") {
			$allDigital = false;
		}
		if ($qtyleft > 0 && $lRecord["isDigital"] == "N") {
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><?php print $lRecord["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><b><?php print $lRecord["name"]; ?></b><?php print $allFields; ?></td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php print $qtyleft; ?></td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><input type="hidden" name="leftQty<?php print $lRecord["lineID"]; ?>" value="<?php print $qtyleft; ?>">
				<?php
					if (retrieveOption("orderAdminDispatchPartial") == 1) {
						$myForm->createText("dispQty".$lRecord["lineID"],5,10,$qtyleft,"integer");
					} else {
						?><input type="hidden" name="dispQty<?php print $lRecord["lineID"]; ?>" value="<?php print $qtyleft; ?>"><?php
						echo $qtyleft;
					}
				?>
			</td>
		</tr>
<?php
		}
		if ($lRecord["isDigital"] == "Y" && $qtyleft > 0) {
			$regName = false;
			$regCode = false;
			if ($lRecord["digitalReg"] == 1 || $lRecord["digitalReg"] == 2) { $regCode = true; }
			if ($lRecord["digitalReg"] == 2) { $regName = true; } 
?>
		<tr>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><?php print $lRecord["code"]; ?></td>
			<td class="table-white-nocenter-light-s" align="left" valign="top"><b><?php print $lRecord["name"]; ?></b><?php print $allFields; ?>
			<table cellpadding="2" cellspacing="0" border="0">
			<?php
				if ($regName) {
					?><tr>
						<td class="table-white-nocenter-light-s" align="left" valign="top">
							<b>Registration Name:</b>
						</td>
						<td class="table-white-nocenter-light-s" align="left" valign="top">
							<?php $myForm->createText("regName".$lRecord["lineID"],40,250,"","general"); ?><BR>
						</td>
					</tr><?php
				}
				if ($regCode) {
					?><tr>
						<td class="table-white-nocenter-light-s" align="left" valign="top">
							<b>Registration Code:</b>
						</td>
						<td class="table-white-nocenter-light-s" align="left" valign="top">
							<?php $myForm->createText("regCode".$lRecord["lineID"],40,250,"","general"); ?><BR>
						</td>
					</tr><?php
				}
			?>
			</table>
			</td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><?php print $qtyleft; ?></td>
			<td class="table-white-nocenter-light-s" align="right" valign="top"><input type="hidden" name="leftQty<?php print $lRecord["lineID"]; ?>" value="<?php print $qtyleft; ?>"><input type="hidden" name="dispQty<?php print $lRecord["lineID"]; ?>" value="<?php print $qtyleft; ?>">
				<?php print $qtyleft; ?>
			</td>
		</tr>
<?php
		}
	}
?>
			</table>
		</td>
	</tr>

<?php
	}
?>

<?php
	if (retrieveOption("orderAdminDispatchTracking") == 1 && $allDigital == false) {
?>
	<tr>
		<td class="table-grey-nocenter" colspan="2" align="left">Tracking Information</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Enable Tracking</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xTrackingEnabled","Y","YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Tracking Reference</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTrackingReference",50,250,"","general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Tracking Misc.</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTrackingMisc",50,250,"","general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Courier</td>
		<td class="table-list-entry1" valign="top">
			<select name="xCourierID" class="form-inputbox">
				<?php
					for ($f = 0; $f < count($courierArray); $f++) {
					?>
						<option value="<?php print $courierArray[$f]["courierID"]; ?>"><?php print $courierArray[$f]["name"]; ?></option>
					<?php
					}
				?>
			</select>
		</td>
	</tr>		
<?php
	}
	if (retrieveOption("orderAdminDispatchTracking") == 1 && $allDigital == true) {
?>
	<tr>
		<td class="table-list-title" valign="top" colspan="2"><input type="hidden" name="xTrackingEnabled" value="N"></td>
	</tr>	
<?php
	}
?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit","Dispatch Order"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>