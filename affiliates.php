<?php
	include("static/includeBase_front.php");
	
	$xCmd=getFORM("xCmd");
	
	
	if ($xCmd == "") {
		dbConnect($dbA);
		$thisTemplate = "affiliatelogin.html";
		$pageType = "affiliatelogin";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}
	if ($xCmd == "login") {
		$loginError = "";
		$xUsername = makeSafe(getFORM("xUsername"));
		$xPassword = makeSafe(getFORM("xPassword"));
		if (chop($xUsername) == "" || chop($xPassword) == "") {
			dbConnect($dbA);
			$thisTemplate = "affiliatelogin.html";
			$loginError = "Y";
			$pageType = "affiliatelogin";
			$affiliateMain = null;
			$affiliateMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
		dbConnect($dbA);
		$xPassword = md5($xPassword);
		$result = $dbA->query("select * from $tableAffiliates where username=\"$xUsername\" and password=\"$xPassword\" and status='L'");
		$count = $dbA->count($result);
		if ($count == 0) {
			dbConnect($dbA);
			$thisTemplate = "affiliatelogin.html";
			$loginError = "Y";
			$pageType = "affiliatelogin";
			$affiliateMain = null;
			$affiliateMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}			
		//ok, got the customer here.
		srand((double)microtime()*1000000);
		$rID = rand();
		$rID = md5($rID);
		$affiliateMain = $dbA->fetch($result);
		$affiliateMain["loggedin"]="Y";
		$dbA->query("update $tableCarts set arID='$rID', affiliateLoginID=".$affiliateMain["affiliateID"]." where cartID='$cartID'");
		$dbA->query("update $tableAffiliates set arID='$rID' where affiliateID=".$affiliateMain["affiliateID"]);
		setcookie("jssAffiliateLogin",$affiliateMain["affiliateID"],time()+2010800);
		doRedirect(configureURL("affiliates.php?xCmd=account"));
		$dbA->close();
	}	
	if ($xCmd == "acedit") {
		dbConnect($dbA);
		if ($affiliateMain["loggedin"] == "N") {
			$xFwd = "affiliates.php?xCmd=login";
			$thisTemplate = "affiliatelogin.html";
			$pageType = "affiliatelogin";
			$affiliateMain = null;
			$affiliateMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;		
		}
		$thisTemplate = "affiliateedit.html";
		$pageType = "affiliateedit";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	if ($xCmd == "update") {
		if ($affiliateMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}
		dbConnect($dbA);
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='AF' and visible=1 and internalOnly=0");
		$rArray = null;
		$xUsername = makeSafe(getFORM("xUsername"));
		$xNewsletter = makeSafe(getFORM("xNewsletter"));
		$xPassword = chop(makeSafe(getFORM("xPassword")));
		$xRepeatPassword = chop(makeSafe(getFORM("xRepeatPassword")));
		$affiliateMain["error"] = "N";
		if ($xPassword != "" && $xRepeatPassword != "") {
			if ($xPassword != $xRepeatPassword) {
				$affiliateMain["error"] = "Y";
				$affiliateMain["passworderror"] = "NOTMATCHING";
			} else {
				if ($xPassword == "") {
					$affiliateMain["error"] = "Y";
					$affiliateMain["passworderror"] = "BLANK";
				} else {
					if (strlen($xPassword) < 8) {
						$affiliateMain["error"] = "Y";
						$affiliateMain["passworderror"] = "TOOSHORT";
					}
				}
			}
			if ($affiliateMain["error"] == "N") {
				$rArray[] = array("password",md5($xPassword),"S");
			}		
		}
		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$affiliateMain[$fieldList[$f]["fieldname"]] = $thisField;
			if ($fieldList[$f]["validation"] == 1 && empty($thisField)) {
				$affiliateMain["error"] = "Y";
				$affiliateMain[$fieldList[$f]["fieldname"]."_error"] = "Y";
			}
		}
		$query=$dbA->query("select * from $tableAffiliates where username=\"$xUsername\" and affiliateID != ".$affiliateMain["affiliateID"]);
		if ($affiliateMain["error"] == "Y") {
			$thisTemplate = "affiliateedit.html";
			$pageType = "affiliateedite";
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$rArray[] = array($fieldList[$f]["fieldname"],$thisField,"S");
		}
		$dbA->updateRecord($tableAffiliates,"affiliateID=".$affiliateMain["affiliateID"],$rArray,0);	
		$cresult = $dbA->query("select * from $tableAffiliates where affiliateID=".$affiliateMain["affiliateID"]);
		$customerMain = $dbA->fetch($cresult);
		$dbA->close();
		doRedirect(configureURL("affiliates.php?xCmd=account"));
	}		
	if ($xCmd == "account") {
		dbConnect($dbA);
		if ($affiliateMain["loggedin"] == "N") {
			$xFwd = "affiliates.php?xCmd=login";
			$thisTemplate = "affiliatelogin.html";
			$pageType = "affiliatelogin";
			$affiliateMain = null;
			$affiliateMain["loggedin"] = "N";
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;			
		}	
		if ($affiliateMain["loggedin"] == "Y") {
			$result = $dbA->query("select * from $tableAffiliatesStats where affiliateID=".$affiliateMain["affiliateID"]);
			$affiliateMain["summary"]["totalclicks"] = $dbA->count($result);
			$result = $dbA->query("select sum(amount) as totalamount, count(*) as totalsales from $tableAffiliatesTrans where affiliateID=".$affiliateMain["affiliateID"]." and status='1' and type='C' group by affiliateID");
			if ($dbA->count($result) == 0) {
				$totalCredits = 0;
				$totalNumSales = 0;
			} else {
				$record = $dbA->fetch($result);
				$totalCredits = $record["totalamount"];
				$totalNumSales = $record["totalsales"];
			}
			$result = $dbA->query("select sum(amount) as totalamount, count(*) as totalsales from $tableAffiliatesTrans where affiliateID=".$affiliateMain["affiliateID"]." and status='1' and type='D' group by affiliateID");
			if ($dbA->count($result) == 0) {
				$totalDebits = 0;
			} else {
				$record = $dbA->fetch($result);
				$totalDebits = $record["totalamount"];
				$totalNumSales = $totalNumSales - $record["totalsales"];
			}
			$totalSales = $totalCredits - $totalDebits;
			$affiliateMain["summary"]["earningstotal"] = formatWithoutCalcPriceInCurrency($totalSales,1);
			$affiliateMain["summary"]["totalsales"] = $totalNumSales;
			$result = $dbA->query("select sum(amount) as totalamount, count(*) as totalsales from $tableAffiliatesTrans where affiliateID=".$affiliateMain["affiliateID"]." and status='1' and type='P' group by affiliateID");
			if ($dbA->count($result) == 0) {
				$totalPayments = 0;
			} else {
				$record = $dbA->fetch($result);
				$totalPayments = $record["totalamount"];
			}
			$result = $dbA->query("select sum(amount) as totalamount, count(*) as totalsales from $tableAffiliatesTrans where affiliateID=".$affiliateMain["affiliateID"]." and status='1' and secondtier='Y' and type='C' group by affiliateID");
			if ($dbA->count($result) == 0) {
				$total2Tier = 0;
			} else {
				$record = $dbA->fetch($result);
				$total2Tier = $record["totalamount"];
			}
			$totalOutstanding = $totalSales-$totalPayments;
			$totalDirect = $totalOutstanding-$total2Tier;
			$affiliateMain["summary"]["outstandingdirect"] = formatWithoutCalcPriceInCurrency($totalDirect,1);
			$affiliateMain["summary"]["outstanding2ndtier"] = formatWithoutCalcPriceInCurrency($total2Tier,1);
			$affiliateMain["summary"]["outstandingtotal"] = formatWithoutCalcPriceInCurrency($totalOutstanding,1);
			$thisTemplate = "affiliateaccount.html";
			$pageType = "affiliateaccount";		
			include ("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}
	}	
	if ($xCmd == "logout") {
		dbConnect($dbA);
		setcookie("jssAffiliateLogin","",time()+2010800);
		srand((double)microtime()*1000000);
		$rID = rand();
		$rID = md5($rID);
		$dbA->query("update $tableCarts set arID=\"\",affiliateLoginID=0 where cartID=\"$cartID\"");
		$dbA->query("update $tableAffiliates set arID=\"$rID\" where affiliateID=$jssAffiliateLogin");
		$jssAffiliateLogin = "";
		$affiliateMain = null;
		$affiliateMain["loggedin"] = "N";
		$dbA->close();
	}
	if ($xCmd == "banners" || $xCmd == "stats" || $xCmd == "sales" || $xCmd == "payments") {
		if ($affiliateMain["loggedin"] == "N") {
			doRedirect(configureURL("index.php"));
		}	
		dbConnect($dbA);
		switch ($xCmd) {
			case "banners":
				$thisTemplate = "affiliatebanners.html";
				$pageType = "affiliatebanners";
				$result = $dbA->query("select * from $tableAffiliatesBanners where groups like '%0%' or groups like '%".$affiliateMain["groupID"]."%'");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					$bRecord = $dbA->fetch($result);
					$bRecord["link"] = "<a href=\"".$jssStoreWebDirHTTP."index.php?a=".$affiliateMain["username"]."\"><img src=\"".$jssStoreWebDirHTTP.$bRecord["filename"]."\" border=\"0\"></a>";
					$bRecord["link"] = htmlspecialchars($bRecord["link"]);
					$affiliateMain["banners"][] = $bRecord;
				}
				break;
			case "stats":
				$thisTemplate = "affiliatestats.html";
				$pageType = "affiliatestats";
				$result = $dbA->query("select * from $tableAffiliatesStats order by datetime ASC limit 1");
				if ($dbA->count($result) == 0) {
					$lowYear = date("Y");
					$lowMonth = 1;
				} else {
					$record = $dbA->fetch($result);
					$lowYear = substr($record["datetime"],0,4);
					$lowMonth = substr($record["datetime"],4,2);
				}
				//create select box for month
				$monthSelect = "";
				$yearSelect = "";
				for ($f = 1; $f <= 12; $f++) {
					$monthSelect[] = sprintf("%02d",$f);
				}
				for ($f = $lowYear; $f <= date("Y"); $f++) {
					$yearSelect[] = $f;
				}
				$affiliateMain["statsform"]["name"] = "statsForm";
				$affiliateMain["statsform"]["action"] = configureURL("affiliates.php?xCmd=stats");
				$affiliateMain["statsform"]["monthlist"] = $monthSelect;
				$affiliateMain["statsform"]["yearlist"] = $yearSelect;
				$affiliateMain["statsform"]["month"] = "xMonth";
				$affiliateMain["statsform"]["year"] = "xYear";
				if (makeSafe(getFORM("xMonth")) == "") {
					$selectedMonth = date("m");
				} else {
					$selectedMonth = makeSafe(getFORM("xMonth"));
				}
				if (makeSafe(getFORM("xYear")) == "") {
					$selectedYear = date("Y");
				} else {
					$selectedYear = makeSafe(getFORM("xYear"));
				}			
				$affiliateMain["statsform"]["selectedMonth"] = $selectedMonth;
				$affiliateMain["statsform"]["selectedYear"] = $selectedYear;	
				$result = $dbA->query("select *,substring(datetime,7,2) as day,count(*) as total from $tableAffiliatesStats where affiliateID=".$affiliateMain["affiliateID"]." and datetime >= '".$selectedYear.$selectedMonth."01000000' and datetime <= '".$selectedYear.$selectedMonth."31999999' group by day order by day ASC");
				$count = $dbA->count($result);
				$totalStats = 0;
				for ($f = 0; $f < $count; $f++) {
					$sRecord = $dbA->fetch($result);
					$sRecord["date"] = formatDate($selectedYear.$selectedMonth.$sRecord["day"]);
					$affiliateMain["stats"]["days"][] = $sRecord;
					$totalStats = $totalStats + $sRecord["total"];
				}
				$affiliateMain["stats"]["total"] = $totalStats;
				break;
			case "sales":
				$thisTemplate = "affiliatesales.html";
				$pageType = "affiliatesales";
				$result = $dbA->query("select * from $tableAffiliatesStats order by datetime ASC limit 1");
				if ($dbA->count($result) == 0) {
					$lowYear = date("Y");
					$lowMonth = 1;
				} else {
					$record = $dbA->fetch($result);
					$lowYear = substr($record["datetime"],0,4);
					$lowMonth = substr($record["datetime"],4,2);
				}
				//create select box for month
				$monthSelect = "";
				$yearSelect = "";
				for ($f = 1; $f <= 12; $f++) {
					$monthSelect[] = sprintf("%02d",$f);
				}
				for ($f = $lowYear; $f <= date("Y"); $f++) {
					$yearSelect[] = $f;
				}
				$affiliateMain["salesform"]["name"] = "statsForm";
				$affiliateMain["salesform"]["action"] = configureURL("affiliates.php?xCmd=sales");
				$affiliateMain["salesform"]["monthlist"] = $monthSelect;
				$affiliateMain["salesform"]["yearlist"] = $yearSelect;
				$affiliateMain["salesform"]["month"] = "xMonth";
				$affiliateMain["salesform"]["year"] = "xYear";
				if (makeSafe(getFORM("xMonth")) == "") {
					$selectedMonth = date("m");
				} else {
					$selectedMonth = makeSafe(getFORM("xMonth"));
				}
				if (makeSafe(getFORM("xYear")) == "") {
					$selectedYear = date("Y");
				} else {
					$selectedYear = makeSafe(getFORM("xYear"));
				}			
				$affiliateMain["salesform"]["selectedMonth"] = $selectedMonth;
				$affiliateMain["salesform"]["selectedYear"] = $selectedYear;
				$result = $dbA->query("select * from $tableAffiliatesTrans where (type='C' or type='D') and status='1' and affiliateID=".$affiliateMain["affiliateID"]." and datetime >= '".$selectedYear.$selectedMonth."01000000' and datetime <= '".$selectedYear.$selectedMonth."31999999' order by datetime DESC");				$count = $dbA->count($result);
				$totalSales = 0;
				for ($f = 0; $f < $count; $f++) {
					$sRecord = $dbA->fetch($result);
					$sRecord["date"] = formatDate($sRecord["datetime"]);
					if ($sRecord["type"] == "C") {
						$totalSales = $totalSales + $sRecord["amount"];
					} else {
						$totalSales = $totalSales - $sRecord["amount"];
					}
					$sRecord["amount"] = formatWithoutCalcPriceInCurrency($sRecord["amount"],1);
					$affiliateMain["sales"][] = $sRecord;
				}			
				$affiliateMain["salestotal"] = formatWithoutCalcPriceInCurrency($totalSales,1);
				break;
			case "payments":
				$thisTemplate = "affiliatepayments.html";
				$pageType = "affiliatepayments";
				$result = $dbA->query("select * from $tableAffiliatesTrans where type='P' and status='1' and affiliateID=".$affiliateMain["affiliateID"]." order by datetime DESC");
				$count = $dbA->count($result);
				$totalPayments = 0;
				for ($f = 0; $f < $count; $f++) {
					$sRecord = $dbA->fetch($result);
					$sRecord["date"] = formatDate($sRecord["datetime"]);
					$totalPayments = $totalPayments + $sRecord["amount"];
					$sRecord["amount"] = formatWithoutCalcPriceInCurrency($sRecord["amount"],1);
					$affiliateMain["payments"][] = $sRecord;
				}			
				$affiliateMain["paymentstotal"] = formatWithoutCalcPriceInCurrency($totalPayments,1);
				break;				
		}
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	if ($xCmd == "register") {
		dbConnect($dbA);
		$thisTemplate = "affiliatesignup.html";
		$pageType = "affiliatenew";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();
		exit;
	}	
	if ($xCmd == "create") {
		dbConnect($dbA);
		$fieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='AF' and visible=1 and internalOnly=0");
		$rArray = null;
		$xUsername = chop(makeSafe(getFORM("xUsername")));
		$xNewsletter = chop(makeSafe(getFORM("xNewsletter")));
		$xPassword = chop(makeSafe(getFORM("xPassword")));
		$xRepeatPassword = chop(makeSafe(getFORM("xRepeatPassword")));
		$affiliateMain["error"] = "N";
		$affiliateMain["username"] = $xUsername;
		if ($xPassword != $xRepeatPassword) {
			$affiliateMain["error"] = "Y";
			$affiliateMain["passworderror"] = "NOTMATCHING";
		} else {
			if ($xPassword == "") {
				$affiliateMain["error"] = "Y";
				$affiliateMain["passworderror"] = "BLANK";
			} else {
				if (strlen($xPassword) < 8) {
					$affiliateMain["error"] = "Y";
					$affiliateMain["passworderror"] = "TOOSHORT";
				}
			}
		}
		if ($xUsername == "") {
			$affiliateMain["error"] = "Y";
			$affiliateMain["usernameerror"] = "INVALID";
		}
		$query=$dbA->query("select * from $tableAffiliates where username=\"$xUsername\"");
		for ($f = 0; $f < count($fieldList); $f++) {
			$thisField = chop(makeSafe(getFORM($fieldList[$f]["fieldname"])));
			$affiliateMain[$fieldList[$f]["fieldname"]] = $thisField;
			if ($fieldList[$f]["validation"] == 1 && empty($thisField)) {
				$affiliateMain["error"] = "Y";
				$affiliateMain[$fieldList[$f]["fieldname"]."_error"] = "Y";
			}
		}

		if ($dbA->count($query) > 0) {
			$affiliateMain["usernameerror"] = "EXISTS";
			$affiliateMain["error"] = "Y";
		}

		if ($affiliateMain["error"] == "Y") {
			$thisTemplate = "affiliatesignup.html";
			$pageType = "affiliatenewe";
			$customerMain["username"] = $xUsername;
			include("routines/cartOutputData.php");
			$tpl->showPage();
			$dbA->close();
			exit;
		}

		if (retrieveOption("affiliatesSignupModerated") == 0) {
			$rArray[] = array("status","L","S");
		} else {
			$rArray[] = array("status","N","S");
		}
		$rArray[] = array("username",$xUsername,"S");
		$rArray[] = array("date",date("Ymd"),"S");
		$rArray[] = array("password",md5($xPassword),"S");
		$rArray[] = array("groupID",1,"N");
		
		if (@$_COOKIE["jssAffiliate"] != "") {
			$rArray[] = array("parentID",affiliatesValidate(@$_COOKIE["jssAffiliate"]),"N");
		}
		for ($f = 0; $f < count($fieldList); $f++) {
			$rArray[] = array($fieldList[$f]["fieldname"],makeSafe(getFORM($fieldList[$f]["fieldname"])),"S");
		}
		$dbA->insertRecord($tableAffiliates,$rArray,0);
		$affiliateID = $dbA->lastID();
		$cresult = $dbA->query("select * from $tableAffiliates where affiliateID=$affiliateID");
		
		$affiliateMain = $dbA->fetch($cresult);
		$affiliateMain["password"] = $xPassword;
		
		include("routines/emailOutput.php");
		
		if (retrieveOption("affiliatesSignupModerated") == 0) {
			@sendEmail($affiliateMain["aff_Email"],"","AFFACCEPTED");
		}
		@sendEmail("COMPANY","","MERCHAFFNEW");
		
		$thisTemplate = "affiliatesignupthanks.html";
		$pageType = "affiliatesignupthanks";
		include("routines/cartOutputData.php");
		$tpl->showPage();
		$dbA->close();	
		exit;
	}	
	$xSec=1;
	$thisTemplate = "index.html";
	dbConnect($dbA);
	include("routines/cartOutputData.php");
	$tpl->showPage();
	$dbA->close();
?>
