<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Newsletter Options";
	$submitButton = "Update Newsletter Options";
	$hiddenFields = "<input type='hidden' name='xAction' value='options'>";
	dbConnect($dbA);

	$xEmailNewsletterFrom = retrieveOption("emailNewsletterFrom");
	
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
<?php $myForm->createForm("detailsForm","newsletter_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Reply/From Email Address For Newsletters</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmailNewsletterFrom",60,250,$xEmailNewsletterFrom,"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Newsletter Test Recipient</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xEmailNewsletterTest",60,250,retrieveOption("emailNewsletterTest"),"email"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Email Addresses To List Per Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xNewsletterEmailsList",10,5,retrieveOption("newsletterEmailsList"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Convert Line Breaks To &lt;BR&gt; on News Items</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xNewsConvertToBR",retrieveOption("newsConvertToBR"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Sending: Emails Per Batch</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xNewsletterBatchSize",10,5,retrieveOption("newsletterBatchSize"),"integer"); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Sending: Automatically Process Next Batch</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xNewsletterBatchAuto",retrieveOption("newsletterBatchAuto"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>		
</table>
<?php $myForm->closeForm("xEmailNewsletterFrom"); ?>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>