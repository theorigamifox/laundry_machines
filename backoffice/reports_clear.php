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
		if (confirm("Are you sure you wish to clear the product / section popularity data?")) {
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
		<td class="detail-title">Clear Report Data</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","reports_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="clearpopular">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Clear Popularity Records Up To And Including</td>
		<td class="table-list-entry1" valign="top">
		<select name="xDay" class="form-inputbox">
		<?php
			$tDay = date("d");
			for ($f = 1; $f <= 31; $f++) {
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
		</select>&nbsp;<select name="xMonth" class="form-inputbox">
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
		</select>&nbsp;<select name="xYear" class="form-inputbox">
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
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Clear Popularity Data Now"); ?></td>
	</tr>
	</table>
	</form>
	<p>
	<script>
	function checkFields2() {
		if (confirm("Are you sure you wish to clear the abandoned cart data?")) {
			return true;
		} else {
			return false;
		}
	}
</script>
<form name="detailsForm2" action="reports_options_process.php" method="POST" onSubmit="return checkFields2();">
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="clearcarts">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">

	<tr>
		<td class="table-list-title" valign="top">Clear Abandoned Carts Up To And Including</td>
		<td class="table-list-entry1" valign="top">
		<select name="xDay" class="form-inputbox">
		<?php
			$tDay = date("d");
			for ($f = 1; $f <= 31; $f++) {
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
		</select>&nbsp;<select name="xMonth" class="form-inputbox">
		<?php
			$tMonth = date("m");
			$tMonth --;
			if ($tMonth == 0) {
				$tMonth = 12;
				$removeOneYear = true;
			} else {
				$removeOneYear = false;
			}
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
		</select>&nbsp;<select name="xYear" class="form-inputbox">
		<?php
			$thisYear = date("Y");
			//if ($removeOneYear) { $thisYear--; }
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
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Clear Abandoned Carts Now"); ?></td>
	</tr>	
</table>
</form>
	<p>
	<script>
	function checkFields3() {
		if (confirm("Are you sure you wish to clear the search statistics data?")) {
			return true;
		} else {
			return false;
		}
	}
</script>
<form name="detailsForm2" action="reports_options_process.php" method="POST" onSubmit="return checkFields3();">
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="clearsearch">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">

	<tr>
		<td class="table-list-title" valign="top">Clear Search Statistics Up To And Including</td>
		<td class="table-list-entry1" valign="top">
		<select name="xDay" class="form-inputbox">
		<?php
			$tDay = date("d");
			for ($f = 1; $f <= 31; $f++) {
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
		</select>&nbsp;<select name="xMonth" class="form-inputbox">
		<?php
			$tMonth = date("m");
			$tMonth --;
			if ($tMonth == 0) {
				$tMonth = 12;
				$removeOneYear = true;
			} else {
				$removeOneYear = false;
			}
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
		</select>&nbsp;<select name="xYear" class="form-inputbox">
		<?php
			$thisYear = date("Y");
			//if ($removeOneYear) { $thisYear--; }
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
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createSubmit("submit","Clear Search Statistics Now"); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
