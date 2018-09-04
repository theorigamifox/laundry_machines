<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function goDelete(newsletterID) {
		if (confirm("Are you sure you wish to delete this newsletter?")) {
			self.location.href="newsletter_process.php?xAction=delete&xNewsletterID="+newsletterID+"&<?php print userSessionGET(); ?>";
		}
	}
	
	function goSend(newsletterID) {
		if (confirm("Are you sure you wish to send this newsletter now?")) {
			window.open("newsletter_process.php?xAction=send&xPre=Y&xNewsletterID="+newsletterID+"&<?php print userSessionGET(); ?>","JSSEmailNewsletter","height=150,width=500,scrollbars=no,status=no,toolbar=no,menubar=no,location=no,resizable=no");
		}
	}
	
	function goResume(newsletterID) {
		if (confirm("Are you sure you wish to resume sending this newsletter now?")) {
			window.open("newsletter_process.php?xAction=send&xPre=Y&xNewsletterID="+newsletterID+"&<?php print userSessionGET(); ?>","JSSEmailNewsletter","height=150,width=500,scrollbars=no,status=no,toolbar=no,menubar=no,location=no,resizable=no");
		}
	}
	
	function goTest(newsletterID) {
		if (confirm("Are you sure you wish to send a test of this newsletter now?")) {
			self.location.href="newsletter_process.php?xAction=test&xNewsletterID="+newsletterID+"&<?php print userSessionGET(); ?>";
		}
	}

	function goReset(newsletterID) {
		if (confirm("Are you sure you wish to reset this newsletter to NOT SENT?")) {
			self.location.href="newsletter_process.php?xAction=reset&xNewsletterID="+newsletterID+"&<?php print userSessionGET(); ?>";
		}
	}	
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Newsletters</td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title">Date</td>
		<td class="table-list-title">Subject</td>
		<td class="table-list-title">Recipient List</td>
		<td class="table-list-title" align="right">Status</td>
		<td class="table-list-title" align="right">Action</td>
	</tr>
<?php
	dbConnect($dbA);
	$uResult = $dbA->query("select * from $tableNewsletters order by newsletterID DESC");
	$uCount = $dbA->count($uResult);
	for ($f = 0; $f < $uCount; $f++) {
		$uRecord = $dbA->fetch($uResult);
		switch ($uRecord["status"]) {
			case 0:
				$showStatus = "NOT SENT";
				break;
			case 1:
				$showStatus = "SEND INCOMPLETE";
				break;
			case 2:
				$showStatus = "SENT";
				break;
		}
		if ($uRecord["recipList"] == "") {
			$uRecord["recipList"] = "C:0";
		}
		$recipBits = explode(":",$uRecord["recipList"]);
		if ($recipBits[0] == "C") {
			if ($recipBits[1] == 0) {
				$recipText = "Full Mailing List";
			} else {
				$accTypeArray = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
				for ($g= 0 ; $g < count($accTypeArray); $g++) {
					if ($accTypeArray[$g]["accTypeID"] == $recipBits[1]) {
						$recipText = "Account Type ".$accTypeArray[$g]["name"];
					}
				}
			}
		}
		if ($recipBits[0] == "A") {
			if ($recipBits[1] == 0) {
				$recipText = "All Affiliates";
			} else {
				$affGroupArray = $dbA->retrieveAllRecords($tableAffiliatesGroups,"groupID");
				for ($g= 0 ; $g < count($affGroupArray); $g++) {
					if ($affGroupArray[$g]["groupID"] == $recipBits[1]) {
						$recipText = "Affiliate Group ".$affGroupArray[$g]["name"];
					}
				}
			}
		}
?>
	<tr>
		<td class="table-list-entry1"><?php print formatDate($uRecord["date"]); ?></td>
		<td class="table-list-entry1"><a href="newsletter_detail.php?xType=edit&xNewsletterID=<?php print $uRecord["newsletterID"]; ?>&<?php print userSessionGET(); ?>"><?php print $uRecord["subject"]; ?></a></td>
		<td class="table-list-entry1"><?php print $recipText; ?></a></td>
		<td class="table-list-entry1"><?php print $showStatus; ?></a></td>
		<td class="table-list-entry1" align="right">
			<button id="buttonEdit<?php print $f; ?>" class="button-edit" onClick="self.location.href='newsletter_detail.php?xType=edit&xNewsletterID=<?php print $uRecord["newsletterID"]; ?>&<?php print userSessionGET(); ?>';">Edit</button>&nbsp;<?php if ($uRecord["status"] == 0) { ?><button id="buttonSend<?php print $f; ?>" class="button-green" onClick="goSend(<?php print $uRecord["newsletterID"]; ?>);">Send</button>&nbsp;<?php } ?><?php if ($uRecord["status"] == 1) { ?><button id="buttonResume<?php print $f; ?>" class="button-green" onClick="goResume(<?php print $uRecord["newsletterID"]; ?>);">Resume</button>&nbsp;<?php } ?><?php if ($uRecord["status"] == 2) { ?><button id="buttonReset<?php print $f; ?>" class="button-green" onClick="goReset(<?php print $uRecord["newsletterID"]; ?>);">Reset</button>&nbsp;<?php } ?><button id="buttonTest<?php print $f; ?>" class="button-orange" onClick="goTest(<?php print $uRecord["newsletterID"]; ?>);">Test</button>&nbsp;<button id="buttonEdit<?php print $f; ?>" class="button-delete" onClick="goDelete(<?php print $uRecord["newsletterID"]; ?>);">Delete</button></td>
	</tr>
<?php
	}
	$dbA->close();
?>
	<tr>
		<td colspan="4" class="table-list-title">Total Newsletters:</td>
		<td class="table-list-title" align="right"><?php print $uCount; ?></td>
	</tr>
</table>
<p>
<button id="buttonNewsletterAdd" class="button-expand" onClick="self.location.href='newsletter_detail.php?xType=new&<?php print userSessionGET(); ?>'">Add New Newsletter</button>
</center>
</BODY>
</HTML>
