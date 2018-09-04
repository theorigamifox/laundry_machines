<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$dbA = new dbAccess();
	$dbA-> connect($databaseHost,$databaseUsername,$databasePassword,$databaseName);
	
	$myForm = new formElements;

	$bestsellerCalcArray = array (
			array("text"=>"Quantity Sold","value"=>"Q"),
			array("text"=>"Number Of Times Ordered","value"=>"O")
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
		<td class="detail-title">List Settings</td>
	</tr>
</table>
<p>
<?php $myForm->createForm("detailsForm","general_options_process.php",""); ?>
<?php userSessionPOST(); ?>
<input type="hidden" name="xAction" value="options">
<input type="hidden" name="xType" value="list">
<?php print hiddenFromPOST(); ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Bestsellers</b></center></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Bestsellers To List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xBestsellersLimit",10,5,retrieveOption("bestsellersLimit"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Calculate By</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createSelect("xBestsellersCalc",retrieveOption("bestsellersCalc"),"BOTH",$bestsellerCalcArray); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Limit Order History Query To</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xBestsellersTimeLimit",7,5,retrieveOption("bestsellersTimeLimit"),"integer"); ?> days (0 = all order history)</td>
	</tr>	
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Other Lists</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top">Maximum New Products To List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xNewProductsLimit",10,5,retrieveOption("newProductsLimit"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Top Products To List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTopProductsLimit",10,5,retrieveOption("topProductsLimit"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Special Offers To List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xSpecialOffersLimit",10,5,retrieveOption("specialOffersLimit"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Recommendations To List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRecommendedLimit",10,5,retrieveOption("recommendedLimit"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Reviews To List On Product Page</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xCustomerReviewsLimit",10,5,retrieveOption("customerReviewsLimit"),"integer"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Maximum Random Products To List</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xRandomProductsMax",10,5,retrieveOption("randomProductsMax"),"integer"); ?></td>
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
