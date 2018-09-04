<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");
	$xAction = getFORM("xAction");
	$xType = getFORM("xType");
	if ($xAction == "import") {
		dbConnect($dbA);
		$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		
		if (@$_FILES["xFile"]["name"] != "") {
			$fileName = @$_FILES["xFile"]["name"];
			$fileType = @$_FILES["xFile"]["type"];
			$fileSize = @$_FILES["xFile"]["size"];
			$fileTmpName = @$_FILES["xFile"]["tmp_name"];
		} else {
			$fileTmpName = getFORM("xFileLocal");
			$fileName = getFORM("xFileLocal");
		}
		if ($fileTmpName == "") {
			$dbA->close();
			createProcessMessage("No File Specified!",
			"Import File Not Specified!",
			"You have not specified either a file from your computer or a file on<br>your server to import from!",
			"&lt; Back",
			"self.location.href='import.php?xType=$xType&".userSessionGET()."';");	
			exit;
		}
		$fp = @fopen($fileTmpName,"r");
		if (!$fp) {
			$dbA->close();
			createProcessMessage("Import File Cannot be Opened!",
			"JShop Server Could Not Open Your Import File!",
			"Your import file ($fileName) could not be opened. If you<br>uploaded the file from your computer it is possible<br>that your server's PHP settings do not allow files of that size to be uploaded.<br>If you gave a path to file on the server,<br>this could not be found or opened by JShop Server",
			"&lt; Back",
			"self.location.href='import.php?xType=$xType&".userSessionGET()."';");		
			exit;
		}
		
		
		
		/*$fileName = @$_FILES["xFile"]["name"];
		$fileType = @$_FILES["xFile"]["type"];
		$fileSize = @$_FILES["xFile"]["size"];
		$fileTmpName = @$_FILES["xFile"]["tmp_name"];
		if (!is_readable($fileTmpName)) {
			echo "<B>Uploaded import file cannot be read - please check PHP configuration</b>";
			exit;
		}
		$fp = fopen ($fileTmpName, "r"); */

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
			case "basepricing":
				$row=0;
				$updates=0;
				$thisProductID = 0;
				$prodArray = null;
				$prodArray[] = -10;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					$cancelAdd = FALSE;
					$thisProductID = 0;
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								$thisProductID = makeInteger(@$data[$g]);
								$rArray[] = array("productID",$thisProductID,"N");	
   								break;
   							case "code":
								if ($thisProductID == 0) {
									$rr = $dbA->query("select * from $tableProducts where code=\"".@$data[$g]."\"");
									if ($dbA->count($rr) > 0) {
										$rc = $dbA->fetch($rr);
										$thisProductID = $rc["productID"];
										$rArray[] = array("productID",$thisProductID,"N");	
									}
								}
   								break;
   							case "accTypeID":
   								$rArray[] = array("accTypeID",@$data[$g],"N");
   								break;
							case "QtyFrom":
   								$rArray[] = array("qtyfrom",@$data[$g],"N");
   								break;
							case "QtyTo":
   								$rArray[] = array("qtyto",@$data[$g],"N");
   								break;
   							case "Percentage":
   								$rArray[] = array("percentage",@$data[$g],"D");
   								break;
   						}
						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "discount".$currArray[$z]["code"]) {
								$rArray[] = array("price".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							switch ($extraFieldsArray[$z]["type"]) {
								case "SELECT":
								case "CHECKBOXES":
								case "RADIOBUTTONS":
									if ($theFields[$g] == $extraFieldsArray[$z]["name"]) {
										$thisFieldValue = $extraFieldsArray[$z]["extraFieldID"];
										if ($thisProductID != 0) {
											if (@$data[$g] == "" || @$data[$g] == "Any") {
												$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],0,"N");
											} else {
												$rr = $dbA->query("select * from $tableExtraFieldsValues where productID=$thisProductID and 	extraFieldID=$thisFieldValue and content=\"".@$data[$g]."\"");
												if ($dbA->count($rr) > 0) {
													$rc = $dbA->fetch($rr);
													$exvalID = $rc["exvalID"];
													$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],$exvalID,"N");
												} else {
													$cancelAdd = TRUE;
												}
											}
										}
									}
									break;
								default:
									break;
							}
						}
   					}
					if ($thisProductID > 0 && $cancelAdd == FALSE) {
						if (makeInteger(array_search($thisProductID,$prodArray)) == 0) {
							$dbA->query("delete from $tableAdvancedPricing where productID = $thisProductID and priceType = 0");
							$prodArray[] = $thisProductID;
						}
						$rArray[] = array("priceType",0,"N");
						$dbA->insertRecord($tableAdvancedPricing,$rArray,0);
						$updates++;
					}
					$row++; 
				} 
				fclose ($fp);
				importComplete("base price combinations",$updates);
				break;
			case "qtydiscounts":
				$row=0;
				$updates=0;
				$thisProductID = 0;
				$prodArray = null;
				$prodArray[] = -10;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					$cancelAdd = FALSE;
					$thisProductID = 0;
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								$thisProductID = makeInteger(@$data[$g]);
								$rArray[] = array("productID",$thisProductID,"N");	
   								break;
   							case "code":
								if ($thisProductID == 0) {
									$rr = $dbA->query("select * from $tableProducts where code=\"".@$data[$g]."\"");
									if ($dbA->count($rr) > 0) {
										$rc = $dbA->fetch($rr);
										$thisProductID = $rc["productID"];
										$rArray[] = array("productID",$thisProductID,"N");	
									}
								}
   								break;
   							case "accTypeID":
   								$rArray[] = array("accTypeID",@$data[$g],"N");
   								break;
							case "QtyFrom":
   								$rArray[] = array("qtyfrom",@$data[$g],"N");
   								break;
							case "QtyTo":
   								$rArray[] = array("qtyto",@$data[$g],"N");
   								break;
   							case "Percentage":
   								$rArray[] = array("percentage",@$data[$g],"D");
   								break;
   						}
						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "discount".$currArray[$z]["code"]) {
								$rArray[] = array("price".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
   					}
					if ($thisProductID > 0 && $cancelAdd == FALSE) {
						if (makeInteger(array_search($thisProductID,$prodArray)) == 0) {
							$dbA->query("delete from $tableAdvancedPricing where productID = $thisProductID and priceType = 2");
							$prodArray[] = $thisProductID;
						}
						$rArray[] = array("priceType",2,"N");
						$dbA->insertRecord($tableAdvancedPricing,$rArray,0);
						$updates++;
					}
					$row++; 
				} 
				fclose ($fp);
				importComplete("quantity discounts",$updates);
				break;
			case "attributes":
				$row=0;
				$updates=0;
				$thisProductID = 0;
				$prodArray = null;
				$prodArray[] = -10;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					$cancelAdd = FALSE;
					$thisProductID = 0;
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								$thisProductID = makeInteger(@$data[$g]);
								$rArray[] = array("productID",$thisProductID,"N");	
   								break;
   							case "code":
								if ($thisProductID == 0) {
									$rr = $dbA->query("select * from $tableProducts where code=\"".@$data[$g]."\"");
									if ($dbA->count($rr) > 0) {
										$rc = $dbA->fetch($rr);
										$thisProductID = $rc["productID"];
										$rArray[] = array("productID",$thisProductID,"N");	
									}
								}
   								break;
   							case "type":
   								$rArray[] = array("type",@$data[$g],"S");
   								break;
   							case "content":
   								$rArray[] = array("content",@$data[$g],"S");
   								break;
							case "exclude":
								$rArray[] = array("exclude",@$data[$g],"YN");
								break;
   						}
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							switch ($extraFieldsArray[$z]["type"]) {
								case "SELECT":
								case "CHECKBOXES":
								case "RADIOBUTTONS":
									if ($theFields[$g] == $extraFieldsArray[$z]["name"]) {
										$thisFieldValue = $extraFieldsArray[$z]["extraFieldID"];
										if ($thisProductID != 0) {
											if (@$data[$g] == "" || @$data[$g] == "Any") {
												$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],0,"N");
											} else {
												$rr = $dbA->query("select * from $tableExtraFieldsValues where productID=$thisProductID and 	extraFieldID=$thisFieldValue and content=\"".@$data[$g]."\"");
												if ($dbA->count($rr) > 0) {
													$rc = $dbA->fetch($rr);
													$exvalID = $rc["exvalID"];
													$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],$exvalID,"N");
												} else {
													$cancelAdd = TRUE;
												}
											}
										}
									}
									break;
								default:
									break;
							}
						}	
   					}
					if ($thisProductID > 0 && $cancelAdd == FALSE) {
						if (makeInteger(array_search($thisProductID,$prodArray)) == 0) {
							$dbA->query("delete from $tableCombinations where productID = $thisProductID");
							$prodArray[] = $thisProductID;
						}
						$dbA->insertRecord($tableCombinations,$rArray,0);
						$updates++;
					}
					$row++; 
				} 
				fclose ($fp);
				importComplete("attribute combinations",$updates);
				break;
			case "customers":
				$row=0;
				$updates=0;
				$accFields = $dbA->retrieveAllRecordsFromQuery("select * from $tableCustomerFields where type='C' and visible=1 order by position");
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					$countrySet = FALSE;
   					$accountSet = FALSE;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "customerID":
   								if ($whereClause != "") {
   									$whereClause .= " and customerID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "customerID=".makeInteger(@$data[$g]);
   								}
   								break;
   							case "Email":
   								$rArray[] = array("email",@$data[$g],"S");
   								break;
   							case "Password":
   								$rArray[] = array("password",md5(@$data[$g]),"S");
   								break;
   							case "Account Type ID":
   								$rArray[] = array("accTypeID",makeInteger(@$data[$g]),"N");
   								$accountSet = TRUE;
   								break;
   							case "Tax Exempt":
   								$rArray[] = array("taxExempt",@$data[$g],"YN");
   								break;
   						}
   						for ($z = 0; $z < count($accFields); $z++) {
							if ($theFields[$g] == $accFields[$z]["fieldname"] && $accFields[$z]["fieldname"] != "") {
								switch ($accFields[$z]["fieldname"]) {
									case "country":
									$countrySet = TRUE;
										$cResult = $dbA->query("select * from $tableCountries where name='".@$data[$g]."'");
										if ($dbA->count($cResult) > 0) {
											$cRecord = $dbA->fetch($cResult);
											$rArray[] = array("country",$cRecord["countryID"],"N");
										} else {
											$rArray[] = array("country",retrieveOption("defaultCountry"),"N");
										}
										break;
									default:
										$rArray[] = array($accFields[$z]["fieldname"],@$data[$g],"S");
										break;
								}
							}
						}
   					}
   					if (!$countrySet) {
   						$rArray[] = array("country",retrieveOption("defaultCountry"),"N");
   					}
   					if (!$accountSet) {
   						$rArray[] = array("accTypeID",retrieveOption("customerDefaultAccount"),"N");
   					}
   					if (is_array($rArray)) {
   						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
   							if ($whereClause != "") {
   								$dbA->updateRecord($tableCustomers,$whereClause,$rArray,0);
   							} else {
   								$rArray[] = array("date",date("Ymd"),"S");
   								$dbA->insertRecord($tableCustomers,$rArray,0);
   							}
   							$updates++;
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("customers",$updates);
				break;
			case "mailinglist":
				$row=0;
				$updates=0;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "recipientID":
   								if ($whereClause != "") {
   									$whereClause .= " and recipientID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "recipientID=".makeInteger(@$data[$g]);
   								}
   								break;
   							case "emailaddress":
   								$rArray[] = array("emailaddress",@$data[$g],"S");
   								break;
   						}
   					}
   					if (is_array($rArray)) {
   						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
   							if ($whereClause != "") {
   								$dbA->updateRecord($tableNewsletter,$whereClause,$rArray,0);
   							} else {
   								$dbA->insertRecord($tableNewsletter,$rArray,0);
   							}
   							$updates++;
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("mailing list",$updates);
				break;
			case "images":
				$row=0;
				$updates=0;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								if ($whereClause != "") {
   									$whereClause .= " and productID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "productID=".makeInteger(@$data[$g]);
   								}
   								break;
   							case "code":
   								if ($whereClause != "") {
   									$whereClause .= " and code=\"".@$data[$g]."\"";
   								} else {
   									$whereClause = "code=\"".@$data[$g]."\"";
   								}
   								break;
   							case "thumbnail":
   								$rArray[] = array("thumbnail",@$data[$g],"S");	
   								break;
   							case "mainimage":
   								$rArray[] = array("mainimage",@$data[$g],"S");	
   								break;
   						}
   					}
   					if ($whereClause != "" && is_array($rArray)) {
   						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
   							$dbA->updateRecord($tableProducts,$whereClause,$rArray,0);
   							$updates++;
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("images",$updates);
				break;
			case "prices":
				$row=0;
				$updates=0;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								if ($whereClause != "") {
   									$whereClause .= " and productID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "productID=".makeInteger(@$data[$g]);
   								}
   								break;
   							case "code":
   								if ($whereClause != "") {
   									$whereClause .= " and code=\"".@$data[$g]."\"";
   								} else {
   									$whereClause = "code=\"".@$data[$g]."\"";
   								}
   								break;
   						}
   						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "price".$currArray[$z]["code"]) {
								$rArray[] = array("price".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
   					}
   					if ($whereClause != "" && is_array($rArray)) {
   						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
   							$dbA->updateRecord($tableProducts,$whereClause,$rArray,0);
   							$updates++;
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("prices",$updates);
				break;				
			case "stocklevels":
				$row=0;
				$updates=0;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								if ($whereClause != "") {
   									$whereClause .= " and productID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "productID=".makeInteger(@$data[$g]);
   								}
   								break;
   							case "code":
   								if ($whereClause != "") {
   									$whereClause .= " and code=\"".@$data[$g]."\"";
   								} else {
   									$whereClause = "code=\"".@$data[$g]."\"";
   								}
   								break;
   							case "scLevel":
   								$rArray[] = array("scLevel",@$data[$g],"N");	
   								break;
   							case "scWarningLevel":
   								$rArray[] = array("scWarningLevel",@$data[$g],"N");		
   								break;
   							case "scEnabled":
   								$rArray[] = array("scEnabled",@$data[$g],"YN");		
   								break;
   						}
   					}
   					if ($whereClause != "" && is_array($rArray)) {
						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
   							$dbA->updateRecord($tableProducts,$whereClause,$rArray,0);
   							$updates++;
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("stock levels",$updates);
				break;
			case "updateproducts":
				$row=0;
				$updates=0;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					$sectionAdditions = "";
   					$sectionNameAdditions = "";
   					$extraAdditions = "";
					$extraPrices = "";
   					$thisProductID = 0;
   					$pCode = "";
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								if ($whereClause != "") {
   									$whereClause .= " and productID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "productID=".makeInteger(@$data[$g]);
   								}
   								$thisProductID = makeInteger(@$data[$g]);
   								break;
   							case "code":
   								if ($whereClause != "") {
   									$whereClause .= " and code=\"".@$data[$g]."\"";
   								} else {
   									$whereClause = "code=\"".@$data[$g]."\"";
   								}	
   								$pCode = @$data[$g];
   								break;
   							case "name":
   								$rArray[] = array("name",@$data[$g],"S");	
   								break;
   							case "shortdescription":
   								$rArray[] = array("shortdescription",@$data[$g],"S");	
   								break;
   							case "description":
   								$rArray[] = array("description",@$data[$g],"S");	
   								break;
   							case "thumbnail":
   								$rArray[] = array("thumbnail",@$data[$g],"S");	
   								break;
   							case "mainimage":
   								$rArray[] = array("mainimage",@$data[$g],"S");	
   								break;
   							case "visible":
   								$rArray[] = array("visible",@$data[$g],"YN");	
   								break;
   							case "metaDescription":
   								$rArray[] = array("metaDescription",@$data[$g],"S");	
   								break;
   							case "metaKeywords":
   								$rArray[] = array("metaKeywords",@$data[$g],"S");	
   								break;
   							case "keywords":
   								$rArray[] = array("keywords",@$data[$g],"S");	
   								break;
   							case "templateFile":
   								$rArray[] = array("templateFile",@$data[$g],"S");	
   								break;
   							case "newproduct":
   								$rArray[] = array("newproduct",@$data[$g],"YN");	
   								$updatenewproduct = true;
   								if (@$data[$g] == "Y") { $newproduct = true; }
   								break;
   							case "topproduct":
   								$rArray[] = array("topproduct",@$data[$g],"YN");	
   								$updatetopproduct = true;
   								if (@$data[$g] == "Y") { $topproduct = true; }
   								break;
   							case "specialoffer":
   								$rArray[] = array("specialoffer",@$data[$g],"YN");	
   								$updatespecialoffer = true;
   								if (@$data[$g] == "Y") { $specialoffer = true; }
   								break;
   							case "freeShipping":
   								$rArray[] = array("freeShipping",@$data[$g],"YN");	
   								break;
   							case "productType":
   								if (@$data[$g] != "N") {
   									@$data[$g] = "N";
   								}
   								$rArray[] = array("productType",@$data[$g],"S");	
   								break;
   							case "weight":
   								$rArray[] = array("weight",@$data[$g],"D");	
   								break;
   							case "taxrate":
   								if (@$data[$g] < 0 || @$data[$g] > 2) {
   									@$data[$g] = 0;
   								}
   								$rArray[] = array("taxrate",@$data[$g],"N");	
   								break;
   							case "scLevel":
   								$rArray[] = array("scLevel",@$data[$g],"N");	
   								break;
   							case "scWarningLevel":
   								$rArray[] = array("scWarningLevel",@$data[$g],"N");		
   								break;
   							case "scEnabled":
   								$rArray[] = array("scEnabled",@$data[$g],"YN");		
   								break;
   							case "scActionZero":
   								$rArray[] = array("scActionZero",@$data[$g],"N");		
   								break;
   							case "sectionIDs":
   								$sectionAdditions = @$data[$g];
   								break;
   							case "sections":
   								$sectionAdditions = @$data[$g];
   								break;
   							case "sectionNames":
   								$sectionNameAdditions = @$data[$g];
   								break;
   						}
   						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "price".$currArray[$z]["code"]) {
								$rArray[] = array("price".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
						for ($z = 0; $z < count($languages); $z++) {
							if ($theFields[$g] == "name_".$languages[$z]["name"]) {
								$rArray[] = array("name".$languages[$z]["languageID"],@$data[$g],"S");	
							}
							if ($theFields[$g] == "shortdescription_".$languages[$z]["name"]) {
								$rArray[] = array("shortdescription".$languages[$z]["languageID"],@$data[$g],"S");	
							}
							if ($theFields[$g] == "description_".$languages[$z]["name"]) {
								$rArray[] = array("description".$languages[$z]["languageID"],@$data[$g],"S");	
							}
						}
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							if ($theFields[$g] == $extraFieldsArray[$z]["name"] && $extraFieldsArray[$z]["name"] != "") {
								switch ($extraFieldsArray[$z]["type"]) {
									case "SELECT":
									case "CHECKBOXES":
									case "RADIOBUTTONS":
										$thisField = $data[$g];
										if (trim($thisField) != "") {
											$thisBits = explode(",",$thisField);
											for ($ex = 0; $ex < count($thisBits); $ex++) {
												$extraAdditions[] = array("id"=>$extraFieldsArray[$z]["extraFieldID"],"content"=>trim($thisBits[$ex]),"position"=>$ex+1);
											}
										}
										break;
									default:
										$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],@$data[$g],"S");
										break;
								}
							}
						}
						//EXTRA FIELD PRICES
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							for ($zz = 0; $zz < count($currArray); $zz++) {
								if ($theFields[$g] == $extraFieldsArray[$z]["name"]."_price".$currArray[$zz]["code"] && $extraFieldsArray[$z]["name"] != "") {
									switch ($extraFieldsArray[$z]["type"]) {
										case "SELECT":
										case "CHECKBOXES":
										case "RADIOBUTTONS":
											$thisField = $data[$g];
											if (trim($thisField) != "") {
												$thisBits = explode(",",$thisField);
												for ($ex = 0; $ex < count($thisBits); $ex++) {
													$extraPrices[$currArray[$zz]["currencyID"]][] = array("id"=>$extraFieldsArray[$z]["extraFieldID"],"price"=>trim($thisBits[$ex]),"position"=>$ex+1);
												}
											}
											break;
										default:
											$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],@$data[$g],"S");
											break;
									}
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
											break;
										default:
											$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"]."_".$languages[$y]["languageID"],@$data[$g],"S");
											break;
									}
								}
							}
						}	
   					} 		
   					if ($whereClause != "" && (is_array($rArray) || $sectionAdditions != "" || $sectionAdditionsName != "" || is_array($extraAdditions))) {
   						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
   							$dbA->updateRecord($tableProducts,$whereClause,$rArray,0);
     						$updates++;
   							if ($thisProductID == 0) {
   								if ($pCode != "") {
   									$rr = $dbA->query("select productID from $tableProducts where code=\"$pCode\"");
   									if ($dbA->count($rr) > 0) {
   										$pr = $dbA->fetch($rr);
   										$thisProductID = $pr["productID"];
   									}
   								}
   							}
   							if ($thisProductID > 0) {
								if (trim($sectionAdditions) != "") {
									$dbA->query("delete from $tableProductsTree where productID=$thisProductID");
									$sectionIDs = explode(",",$sectionAdditions);
									for ($ex = 0; $ex < count($sectionIDs); $ex++) {
										$rArray = "";
										$rArray[] = array("productID",$thisProductID,"N");
										$rArray[] = array("sectionID",$sectionIDs[$ex],"N");
										$dbA->insertRecord($tableProductsTree,$rArray,0);
									}
								}
								if (trim($sectionNameAdditions) != "") {
									$dbA->query("delete from $tableProductsTree where productID=$thisProductID");
									$sectionIDs = explode(",",$sectionNameAdditions);
									for ($ex = 0; $ex < count($sectionIDs); $ex++) {
										$sr = $dbA->query("select * from $tableSections where title = \"".trim($sectionIDs[$ex])."\"");
										if ($dbA->count($sr) > 0) {
											$src = $dbA->fetch($sr);
	   										$rArray = "";
	   										$rArray[] = array("productID",$thisProductID,"N");
	   										$rArray[] = array("sectionID",$src["sectionID"],"N");
	   										$dbA->insertRecord($tableProductsTree,$rArray,0);
	   									}
									}
								}
								if (is_array($extraAdditions)) {
									$dbA->query("delete from $tableExtraFieldsValues where productID=$thisProductID");
   									for ($ex = 0; $ex < count($extraAdditions); $ex++) {
   										$rArray = "";
   										$rArray[] = array("productID",$thisProductID,"N");
   										$rArray[] = array("extraFieldID",$extraAdditions[$ex]["id"],"N");
   										$rArray[] = array("content",$extraAdditions[$ex]["content"],"S");
   										$rArray[] = array("position",$extraAdditions[$ex]["position"],"N");
   										$dbA->insertRecord($tableExtraFieldsValues,$rArray,0);
										$exvalID = $dbA->lastID();
										$rArray = "";
										//so here, we check the position plus the extrafieldID to find the correct price (if one exists)
										if (is_array($extraPrices)) {
											for ($zz = 0; $zz < count($currArray); $zz++) {
												if (is_array(@$extraPrices[$currArray[$zz]["currencyID"]])) {
													for ($xx = 0; $xx < count($extraPrices[$currArray[$zz]["currencyID"]]); $xx++) {
														if ($extraPrices[$currArray[$zz]["currencyID"]][$xx]["id"] == $extraAdditions[$ex]["id"] && $extraPrices[$currArray[$zz]["currencyID"]][$xx]["position"] == $extraAdditions[$ex]["position"]) {
															if ($extraPrices[$currArray[$zz]["currencyID"]][$xx]["price"] > 0) {
																$rArray[] = array("price".$currArray[$zz]["currencyID"],$extraPrices[$currArray[$zz]["currencyID"]][$xx]["price"],"D");
															}
														}
													}
												}
											}
											if (is_array($rArray)) {
												$rArray[] = array("exvalID",$exvalID,"N");
												$dbA->insertRecord($tableExtraFieldsPrices,$rArray,0);
											}
										}
   									}
   								}
							}
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("products",$updates);
				break;					
			case "products":
				$xInsertMethod = getFORM("xInsertMethod");
				if ($xInsertMethod == "D") {
					$result = $dbA->query("delete from $tableProducts");
					$result = $dbA->query("delete from $tableProductsOptions");
				}
				$row=0;
				$updates=0;
				while ($data = fgetcsv ($fp, 20000, $xDelimiter)) { 
					set_time_limit(30);
  		 			$num = count ($data); 
   					$foundWhere = 0;
   					$whereClause = "";
   					$rArray = null;
   					$updatenewproduct = false;
   					$updatetopproduct = false;
   					$updatespecialoffer = false;
   					$newproduct = false;
   					$topproduct = false;
   					$specialoffer = false;
   					$thisProductID = 0;
   					$extraAdditions = "";
					$extraPrices = "";
   					$sectionAdditions = "";
   					$sectionNameAdditions = "";
   					for ($g = 0; $g < count($theFields); $g++) {
   						switch ($theFields[$g]) {
   							case "SKIP_FIELD":
   								break;
   							case "productID":
   								if ($whereClause != "") {
   									$whereClause .= " and productID=".makeInteger(@$data[$g]);
   								} else {
   									$whereClause = "productID=".makeInteger(@$data[$g]);
   								}
   								if ($xInsertMethod == "R") {
   									$thisProductID = makeInteger(@$data[$g]);
   								}
   								break;
   							case "code":
   								if ($whereClause != "") {
   									$whereClause .= " and code=\"".@$data[$g]."\"";
   								} else {
   									$whereClause = "code=\"".@$data[$g]."\"";
   								}
   								$rArray[] = array("code",@$data[$g],"S");	
   								break;
   							case "name":
   								$rArray[] = array("name",@$data[$g],"S");	
   								break;
   							case "shortdescription":
   								$rArray[] = array("shortdescription",@$data[$g],"S");	
   								break;
   							case "description":
   								$rArray[] = array("description",@$data[$g],"S");	
   								break;
   							case "thumbnail":
   								$rArray[] = array("thumbnail",@$data[$g],"S");	
   								break;
   							case "mainimage":
   								$rArray[] = array("mainimage",@$data[$g],"S");	
   								break;
   							case "visible":
   								$rArray[] = array("visible",@$data[$g],"YN");	
   								break;
   							case "metaDescription":
   								$rArray[] = array("metaDescription",@$data[$g],"S");	
   								break;
   							case "metaKeywords":
   								$rArray[] = array("metaKeywords",@$data[$g],"S");	
   								break;
   							case "keywords":
   								$rArray[] = array("keywords",@$data[$g],"S");	
   								break;
   							case "templateFile":
   								$rArray[] = array("templateFile",@$data[$g],"S");	
   								break;
   							case "newproduct":
   								$rArray[] = array("newproduct",@$data[$g],"YN");	
   								$updatenewproduct = true;
   								if (@$data[$g] == "Y") { $newproduct = true; }
   								break;
   							case "topproduct":
   								$rArray[] = array("topproduct",@$data[$g],"YN");	
   								$updatetopproduct = true;
   								if (@$data[$g] == "Y") { $topproduct = true; }
   								break;
   							case "specialoffer":
   								$rArray[] = array("specialoffer",@$data[$g],"YN");	
   								$updatespecialoffer = true;
   								if (@$data[$g] == "Y") { $specialoffer = true; }
   								break;
   							case "freeShipping":
   								$rArray[] = array("freeShipping",@$data[$g],"YN");	
   								break;
   							case "productType":
   								if (@$data[$g] != "N") {
   									@$data[$g] = "N";
   								}
   								$rArray[] = array("productType",@$data[$g],"S");	
   								break;
   							case "weight":
   								$rArray[] = array("weight",@$data[$g],"D");	
   								break;
   							case "taxrate":
   								if (@$data[$g] < 0 || @$data[$g] > 2) {
   									@$data[$g] = 0;
   								}
   								$rArray[] = array("taxrate",@$data[$g],"N");	
   								break;
   							case "scLevel":
   								$rArray[] = array("scLevel",@$data[$g],"N");	
   								break;
   							case "scWarningLevel":
   								$rArray[] = array("scWarningLevel",@$data[$g],"N");		
   								break;
   							case "scEnabled":
   								$rArray[] = array("scEnabled",@$data[$g],"YN");		
   								break;
   							case "scActionZero":
   								$rArray[] = array("scActionZero",@$data[$g],"N");		
   								break;
   							case "sectionIDs":
   								$sectionAdditions = @$data[$g];
   								break;
   							case "sections":
   								$sectionAdditions = @$data[$g];
   								break;
   							case "sectionNames":
   								$sectionNameAdditions = @$data[$g];
   								break;
   						}
   						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "price".$currArray[$z]["code"]) {
								$rArray[] = array("price".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "normalPrice".$currArray[$z]["code"]) {
								$rArray[] = array("rrp".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
						for ($z = 0; $z < count($currArray); $z++) {
							if ($theFields[$g] == "oneOffPrice".$currArray[$z]["code"]) {
								$rArray[] = array("ooPrice".$currArray[$z]["currencyID"],@$data[$g],"D");	
							}
						}
						for ($z = 0; $z < count($languages); $z++) {
							if ($theFields[$g] == "name_".$languages[$z]["name"]) {
								$rArray[] = array("name".$languages[$z]["languageID"],@$data[$g],"S");	
							}
							if ($theFields[$g] == "shortdescription_".$languages[$z]["name"]) {
								$rArray[] = array("shortdescription".$languages[$z]["languageID"],@$data[$g],"S");	
							}
							if ($theFields[$g] == "description_".$languages[$z]["name"]) {
								$rArray[] = array("description".$languages[$z]["languageID"],@$data[$g],"S");	
							}
						}
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							if ($theFields[$g] == $extraFieldsArray[$z]["name"] && $extraFieldsArray[$z]["name"] != "") {
								switch ($extraFieldsArray[$z]["type"]) {
									case "SELECT":
									case "CHECKBOXES":
									case "RADIOBUTTONS":
										$thisField = $data[$g];
										if (trim($thisField) != "") {
											$thisBits = explode(",",$thisField);
											for ($ex = 0; $ex < count($thisBits); $ex++) {
												$extraAdditions[] = array("id"=>$extraFieldsArray[$z]["extraFieldID"],"content"=>trim($thisBits[$ex]),"position"=>$ex+1);
											}
										}
										break;
									default:
										$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],@$data[$g],"S");
										break;
								}
							}
						}
						//EXTRA FIELD PRICES
						for ($z = 0; $z < count($extraFieldsArray); $z++) {
							for ($zz = 0; $zz < count($currArray); $zz++) {
								if ($theFields[$g] == $extraFieldsArray[$z]["name"]."_price".$currArray[$zz]["code"] && $extraFieldsArray[$z]["name"] != "") {
									switch ($extraFieldsArray[$z]["type"]) {
										case "SELECT":
										case "CHECKBOXES":
										case "RADIOBUTTONS":
											$thisField = $data[$g];
											if (trim($thisField) != "") {
												$thisBits = explode(",",$thisField);
												for ($ex = 0; $ex < count($thisBits); $ex++) {
													$extraPrices[$currArray[$zz]["currencyID"]][] = array("id"=>$extraFieldsArray[$z]["extraFieldID"],"price"=>trim($thisBits[$ex]),"position"=>$ex+1);
												}
											}
											break;
										default:
											$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"],@$data[$g],"S");
											break;
									}
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
											break;
										default:
											$rArray[] = array("extrafield".$extraFieldsArray[$z]["extraFieldID"]."_".$languages[$y]["languageID"],@$data[$g],"S");
											break;
									}
								}
							}
						}	
   					}
   					if ($whereClause != "" && is_array($rArray)) {
						if (($row==0 && $xFirstRowHeadings != "Y") || $row > 0) {
							if ($xInsertMethod == "I" || $xInsertMethod == "D") {
   								$dbA->insertRecord($tableProducts,$rArray,0);
   								$thisProductID = $dbA->lastID();
   								if (is_array($extraAdditions)) {
   									for ($ex = 0; $ex < count($extraAdditions); $ex++) {
   										$rArray = "";
   										$rArray[] = array("productID",$thisProductID,"N");
   										$rArray[] = array("extraFieldID",$extraAdditions[$ex]["id"],"N");
   										$rArray[] = array("content",$extraAdditions[$ex]["content"],"S");
   										$rArray[] = array("position",$extraAdditions[$ex]["position"],"N");
   										$dbA->insertRecord($tableExtraFieldsValues,$rArray,0);
										$exvalID = $dbA->lastID();
										$rArray = "";
										//so here, we check the position plus the extrafieldID to find the correct price (if one exists)
										if (is_array($extraPrices)) {
											for ($zz = 0; $zz < count($currArray); $zz++) {
												if (is_array(@$extraPrices[$currArray[$zz]["currencyID"]])) {
													for ($xx = 0; $xx < count($extraPrices[$currArray[$zz]["currencyID"]]); $xx++) {
														if ($extraPrices[$currArray[$zz]["currencyID"]][$xx]["id"] == $extraAdditions[$ex]["id"] && $extraPrices[$currArray[$zz]["currencyID"]][$xx]["position"] == $extraAdditions[$ex]["position"]) {
															if ($extraPrices[$currArray[$zz]["currencyID"]][$xx]["price"] > 0) {
																$rArray[] = array("price".$currArray[$zz]["currencyID"],$extraPrices[$currArray[$zz]["currencyID"]][$xx]["price"],"D");
															}
														}
													}
												}
											}
											if (is_array($rArray)) {
												$rArray[] = array("exvalID",$exvalID,"N");
												$dbA->insertRecord($tableExtraFieldsPrices,$rArray,0);
											}
										}
   									}
   								}
   								if (trim($sectionAdditions) != "") {
   									$sectionIDs = explode(",",$sectionAdditions);
   									for ($ex = 0; $ex < count($sectionIDs); $ex++) {
   										$rArray = "";
   										$rArray[] = array("productID",$thisProductID,"N");
   										$rArray[] = array("sectionID",$sectionIDs[$ex],"N");
   										$dbA->insertRecord($tableProductsTree,$rArray,0);
   									}
   								}
   								if (trim($sectionNameAdditions) != "") {
   									$sectionIDs = explode(",",$sectionNameAdditions);
   									for ($ex = 0; $ex < count($sectionIDs); $ex++) {
   										$sr = $dbA->query("select * from $tableSections where title = \"".trim($sectionIDs[$ex])."\"");
   										if ($dbA->count($sr) > 0) {
   											$src = $dbA->fetch($sr);
	   										$rArray = "";
	   										$rArray[] = array("productID",$thisProductID,"N");
	   										$rArray[] = array("sectionID",$src["sectionID"],"N");
	   										$dbA->insertRecord($tableProductsTree,$rArray,0);
	   									}
   									}
   								}
   							}
   							if ($xInsertMethod == "R") {
   								$dbA->replaceRecord($tableProducts,$whereClause,$rArray,0);
   							}
   							$updates++;
   						}
   					}
   					if ($thisProductID > 0) {
   						if ($updatenewproduct == true) {
   							if ($newproduct == true) {
   								$dbA->query("insert into $tableProductsOptions (productID,type,position) VALUES($thisProductID,'N',9999)");
   							} else {
   								$dbA->query("delete form $tableProductsOptions where productID=$thisProductID and type='N'");
   							}
   						}
   						if ($updatetopproduct == true) {
   							if ($topproduct == true) {
   								$dbA->query("insert into $tableProductsOptions (productID,type,position) VALUES($thisProductID,'T',9999)");
   							} else {
   								$dbA->query("delete form $tableProductsOptions where productID=$thisProductID and type='T'");
   							}
   						}
   						if ($updatespecialoffer == true) {
   							if ($specialoffer == true) {
   								$dbA->query("insert into $tableProductsOptions (productID,type,position) VALUES($thisProductID,'S',9999)");
   							} else {
   								$dbA->query("delete form $tableProductsOptions where productID=$thisProductID and type='S'");
   							}
   						}
   					}
   					$row++; 
				} 
				fclose ($fp);
				importComplete("products",$updates);
				break;								
		}
		$dbA->close();
		exit;
	}
	
	function importComplete($xT,$records) {
		global $dbA,$xType;
		$dbA->close();
		createProcessMessage("Import Finished!",
		"Your import has finished!",
		"Your data import of $xT has completed.<BR>Total Records: $records",
		"&lt; Back",
		"self.location.href='import.php?xType=$xType&".userSessionGET()."';");		
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
			echo $headingOutput."\r\n";
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