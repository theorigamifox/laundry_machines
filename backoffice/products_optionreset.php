<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$myForm = new formElements;
	
	dbConnect($dbA);
	
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script>
	function checkFields() {
		 if (confirm("Are you sure you wish to reset selected options?\nNOTE: There is no undo function!")) {
			return true;
		} else {
			return false;
		}
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Global Option Reset (Sets to NO)</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","products_optionreset_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="optionreset">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Option Selection</td>
		<td class="table-list-entry1" valign="top">
			<input type="checkbox" name="xSelectSpecial" value="ALL"> Special Offer Option<br><input type="checkbox" name="xSelectNew" value="ALL"> New Product Option<br><input type="checkbox" name="xSelectTop" value="ALL"> Top Product Option
		</td>
	</tr>	
	<?php
		if (is_array($flagArray)) {
	?>
	<tr>
		<td class="table-list-title" valign="top">Flag Selection</td>
		<td class="table-list-entry1" valign="top">
			<?php
				for ($f = 0; $f < count($flagArray); $f++) {
			?>
					<input type="checkbox" name="xFlag<?php print $flagArray[$f]["flagID"]; ?>" value="ALL"> <?php print $flagArray[$f]["description"]; ?><br>
			<?php
				}
			?>
		</td>
	</tr>
	<?php
		}
	?>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Update Options"); ?></td>
	</tr>
</table>
</form>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
