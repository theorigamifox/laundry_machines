<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$recordType = "User";
	$linkBackLink = urldecode(getFORM("xReturn"));
	
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");

	if ($xAction == "options") {
		if ($xType == "offercodes") {
			updateOption("offerCodesEnabled",getFORM("xOfferCodesEnabled"));
			userLog("Updated Offer Code Settings");
		}
		if ($xType == "search") {
			updateOption("searchIncludeSections",getFORM("xSearchIncludeSections"));
			updateOption("searchProductsPerPage",makeInteger(getFORM("xSearchProductsPerPage")));
			updateOption("searchFields",getFORM("xSearchFields"));
			updateOption("searchMaxSections",makeInteger(getFORM("xSearchMaxSections")));
			updateOption("searchSoloProductShow",makeInteger(getFORM("xSearchSoloProductShow")));
			userLog("Updated Search Settings");
		}
		if ($xType == "searchlist") {
			updateOption("adminProdPerPage",getFORM("xAdminProdPerPage"));
			updateOption("adminSecPerPage",getFORM("xAdminSecPerPage"));
			updateOption("adminOrdersPerPage",getFORM("xAdminOrdersPerPage"));
			updateOption("adminReviewsPerPage",getFORM("xAdminReviewsPerPage"));
			updateOption("adminCustomersPerPage",getFORM("xAdminCustomersPerPage"));
			userLog("Updated Search/Listing Settings");
		}
		if ($xType == "productediting") {
			updateOption("prodDivsAdd",getFORM("xProdDivsAdd"));
			updateOption("prodDivsEdit",getFORM("xProdDivsEdit"));
			updateOption("prodDivsClone",getFORM("xProdDivsClone"));
			updateOption("prodEditAssociatedLinkDefault",getFORM("xProdEditAssociatedLinkDefault"));
			userLog("Updated Product Editing Settings");
		}		
		if ($xType == "global") {
			updateOption("shopAvailable",getFORM("xShopAvailable"));
			updateOption("orderNumberOffset",getFORM("xOrderNumberOffset"));
			updateOption("defaultCountry",getFORM("xDefaultCountry"));
			updateOption("useRewriteURLs",getFORM("xUseRewriteURLs"));
			updateOption("cookieName",getFORM("xCookieName"));
			updateOption("cookieTime",getFORM("xCookieTime"));
			userLog("Updated Global Settings");
		}		
		if ($xType == "templates") {
			updateOption("templateCompileMode",getFORM("xTemplateCompileMode"));
			updateOption("templatesEditWordWrap",getFORM("xTemplatesEditWordWrap"));
			updateOption("templateAllowTFC",getFORM("xTemplateAllowTFC"));
			updateOption("templateAllowRTU",getFORM("xTemplateAllowRTU"));
			updateOption("snippetsConvertToBR",getFORM("xSnippetsConvertToBR"));
			userLog("Updated Template Settings");
		}	
		if ($xType == "reviews") {			
			updateOption("reviewsEnabled",getFORM("xReviewsEnabled"));
			updateOption("reviewsModerated",getFORM("xReviewsModerated"));
			userLog("Updated Review Settings");
		}
		if ($xType == "stockcontrol") {			
			updateOption("featureStockControl",getFORM("xFeatureStockControl"));
			updateOption("stockDeductMode",getFORM("xStockDeductMode"));
			updateOption("stockWarningEmail",getFORM("xStockWarningEmail"));
			updateOption("stockZeroEmail",getFORM("xStockZeroEmail"));
			updateOption("stockShowSectionStructure",getFORM("xStockShowSectionStructure"));
			updateOption("stockWarningNotZero",getFORM("xStockWarningNotZero"));
			updateOption("stockCheckoutCheck",getFORM("xStockCheckoutCheck"));
			//updateOption("stockQtyLimit",getFORM("xStockQtyLimit"));
			userLog("Updated Stock Control Settings");
		}	
		if ($xType == "section") {
			updateOption("sectionSubSectionsPlus",getFORM("xSectionSubSectionsPlus"));
			updateOption("sectionProductsPerPage",getFORM("xSectionProductsPerPage"));
			updateOption("rootSectionGetSubs",getFORM("xRootSectionGetSubs"));
			userLog("Updated Section Settings");
		}	
		if ($xType == "general") {
			updateOption("dateFormat",getFORM("xDateFormat"));
			updateOption("timeFormat",getFORM("xTimeFormat"));
			updateOption("convertToBR",getFORM("xConvertToBR"));			
			userLog("Updated Section Settings");
		}	
		if ($xType == "checkout") {
			updateOption("orderingForceAccount",getFORM("xOrderingForceAccount"));
			updateOption("minimumOrderValue",getFORM("xMinimumOrderValue"));
			updateOption("orderingSkipPayment",getFORM("xOrderingSkipPayment"));
			userLog("Updated Section Settings");
		}
		if ($xType == "list") {
			updateOption("bestsellersLimit",makeInteger(getFORM("xBestsellersLimit")));
			updateOption("bestsellersCalc",getFORM("xBestsellersCalc"));
			updateOption("bestsellersTimeLimit",getFORM("xBestsellersTimeLimit"));
			updateOption("topProductsLimit",makeInteger(getFORM("xTopProductsLimit")));
			updateOption("newProductsLimit",makeInteger(getFORM("xNewProductsLimit")));
			updateOption("specialOffersLimit",makeInteger(getFORM("xSpecialOffersLimit")));
			updateOption("recommendedLimit",makeInteger(getFORM("xRecommendedLimit")));
			updateOption("customerReviewsLimit",makeInteger(getFORM("xCustomerReviewsLimit")));
			updateOption("randomProductsMax",makeInteger(getFORM("xRandomProductsMax")));
			userLog("Updated List Settings");
		}
		if ($xType == "basket") {
			updateOption("cartSortOrder",getFORM("xCartSortOrder"));
			updateOption("basketAddGoBasket",getFORM("xBasketAddGoBasket"));
			
			updateOption("cartAssociatedActivated",getFORM("xCartAssociatedActivated"));
			updateOption("cartAssociatedMax",getFORM("xCartAssociatedMax"));
			updateOption("cartAssociatedOrder",getFORM("xCartAssociatedOrder"));
			userLog("Updated Basket Settings");
		}
		if ($xType == "countries") {
			$xCountryList = getFORM("xCountryList");
			$xCountryDeletedList = getFORM("xCountryDeletedList");
			$delList = split(";",$xCountryDeletedList);
			for ($f = 0; $f < count($delList); $f++) {
				if ($delList[$f] != "") {
					$dbA->query("update $tableCountries set visible='N' where countryID=".$delList[$f]);
				}
			}
			$delList = split(";",$xCountryList);
			for ($f = 0; $f < count($delList); $f++) {
				if ($delList[$f] != "") {
					$dbA->query("update $tableCountries set visible='Y' where countryID=".$delList[$f]);
				}
			}
			userLog("Updated Country Settings");
		}
		if ($xType == "orderadmin") {
			updateOption("orderAdminActivateDispatch",getFORM("xOrderAdminActivateDispatch"));
			updateOption("orderAdminEmailDispatch",getFORM("xOrderAdminEmailDispatch"));
			updateOption("orderAdminDispatchCopy",getFORM("xOrderAdminDispatchCopy"));
			updateOption("orderAdminDispatchCopyAddress",getFORM("xOrderAdminDispatchCopyAddress"));
			updateOption("orderAdminDispatchTracking",getFORM("xOrderAdminDispatchTracking"));
			updateOption("orderAdminActivateReceipt",getFORM("xOrderAdminActivateReceipt"));
			updateOption("orderAdminDispatchPartial",getFORM("xOrderAdminDispatchPartial"));
			updateOption("orderAdminClearCC",getFORM("xOrderAdminClearCC"));
			updateOption("ordersSpaceCC",getFORM("xOrdersSpaceCC"));
			userLog("Updated Order Admin Settings");
		}		
		if ($xType == "giftcerts") {
			updateOption("giftCertificatesEnabled",getFORM("xGiftCertificatesEnabled"));
			updateOption("giftCertificatesExpiry",makeInteger(getFORM("xGiftCertificatesExpiry")));
			userLog("Updated Gift Certificate Settings");
		}
		if ($xType == "recentlyviewed") {
			updateOption("recentViewActivated",makeInteger(getFORM("xRecentViewActivated")));
			updateOption("recentViewProducts",makeInteger(getFORM("xRecentViewProducts")));
			updateOption("recentViewSections",makeInteger(getFORM("xRecentViewSections")));
			userLog("Updated Recently Viewed Settings");
		}	
		if ($xType == "usersonline") {
			updateOption("usersOnlineActivated",makeInteger(getFORM("xUsersOnlineActivated")));
			updateOption("usersOnlineTimeFrame",makeInteger(getFORM("xUsersOnlineTimeFrame")));
			userLog("Updated Users Online Settings");
		}	
		if ($xType == "digitalproducts") {
			updateOption("downloadsActivate",makeInteger(getFORM("xDownloadsActivate")));
			updateOption("downloadsTime",makeInteger(getFORM("xDownloadsTime")));
			updateOption("downloadsUses",makeInteger(getFORM("xDownloadsUses")));
			updateOption("downloadsDirectory",getFORM("xDownloadsDirectory"));
			updateOption("downloadsAllowInstant",getFORM("xDownloadsAllowInstant"));
			userLog("Updated Digital Products Settings");
		}	
		doRedirect($linkBackLink."&".userSessionGET());
	}
?>
