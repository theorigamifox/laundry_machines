<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
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
		<td class="detail-title">Global Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="global">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Is Shop Available?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xShopAvailable",retrieveOption("shopAvailable"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Order Number Offset</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xOrderNumberOffset",40,250,retrieveOption("orderNumberOffset"),"integer"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Default Country</td>
		<td class="table-list-entry1" valign="top"><select name="xDefaultCountry" class="form-inputbox">
<?php
		$result = $dbA->query("select * from $tableCountries where visible='Y' order by name");
		$count = $dbA->count($result);
		$defaultCountry = retrieveOption("defaultCountry");
		for ($f = 0; $f < $count; $f++) {
			$record = $dbA->fetch($result);
			if ($defaultCountry == $record["countryID"]) {
				$thisSelected = "SELECTED";
			} else {
				$thisSelected = "";
			}
?>
			<option value="<?php print $record["countryID"]; ?>" <?php print $thisSelected; ?>><?php print $record["name"]; ?></option>
<?php
		}
?>		
		</select>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Use Safe URLs Where Applicable<br>(see documentation for requirements)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xUseRewriteURLs",retrieveOption("useRewriteURLs"),"01"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Cookie Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCookieName",20,20,retrieveOption("cookieName"),"alpha-numeric"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Cookie Expiry Time</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCookieTime",5,3,retrieveOption("cookieTime"),"decimal"); ?> hours</td>
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
