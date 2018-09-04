<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	dbConnect($dbA);
	
	$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
	
	$recipArray[] = array("value"=>"C:0","text"=>"Full Mailing List");
	for ($g= 0 ; $g < count($accTypeArray); $g++) {
		$recipArray[] = array("value"=>"C:".$accTypeArray[$g]["accTypeID"],"text"=>"Account Type ".$accTypeArray[$g]["name"]." only");
	}
	if (retrieveOption("affiliatesActivated") == 1) {
		$recipArray[] = array("value"=>"A:0","text"=>"All Affiliates");
		$affGroupArray = $dbA->retrieveAllRecords($tableAffiliatesGroups,"groupID");
		for ($g= 0 ; $g < count($affGroupArray); $g++) {
			$recipArray[] = array("value"=>"A:".$affGroupArray[$g]["groupID"],"text"=>"Affiliate Group ".$affGroupArray[$g]["name"]." only");
		}
	}

	
	$xType=getFORM("xType");
	if ($xType=="new") {
		$pageTitle = "Add New Newsletter";
		$submitButton = "Insert Newsletter";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";
	}
	$xUseexchangerate = "N";
	if ($xType=="edit") {
		$xNewsletterID = getFORM("xNewsletterID");
		$pageTitle = "Edit Existing Newsletter";
		$submitButton = "Update Newsletter";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xNewsletterID' value='$xNewsletterID'>";
		dbConnect($dbA);
		$uResult = $dbA->query("select * from $tableNewsletters where newsletterID=$xNewsletterID");	
		$uRecord = $dbA->fetch($uResult);
	}

	
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
		<td class="table-list-title" valign="top">Recipient List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xRecipList",@$uRecord["recipList"],"BOTH",$recipArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Subject</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSubject",80,250,@getGENERIC("subject",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Content<BR>(Text Version)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContent",60,12,@getGENERIC("content",$uRecord),""); ?></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Content<br>(HTML Version)</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xContentHTML",60,12,@getGENERIC("contentHTML",$uRecord),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top" colspan="2" align="center">
		You can add a remove link into either the Text or HTML versions of the newsletter<br> by including {removelink}.
		This will output a tailored URL for each newsletter<br>recipient to be able
		to automatically unsubscribe.<br>Note: Unsubscribe link is not available for newsletters sent to affiliates</td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
<?php $dbA->close(); ?>