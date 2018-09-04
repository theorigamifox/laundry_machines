<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$xType=getFORM("xType");
	if ($xType=="edit") {
		$xReviewID = getFORM("xReviewID");
		$pageTitle = "Edit Review";
		$submitButton = "Update Review";
		$hiddenFields = "<input type='hidden' name='xAction' value='update'><input type='hidden' name='xReviewID' value='$xReviewID'>".hiddenReturnPOST();
		dbConnect($dbA);
		$uResult = $dbA->query("select $tableReviews.*, $tableProducts.code, $tableProducts.name as pname from $tableReviews,$tableProducts where $tableReviews.productID = $tableProducts.productID and $tableReviews.reviewID=$xReviewID");	
		$uRecord = $dbA->fetch($uResult);
		$dbA->close();
		$productString = ($uRecord["code"] != "") ? $uRecord["code"]." : ".$uRecord["pname"] : $uRecord["pname"];
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
<?php $myForm->createForm("detailsForm","customers_reviews_process.php",""); ?>
<?php userSessionPOST(); ?>
<?php print $hiddenFields; ?>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title" valign="top">Author</td>
		<td class="table-list-entry1" valign="top"><?php print $uRecord["name"]; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Product</td>
		<td class="table-list-entry1" valign="top"><?php print $productString; ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Rating</td>
		<td class="table-list-entry1" valign="top"><?php print $uRecord["rating"]; ?></td>
	</tr>		
	<tr>
		<td class="table-list-title" valign="top">Title</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createText("xTitle",50,250,@getGENERIC("title",$uRecord),"general"); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Review</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createTextArea("xReview",40,10,@getGENERIC("review",$uRecord),""); ?></td>
	</tr>
	<tr>
		<td class="table-list-title" valign="top">Visible</td>
		<td class="table-list-entry1" valign="top"><?php $myForm->createYesNo("xVisible",@getGENERIC("visible",$uRecord),"YN"); ?></td>
	</tr>
	<tr>
		<td class="table-list-entry0" colspan="2" align="right"><?php $myForm->createBack(); ?>&nbsp;<?php $myForm->createSubmit("submit",$submitButton); ?></td>
	</tr>	
</table>
</form>
</center>
</BODY>
</HTML>
