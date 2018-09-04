<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<script>
	function checkFields() {
	}
</script>
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title">Redeemed Offer Codes Report</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","offercodes_report_show.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="clearpopular">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">From Date</td>
		<td class="table-list-entry1" valign="top">
		<select name="xDayF" class="form-inputbox">
		<?php
			$tDay = date("d");
			for ($f = 1; $f < 31; $f++) {
				if ($f == 1) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				if ($f < 10) {
					$fshow = "0".$f;
				} else {
					$fshow = $f;
				}
		?>
			<option <?php print $selected; ?>><?php print $fshow; ?></option>
		<?php
			}
		?>
		</select>&nbsp;<select name="xMonthF" class="form-inputbox">
		<?php
			$tMonth = date("m");
			for ($f = 1; $f <= 12; $f++) {
				if ($f == $tMonth) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				if ($f < 10) { $padder = "0"; } else { $padder = ""; }
		?>
			<option <?php print $selected; ?>><?php print $padder.$f; ?></option>
		<?php
			}
		?>
		</select>&nbsp;<select name="xYearF" class="form-inputbox">
		<?php
			$thisYear = date("Y");
			for ($f = 2003; $f <= $thisYear; $f++) {
				if ($f == $thisYear) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
		?>
		<option <?php print $selected; ?>><?php print $f; ?></option>
		<?php
			}
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">To Date</td>
		<td class="table-list-entry1" valign="top">
		<select name="xDayT" class="form-inputbox">
		<?php
			$tDay = date("d");
			for ($f = 1; $f < 31; $f++) {
				if ($f == $tDay) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				if ($f < 10) {
					$fshow = "0".$f;
				} else {
					$fshow = $f;
				}
		?>
			<option <?php print $selected; ?>><?php print $fshow; ?></option>
		<?php
			}
		?>
		</select>&nbsp;<select name="xMonthT" class="form-inputbox">
		<?php
			$tMonth = date("m");
			for ($f = 1; $f <= 12; $f++) {
				if ($f == $tMonth) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
				if ($f < 10) { $padder = "0"; } else { $padder = ""; }
		?>
			<option <?php print $selected; ?>><?php print $padder.$f; ?></option>
		<?php
			}
		?>
		</select>&nbsp;<select name="xYearT" class="form-inputbox">
		<?php
			$thisYear = date("Y");
			for ($f = 2003; $f <= $thisYear; $f++) {
				if ($f == $thisYear) {
					$selected = "SELECTED";
				} else {
					$selected = "";
				}
		?>
		<option <?php print $selected; ?>><?php print $f; ?></option>
		<?php
			}
		?>
		</select>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Show Report"); ?></td>
	</tr>
	</table>
	</form>

</center>
</BODY>
</HTML>
