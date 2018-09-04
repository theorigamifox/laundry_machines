<?php
	$sectionsArray["users"] = array("Users","users_list.php");
	
	$menuOptions["users"] = array(
								array("List of Users","users_list.php",""),
								array("Add New User","users_detail.php","&xType=new"),
								array("Management Options","users_options.php",""),
								array("View Actions Log File","users_log.php","&xCommand=view"),
								array("Clear Actions Log File","users_log.php","&xCommand=clear")
							);
							
	$subpanelArray["users"] = array("","");
							
	$sectionsArray["general"] = array("Administration","general_company.php");
	
	$menuOptions["general"] = array(
								array("Company Details","general_company.php",""),
								array("Global Options","general_global.php",""),
								array("General Settings","general_general.php",""),
								array("Currency Settings","general_currencies.php",""),
								array("Language Settings","general_languages.php",""),
								array("Extra Product Fields","general_extrafields.php",""),
								array("Contact Form Fields","customers_fields.php","&xType=F"),
								array("Search/Listing Settings","general_searchlist.php",""),
								array("Product Editing","general_productediting.php",""),
								array("Meta Tag Details","general_metatags.php",""),
								array("Stock Control Settings","general_stockcontrol.php",""),
								array("Order Admin Settings","general_orderadmin.php",""),
								array("Extra Order Paperwork","general_paperwork.php",""),
								array("Digital Products Settings","general_digitalproducts.php",""),
								array("NEWMENU","Shop Navigation",""),
								array("List Settings","general_list.php",""),
								array("Section Settings","general_section.php",""),
								array("Search Settings","general_search.php",""),
								array("Basket Settings","general_basket.php",""),
								array("Reviews Settings","general_reviews.php",""),
								array("Recent View Settings","general_recentlyviewed.php",""),
								array("Users Online Settings","general_usersonline.php","")
							);
							
	$subpanelArray["general"] = array("","");							

	$sectionsArray["contents"] = array("Sections","sections_structure.php");
	
	$menuOptions["contents"] = array(
								array("Sections Structure","sections_structure.php",""),
								array("ABC Sections Listing","sections_listing.php","&xType=ABC"),
								array("Invisible Sections Listing","sections_listing.php","&xType=INVISIBLE"),
								array("NEWMENU","Products",""),
								array("Product Categories","products_categories.php",""),
								array("Product Flags","products_flags.php",""),
								array("Add New Product","products_product_detail.php","&xType=new"),
								array("Edit Template Product","products_product_detail.php","&xType=edit&xProductID=1"),
								array("ABC Products Listing","products_listing.php","&xType=ABC"),
								array("Invisible Products Listing","products_listing.php","&xType=INVISIBLE"),
								array("Sort Special Offers","reorder.php","&xType=specialoffers"),
								array("Sort New Products","reorder.php","&xType=newproducts"),
								array("Sort Top Products","reorder.php","&xType=topproducts"),
								array("Global Price Change","products_pricechange.php",""),
								array("Global Option Reset","products_optionreset.php","")
							);							

	$subpanelArray["contents"] = array("Search Sections","contents_search_panel.php");

	$sectionsArray["customers"] = array("Customers","customers_listing.php?xType=ABC");
	
	$menuOptions["customers"] = array(
								array("General Settings","customers_settings.php",""),
								array("Account Types","customers_acctypes.php",""),
								array("Special Discounts","customers_discounts.php",""),
								array("Customer Fields","customers_fields.php","&xType=C"),
								array("Delivery Address Fields","customers_fields.php","&xType=D"),
								array("Add New Customer","customers_detail.php","&xType=new"),
								array("ABC Customer Listing","customers_listing.php","&xType=ABC"),
								array("Date Customer Listing","customers_listing.php","&xType=DATE"),
								array("Unmoderated Reviews","customers_reviews.php","&xType=UNMOD"),
								array("Reviews By Product","customers_reviews.php","&xType=ABC")
							);			
							
	$subpanelArray["customers"] = array("Search Customers","customers_search_panel.php");

	$sectionsArray["affiliates"] = array("Affiliates","affiliates_listing.php?xType=ABC");
	
	$menuOptions["affiliates"] = array(
								array("General Settings","affiliates_settings.php",""),
								array("Affiliate Groups","affiliates_groups.php",""),
								array("Banners","affiliates_banners.php",""),
								array("Affiliate Fields","customers_fields.php","&xType=AF"),
								array("Add New Affiliate","affiliates_detail.php","&xType=new"),
								array("New Affiliate Listing","affiliates_listing.php","&xType=NEW"),
								array("ABC Affiliate Listing","affiliates_listing.php","&xType=ABC"),
								array("Date Affiliate Listing","affiliates_listing.php","&xType=DATE"),
								array("Transactions (All)","affiliates_trans_listing.php","&xType=DATE"),
								array("Transactions (UnAuth'd)","affiliates_trans_listing.php","&xType=NOTAUTH"),
								array("Create Transaction","affiliates_trans_detail.php","&xType=new"),
								array("Show Payment Due List","affiliates_payment_listing.php","&xType=LIST")
							);	
											

	$subpanelArray["affiliates"] = array("Search Affiliates","affiliates_search_panel.php");	

	$sectionsArray["templates"] = array("Email Templates","emails_templates.php");
	
	$menuOptions["templates"] = array(
								array("Email Options","emails_options.php",""),
								array("Email Templates","emails_templates.php",""),
								array("NEWMENU","HTML Templates",""),
								array("Template Settings","general_templates_options.php",""),
								array("Remove Compiled","templates_remove.php",""),
								array("Snippets","general_snippets.php",""),
								array("Labels","general_labels.php","")								
							);							

	$subpanelArray["templates"] = array("Template Navigation","templates_panel.php");		

	$sectionsArray["backup"] = array("Backup","backup_backup.php");
	
	$menuOptions["backup"] = array(
								array("Backup Database","backup_backup.php",""),
								array("Restore Database","backup_restore.php","")
							);							

	$subpanelArray["backup"] = array("","");
	
	
	$sectionsArray["logs"] = array("Logs","logs_summary.php");
	
	$menuOptions["logs"] = array(
								array("General Options","logs_options.php",""),
								array("Logs Summary","logs_summary.php",""),
								array("Clear Logs","logs_clear.php","")
							);							

	$subpanelArray["logs"] = array("Show Reports","logs_panel.php");

	$sectionsArray["reports"] = array("Reports","reports_summary.php");
	
	$menuOptions["reports"] = array(
								array("General Options","reports_options.php",""),
								array("Reports Summary","reports_summary.php",""),
								array("Clear Report Data","reports_clear.php",""),
								array("NEWMENU","Special Reports",""),
								array("Order Summary Report","reports_orders.php","")
							);							

	$subpanelArray["reports"] = array("Show Report","reports_panel.php");		

	$sectionsArray["export"] = array("Import","import.php");
	
	$menuOptions["export"] = array(
								array("Update Product Images","import.php","&xType=images"),
								array("Update Stock Levels","import.php","&xType=stocklevels"),
								array("Update Main Prices","import.php","&xType=prices"),
								array("Update Existing Products","import.php","&xType=updateproducts"),
								array("Import New Products","import.php","&xType=products"),
								array("Update Qty Discounts","import.php","&xType=qtydiscounts"),
								array("Update Base Pricing","import.php","&xType=basepricing"),
								array("Update Attribute Comb.","import.php","&xType=attributes"),
								array("Import Mailing List","import.php","&xType=mailinglist"),
								array("Import Customers","import.php","&xType=customers"),
								array("NEWMENU","Export",""),
								array("Export Mailing List","export.php","&xType=mailinglist"),
								array("Export Orders","export.php","&xType=orders"),
								array("Export Products","export.php","&xType=products"),
								array("Export Customers","export.php","&xType=customers"),
								array("Export Stock Levels","export.php","&xType=stocklevels"),
								array("Export Affiliates","export.php","&xType=affiliates")
							);
							
	$subpanelArray["export"] = array("","");

	$sectionsArray["newsletter"] = array("Newsletter","newsletter_list.php");
	
	$menuOptions["newsletter"] = array(
								array("Newsletter Options","newsletter_options.php",""),
								array("List of Newsletters","newsletter_list.php",""),
								array("Add New Newsletter","newsletter_detail.php","&xType=new"),
								array("Subscribed Emails","newsletter_emails_listing.php",""),
								array("Bulk Remove Emails","newsletter_emails_bulk_remove.php",""),
								array("NEWMENU","News Items",""),
								array("Latest News","general_news.php","")
							);

	$subpanelArray["newsletter"] = array("Search Mailing List","newsletter_emails_search_panel.php");

	$sectionsArray["taxshipping"] = array("General","general_countries.php");
	
	$menuOptions["taxshipping"] = array(
								array("Main Country List","countries_listing.php",""),
								array("Country Settings","general_countries.php",""),
								array("Reorder Countries","reorder.php","&xType=countries"),
								array("NEWMENU","Tax",""),
								array("General Settings","tax_settings.php",""),
								array("Country Level Tax","tax_countries.php",""),
								array("County/State Level Tax","tax_counties.php",""),
								array("NEWMENU","Shipping",""),
								array("General Settings","shipping_settings.php",""),
								array("Shipping Zones","shipping_zones.php",""),
								array("Shipping Types","shipping_types.php",""),
								array("Couriers","shipping_couriers.php",""),
								array("NEWMENU","Suppliers",""),
								array("General Settings","suppliers_settings.php",""),
								array("Email Templates","suppliers_emails.php",""),
								array("Supplier Fields","customers_fields.php","&xType=SU"),
								array("ABC Supplier Listing","suppliers_listing.php","&xType=ABC"),
								array("Add New Supplier","suppliers_detail.php","&xType=new")
							);
	$subpanelArray["taxshipping"] = array("Search Suppliers","suppliers_search_panel.php");							

	$sectionsArray["checkout"] = array("Checkout","general_checkout.php");
	
	$menuOptions["checkout"] = array(
								array("Checkout Settings","general_checkout.php",""),
								array("Extra Order Fields","customers_fields.php","&xType=O"),
								array("Payment Options","payment_options.php",""),
								array("Credit Card Fields","customers_fields.php","&xType=CC"),
								array("Gift Certificate Settings","general_giftcerts.php",""),
								array("Gift Certificate Fields","customers_fields.php","&xType=G"),
								array("Create New Gift Cert","giftcerts_detail.php","&xType=new"),
								array("Offer Code Settings","general_offercodes.php",""),
								array("List Of Offer Codes","offercodes_listing.php","&xType=ABC"),
								array("Create New Offer Code","offercodes_detail.php","&xType=new"),
								array("Redeemed Codes Report","offercodes_report_pre.php","")
							);							

	$subpanelArray["checkout"] = array("Gift Certificates","checkout_search_panel.php");								
?>
