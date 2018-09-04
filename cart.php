<?php
	include("static/includeBase_front.php");
	include("routines/stockControl.php");

	$pageType = "cart";
	$thisTemplate = "cart.html";
	
	dbConnect($dbA);
	
	$xCmd = getFORM("xCmd");
	if ($cartID == "") { $xCmd = ""; }
	switch ($xCmd) {
		case "remove":
			$pageType = "cart";
			$thisTemplate = "cart.html";
			$cartContents = cartRemoveItem(makeInteger(getFORM("xUnq")));
			doRedirect(configureURL("cart.php"));
			break;
		case "update":
			$formArray = null;
			foreach($_GET as $key => $value) {
				$formArray[$key] = $value;
			}
			foreach($_POST as $key => $value) {
				$formArray[$key] = $value;
			}
			cartUpdateItems($formArray);	
			$pageType = "cart";
			$thisTemplate = "cart.html";
			if (makeSafe(getFORM("xFwd")) == "checkout") {
				doRedirect(configureURL("cart.php?xCmd=checkout"));
			}
			doRedirect(configureURL("cart.php"));
			break;
		case "checkout":
			$pResult = $dbA->query("select * from $tableCartsContents where cartID=\"$cartID\"");
			$prodCount = $dbA->count($pResult);
			$checkoutNotAllowedError = "";
			if ($prodCount == 0) {
				$pageType = "cart";
				$thisTemplate = "cart.html";
				$checkoutNotAllowedError = "EMPTY";
			} else {
				$minimumOrderValue = retrieveOption("minimumOrderValue");
				$orderStopped = false;
				if (retrieveOption("stockCheckoutCheck") == 1) {
					//need to force the stock check here.
					//include("routines/stockControl.php");
					if (checkoutStockCheck() == false) {
						//ok, problem here with the stock so show the correct template etc.
						$pageType = "cart";
						$thisTemplate = "cartstockproblem.html";
						$checkoutNotAllowedError = "STOCK";
						break;
					}
				}
				if ($minimumOrderValue != 0) {
					$cartTotal = 0;
					for ($f =0; $f < count($cartMain["products"]); $f++) {
						$cartTotal = $cartTotal + ($cartMain["products"][$f]["price1"] * $cartMain["products"][$f]["qty"]);
						$cartTotal = $cartTotal + $cartMain["products"][$f]["ooPrice1"];
					}
					
					if ($cartTotal < $minimumOrderValue) {
						$orderStopped = true;
						$checkoutNotAllowedError = "VALUE";
					}
				}
				if ($orderStopped == false) {
					if ($cartID != "") {
						if ($cartMain["currency"]["checkout"] == "N") {
							$dbA->query("update $tableCarts set currencyID=1");
						}
						doRedirect(configureURL("checkout.php?xCmd=login"));
						$dbA->close();
					}
				} else {
					$pageType = "cart";
					$thisTemplate = "cart.html";
				}
			}
			break;
		case "clear":
			$pageType = "cart";
			$thisTemplate = "cart.html";
			clearCart();
			doRedirect(configureURL("cart.php"));
			break;			
		case "add":
			$formArray = null;
			foreach($_GET as $key => $value) {
				$formArray[$key] = $value;
			}
			foreach($_POST as $key => $value) {
				$formArray[$key] = $value;
			}
			$xProd=makeInteger(getFORM("xProd"));
			$xQty=makeInteger(getFORM("qty$xProd"));
			$returnArray = cartAddItem($xProd,$xQty,$formArray);
			$forceCartShow = FALSE;
			if (is_array($returnArray)) {
				if (array_key_exists("error",$returnArray)) {
					switch ($returnArray["error"]) {
						case "EXTRAFIELDS":
							doRedirect(createProductLink($xProd));
							break;
						case "USERINPUT":
							doRedirect(createProductLink($xProd)."&xInpR=".$returnArray["xInpR"]);
							break;
						case "OUTOFSTOCK":
							$thisTemplate = "cart.html";
							include("routines/cartOutputData.php");		
							$tpl->showPage();
							break;
						case "STOCKQUANTITY":
							$thisTemplate = "cart.html";
							include("routines/cartOutputData.php");		
							$tpl->showPage();
							break;
					}
				}
				if (array_key_exists("advisory",$returnArray)) {
					switch ($returnArray["advisory"]) {
						case "LIMITESTOCK":
							$forceShowCart = TRUE;
							break;
					}
				}
			}
			if (retrieveOption("basketAddGoBasket") == 1 || $forceCartShow) {
				doRedirect(configureURL("cart.php"));
			} else {
				$xFwd = urldecode(getFORM("xFwd"));
				if ($xFwd == "") {
					doRedirect(configureURL("cart.php"));
				} else {
					doRedirect(configureURL($xFwd));
				}
			}
			break;
		default:
			$pageType = "cart";
			$thisTemplate = "cart.html";
	}
	if (@$cartMain["taxID"] != 0) {
		$taxRecord = retrieveTaxRecord($cartMain["taxID"]);
		$cartMain["taxname"] = $taxRecord["name"];
		$cartMain["taxtotal"] = formatPrice($taxRecord["standardrate"]);
	} else {
		$cartMain["taxname"] = "";
		$cartMain["taxtotal"] = formatPrice(0);
	}
	
	
	include("routines/cartOutputData.php");		
	
	$tpl->showPage();
	$dbA->close();
	
?>
