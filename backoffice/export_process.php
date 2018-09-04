<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	
	include ("../routines/Xtea.php");
	

	function outputHeader() {
		global $xOutputMethod,$fp,$fileTmpName,$xType;
		switch ($xOutputMethod) {
			case "D":
				header("Content-type: text/plain");
				header("Content-Disposition: attachment; filename=export.txt");
				break;
			case "O":
				break;
			case "S":
				$fileTmpName = getFORM("xFileLocal");
				$fp = @fopen($fileTmpName,"w");
				if (!$fp) {
					createProcessMessage("Export File Cannot be Created!",
					"JShop Server Could Not Create Your Export File!",
					"Your export file ($fileTmpName) could not be opened. You should ensure that the directory exists,<br>that the correct permissions are set and that you<br>entered a valid file name.",
					"&lt; Back",
					"self.location.href='export.php?xType=$xType&".userSessionGET()."';");		
					exit;
				}
				break;
		}
	}
	
	function outputData($data) {
		global $xOutputMethod;
		global $fp;
		switch ($xOutputMethod) {
			case "D":
				echo $data."\r\n";
				break;
			case "O":
				echo $data."\r\n";	
				break;
			case "S":
				fwrite($fp, $data."\r\n");	
				break;
		}
	}
	
	function outputFooter() {
		global $xOutputMethod,$fp,$fileTmpName,$xType;
		switch ($xOutputMethod) {
			case "D":
				break;
			case "O":	
				break;
			case "S":
				fclose($fp);
				createProcessMessage("Export File Created!",
					"JShop Server Has Created Your Export File!",
					"Your export file ($fileTmpName) has been created.",
					"&lt; Back",
					"self.location.href='export.php?xType=$xType&".userSessionGET()."';");	
				exit;
				break;
		}
	}
   		
	$crypt = new Crypt_Xtea();	
	$xAction = getFORM("xAction");
	$xOutputMethod = getFORM("xOutputMethod");
	$xType = getFORM("xType");
	
	if ($xAction == "export") {
		outputHeader();
		dbConnect($dbA);
		$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		$xFieldList = getFORM("xFieldList");
		$theFields = split(";",$xFieldList);
		$xFirstRowHeadings = getFORM("xFirstRowHeadings");
		$xDelimiter = getFORM("xDelimiter");
		$xTextQualifier = getFORM("xTextQualifier");
		switch ($xDelimiter) {
			case "tab":
				$xDelimiter = "\t";
				break;
			case "comma":
				$xDelimiter = ",";
				break;
			case "semicolon":
				$xDelimiter = ";";
				break;
			case "pipe":
				$xDelimiter = "|";
				break;
		}
		switch ($xTextQualifier) {
			case "Y":
				$xTextQualifier = "\"";
				break;
			default:
				$xTextQualifier = "";
		}
		switch ($xType) {
			case "affiliates":
				outputFirstRowHeading();
				$result = $dbA->query("select * from $tableAffiliates,$tableAffiliatesGroups where $tableAffiliates.groupID = $tableAffiliatesGroups.groupID order by affiliateID");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					if (function_exists('set_time_limit')) { @set_time_limit(30); }
					$thisLine = "";
					$record = $dbA->fetch($result);
					for ($g = 0; $g < count($theFields); $g++) {
						$fieldValue = "";
						switch ($theFields[$g]) {
							case "BLANK":
								$fieldValue = $xTextQualifier.$xTextQualifier; 
								break;
							case "affiliateID":
								$fieldValue = $record["affiliateID"];
								break;
							case "username":
								$fieldValue = $xTextQualifier.$record["username"].$xTextQualifier;
								break;
							case "joindate":
								$fieldValue = $xTextQualifier.formatDate($record["date"]).$xTextQualifier;
								break;
							case "status":
								$fieldValue = $xTextQualifier.$record["status"].$xTextQualifier;
								break;
							case "groupID":
								$fieldValue = $record["groupID"];
								break;
							case "groupName":
								$fieldValue = $record["name"];
								break;
						}
						$affiliateFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='AF' and visible=1 order by position");
						for ($z = 0; $z < count($affiliateFields); $z++) {
							if ($theFields[$g] == $affiliateFields[$z]["fieldname"]) {
								$fieldValue = $xTextQualifier.$record[$affiliateFields[$z]["fieldname"]].$xTextQualifier;
							}
						}
						if ($theFields[$g] != "") {
							if ($thisLine == "") {
								$thisLine = $fieldValue;
							} else {
								$thisLine .= $xDelimiter.$fieldValue;
							}
						}
					}
					outputData($thisLine);			
				}
				outputFooter();
				break;
			case "mailinglist":
				outputFirstRowHeading();
				$result = $dbA->query("select * from $tableNewsletter order by emailaddress");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					if (function_exists('set_time_limit')) { @set_time_limit(30); }
					$thisLine = "";
					$record = $dbA->fetch($result);
					for ($g = 0; $g < count($theFields); $g++) {
						$fieldValue = "";
						switch ($theFields[$g]) {
							case "BLANK":
								$fieldValue = $xTextQualifier.$xTextQualifier; 
								break;
							case "recipientID":
								$fieldValue = $record["recipientID"];
								break;
							case "emailaddress":
								$fieldValue = $xTextQualifier.$record["emailaddress"].$xTextQualifier;
								break;
						}
						if ($theFields[$g] != "") {
							if ($thisLine == "") {
								$thisLine = $fieldValue;
							} else {
								$thisLine .= $xDelimiter.$fieldValue;
							}
						}
					}	
					outputData($thisLine);			
				}
				outputFooter();
				break;
			case "stocklevels":
				outputFirstRowHeading();
				$result = $dbA->query("select * from $tableProducts where productID != 1 order by code,name");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					if (function_exists('set_time_limit')) { @set_time_limit(30); }
					$thisLine = "";
					$record = $dbA->fetch($result);
					for ($g = 0; $g < count($theFields); $g++) {
						$fieldValue = "";
						switch ($theFields[$g]) {
							case "BLANK":
								$fieldValue = $xTextQualifier.$xTextQualifier; 
								break;
							case "productID":
								$fieldValue = $record["productID"];
								break;
							case "code":
								$fieldValue = $xTextQualifier.$record["code"].$xTextQualifier;
								break;
							case "name":
								$fieldValue = $xTextQualifier.$record["name"].$xTextQualifier;
								break;
							case "scLevel":
								$fieldValue = $record["scLevel"];
								break;
							case "scWarningLevel":
								$fieldValue = $record["scWarningLevel"];
								break;								
							case "scEnabled":
								$fieldValue = $xTextQualifier.$record["scEnabled"].$xTextQualifier;
								break;	
							case "scActionZero":
								$fieldValue = $record["scActionZero"];
								break;									
						}
						if ($theFields[$g] != "") {
							if ($thisLine == "") {
								$thisLine = $fieldValue;
							} else {
								$thisLine .= $xDelimiter.$fieldValue;
							}
						}
					}	
					outputData($thisLine);				
				}
				outputFooter();
				break;	
			case "products":
				outputFirstRowHeading();
				$result = $dbA->query("select * from $tableProducts where productID != 1 order by code,name");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					if (function_exists('set_time_limit')) { @set_time_limit(30); }
					$thisLine = "";
					$record = $dbA->fetch($result);
					$extraFieldValues = $dbA->retrieveAllRecordsFromQuery("select * from $tableExtraFieldsValues where productID = ".$record["productID"]." order by extraFieldID,position,content");
					for ($g = 0; $g < count($theFields); $g++) {
						$fieldValue = "";
						switch ($theFields[$g]) {
							case "BLANK":
								$fieldValue = $xTextQualifier.$xTextQualifier; 
								break;
							case "productID":
								$fieldValue = $record["productID"];
								break;
							case "product URL":
								if (retrieveOption("useRewriteURLs") == 0) {
									$fieldValue = $xTextQualifier.$jssStoreWebDirHTTP."product.php?xProd=".$record["productID"].$xTextQualifier;
								} else {
									$fieldValue = $xTextQualifier.$jssStoreWebDirHTTP."product.php/".$record["productID"]."/0".$xTextQualifier;
								}
								break;
							case "code":
								$fieldValue = $xTextQualifier.$record["code"].$xTextQualifier;
								break;
							case "name":
								$fieldValue = $xTextQualifier.$record["name"].$xTextQualifier;
								break;
							case "shortdescription":
								$fieldValue = $xTextQualifier.$record["shortdescription"].$xTextQualifier;
								break;
							case "description":
								$fieldValue = $xTextQualifier.str_replace("\r\n","\\r\\n",$record["description"]).$xTextQualifier;
								break;
							case "thumbnail":
								$fieldValue = $xTextQualifier.$record["thumbnail"].$xTextQualifier;
								break;
							case "mainimage":
								$fieldValue = $xTextQualifier.$record["mainimage"].$xTextQualifier;
								break;
							case "thumbnail (full URL)":
								$fieldValue = $xTextQualifier.$jssStoreWebDirHTTP.$record["thumbnail"].$xTextQualifier;
								break;
							case "mainimage (full URL)":
								$fieldValue = $xTextQualifier.$jssStoreWebDirHTTP.$record["mainimage"].$xTextQualifier;
								break;
							case "visible":
								$fieldValue = $xTextQualifier.$record["visible"].$xTextQualifier;
								break;
							case "metaDescription":
								$fieldValue = $xTextQualifier.$record["metaDescription"].$xTextQualifier;
								break;
							case "metaKeywords":
								$fieldValue = $xTextQualifier.$record["metaKeywords"].$xTextQualifier;
								break;
							case "keywords":
								$fieldValue = $xTextQualifier.$record["keywords"].$xTextQualifier;
								break;
							case "templateFile":
								$fieldValue = $xTextQualifier.$record["templateFile"].$xTextQualifier;
								break;
							case "newproduct":
								$fieldValue = $xTextQualifier.$record["newproduct"].$xTextQualifier;
								break;
							case "topproduct":
								$fieldValue = $xTextQualifier.$record["topproduct"].$xTextQualifier;
								break;
							case "scLevel":
								$fieldValue = $record["scLevel"];
								break;
							case "scWarningLevel":
								$fieldValue = $record["scWarningLevel"];
								break;								
							case "scEnabled":
								$fieldValue = $xTextQualifier.$record["scEnabled"].$xTextQualifier;
								break;	
							case "scActionZero":
								$fieldValue = $record["scActionZero"];
								break;									
							case "productType":
								$fieldValue = $xTextQualifier.$record["productType"].$xTextQualifier;
								break;
							case "weight":
								$fieldValue = $record["weight"];
								break;
							case "taxrate":
								$fieldValue = $record["taxrate"];
								break;
							case "freeShipping":
								$fieldValue = $xTextQualifier.$record["freeShipping"].$xTextQualifier;
								break;
							case "specialoffer":
								$fieldValue = $xTextQualifier.$record["specialoffer"].$xTextQualifier;
								break;
							case "sectionIDs";
								$secResult = $dbA->query("select sectionID from $tableProductsTree where productID=".$record["productID"]." order by sectionID");
								$secList = "";
								if ($dbA->count($secResult) > 0) {
									$secCount = $dbA->count($secResult);
									for ($secs = 0; $secs < $secCount; $secs++) {
										$secRecord = $dbA->fetch($secResult);
										if ($secList != "") { $secList = $secList . ","; }
										$secList = $secList . $secRecord["sectionID"];
									}
								}
								$fieldValue = $secList;
								break;
							case "sectionNames";
								$secResult = $dbA->query("select $tableSections.title from $tableProductsTree,$tableSections where productID=".$record["productID"]." and $tableSections.sectionID = $tableProductsTree.sectionID order by title");
								$secList = "";
								if ($dbA->count($secResult) > 0) {
									$secCount = $dbA->count($secResult);
									for ($secs = 0; $secs < $secCount; $secs++) {
										$secRecord = $dbA->fetch($secResult);
										if ($secList != "") { $secList = $secList . ","; }
										$secList = $secList . $secRecord["title"];
									}
								}
								$fieldValue = $xTextQualifier.$secList.$xTextQualifier;
								break;
						}
						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "price".$currArray[$z]["code"]) {
								$fieldValue = $record["price".$currArray[$z]["currencyID"]];
							}
						}
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							if ($theFields[$g] == $extraFieldsArray[$z]["name"]) {
								switch ($extraFieldsArray[$z]["type"]) {
									case "SELECT":
									case "CHECKBOXES":
									case "RADIOBUTTONS":
										for ($x = 0; $x < count($extraFieldValues); $x++) {
											if ($extraFieldValues[$x]["extraFieldID"] == $extraFieldsArray[$z]["extraFieldID"]) {
												if ($fieldValue == "") {
													$fieldValue = $extraFieldValues[$x]["content"];
												} else {
													$fieldValue .= ",".$extraFieldValues[$x]["content"];
												}
											}
										}
										$fieldValue = $xTextQualifier.$fieldValue.$xTextQualifier;
										break;
									default:
										$fieldValue = $xTextQualifier.str_replace("\r\n","\\r\\n",@$record["extrafield".$extraFieldsArray[$z]["extraFieldID"]]).$xTextQualifier;
										break;
								}
							}
						}
						for ($y = 0; $y < count($languages); $y++) {
							for ($z = 0; $z < count($extraFieldsArray); $z++) {
								if ($theFields[$g] == $extraFieldsArray[$z]["name"]."_".$languages[$y]["name"]) {
									switch ($extraFieldsArray[$z]["type"]) {
										case "SELECT":
										case "CHECKBOXES":
										case "RADIOBUTTONS":
											for ($x = 0; $x < count($extraFieldValues); $x++) {
												if ($extraFieldValues[$x]["extraFieldID"] == $extraFieldsArray[$z]["extraFieldID"]) {
													if ($fieldValue == "") {
														$fieldValue = $extraFieldValues[$x]["content".$languages[$y]["languageID"]];
													} else {
														$fieldValue .= ",".$extraFieldValues[$x]["content".$languages[$y]["languageID"]];
													}
												}
											}
											$fieldValue = $xTextQualifier.$fieldValue.$xTextQualifier;
											break;
										default:
											$fieldValue = $xTextQualifier.str_replace("\r\n","\\r\\n",@$record["extrafield".$extraFieldsArray[$z]["extraFieldID"]."_".$languages[$y]["languageID"]]).$xTextQualifier;
											break;
									}
								}
							}
						}
						for ($z = 0; $z < count($languages); $z++) {
							if ($theFields[$g] == "name_".$languages[$z]["name"]) {
								$fieldValue = $xTextQualifier.$record["name".$languages[$z]["languageID"]].$xTextQualifier;
							}
							if ($theFields[$g] == "shortdescription_".$languages[$z]["name"]) {
								$fieldValue = $xTextQualifier.$record["shortdescription".$languages[$z]["languageID"]].$xTextQualifier;
							}
							if ($theFields[$g] == "description_".$languages[$z]["name"]) {
								$fieldValue = $xTextQualifier.str_replace("\r\n","\\r\\n",$record["description".$languages[$z]["languageID"]]).$xTextQualifier;
							}

						}	
						if ($theFields[$g] != "") {
							if ($thisLine == "") {
								$thisLine = $fieldValue;
							} else {
								$thisLine .= $xDelimiter.$fieldValue;
							}
						}
					}	
					outputData($thisLine);				
				}
				outputFooter();
				break;						
			case "customers":
				$extraCustomerFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and deletable=1 order by position,titleText");
				outputFirstRowHeading();
				$result = $dbA->query("select * from $tableCustomers order by customerID");
				$count = $dbA->count($result);
				for ($f = 0; $f < $count; $f++) {
					if (function_exists('set_time_limit')) { @set_time_limit(30); }
					$thisLine = "";
					$record = $dbA->fetch($result);
					foreach ($record as $key => $value) { 
						$record[$key] = unhtmlentities($value);
					} 
					for ($g = 0; $g < count($theFields); $g++) {
						$fieldValue = "";
						switch ($theFields[$g]) {
							case "BLANK":
								$fieldValue = $xTextQualifier.$xTextQualifier; 
								break;
							case "customerID":
								$fieldValue = $record["customerID"];
								break;
							case "accTypeID":
								$fieldValue = $record["accTypeID"];
								break;								
							case "title":
								$fieldValue = $xTextQualifier.$record["title"].$xTextQualifier;
								break;
							case "forename":
								$fieldValue = $xTextQualifier.$record["forename"].$xTextQualifier;
								break;
							case "surname":
								$fieldValue = $xTextQualifier.$record["surname"].$xTextQualifier;
								break;
							case "fullname":
								$fieldValue = $xTextQualifier.$record["title"]." ".$record["forename"]." ".$record["surname"].$xTextQualifier;
								break;
							case "address1":
								$fieldValue = $xTextQualifier.$record["address1"].$xTextQualifier;
								break;
							case "address2":
								$fieldValue = $xTextQualifier.$record["address2"].$xTextQualifier;
								break;							
							case "town":
								$fieldValue = $xTextQualifier.$record["town"].$xTextQualifier;
								break;
							case "county":
								$fieldValue = $xTextQualifier.$record["county"].$xTextQualifier;
								break;
							case "postcode":
								$fieldValue = $xTextQualifier.$record["postcode"].$xTextQualifier;
								break;
							case "country":
								$fieldValue = $xTextQualifier.$record["country"].$xTextQualifier;
								break;
							case "telephone":
								$fieldValue = $xTextQualifier.$record["telephone"].$xTextQualifier;
								break;
							case "fax":
								$fieldValue = $xTextQualifier.$record["fax"].$xTextQualifier;
								break;
							case "email":
								$fieldValue = $xTextQualifier.$record["email"].$xTextQualifier;
								break;
							case "company":
								$fieldValue = $xTextQualifier.$record["company"].$xTextQualifier;
								break;
							case "newsletter":
								$fieldValue = $xTextQualifier.$record["newsletter"].$xTextQualifier;
								break;
							case "creationdate":
								$fieldValue = $xTextQualifier.formatDate($record["date"]).$xTextQualifier;
								break;
						}
						for ($z = 0; $z < count($extraCustomerFields); $z++) {
							if ($theFields[$g] == $extraCustomerFields[$z]["fieldname"]) {
								$fieldValue = $xTextQualifier.$record[$extraCustomerFields[$z]["fieldname"]].$xTextQualifier;
							}
						}
						if ($theFields[$g] != "") {
							if ($thisLine == "") {
								$thisLine = $fieldValue;
							} else {
								$thisLine .= $xDelimiter.$fieldValue;
							}
						}
					}	
					outputData($thisLine);			
				}
				outputFooter();
				break;						
			case "orders":
				$accTypes = $dbA->retrieveAllRecords($tableCustomersAccTypes,"accTypeID");
				$extraCustomerFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' order by position,titleText");
				$extraDeliveryFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='D' order by position,titleText");
				$extraOrderFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='O' order by position,titleText");
				outputFirstRowHeading();
				$incOrderLines = false;
				for ($g = 0; $g < count($theFields); $g++) {
					switch ($theFields[$g]) {
						case "lineID":
						case "productID":
						case "code":
						case "name":
						case "qty":
						case "weight":
						case "price":
							$incOrderLines = true;
					}
				}				

				$xDayF = getFORM("xDayF");
				$xMonthF = getFORM("xMonthF");
				$xYearF = getFORM("xYearF");
				$xDayT = getFORM("xDayT");
				$xMonthT = getFORM("xMonthT");
				$xYearT = getFORM("xYearT");
				$dateFrom = $xYearF.$xMonthF.$xDayF."000000";
				$dateTo = $xYearT.$xMonthT.$xDayT."999999";
					
				if (getFORM("xStatus") != "") {
					$statusBit = " and $tableOrdersHeaders.status = '".getFORM("xStatus")."'";
				} else {
					$statusBit = "";
				}
				if ($incOrderLines) {
					$result = $dbA->query("select * from $tableOrdersHeaders,$tableOrdersLines where ($tableOrdersHeaders.datetime >= '$dateFrom' and $tableOrdersHeaders.datetime <= '$dateTo') and $tableOrdersHeaders.orderID = $tableOrdersLines.orderID $statusBit order by $tableOrdersHeaders.orderID,lineID");
					$count = $dbA->count($result);
				} else {
					$result = $dbA->query("select * from $tableOrdersHeaders where ($tableOrdersHeaders.datetime >= '$dateFrom' and $tableOrdersHeaders.datetime <= '$dateTo') $statusBit order by orderID");
					$count = $dbA->count($result);
				}
				
				for ($f = 0; $f < $count; $f++) {
					if (function_exists('set_time_limit')) { @set_time_limit(30); }
					$thisLine = "";
					$record = $dbA->fetch($result);
					for ($g = 0; $g < count($theFields); $g++) {
						$fieldValue = "";
						switch ($theFields[$g]) {
							case "BLANK":
								$fieldValue = $xTextQualifier.$xTextQualifier; 
								break;
							case "orderID":
								$fieldValue = $record["orderID"] + retrieveOption("orderNumberOffset");
								break;
							case "customerID":
								$fieldValue = $record["customerID"];
								break;
							case "accountTypeID":
								$fieldValue = $record["accTypeID"];
								break;
							case "accountTypeText":
								$accType = "";
								for ($zz = 0; $zz < count($accTypes); $zz++) {
									if ($accTypes[$zz]["accTypeID"] == $record["accTypeID"]) {
										$accType = $accTypes[$zz]["name"];
										break;
									}
								}
								$fieldValue = $xTextQualifier.$accType.$xTextQualifier;
								break;
							case "date":
								$fieldValue = $xTextQualifier.formatDate($record["datetime"]).$xTextQualifier;
								break;
							case "time":
								$fieldValue = $xTextQualifier.formatTime(substr($record["datetime"],8,6)).$xTextQualifier;
								break;
							case "ip":
								$fieldValue = $xTextQualifier.$record["ip"].$xTextQualifier;
								break;
							case "customerID":
								$fieldValue = $record["customerID"];
								break;
							case "title":
								$fieldValue = $xTextQualifier.$record["title"].$xTextQualifier;
								break;
							case "forename":
								$fieldValue = $xTextQualifier.$record["forename"].$xTextQualifier;
								break;
							case "surname":
								$fieldValue = $xTextQualifier.$record["surname"].$xTextQualifier;
								break;
							case "fullname":
								$fieldValue = $xTextQualifier.$record["title"]." ".$record["forename"]." ".$record["surname"].$xTextQualifier;
								break;
							case "address1":
								$fieldValue = $xTextQualifier.$record["address1"].$xTextQualifier;
								break;
							case "address2":
								$fieldValue = $xTextQualifier.$record["address2"].$xTextQualifier;
								break;							
							case "town":
								$fieldValue = $xTextQualifier.$record["town"].$xTextQualifier;
								break;
							case "county":
								$fieldValue = $xTextQualifier.$record["county"].$xTextQualifier;
								break;
							case "postcode":
								$fieldValue = $xTextQualifier.$record["postcode"].$xTextQualifier;
								break;
							case "country":
								$fieldValue = $xTextQualifier.retrieveCountry($record["country"]).$xTextQualifier;
								break;
							case "telephone":
								$fieldValue = $xTextQualifier.$record["telephone"].$xTextQualifier;
								break;
							case "fax":
								$fieldValue = $xTextQualifier.$record["fax"].$xTextQualifier;
								break;
							case "email":
								$fieldValue = $xTextQualifier.$record["email"].$xTextQualifier;
								break;
							case "company":
								$fieldValue = $xTextQualifier.$record["company"].$xTextQualifier;
								break;
							case "deliveryName":
								$fieldValue = $xTextQualifier.$record["deliveryName"].$xTextQualifier;
								break;
							case "deliveryAddress1":
								$fieldValue = $xTextQualifier.$record["deliveryAddress1"].$xTextQualifier;
								break;
							case "deliveryAddress2":
								$fieldValue = $xTextQualifier.$record["deliveryAddress2"].$xTextQualifier;
								break;							
							case "deliveryTown":
								$fieldValue = $xTextQualifier.$record["deliveryTown"].$xTextQualifier;
								break;
							case "deliveryCounty":
								$fieldValue = $xTextQualifier.$record["deliveryCounty"].$xTextQualifier;
								break;
							case "deliveryPostcode":
								$fieldValue = $xTextQualifier.$record["deliveryPostcode"].$xTextQualifier;
								break;
							case "deliveryCountry":
								$fieldValue = $xTextQualifier.retrieveCountry($record["deliveryCountry"]).$xTextQualifier;
								break;
							case "deliveryTelephone":
								$fieldValue = $xTextQualifier.$record["deliveryTelephone"].$xTextQualifier;
								break;
							case "deliveryCompany":
								$fieldValue = $xTextQualifier.$record["deliveryCompany"].$xTextQualifier;
								break;	
							case "currencyCode":
								for ($xx = 0; $xx < count($currArray); $xx++) {
									if ($currArray[$xx]["currencyID"] == $record["currencyID"]) {
										$fieldValue = $xTextQualifier.$currArray[$xx]["code"].$xTextQualifier;							
									}
								}
								break;
							case "goodsTotal":
								$fieldValue = $record["goodsTotal"];
								break;
							case "shippingTotal":
								$fieldValue = $record["shippingTotal"];
								break;
							case "taxTotal":
								$fieldValue = $record["taxTotal"];
								break;
							case "discountTotal":
								$fieldValue = $record["discountTotal"];
								break;
							case "giftCertTotal":
								$fieldValue = $record["giftCertTotal"];
								break;
							case "orderTotal":
								$fieldValue = $record["goodsTotal"]+$record["shippingTotal"]+$record["taxTotal"]-$record["discountTotal"]-$record["giftCertTotal"];
								break;
							case "status":
								$fieldValue = $xTextQualifier.$record["status"].$xTextQualifier;
								break;
							case "shippingMethod":
								$fieldValue = $xTextQualifier.$record["shippingMethod"].$xTextQualifier;
								break;
							case "paymentName":
								$fieldValue = $xTextQualifier.$record["paymentName"].$xTextQualifier;
								break;
							case "ccName":
								$fieldValue = $xTextQualifier.$record["ccName"].$xTextQualifier;
								break;
							case "ccNumber":
								$checkingString="01234567890 ";
								if ($record["ccNumber"] != "") {
									$ccEnc = isValidCard($record["ccNumber"]);
									$myCounter = 0;
									while ($ccEnc && $myCounter < 20) {
										$record["ccNumber"] = $crypt->decrypt(base64_decode($record["ccNumber"]), $teaEncryptionKey);
										$ccEnc = isValidCard($record["ccNumber"]);
										$myCounter++;
									}
								}
								$fieldValue = $xTextQualifier.$record["ccNumber"].$xTextQualifier;
								break;
							case "ccExpiryDate":
								$fieldValue = $xTextQualifier.$record["ccExpiryDate"].$xTextQualifier;
								break;
							case "ccType":
								$fieldValue = $xTextQualifier.$record["ccType"].$xTextQualifier;
								break;
							case "ccStartDate":
								$fieldValue = $xTextQualifier.$record["ccStartDate"].$xTextQualifier;
								break;
							case "ccIssue":
								$fieldValue = $xTextQualifier.$record["ccIssue"].$xTextQualifier;
								break;
							case "ccCVV":
								$checkingString="01234567890 ";
								if ($record["ccCVV"] != "") {
									$ccEnc = isValidCard($record["ccCVV"]);
									$myCounter = 0;
									while ($ccEnc && $myCounter < 20) {
										$record["ccCVV"] = $crypt->decrypt(base64_decode($record["ccCVV"]), $teaEncryptionKey);
										$ccEnc = isValidCard($record["ccCVV"]);
										$myCounter++;
									}
								}
								$fieldValue = $xTextQualifier.$record["ccCVV"].$xTextQualifier;
								break;
							case "orderPrinted":
								$fieldValue = $xTextQualifier.$record["orderPrinted"].$xTextQualifier;
								break;
							case "orderNotes":
								$fieldValue = $xTextQualifier.$record["orderNotes"].$xTextQualifier;
								break;	
							case "lineID":
								$fieldValue = $record["lineID"];
								break;
							case "productID":
								$fieldValue = $record["productID"];
								break;
							case "code":
								$fieldValue = $xTextQualifier.$record["code"].$xTextQualifier;
								break;
							case "name":
								$fieldValue = $xTextQualifier.$record["name"].$xTextQualifier;
								break;
							case "qty":
								$fieldValue = $record["qty"];
								break;
							case "weight":
								$fieldValue = $record["weight"];
								break;
							case "price":					
								$fieldValue = roundWithoutCalcDisplay($record["price"],$record["currencyID"]);
								break;	
							case "ooprice":					
								$fieldValue = roundWithoutCalcDisplay($record["ooprice"],$record["currencyID"]);;			
								break;	
							case "referURL":					
								$fieldValue = $xTextQualifier.$record["referURL"].$xTextQualifier;			
								break;		
							case "affiliateID":					
								$fieldValue = $xTextQualifier.$record["affiliateID"].$xTextQualifier;			
								break;	
							case "affiliateUsername":					
								$affiliateName = "";
								if ($record["affiliateID"] != 0) {
									$afResult = $dbA->query("select * from $tableAffiliates where affiliateID=".$record["affiliateID"]);
									if ($dbA->count($afResult) > 0) {
										$afRecord = $dbA->fetch($afResult);
										$affiliateName = $afRecord["username"];
									}
								}
								$fieldValue = $affiliateName;
								break;	
						}
						if ($record["customerID"] > 0) {
							$custRes = $dbA->query("select * from $tableCustomers where customerID=".makeInteger($record["customerID"]));
							if ($dbA->count($custRes) > 0) {
								$custRecord = $dbA->fetch($custRes);
								for ($z = 0; $z < count($extraCustomerFields); $z++) {
									if ($theFields[$g] == $extraCustomerFields[$z]["fieldname"]) {
										$fieldValue = $xTextQualifier.$custRecord[$extraCustomerFields[$z]["fieldname"]].$xTextQualifier;
									}
								}
							}
						} else {
							$oRecord["customerID"] = 0;
							for ($z = 0; $z < count($extraCustomerFields); $z++) {
								if ($theFields[$g] == $extraCustomerFields[$z]["fieldname"]) {
									$fieldValue = $xTextQualifier.@$record[$extraCustomerFields[$z]["fieldname"]].$xTextQualifier;
								}
							}
						}	
						if ($record["customerID"] != 0) {
							for ($z = 0; $z < count($extraCustomerFields); $z++) {
								if ($theFields[$g] == $extraCustomerFields[$z]["fieldname"]) {
									$fieldValue = $xTextQualifier.$custRecord[$extraCustomerFields[$z]["fieldname"]].$xTextQualifier;
								}
							}
							for ($z = 0; $z < count($extraDeliveryFields); $z++) {
								if ($theFields[$g] == $extraDeliveryFields[$z]["fieldname"]) {
									$fieldValue = $xTextQualifier.$record[$extraDeliveryFields[$z]["fieldname"]].$xTextQualifier;
								}
							}
							for ($z = 0; $z < count($extraOrderFields); $z++) {
								if ($theFields[$g] == $extraOrderFields[$z]["fieldname"]) {
									$fieldValue = $xTextQualifier.$record[$extraOrderFields[$z]["fieldname"]].$xTextQualifier;
								}
							}
						}
						if ($incOrderLines) {
							$extraFieldValues = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where lineID = ".$record["lineID"]."");
							for ($z = 0; $z < count($extraFieldsArray); $z++) {
								if ($theFields[$g] == $extraFieldsArray[$z]["name"]) {
									switch ($extraFieldsArray[$z]["type"]) {
										case "SELECT":
										case "CHECKBOXES":
										case "RADIOBUTTONS":
											for ($x = 0; $x < count($extraFieldValues); $x++) {
												if ($extraFieldValues[$x]["extraFieldID"] == $extraFieldsArray[$z]["extraFieldID"]) {
													if ($fieldValue == "") {
														$fieldValue = $extraFieldValues[$x]["content"];
													} else {
														$fieldValue .= ",".$extraFieldValues[$x]["content"];
													}
												}
											}
											$fieldValue = $xTextQualifier.$fieldValue.$xTextQualifier;
											break;
									}
								}
							}						
						}
						if ($theFields[$g] != "") {
							if ($thisLine == "") {
								$thisLine = $fieldValue;
							} else {
								$thisLine .= $xDelimiter.$fieldValue;
							}
						}
					}	
					outputData($thisLine);			
				}
				outputFooter();
				break;						
		}
		$dbA->close();
		exit;
	}
	
	function outputFirstRowHeading() {
		global $xFirstRowHeadings,$theFields,$xDelimiter,$xTextQualifier;
		if ($xFirstRowHeadings == "Y") {
			$headingOutput = "";
			for ($f = 0;$f < count($theFields); $f++) {
				if ($theFields[$f] != "") {
					if ($headingOutput == "") {
						$headingOutput = $xTextQualifier.$theFields[$f].$xTextQualifier;
					} else {
						$headingOutput .= $xDelimiter.$xTextQualifier.$theFields[$f].$xTextQualifier;
					}
				}
			}
			outputData($headingOutput);
		}
	}

	function retrieveCountry($countryID) {
		global $dbA,$tableCountries;
		$countryID = makeInteger($countryID);
		if ($countryID != "") {
			$result = $dbA->query("select * from $tableCountries where countryID=$countryID");
			if ($dbA->count($result) != 1) {
				return false;
			} else {
				$record = $dbA->fetch($result);
				return $record["name"];
			}
		} else {
			return "";
		}
	}	
?>