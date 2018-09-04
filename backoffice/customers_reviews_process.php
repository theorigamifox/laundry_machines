<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$recordType = "Review";
	$tableName = $tableReviews;
	$linkBackButton = "Customer Reviews";
	$linkBackLink = "customers_reviews.php?xType=UNMOD&";
	if (getFORM("xReturn") != "") {
		$linkBackLink = urldecode(getFORM("xReturn"))."&";
	}		

	$xAction=getFORM("xAction");
	if ($xAction == "delete") {
		$xReviewID = getFORM("xReviewID");
		if (!$dbA->doesIDExist($tableName,"reviewID",$xReviewID,$uRecord)) {
			setupProcessMessage($recordType,getFORM($xTitle),"error_existance","BACK","");
		} else {
			$reviewID = $uRecord["reviewID"];
			$dbA->deleteRecord($tableName,"reviewID",$xReviewID);
			userLogActionDelete($recordType,$uRecord["reviewID"]);
			doRedirect("$linkBackLink".userSessionGET());
		}
	}
	if ($xAction == "update") {
		$xReviewID = getFORM("xReviewID");
		$xCode = getFORM("xCode");
		$rArray[] = array("title",getFORM("xTitle"),"S");			
		$rArray[] = array("review",getFORM("xReview"),"S");
		$rArray[] = array("visible",getFORM("xVisible"),"YN");
		$dbA->updateRecord($tableName,"reviewID=$xReviewID",$rArray,0);
		userLogActionUpdate($recordType,$xReviewID);
		doRedirect("$linkBackLink".userSessionGET());
	}
?>
