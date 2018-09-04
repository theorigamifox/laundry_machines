<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$myForm = new formElements;
	
	$shipArray = $dbA->retrieveAllRecords($tableShippingTypes,"name");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<script>
	function checkFields() {
	}
</script>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Shipping Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","shipping_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Shipping Enabled</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xShippingEnabled",retrieveOption("shippingEnabled"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Default Shipping</td>
		<td class="table-list-entry1" valign="top">
			<select name="xDefaultShipping" class="form-inputbox">
<?php
				$defaultShipping = retrieveOption("defaultShipping");
				for ($f = 0; $f < count($shipArray); $f++) {
					$thisSelected = "";
					if ($defaultShipping == $shipArray[$f]["shippingID"]) {
						$thisSelected = "SELECTED";
					}
?>
				<option value="<?php print $shipArray[$f]["shippingID"]; ?>" <?php print $thisSelected; ?>><?php print $shipArray[$f]["name"]; ?></option>
<?php				
				}
?>
			</select>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Settings"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
