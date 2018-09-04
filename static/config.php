<?php
	//JShop Server is Copyright (c)2003, Whorl Ltd.
	//Web Site: http://www.whorl.co.uk/ and http://www.jshop.co.uk/
	
	//Main configuration file for JShop Server.
	//Note: You should not edit any other .php files.

	//mySQL database connection details.
	//If the database you enter here does not already exist JShop Server will attempt to create it. If this fails the installation
	//routines will let you know.
	
	$databaseType="mysql";				//mySQL is currently the only supported datbase system so do not change this!!
	//$databaseHost="localhost";
	$databaseHost="localhost";
	$databaseUsername="laundrymachines";
	$databasePassword="bpnjfd34v65p";
	$databaseName="laundrymachines";

	//Directory Structure
	//Note: If you have no SSL, set the HTTPS link to be the same as the HTTP link
	
	$jssStoreWebDirHTTP="http://www.laundrymachines.co.uk/";
	$jssStoreWebDirHTTPS="http://www.laundrymachines.co.uk/";
	$jssShopImagesWeb = "shopimages/";
	
	//These should be absolute paths to the main JShop Server directory and to the shopimages directory.
	
	$jssShopFileSystem="/usr/home/laundrymachines/public_html/";
	$jssShopImagesFileSystem="/usr/home/laundrymachines/public_html/shopimages/";
	
	//Encryption Key.
	//This is used to encrypt credit card details - DO NOT LEAVE THIS SET TO THE DEFAULT
	//Change this to a 32-character combination of alphanumerics.
	
	//$teaEncryptionKey = "0123456789abcdeffedcba9876543210";
	$teaEncryptionKey = "J7k0d4ZVkGvrSaTBVyCJEvjvwNRSU9Se";
	
	
	//Registration information.
	//Please ensure that this is entered.
	
	$jssRegistrationCompany = "Ina4 Media Limited";
	$jssRegistrationCode = "4638360KECM6470E";

	//Names of the tables that JShop Server uses.
	//You should only edit these if there will be a conflict with other existing tables in the mySQL database.
	
	$tableUsers = "jss_users";
	$tableOptions = "jss_options";
	$tableUserLog = "jss_userlog";
	$tableSections = "jss_sections";
	$tableProductsCategories = "jss_products_categories";
	$tableCustomersAccTypes = "jss_customers_acc_types";
	$tableCurrencies = "jss_currencies";
	$tableGeneral = "jss_general";
	$tableEmails = "jss_emails";
	$tableProducts = "jss_products";
	$tableProductsTree = "jss_products_tree";
	$tableCarts = "jss_carts";
	$tableCartsContents = "jss_carts_contents";
	$tableLogs = "jss_logs";
    $tableExtraFields = "jss_extrafields";
    $tableExtraFieldsValues = "jss_extrafields_values";
    $tableExtraFieldsPrices = "jss_extrafields_prices";
    $tableAssociated = "jss_associated";
    $tableAdvancedPricing = "jss_advancedpricing";
    $tableProductsOptions = "jss_products_options";
    $tableCustomers = "jss_customers";
    $tableWishlists = "jss_wishlists";
    $tableCustomerFields = "jss_customer_fields";
    $tableCustomersAddresses = "jss_customers_addresses";
    $tableOrdersHeaders = "jss_orders_headers";
    $tableOrdersLines = "jss_orders_lines";
    $tableOrdersExtraFields = "jss_orders_extrafields";
    $tableCombinations = "jss_combinations";
    $tableSnippets = "jss_snippets";
    $tableNewsletter = "jss_newsletter";
    $tableReviews = "jss_reviews";
    $tablePaymentOptions = "jss_payment_options";
    $tableCountries = "jss_countries";
    $tableTaxRates = "jss_tax_rates";
    $tableTaxTree = "jss_tax_tree";
    $tableZones = "jss_zones";
    $tableShippingTypes = "jss_shipping_types";
    $tableShippingRates = "jss_shipping_rates";
    $tableReportsPopular = "jss_reports_popular";
    $tableNewsletters = "jss_newsletters";
    $tableLanguages = "jss_languages";
    $tableDataTransfers = "jss_datatransfers";
    $tableCouriers = "jss_couriers";
    $tableCCProcessing = "jss_ccprocessing";
    $tableGatewayConfigs = "jss_gateway_configs";
    $tableDiscounts = "jss_discounts";
    $tableDispatches = "jss_dispatches";
    $tableDispatchesTree = "jss_dispatches_tree";
    $tableLabels = "jss_labels";
    $tableGiftCertificates = "jss_giftcertificates";
    $tableGiftCertificatesTrans = "jss_giftcertificates_trans";
    $tableCCServerAuths = "jss_ccserverauths";
    $tableNews = "jss_news";
    $tableDigitalPurchases = "jss_digitalpurchases";
    $tableReportsSearch = "jss_reports_search";
    $tableProductsGrouped = "jss_products_grouped";
    $tableOrdersLinesGrouped = "jss_orders_lines_grouped";    
    $tableAffiliates = "jss_affiliates";
    $tableAffiliatesGroups = "jss_affiliates_groups";
    $tableAffiliatesBanners = "jss_affiliates_banners";
    $tableAffiliatesStats = "jss_affiliates_stats";
    $tableAffiliatesTrans = "jss_affiliates_trans";
    $tableProductsFlags = "jss_products_flags";
	$tableOfferCodes = "jss_offer_codes";
    $tableOfferCodesTrans = "jss_offer_codes_trans";
	$tableSuppliers = "jss_suppliers";
	$tableSuppliersEmails = "jss_suppliers_emails";
	$tablePaperwork = "jss_paperwork";
?>
