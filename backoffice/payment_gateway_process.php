<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "Payment Gateway";
	$linkBackLink = "payment_options.php";
	
	$xAction = getFORM("xAction");
	$xGateway = getFORM("xGateway");

	if ($xAction == "options") {
		switch ($xGateway) {
			case "AUTHORIZEAIM":
				updateGatewayOption($xGateway,"x_login",getFORM("xLoginID"));
				updateGatewayOption($xGateway,"x_password",getFORM("xXpassword"));
				updateGatewayOption($xGateway,"x_test_request",getFORM("xTestRequest"));
				updateGatewayOption($xGateway,"type",getFORM("xType"));
				break;
			case "VELOCITYPAYFORM":
				updateGatewayOption($xGateway,"VPMerchantID",getFORM("xVPMerchantID"));
				updateGatewayOption($xGateway,"VPMerchantPassword",getFORM("xVPMerchantPassword"));
				updateGatewayOption($xGateway,"VPCountryCode",getFORM("xVPCountryCode"));
				updateGatewayOption($xGateway,"formURL",getFORM("xFormURL"));
				break;
			case "YELLOWPAY":
				updateGatewayOption($xGateway,"shopID",getFORM("xShopID"));
				updateGatewayOption($xGateway,"paytype",getFORM("xPaytype"));
				updateGatewayOption($xGateway,"language",getFORM("xLanguage"));
				updateGatewayOption($xGateway,"ddStatus",getFORM("xddStatus"));
				updateGatewayOption($xGateway,"yellowStatus",getFORM("xyellowStatus"));
				updateGatewayOption($xGateway,"masterStatus",getFORM("xmasterStatus"));
				updateGatewayOption($xGateway,"visaStatus",getFORM("xvisaStatus"));
				updateGatewayOption($xGateway,"amexStatus",getFORM("xamexStatus"));
				updateGatewayOption($xGateway,"dinersStatus",getFORM("xdinersStatus"));
				break;
			case "SAFERPAY":
				updateGatewayOption($xGateway,"path",getFORM("xPath"));
				updateGatewayOption($xGateway,"accountID",getFORM("xAccountID"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				break;
			case "GESTPAY":
				updateGatewayOption($xGateway,"ShopLogin",getFORM("xShopLogin"));
				updateGatewayOption($xGateway,"Language",getFORM("xLanguage"));
				break;
			case "PAYMATE":
				updateGatewayOption($xGateway,"mid",getFORM("xMid"));
				break;
			case "SYSPAY":
				updateGatewayOption($xGateway,"codcli",getFORM("xCodcli"));
				updateGatewayOption($xGateway,"language",getFORM("xLanguage"));
				break;
			case "BARCLAYSEPDQ":
				$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
				$clientIDs = "";
				for ($f = 0; $f < count($currArray); $f++) {
					$curPage = makeInteger(getFORM("xClientid".$currArray[$f]["currencyID"]));
					if ($curPage == 0) { $curPage = 1; }
					$clientIDs .= $currArray[$f]["currencyID"].":".$curPage."|";
				}
				updateGatewayOption($xGateway,"clientid",$clientIDs);
				updateGatewayOption($xGateway,"chargetype",getFORM("xChargetype"));
				updateGatewayOption($xGateway,"password",getFORM("xPassword"));
				updateGatewayOption($xGateway,"merchantdisplayname",getFORM("xMerchantdisplayname"));
				updateGatewayOption($xGateway,"cpi_textcolor",getFORM("xCPItext"));
				updateGatewayOption($xGateway,"cpi_bgcolor",getFORM("xCPIbg"));
				updateGatewayOption($xGateway,"cpi_logo",getFORM("xCPIlogo"));
				updateGatewayOption($xGateway,"supportedcardtypes",getFORM("xSupportedcardtypes"));
				break;
			case "VERISIGN_AUS":
				updateGatewayOption($xGateway,"login",getFORM("xLogin"));
				updateGatewayOption($xGateway,"partner",getFORM("xPartner"));
				updateGatewayOption($xGateway,"type",getFORM("xType"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				break;
			case "VERISIGN_USA":
				updateGatewayOption($xGateway,"login",getFORM("xLogin"));
				updateGatewayOption($xGateway,"partner",getFORM("xPartner"));
				updateGatewayOption($xGateway,"type",getFORM("xType"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				break;
			case "VERISIGN_USA_PRO":
				updateGatewayOption($xGateway,"login",getFORM("xLogin"));
				updateGatewayOption($xGateway,"partner",getFORM("xPartner"));
				updateGatewayOption($xGateway,"type",getFORM("xType"));
				updateGatewayOption($xGateway,"password",getFORM("xPassword"));
				break;
			case "HSBCCPI":
				updateGatewayOption($xGateway,"Mode",getFORM("xMode"));
				updateGatewayOption($xGateway,"CpiReturnUrl",getFORM("xCpiReturnUrl"));
				updateGatewayOption($xGateway,"OrderDesc",getFORM("xOrderDesc"));
				updateGatewayOption($xGateway,"StorefrontId",getFORM("xStorefrontId"));
				updateGatewayOption($xGateway,"TransactionType",getFORM("xTransactionType"));
				updateGatewayOption($xGateway,"EncryptionKey",getFORM("xEncryptionKey"));
				updateGatewayOption($xGateway,"EncryptLocation",getFORM("xEncryptLocation"));
				break;
			case "AUTHORIZESIM":
				updateGatewayOption($xGateway,"x_login",getFORM("xLoginID"));
				updateGatewayOption($xGateway,"trankey",getFORM("xTransactionKey"));
				updateGatewayOption($xGateway,"x_test_request",getFORM("xTestRequest"));
				updateGatewayOption($xGateway,"x_description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"x_email_customer",getFORM("xEmailCustomer"));
				updateGatewayOption($xGateway,"type",getFORM("xType"));
				break;
			case "MULTICARDS":
				updateGatewayOption($xGateway,"merchantID",getFORM("xMerchantID"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"deferred",getFORM("xDeferred"));
				updateGatewayOption($xGateway,"postpassword",getFORM("xPostpassword"));
				$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
				$pageIDs = "";
				for ($f = 0; $f < count($currArray); $f++) {
					$curPage = makeInteger(getFORM("xPage".$currArray[$f]["currencyID"]));
					if ($curPage == 0) { $curPage = 1; }
					$pageIDs .= $currArray[$f]["currencyID"].":".$curPage."|";
				}
				updateGatewayOption($xGateway,"pageIDs",$pageIDs);
				break;
			case "MULTIPAY":
				updateGatewayOption($xGateway,"adminID",getFORM("xAdminID"));
				updateGatewayOption($xGateway,"sellerID",getFORM("xSellerID"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"country",getFORM("xCountry"));
				updateGatewayOption($xGateway,"language",getFORM("xLanguage"));
				$accString = "";
				for ($f=1; $f <=7; $f++) {
					$accString .= getFORM("xPT$f");
				}
				updateGatewayOption($xGateway,"allowpayment",$accString);
				break;
			case "PAYSYSTEMS_PRO":
				updateGatewayOption($xGateway,"companyid",getFORM("xCompanyid"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				break;
			case "GOEMERCHANT":
				updateGatewayOption($xGateway,"merchantname",getFORM("xMerchantName"));
				break;
			case "SECUREHOSTING":
				updateGatewayOption($xGateway,"shreference",getFORM("xSHreference"));
				updateGatewayOption($xGateway,"checkcode",getFORM("xCheckCode"));
				updateGatewayOption($xGateway,"filename",getFORM("xFilename"));
				break;
			case "SECPAY":
				updateGatewayOption($xGateway,"merchant",getFORM("xMerchant"));
				updateGatewayOption($xGateway,"test_status",getFORM("xTestStatus"));
				updateGatewayOption($xGateway,"authType",getFORM("xAuthType"));
				updateGatewayOption($xGateway,"DigestKey",getFORM("xDigestKey"));								
				updateGatewayOption($xGateway,"template",getFORM("xTemplate"));	
				break;
			case "PAYPAL":
				updateGatewayOption($xGateway,"account",getFORM("xAccount"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"logo",getFORM("xLogo"));
				break;
			case "NOCHEX":
				updateGatewayOption($xGateway,"account",getFORM("xAccount"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"logo",getFORM("xLogo"));
				break;
			case "PROTX":
				updateGatewayOption($xGateway,"vendor",getFORM("xVendor"));
				updateGatewayOption($xGateway,"encryptionPassword",getFORM("xEncryptionPassword"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"testMode",getFORM("xTestMode"));
				updateGatewayOption($xGateway,"txType",getFORM("xTxType"));
				updateGatewayOption($xGateway,"vendorEmail",getFORM("xVendorEmail"));
				updateGatewayOption($xGateway,"sendEmail",getFORM("xSendEmail"));
				break;
			case "WORLDPAY":
				updateGatewayOption($xGateway,"installationID",getFORM("xInstallationID"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				updateGatewayOption($xGateway,"testmode",getFORM("xTestmode"));
				updateGatewayOption($xGateway,"authmode",getFORM("xAuthmode"));
				updateGatewayOption($xGateway,"callbackpassword",getFORM("xCallbackpassword"));
				break;
			case "SECURETRADING":
				updateGatewayOption($xGateway,"merchantID",getFORM("xMerchantID"));
				updateGatewayOption($xGateway,"emailconf",getFORM("xEmailconf"));
				updateGatewayOption($xGateway,"email",getFORM("xEmail"));
				break;
			case "USAEPAY":
				updateGatewayOption($xGateway,"sourcekey",getFORM("xSourceKey"));
				updateGatewayOption($xGateway,"testmode",getFORM("xTestmode"));
				updateGatewayOption($xGateway,"emailcustomer",getFORM("xEmailcustomer"));
				updateGatewayOption($xGateway,"command",getFORM("xAuthCommand"));
				break;
			case "INTERNETSECURE":
				updateGatewayOption($xGateway,"MerchantNumber",getFORM("xMerchantNumber"));
				updateGatewayOption($xGateway,"testmode",getFORM("xTestmode"));
				updateGatewayOption($xGateway,"language",getFORM("xLanguage"));
				updateGatewayOption($xGateway,"description",getFORM("xDescription"));
				break;
			case "2CHECKOUT":
				updateGatewayOption($xGateway,"accountNumber",getFORM("xAccountNumber"));
				updateGatewayOption($xGateway,"secretword",getFORM("xSecretword"));
				break;
			case "EPROCNET":
				updateGatewayOption($xGateway,"ePNAccount",getFORM("xEPNAccount"));
				updateGatewayOption($xGateway,"BackgroundColor",getFORM("xBackgroundColor"));
				updateGatewayOption($xGateway,"TextColor",getFORM("xTextColor"));
				break;				
		}
		userLog("Updated Payment Gateway: $xGateway");
		doRedirect($linkBackLink."?".userSessionGET());
	}


	function updateGatewayOption($theGateway,$theOption,$theValue) {
		global $dbA,$tableGatewayConfigs;
		$rArray[] = array("fieldvalue",$theValue,"S");
		$dbA->updateRecord($tableGatewayConfigs,"gateway='$theGateway' and fieldname='$theOption'",$rArray,0);
	}	
?>
