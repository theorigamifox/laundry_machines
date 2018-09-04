<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;
	
	$sortModeArray = array (
		array("Product Name","name"),
		array("Product Code","code"),
		array("Price (High to Low)","price1 DESC"),
		array("Price (Low to High)","price1"),
		array("Quantity","qty")
	);
	
	$sortArray = array (
			array("text"=>"Product Name","value"=>"name"),
			array("text"=>"Product Code","value"=>"code"),
			array("text"=>"Price (High to Low)","value"=>"pricehl"),
			array("text"=>"Price (Low to High)","value"=>"pricelh"),
			array("text"=>"Associated Position","value"=>"position"),
			array("text"=>"Random - selects sort order randomly","value"=>"random")
			);
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
		<td class="detail-title">Basket Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="basket">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>General</b></center></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Basket Sort Order</td>
		<td class="table-list-entry1" valign="top">
			<select name="xCartSortOrder" class="form-inputbox">
			<?php
				$xCartSortOrder = retrieveOption("cartSortOrder");
				for ($f = 0; $f < count($sortModeArray); $f++) {
					if ($sortModeArray[$f][1] == $xCartSortOrder) {
						$thisSelected = "SELECTED";
					} else {
						$thisSelected = "";
					}
			?>
				<option value="<?php print $sortModeArray[$f][1]; ?>" <?php print $thisSelected; ?>><?php print $sortModeArray[$f][0]; ?></option>
			<?php
				}
			?>
		</select>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Show Basket After Add?</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xBasketAddGoBasket",retrieveOption("basketAddGoBasket"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Associated Products</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Show Associated Products</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xCartAssociatedActivated",retrieveOption("cartAssociatedActivated"),"01"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Associated Products To Show</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCartAssociatedMax",10,5,retrieveOption("cartAssociatedMax"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Select And Sort By</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xCartAssociatedOrder",retrieveOption("cartAssociatedOrder"),"BOTH",$sortArray); ?></td>
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
