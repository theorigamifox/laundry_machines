<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Edit Company Details";
	$submitButton = "Update Company Details";
	$hiddenFields = "<input type='hidden' name='xAction' value='update'>";
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableGeneral");	
	$uRecord = $dbA->fetch($uResult);
	
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
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_company_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Company Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCompanyName",50,250,@getGENERIC("companyName",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Address Line 1</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAddressLine1",50,250,@getGENERIC("addressLine1",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Address Line 2</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xAddressLine2",50,250,@getGENERIC("addressLine2",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Town/City</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCity",30,250,@getGENERIC("city",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">County/State</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCounty",30,250,@getGENERIC("county",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Postcode/ZIP</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xPostcode",20,250,@getGENERIC("postcode",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Country</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCountry",40,250,@getGENERIC("country",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Telephone</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTelephone",20,250,@getGENERIC("telephone",$uRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Fax</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xFax",20,250,@getGENERIC("fax",$uRecord),"general"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">General Email</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xGeneralemail",40,250,@getGENERIC("generalemail",$uRecord),"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Store URL</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xStoreurl",50,250,@getGENERIC("storeurl",$uRecord),"url"); ?></td>
	</tr>
	<!--<tr>
		<td class="table-list-title" valign="top" colspan="2">License / Registration Details</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Registration Company Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xJssRegCompanyName",50,250,retrieveOption("jssRegCompanyName"),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Registration Code</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xJssRegCode",40,250,retrieveOption("jssRegCode"),"general"); ?></td>
	</tr>-->					
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
<?php $myForm->closeForm("xCompanyName"); ?>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>