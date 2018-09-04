<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	dbConnect($dbA);

	if ($xType=="new") {
		$pageTitle = "Add New User";
		$submitButton = "Insert User";
		$hiddenFields = "<input type='hidden' name='xAction' value='insert'>";

		$xLoginEnabledYes = "CHECKED";
		$xLoginEnabledNo = "";	
		$uRecord["deniedList"]="";	
	}
	if ($xType=="edit") {
		$xUserID = getFORM("xUserID");
		$pageTitle = "Edit Existing User";
		$submitButton = "Update User";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xUserID' value='$xUserID'>";
		$uResult = $dbA->query("select * from $tableUsers where userID=$xUserID");	
		$uRecord = $dbA->fetch($uResult);

		$loginEnabled = $uRecord["loginEnabled"];
		if ($loginEnabled == 0) {
			$xLoginEnabledYes = "";
			$xLoginEnabledNo = "CHECKED";
		} else {
			$xLoginEnabledYes = "CHECKED";
			$xLoginEnabledNo = "";
		}		
	}
	$xDeniedList = split(";",$uRecord["deniedList"]);
	$sectionsArray = array(
					array("name"=>"users","title"=>"Users","selected"=>""),
					array("name"=>"general","title"=>"General","selected"=>""),
					array("name"=>"contents","title"=>"Contents","selected"=>""),
					array("name"=>"taxshipping","title"=>"Tax/Shipping","selected"=>""),
					array("name"=>"logs","title"=>"Logs","selected"=>""),
					array("name"=>"templates","title"=>"Templates","selected"=>""),
					array("name"=>"export","title"=>"Import/Export","selected"=>""),
					array("name"=>"newsletter","title"=>"Newsletter","selected"=>""),
					array("name"=>"customers","title"=>"Customers","selected"=>""),
					array("name"=>"checkout","title"=>"Checkout","selected"=>""),
					array("name"=>"reports","title"=>"Reports","selected"=>""),
					array("name"=>"affiliates","title"=>"Affiliates","selected"=>""),
					array("name"=>"orders","title"=>"Orders","selected"=>""),
					array("name"=>"backup","title"=>"Backup","selected"=>"")
					);
	for ($f = 0; $f < count($sectionsArray); $f++) {
		for ($g = 0; $g < count($xDeniedList); $g++) {
			if ($sectionsArray[$f]["name"] == $xDeniedList[$g]) {
				$sectionsArray[$f]["selected"] = " CHECKED";
			}
		}
	}
	
	$dbA->close();	
	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script language="JavaScript">
	function recalculateFields() {
		newFields = "";
		for (f = 0; f <=<?php print count($sectionsArray)-1; ?>; f++) {
			fieldChecked = eval("document.detailsForm.sectionField"+f+".checked");
			if (fieldChecked) {
				theValue = eval("document.detailsForm.sectionField"+f+".value");
				newFields = newFields + theValue+";";
			}
		}
		document.detailsForm.xDeniedList.value = newFields;
	}
</script>
<script>
	function checkFields() {
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","users_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<input type="hidden" name="xDeniedList" value="<?php print $uRecord["deniedList"]; ?>">
<table cellpadding="2" cellspacing="0" class="table-list">
<?php
	if ($xType=="new") {
?>
	<tr>
		<td class="table-list-title" valign="top">Username</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xUsernameInput",20,15,@getGENERIC("username",$uRecord),"alpha-numeric"); ?></td>
	</tr>
<?php
	}
?>
<?php
	if ($xType=="edit") {
?>
	<tr>
		<td class="table-list-title" valign="top">Username</td>
		<td class="table-list-entry1" valign="top"><?php print @getGENERIC("username",$uRecord); ?><input type="hidden" name="xUsernameInput" value="<?php print $uRecord["username"]; ?>"></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="table-list-title" valign="top">Password</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createPassword("xPassword",15,15,"","alpha-numeric"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Real Name</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRealname",50,150,@getGENERIC("realname",$uRecord),"general"); ?></td>
	</tr>
<?php
	if (@$uRecord["userID"] == 1) {
?>
	<input type="hidden" name="xLoginEnabled" value="1">
<?php
	} else {
?>	
	<tr>
		<td class="table-list-title" valign="top">Login Enabled?</td>
		<td class="table-list-entry1" valign="top"><input type="radio" name="xLoginEnabled" value="0" <?php print $xLoginEnabledNo; ?>> NO&nbsp;&nbsp;&nbsp;<input type="radio" name="xLoginEnabled" value="1" <?php print $xLoginEnabledYes; ?>> YES</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Deny Access To</td>
		<td class="table-list-entry1" valign="top">
<?php
			for ($f = 0; $f < count($sectionsArray); $f++) {
?>
<input type="checkbox" name="sectionField<?php print $f; ?>" value="<?php print $sectionsArray[$f]["name"]; ?>" <?php print $sectionsArray[$f]["selected"]; ?> onClick="recalculateFields();"> <?php print $sectionsArray[$f]["title"]; ?><br>
<?php
			}
?>
		</td>
	</tr>
<?php
	}
?>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
