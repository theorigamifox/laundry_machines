<?php
	include("static/includeBase_front.php");

	if (retrieveOption("useRewriteURLs") == 0 || array_key_exists("xProd",$_GET)) {
		$xProd=makeInteger(getFORM("xProd"));
	} else {
		$xBits = explode(".php/",$_SERVER["REQUEST_URI"]);
		$xOptions = explode("/",@$xBits[1]);
		if (count($xOptions) < 2) {
			doRedirect(configureURL("index.php"));
		}
		$_GET["xProd"] = makeInteger($xOptions[0]);
		$xProd = makeInteger($xOptions[0]);
		$_GET["xSec"] = makeInteger($xOptions[1]);
		if (count($xOptions) > 2) {
			if ($xOptions[2] == "rev") {
				$_GET["xCmd"] = "rev";
			}
		}
	}
	$xInpR = makeInteger(getFORM("xInpR"));
	dbConnect($dbA);
	if ($xProd != 0) {
		if (getFORM("xCmd") == "rev") {
			if (retrieveOption("reviewsEnabled") == 0) { moduleError("Customer Reviews"); }
			$thisTemplate = "productreviews.html";
			$pageType = "productreviews";
		} else {
			$thisTemplate = "product.html";
			$pageType = "product";
		}
		if (retrieveOption("stockWarningNotZero") == 0) {
			$scBit = "((scActionZero = 1 and scEnabled='Y' and scLevel > 0) or (scEnabled = 'N') or (scEnabled = 'Y' and scActionZero != 1))";
			//$scBit = "((scEnabled='Y' and scLevel<1 and scActionZero=0) or (scEnabled != 'Y') or (scLevel > 0))";
		} else {
			$scBit = "((scActionZero = 1 and scEnabled='Y' and scLevel > scWarningLevel) or (scEnabled = 'N') or (scEnabled = 'Y' and scActionZero != 1))";
			//$scBit = "((scEnabled='Y' and scLevel<=scWarningLevel and scActionZero=0) or (scEnabled != 'Y') or (scLevel > scWarningLevel))";
		}
		if (retrieveOption("featureStockControl") == 1) {
			$stockControlClause = "$scBit and ($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y' or (visible = 'N' and allowDirect='Y')) and ";
		} else {
			$stockControlClause = "($tableProducts.productID > 1) and (accTypes like '%;".$cartMain["accTypeID"].";%' or accTypes like '%;0;%') and (visible = 'Y' or (visible = 'N' and allowDirect='Y')) and ";
		}
		$result = $dbA->query("select * from $tableProducts where $stockControlClause productID=$xProd");
		$count = $dbA->count($result);
		if ($count != 0) {
			$record = $dbA->fetch($result);
			if ((strpos($record["accTypes"],";".$cartMain["accTypeID"].";") === false && strpos($record["accTypes"],";0;") === false) || ($record["visible"] == "N" && $record["allowDirect"] == "N")) {
				doRedirect(configureURL("index.php"));
			} else {
				if (getFORM("xCmd") != "rev") {
					$thisTemplate = $record["templateFile"];
					addToRecentlyViewed("product",$xProd);
				}
			}			
			addReportsPopularityRecord("P",$xProd);
		} else {
			doRedirect(configureURL("index.php"));
		}
		include("routines/cartOutputData.php");
		
		$tpl->showPage();
		$dbA->close();
	} else {
		doRedirect(configureURL("index.php"));
	}
?>