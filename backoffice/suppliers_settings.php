<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$myForm = new formElements;

	$emailTimes = null;
	$emailTimes[] = array("text"=>"None","value"=>0);
	$emailTimes[] = array("text"=>"When Order Comes In (NEW)","value"=>1);
	$emailTimes[] = array("text"=>"When Order Marked As PAID","value"=>2);
	$emailTimes[] = array("text"=>"Manually From Order Admin","value"=>3);

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
		<td class="detail-title">Suppliers General Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","suppliers_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Activate Supplier System</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xSuppliersEnabled",retrieveOption("suppliersEnabled"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Supplier Email Timing</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xSuppliersEmailTiming",retrieveOption("suppliersEmailTiming"),"BOTH",$emailTimes); ?></td>
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
