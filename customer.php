<?php
	include("static/includeBase_front.php");
	include("routines/fieldValidation.php");
	
	$xCmd=getFORM("xCmd");

	if ($xCmd == "login") {
		$xFwd = urldecode(getFORM("xFwd"));		
		$loginError = "";
		$xEmailAddress = makeSafe(getFORM("xEmailAddress"));
		$xCustPassword = makeSafe(getFORM("xCustPassword"));
		if (chop($xEmailAddress) == "" || chop($xCustPassword) == "") {
			dbConnect($dbA);
			$thisTemplate = "customerlogin.html";
			$loginError = "Y";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
		dbConnect($dbA);
		$isOK = performCustomerLogin($xEmailAddress,$xCustPassword);
		if (!$isOK) {
			$thisTemplate = "customerlogin.html";
			$loginError = "Y";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";		
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
		$xFwd = $xFwd;
		if ($xFwd != "") {
			doRedirect(configureURL($xFwd));
		}
		if (retrieveOption("customerLoginGoAccount")) {
			doRedirect(configureURL("customer.php?xCmd=account"));
		} else {
			doRedirect(configureURL("index.php"));
		}
		$dbA->close();
	}
	if ($xCmd == "account") {
		dbConnect($dbA);
		if ($customerMain["loggedin"] == "N") {
			$xFwd = "customer.php?xCmd=account";
			$thisTemplate = "customerlogin.html";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}	
		if ($customerMain["loggedin"] == "Y") {
			$thisTemplate = "customeraccount.html";
			$pageType = "customeraccount";		
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
	}		
			
	if ($xCmd == "logout") {
		dbConnect($dbA);
		setcookie("jssCustomer","",time()+2010800);
		srand((double)microtime()*1000000);
		$rID = rand();
		$rID = md5($rID);
		$countryID=makeInteger(retrieveOption("defaultCountry"));
		$dbA->query("update $tableCarts set rID=\"\",customerID=0,accTypeID=1,country=$countryID,county='' where cartID=\"$cartID\"");
		$dbA->query("update $tableCustomers set rID=\"$rID\" where customerID=$jssCustomer");
		$jssCustomer = "";
		$customerMain = null;
		$customerMain["loggedin"] = "N";
		doRedirect(configureURL("index.php"));
		$dbA->close();
	}
	if ($xCmd == "register") {
		dbConnect($dbA);
		$thisTemplate = "customernew.html";
		$pageType = "customernew";
		$xFwd = urldecode(getFORM("xFwd"));
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	

	if ($xCmd == "revform") {
		dbConnect($dbA);
		if (retrieveOption("reviewsEnabled") == 0) { moduleError("Customer Reviews"); }
		$xProd = makeInteger(getFORM("xProd"));
		if ($customerMain["loggedin"] == "N") {
			$xFwd = "customer.php?xCmd=revform&xProd=".$xProd;
			$thisTemplate = "customerlogin.html";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}
		if ($xProd > 0) {
			$result = $dbA->query("select * from $tableProducts where productID=$xProd");
			if ($dbA->count($result) > 0) {
				$pRecord = $dbA->fetch($result);
				$customerMain["review"]["product"] = $pRecord;
				$customerMain["review"]["product"]["link"] = configureURL("product.php?xProd=$xProd");
				$result = $dbA->query("select * from $tableReviews where customerID=$jssCustomer and productID=$xProd");
				if ($dbA->count($result) > 0) {
					$customerMain["review"]["error"] = "DUPLICATE";
					$thisTemplate = "customerreviewerror.html";
					$pageType = "customerreviewerror";
				} else {
					$thisTemplate = "customerreview.html";
					$pageType = "customerreview";
				}				
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}
		}
		$dbA->close();
		doRedirect(configureURL($xFwd));
	}	
	if ($xCmd == "revadd") {
		dbConnect($dbA);
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		if (retrieveOption("reviewsEnabled") == 0) { moduleError("Customer Reviews"); }
		$xProd = makeInteger(getFORM("xProd"));
		$xRating = makeInteger(getFORM("xRating"));
		$xTitle = chop(makeSafe(getFORM("xTitle")));
		$xDisplayName = makeInteger(getFORM("xDisplayName"));
		$xReview = chop(makeSafe(getFORM("xReview")));
		$xRating = ($xRating > 5) ? 5 : $xRating;
		$xRating = ($xRating < 1) ? 1 : $xRating;
		if ($xProd > 0) {
			$result = $dbA->query("select * from $tableProducts where productID=$xProd");
			if ($dbA->count($result) > 0) {
				$pRecord = $dbA->fetch($result);
				$customerMain["review"]["product"] = $pRecord;
				$customerMain["review"]["product"]["link"] = configureURL("product.php?xProd=$xProd");
				$result = $dbA->query("select * from $tableReviews where customerID=$jssCustomer and productID=$xProd");
				if ($dbA->count($result) > 0) {
					$customerMain["review"]["error"] = "DUPLICATE";
					$thisTemplate = "customerreviewerror.html";
					$pageType = "customerreviewerror";
				} else {
					if ($xReview == "") {
						$thisTemplate = "customerreview.html";
						$pageType = "customerreview";
						$customerMain["review"]["error"] = "NOREVIEW";
					} else {
						$rArray = null;
						$timestamp = date("Ymd");
						$rArray[] = array("productID",$xProd,"N");
						$rArray[] = array("customerID",$jssCustomer,"S");
						$rArray[] = array("rating",$xRating,"N");
						$rArray[] = array("title",$xTitle,"S");
						$rArray[] = array("review",$xReview,"S");
						$rArray[] = array("reviewdate",$timestamp,"S");
						if ($xDisplayName == 1) {
							$theName = "Anonymous";
						} else {
							$theName = $customerMain["forename"]." ".$customerMain["surname"];
						}
						$rArray[] = array("name",$theName,"S");
						if (retrieveOption("reviewsModerated") == 1) {
							$rArray[] = array("visible","N","S");
						} else {
							$rArray[] = array("visible","Y","S");
						}
						$result = $dbA->insertRecord($tableReviews,$rArray,0);
						$reviewID = $dbA->lastID();
						$cresult = $dbA->query("select * from $tableReviews where reviewID=$reviewID");
		
						$reviewRecord = $dbA->fetch($cresult);
						
						$cresult = $dbA->query("select * from $tableProducts where productID=$xProd");
						if ($dbA->count($cresult) > 0) {
							$pRecord = $dbA->fetch($cresult);
							$reviewRecord["product"]["name"] = $pRecord["name"];
							$reviewRecord["product"]["code"] = $pRecord["code"];
						}
						include("routines/emailOutput.php");
		
						@sendEmail("COMPANY","","MERCHREVIEW");
						$thisTemplate = "customerreviewthanks.html";
						$pageType = "customerreviewthanks";
					}
				}
				include("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}			
		}
	}
	//wish list functionality
	if ($xCmd == "wladd") {
		dbConnect($dbA);
		$xProd = makeInteger(getFORM("xProd"));
		if ($customerMain["loggedin"] == "N") {
			$xFwd = "customer.php?xCmd=wladd&xProd=".$xProd;
			$thisTemplate = "customerlogin.html";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}
		if ($xProd > 0) {
			$wishlistID = getWishListID();
			$dbA->query("insert into $tableWishlists (wishlistID,productID,qty,comment) VALUES('$wishlistID',$xProd,1,'')");
		}
		$dbA->close();
		$xCmd = "wlshow";
	}
	if ($xCmd == "wldelete") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		$xProd = makeInteger(getFORM("xProd"));
		dbConnect($dbA);
		$wishlistID = getWishListID();
		$dbA->query("delete from $tableWishlists where wishlistID='$wishlistID' and productID=$xProd");
		$dbA->close();
		$xCmd = "wlshow";	
	}
	if ($xCmd == "wlclear") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		$xProd = makeInteger(getFORM("xProd"));
		dbConnect($dbA);
		$wishlistID = getWishListID();
		$dbA->query("delete from $tableWishlists where wishlistID='$wishlistID'");
		$dbA->close();
		$xCmd = "wlshow";
	}
	if ($xCmd == "wlupdate") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		dbConnect($dbA);
		$wishlistID = getWishListID();
		$result = $dbA->query("select * from $tableWishlists where wishlistID='$wishlistID' order by uniqueID");
		$count = $dbA->count($result);
		for ($f = 0; $f < $count; $f++) {
			$wlRecord = $dbA->fetch($result);
			$theQty = makeInteger(getFORM("qty".$wlRecord["uniqueID"]));
			if ($theQty == 0) {
				$theQty = 1;
			}
			$theComment = addSlashes(getFORM("comment".$wlRecord["uniqueID"]));
			$thisUnique = $wlRecord["uniqueID"];
			$dbA->query("update $tableWishlists set qty=$theQty, comment=\"$theComment\" where uniqueID=$thisUnique");
		}
		$dbA->close();
		$xCmd = "wlshow";
			
	}
	if ($xCmd == "wlshow") {
		dbConnect($dbA);
		if ($customerMain["loggedin"] == "N") {
			$xFwd = "customer.php?xCmd=wlshow";
			$thisTemplate = "customerlogin.html";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}	
		$thisTemplate = "customerwishlist.html";
		$pageType = "customerwishlist";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	if ($xCmd == "wlsend") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		dbConnect($dbA);
		$xEmailList = chop(makeSafe(getFORM("xEmailList")));
		include("routines/emailOutput.php");
		sendEmail($customerMain["email"],$xEmailList,"WISHLIST");		
		doRedirect(configureURL("customer.php?xCmd=wlshow"));
	}
	if ($xCmd == "acupdate") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		dbConnect($dbA);
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0");
		$rArray = null;
		$xEmailAddress = makeSafe(getFORM("xEmailAddress"));
		$xNewsletter = makeSafe(getFORM("xNewsletter"));
		$xPassword = chop(makeSafe(getFORM("xPassword")));
		$xRepeatPassword = chop(makeSafe(getFORM("xRepeatPassword")));
		$customerMain["error"] = "N";
		if ($xPassword != "" && $xRepeatPassword != "") {
			if ($xPassword != $xRepeatPassword) {
				$customerMain["error"] = "Y";
				$customerMain["passworderror"] = "NOTMATCHING";
			} else {
				if ($xPassword == "") {
					$customerMain["error"] = "Y";
					$customerMain["passworderror"] = "BLANK";
				} else {
					if (strlen($xPassword) < retrieveOption("minPasswordLength")) {
						$customerMain["error"] = "Y";
						$customerMain["passworderror"] = "TOOSHORT";
					}
				}
			}
			if ($customerMain["error"] == "N") {
				$rArray[] = array("password",md5($xPassword),"S");
			}		
		}
		$error = validateFields($fieldList,$customerMain);
		
		$query=$dbA->query("select * from $tableCustomers where email=\"$xEmailAddress\" and customerID != $jssCustomer");
		if (!validateIndividual($xEmailAddress,"Email Address","")) {
			$customerMain["emailerror"] = "INVALID";
			$customerMain["error"] = "Y";
		} else {
			if ($dbA->count($query) > 0) {
				//changed their email address, but it already exists!
				$customerMain["emailerror"] = "EXISTS";
				$customerMain["error"] = "Y";
			} else {
				$rArray[] = array("email",$xEmailAddress,"S");
			}
		}
		if ($customerMain["error"] == "Y") {
			$thisTemplate = "customerdetails.html";
			$pageType = "customerdetails";
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$rArray[] = array($fieldList[$f]["fieldname"],$thisField,"S");
		}
		$rArray[] = array("newsletter",$xNewsletter,"YN");
		$dbA->updateRecord($tableCustomers,"customerID=\"$jssCustomer\"",$rArray,0);
		if ($xNewsletter == "Y") {
			$dbA->query("insert into $tableNewsletter (emailaddress) VALUES(\"$xEmailAddress\")");
		} else {
			$dbA->query("delete from $tableNewsletter where emailaddress=\"$xEmailAddress\"");
		}	
		$cresult = $dbA->query("update $tableCarts set country=".makeInteger(getFORM("country")).", county='".makeSafe(getFORM("county"))."' where cartID='$cartID'");
		$cresult = $dbA->query("select * from $tableCustomers where customerID=$jssCustomer");
		$customerMain = $dbA->fetch($cresult);
		$dbA->close();
		doRedirect(configureURL("customer.php?xCmd=acshow&xUpd=Y"));
	}	
		
	if ($xCmd == "acshow") {
		dbConnect($dbA);
		$xUpd = makeSafe(getFORM("xUpd"));
		if ($customerMain["loggedin"] == "N") {
			$xFwd = "customer.php?xCmd=acshow";
			$thisTemplate = "customerlogin.html";
			$pageType = "customerlogin";
			$customerMain = null;
			$customerMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}
		if ($xUpd == "Y") {
			$customerMain["updated"] = "Y";
		}
		$thisTemplate = "customerdetails.html";
		$pageType = "customerdetails";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	
	if ($xCmd == "addelete") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		dbConnect($dbA);
		$xAid = makeInteger(chop(getFORM("xAid")));
		$result = $dbA->query("delete from $tableCustomersAddresses where addressID=$xAid and customerID=$jssCustomer");
		$xCmd = "adshow";
	}
	
	if ($xCmd == "adadd") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		dbConnect($dbA);
		$thisTemplate = "customeraddress.html";
		$pageType = "customeraddressesadd";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	
	if ($xCmd == "adcreate") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		//ok, we're creating a new address here
		dbConnect($dbA);
		$rArray = null;
		$rArray[] = array("customerID",$jssCustomer,"N");
		$addressRecord = null;
		$addressRecord["error"] = "N";
		
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0");
		$error = validateFields($fieldList,$addressRecord);
		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$rArray[] = array($fieldList[$f]["fieldname"],$thisField,"S");
		}
		
		if ($addressRecord["error"] == "Y") {
			$thisTemplate = "customeraddress.html";
			$pageType = "customeraddressesadd";
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
		$dbA->insertRecord($tableCustomersAddresses,$rArray,0);
		$dbA->Close();
		$xCmd = "adshow";
	}
	
	if ($xCmd == "adedit") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		//show the editing page
		dbConnect($dbA);
		$xAid = makeInteger(chop(getFORM("xAid")));
		$result = $dbA->query("select * from $tableCustomersAddresses where addressID=$xAid and customerID=$jssCustomer");
		$addressRecord = $dbA->fetch($result);
		$thisTemplate = "customeraddress.html";
		$pageType = "customeraddressesedit";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	
	if ($xCmd == "adupdate") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		//ok, we're updating an address here
		dbConnect($dbA);
		$xAid = makeInteger(chop(getFORM("xAid")));
		$rArray = null;
		$rArray[] = array("customerID",$jssCustomer,"N");
		$addressRecord = null;
		$addressRecord["error"] = "N";
		
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' and visible=1 and internalOnly=0");
		$error = validateFields($fieldList,$addressRecord);
		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$rArray[] = array($fieldList[$f]["fieldname"],$thisField,"S");
		}
		
		if ($addressRecord["error"] == "Y") {
			$thisTemplate = "customeraddress.html";
			$pageType = "customeraddressesedit";
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}		
		$dbA->updateRecord($tableCustomersAddresses,"addressID=$xAid and customerID=$jssCustomer",$rArray,0);
		$dbA->Close();
		$xCmd = "adshow";	
	}

	if ($xCmd == "adshow") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		dbConnect($dbA);
		$thisTemplate = "customeraddresses.html";
		$pageType = "customeraddresses";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	
	if ($xCmd == "fpshow") {
		dbConnect($dbA);
		$thisTemplate = "forgottenpassword.html";
		$pageType = "forgottenpassword";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	
	if ($xCmd == "fgsend") {
		dbConnect($dbA);
		$xEmailAddress = makeSafe(getFORM("xEmailAddress"));
		$result = $dbA->query("select * from $tableCustomers where email=\"$xEmailAddress\"");
		$count = $dbA->count($result);
		if ($count == 0) {
			$thisTemplate = "forgottenpassword.html";
			$emailError = "Y";
			$pageType = "forgottenpassword";
			$customerMain = null;
			$customerMain["loggedin"] = "N";			
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}		
		$customerMain = $dbA->fetch($result);
		include("routines/emailOutput.php");
		$newPassword = createRandomPassword(10);
		$customerMain["newpassword"] = $newPassword;
		$newPassword = md5($newPassword);
		$result = $dbA->query("update $tableCustomers set password=\"$newPassword\" where email=\"$xEmailAddress\"");
		sendEmail($customerMain["email"],"","NEWPASSWORD");
		$thisTemplate = "forgottenpasswordsuccess.html";
		$pageType = "forgottenpasswordsuccess";
		$customerMain["loggedin"] = "N";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();		
		exit;
	}

	if ($xCmd == "orders") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		dbConnect($dbA);
		$thisTemplate = "customerorderlist.html";
		$pageType = "customerorders";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}

	if ($xCmd == "vieworder") {
		if ($customerMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		dbConnect($dbA);
		$orderID=makeInteger(getFORM("xOid"))-retrieveOption("orderNumberOffset");
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID and customerID=".$customerMain["customerID"]);
		if ($dbA->count($result) == 0) {
			doRedirect(configureURL("index.php"));
		}
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID order by lineID");
		$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
		for ($f = 0; $f < count($orderProducts); $f++) {
			if ($orderProducts[$f]["isDigital"] == "Y") {
				//this is a digital one!
				$dResult = $dbA->query("select * from $tableDigitalPurchases where downloadID=".$orderProducts[$f]["downloadID"]);
				if ($dbA->count($dResult) > 0) {
					$digitalRecord = $dbA->fetch($dResult);
					$digitalLink = configureURL("digital.php?xRef=".$digitalRecord["downloadRef"]);
					$orderProducts[$f]["downloadLink"] = $digitalLink;
					$orderProducts[$f]["regName"] = $digitalRecord["regName"];
					$orderProducts[$f]["regCode"] = $digitalRecord["regCode"];
				}
			}
		}
		$orderArray["products"] = $orderProducts;
		$thisTemplate = "customerorder.html";
		$pageType = "customerorder";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	
	if ($xCmd == "create") {
		dbConnect($dbA);
		$xFwd = makeSafe(getFORM("xFwd"));
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 and internalOnly=0");
		$rArray = null;
		$xEmailAddress = chop(makeSafe(getFORM("xEmailAddress")));
		$xNewsletter = chop(makeSafe(getFORM("xNewsletter")));
		$xPassword = chop(makeSafe(getFORM("xPassword")));
		$xRepeatPassword = chop(makeSafe(getFORM("xRepeatPassword")));
		$customerMain["error"] = "N";
		$customerMain["email"] = $xEmailAddress;
		if ($xPassword != $xRepeatPassword) {
			$customerMain["error"] = "Y";
			$customerMain["passworderror"] = "NOTMATCHING";
		} else {
			if ($xPassword == "") {
				$customerMain["error"] = "Y";
				$customerMain["passworderror"] = "BLANK";
			} else {
				if (strlen($xPassword) < retrieveOption("minPasswordLength")) {
					$customerMain["error"] = "Y";
					$customerMain["passworderror"] = "TOOSHORT";
				}
			}
		}
		if (!validateIndividual($xEmailAddress,"Email Address","")) {
			$customerMain["error"] = "Y";
			$customerMain["emailerror"] = "INVALID";
		}
		$query=$dbA->query("select * from $tableCustomers where email=\"$xEmailAddress\"");
		
		$error = validateFields($fieldList,$customerMain);
		
		if ($dbA->count($query) > 0) {
			$customerMain["emailerror"] = "EXISTS";
			$customerMain["error"] = "Y";
		}
		if ($customerMain["error"] == "Y") {
			$thisTemplate = "customernew.html";
			$pageType = "customernewe";
			$customerMain["email"] = $xEmailAddress;
			$xFwd = urlencode($xFwd);
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}

		$rArray[] = array("newsletter",$xNewsletter,"YN");
		$rArray[] = array("email",$xEmailAddress,"S");
		$rArray[] = array("date",date("Ymd"),"S");
		$rArray[] = array("password",md5($xPassword),"S");
		$rArray[] = array("accTypeID",retrieveOption("customerDefaultAccount"),"N");
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],makeSafe(getFORM($fieldList[$f]["fieldname"])),"S");
		}
		$dbA->insertRecord($tableCustomers,$rArray,0);
		$jssCustomer = $dbA->lastID();
		if ($xNewsletter == "Y") {
			$dbA->query("insert into $tableNewsletter (emailaddress) VALUES(\"$xEmailAddress\")");
		}
		$cresult = $dbA->query("update $tableCarts set country=".makeInteger(getFORM("country"))." where cartID='$cartID'");
		$cresult = $dbA->query("select * from $tableCustomers where customerID=$jssCustomer");
		
		$customerMain = $dbA->fetch($cresult);
		$customerMain["password"] = $xPassword;
		include("routines/emailOutput.php");
		
		@sendEmail($customerMain["email"],"","CUSTACCOPEN");
		@sendEmail("COMPANY","","MERCHACCOPEN");
		
		$customerMain["loggedin"] = "N";
		setcookie("jssCustomer",$jssCustomer,time()+2010800);
		if (retrieveOption("autoCustomerLogin") == 1) {
			$isOK = performCustomerLogin($xEmailAddress,$xPassword);
			if (!$isOK) {
				$thisTemplate = "customerlogin.html";
				$loginError = "Y";
				$pageType = "customerlogin";
				$customerMain = null;
				$customerMain["loggedin"] = "N";		
				include ("routines/cartOutputData.php");
				$tpl->showPage();
				$dbA->close();
				exit;
			}
			if ($xFwd != "") {
				doRedirect(configureURL(urldecode($xFwd)));
			} else {
				if (retrieveOption("customerLoginGoAccount")) {
					doRedirect(configureURL("customer.php?xCmd=account"));
				} else {
					$thisTemplate = "customerlogin.html";
					$pageType = "customerlogin";
					include("routines/cartOutputData.php");
					$tpl->showPage();
				}
			}
		} else {
			if ($xFwd != "") {
				doRedirect(configureURL(urldecode($xFwd)));
			}
			$thisTemplate = "customerlogin.html";
			$pageType = "customerlogin";
			include("routines/cartOutputData.php");
			$tpl->showPage();
		}
		$dbA->close();	
		exit;
	}
	$xSec=1;
	$thisTemplate = "index.html";
	dbConnect($dbA);
	include("routines/cartOutputData.php");
	$tpl->showPage();
	$dbA->close();
	
	function getWishListID() {
		global $dbA,$tableCustomers,$customerMain,$tableWishlists,$jssCustomer;
		if ($customerMain["wishlistID"] != "") {
			return $customerMain["wishlistID"];
		} else {
			srand((double)microtime()*1000000);
			$rID = rand();
			$rID = md5($rID);
			$result = $dbA->query("select wishlistID from $tableWishlists where wishlistID='$rID'");
			while ($dbA->count($result) > 0) {
				$rID = rand();
				$rID = md5($rID);
				$result = $dbA->query("select wishlistID from $tableWishlists where wishlistID='$rID'");
			}
			$dbA->query("update $tableCustomers set wishlistID='$rID' where customerID=$jssCustomer");
			$customerMain["wishlistID"] = $rID;
			return $rID;
		}
	}
	
	function createRandomPassword($passwordLength) {
	    $password = "";
	    for ($index = 1; $index <= $passwordLength; $index++) {
	         $randomNumber = rand(1, 62);
	         if ($randomNumber < 11) $password .= Chr($randomNumber + 48 - 1); // [ 1,10] => [0,9]
	         else if ($randomNumber < 37) $password .= Chr($randomNumber + 65 - 10); // [11,36] => [A,Z]
	         else $password .= Chr($randomNumber + 97 - 36); // [37,62] => [a,z]
	    }
	    return $password;
	}	
	
	function performCustomerLogin($xEmailAddress,$xCustPassword) {
		global $tableCustomers,$tableCarts,$dbA,$cartID;
		$xCustPassword = md5($xCustPassword);
		$result = $dbA->query("select * from $tableCustomers where email=\"$xEmailAddress\" and password=\"$xCustPassword\"");
		$count = $dbA->count($result);
		if ($count == 0) {
			return false;
		}			
		//ok, got the customer here.
		srand((double)microtime()*1000000);
		$rID = rand();
		$rID = md5($rID);
		$customerMain = $dbA->fetch($result);
		$customerMain["loggedin"]="Y";
		$dbA->query("update $tableCarts set rID=\"$rID\", accTypeID=".$customerMain["accTypeID"].", customerID=".$customerMain["customerID"].", country=".$customerMain["country"].", county=\"".$customerMain["county"]."\" where cartID=\"$cartID\"");
		$dbA->query("update $tableCustomers set rID=\"$rID\" where email=\"$xEmailAddress\"");
		setcookie("jssCustomer",$customerMain["customerID"],time()+2010800);
		return true;
	}

?>