<?php
	function setupProcessMessage($recType,$recValue,$messageType,$processButtonText,$processButtonLink) {
		global $dbA;
		switch ($messageType) {
			case "added":
				$processTitle = $recType." Added";
				$processTitle2 = $recType.": $recValue has been created";
				$processMessage = "The new $recType has now been created. Click the button below to continue.";
				break;		
			case "updated":
				$processTitle = $recType." Updated";
				$processTitle2 = $recType.": $recValue has been updated";
				$processMessage = "The $recType has now been updated. Click the button below to continue.";			
				break;
			case "sorted":
				$processTitle = $recType." Sorted";
				$processTitle2 = $recType.": $recValue has been sorted";
				$processMessage = "The $recType has now been sorted. Click the button below to continue.";			
				break;
			case "deleted":
				$processTitle = $recType." Deleted";
				$processTitle2 = $recType.": $recValue has been deleted";
				$processMessage = "The $recType has now been deleted. Click the button below to continue.";				
				break;
			case "error_duplicate_add":
				$processTitle = "Error Adding $recType!";
				$processTitle2 = $recType.": $recValue already exists!";
				$processMessage = "The new $recType you have entered already exists in the database.<br>Click the button below to continue.";
				break;
			case "error_duplicate_update":
				$processTitle = "Error Updating $recType!";
				$processTitle2 = $recType.": $recValue already exists!";
				$processMessage = "The $recType could not be updated as $recType $recValue already exists.<br>Click the button below to continue.";
				break;				
			case "error_existance":	
				$processTitle = "Error Deleting $recType!";
				$processTitle2 = $recType." doesn't exist!";
				$processMessage = "The $recType you have tried to update or delete does not exist.<br>Click the button below to continue.";
				break;			
			default:
				print "system setupProcessMessage() error";
				break;
		}
		if ($processButtonText == "BACK") {
			$processButtonLink = "self.history.go(-1);";
			$processButtonText = "&lt; Back";
		} else {
			$processButtonLink = "self.location.href='$processButtonLink';";
		}
		$dbA->close();
		createProcessMessage($processTitle,$processTitle2,$processMessage,$processButtonText,$processButtonLink);
	}

	function createProcessMessage($processTitle,$processTitle2,$processMessage,$processButtonText,$processButtonLink) {
		$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE>Process Message</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css"
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $processTitle; ?></td>
	</tr>
</table>
<br>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title"><?php print $processTitle2; ?></td>
	</tr>
	<tr>
		<td class="table-list-entry1"><br><center><?php print $processMessage; ?></center><br></td>
	</tr>
	<tr>
		<td class="table-list-title" colspan="2" align="right"><?php $myForm->createExpandButton("buttonAccount",$processButtonText,$processButtonLink); ?></td>
	</tr>
</table>
</center>
</BODY>
</HTML>
<?php
		exit;
	}
?>