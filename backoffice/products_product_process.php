<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	@ignore_user_abort(true);
	dbConnect($dbA);

	$xSectionID = getFORM("xSectionID");
	$linkBackLink = "";
	if ($xSectionID != "") {
		//link back can be to the section
		$linkBackLink = "sections_structure.php?xSectionID=$xSectionID&";
		$linkBackButton = "PARENT SECTION";
	} else {
		$linkBackLink = "sections_structure.php?xSectionID=1&";
	}
	if (getFORM("xReturn") != "") {
		$linkBackLink = urldecode(getFORM("xReturn"));
	}
		
	$recordType = "Product";
	$tableName = $tableProducts;
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	$flagArray = $dbA->retrieveAllRecords($tableProductsFlags,"flagID");

	$xAction=getFORM("xAction");
	$myMode = "update";
	if ($xAction == "insert") {
		$myMode = "insert";
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		if (!$dbA->isUnique($tableName,"productID",0,"code",getFORM("xCode")) && getFORM("xCode") != "") {
			setupProcessMessage($recordType,getFORM("xCode"),"error_duplicate_update","BACK","");					
		}
		$rArray[] = array("code",getFORM("xCode"),"S");			
		$rArray[] = array("name",getFORM("xName"),"S");
		$rArray[] = array("shortdescription",getFORM("xShortDescription"),"S");	
		$rArray[] = array("description",getFORM("xDescription"),"S");	
		$rArray[] = array("keywords",getFORM("xKeywords"),"S");	
		$rArray[] = array("metaDescription",getFORM("xMetaDescription"),"S");	
		$rArray[] = array("metaKeywords",getFORM("xMetaKeywords"),"S");	
		$rArray[] = array("visible",getFORM("xIsVisible"),"YN");	
		$rArray[] = array("templateFile",getFORM("xTemplateFile"),"S");	
		$rArray[] = array("scEnabled",getFORM("xScEnabled"),"YN");	
		$rArray[] = array("scLevel",getFORM("xScLevel"),"N");	
		$rArray[] = array("scWarningLevel",getFORM("xScWarningLevel"),"N");	
		$rArray[] = array("scActionZero",getFORM("xScActionZero"),"N");
		$rArray[] = array("categories",getFORM("xCategories"),"N");
		$rArray[] = array("weight",getFORM("xWeight"),"D");
		$rArray[] = array("minQty",getFORM("xMinQty"),"N");
		$rArray[] = array("maxQty",getFORM("xMaxQty"),"N");
		$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");	
		$rArray[] = array("taxrate",getFORM("xTaxRate"),"N");
		$rArray[] = array("freeShipping",getFORM("xFreeShipping"),"YN");	
		$rArray[] = array("ignoreDiscounts",getFORM("xIgnoreDiscounts"),"YN");
		$rArray[] = array("specialoffer",getFORM("xSpecialoffer"),"YN");	
		$rArray[] = array("allowDirect",getFORM("xAllowDirect"),"YN");
		$rArray[] = array("topproduct",getFORM("xTopProduct"),"YN");
		$rArray[] = array("newproduct",getFORM("xNewProduct"),"YN");
		$rArray[] = array("isDigital",getFORM("xIsDigital"),"YN");
		$rArray[] = array("digitalReg",getFORM("xDigitalReg"),"N");
		$rArray[] = array("combineQty",getFORM("xCombineQty"),"YN");
		$rArray[] = array("groupedProduct",getFORM("xGroupedProduct"),"YN");
		$rArray[] = array("supplierID",getFORM("xSupplierID"),"N");
		$rArray[] = array("suppliercode",getFORM("xSuppliercode"),"S");
		
		for ($f = 0; $f < count($languages); $f++) {
			$thisLanguage = $languages[$f]["languageID"];
			if ($thisLanguage != 1) {
				$rArray[] = array("name".$thisLanguage,getFORM("xName".$thisLanguage),"S");			
				$rArray[] = array("shortdescription".$thisLanguage,getFORM("xShortDescription".$thisLanguage),"S");	
				$rArray[] = array("description".$thisLanguage,getFORM("xDescription".$thisLanguage),"S");	
				$rArray[] = array("metaDescription".$thisLanguage,getFORM("xMetaDescription".$thisLanguage),"S");	
				$rArray[] = array("metaKeywords".$thisLanguage,getFORM("xMetaKeywords".$thisLanguage),"S");	
			}
		}
		
		if (is_array($flagArray)) {
			for ($f = 0; $f < count($flagArray); $f++) {
				$rArray[] = array("flag".$flagArray[$f]["flagID"],getFORM("xFlag".$flagArray[$f]["flagID"]),"YN");
			}
		}
					
		addImageUpdate("xThumbnail","thumbnail","products/thumbnails/",$rArray);			
		addImageUpdate("xImage","mainimage","products/normal/",$rArray);
		addDigitalUpdate("xDigitalFile","digitalFile",$rArray);
		
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"extraFieldID");
		$exFieldCurrent = "";
		if (is_array($extraFieldsArray)) {
			for ($f = 0; $f < count($extraFieldsArray); $f++) {
				switch ($extraFieldsArray[$f]["type"]) {
					case "USERINPUT":
						$rArray[] = array("extrafield".$extraFieldsArray[$f]["extraFieldID"],getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]),"N");	
						break;
					case "SELECT":
					case "CHECKBOXES":
					case "RADIOBUTTONS":
						break;			
					case "IMAGE":
						addImageUpdate("xExtra".$extraFieldsArray[$f]["extraFieldID"],"extrafield".$extraFieldsArray[$f]["extraFieldID"],"products/extras/",$rArray);	
						break;
					default:
						$rArray[] = array("extrafield".$extraFieldsArray[$f]["extraFieldID"],getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]),"S");
						for ($g = 0; $g < count($languages); $g++) {
							$thisLanguage = $languages[$g]["languageID"];
							if ($thisLanguage != 1) {
								$rArray[] = array("extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$thisLanguage,getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]."_".$thisLanguage),"S");
							}
						}
						
				}
			}
		}		
		for ($f = 0; $f < count($currArray); $f++) {
			if ($currArray[$f]["useexchangerate"] != "Y") {
				$rArray[] = array("price".$currArray[$f]["currencyID"],getFORM("xPrice".$currArray[$f]["currencyID"]),"D");
				$rArray[] = array("rrp".$currArray[$f]["currencyID"],getFORM("xRRP".$currArray[$f]["currencyID"]),"D");
				$rArray[] = array("ooPrice".$currArray[$f]["currencyID"],getFORM("xOOPrice".$currArray[$f]["currencyID"]),"D");
			}
		}
		$dbA->insertRecord($tableName,$rArray,0);
		$xProductID = $dbA->lastID();
		if (is_array($extraFieldsArray)) {
			for ($f = 0; $f < count($extraFieldsArray); $f++) {
				switch ($extraFieldsArray[$f]["type"]) {
					case "SELECT":
					case "CHECKBOXES":
					case "RADIOBUTTONS":
						updateExtraField($extraFieldsArray[$f]["extraFieldID"],getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]."Send"),getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]."Delete"));
						break;
				}
			}
		}
		
		$sectionsList = split(";",getFORM("xSectionsSend"));
		for ($f = 0; $f < count($sectionsList); $f++) {
			$dbA->query("insert into $tableProductsTree (productID,sectionID,position) VALUES($xProductID,$sectionsList[$f],9999)");
		}
		$associatedList = split(";",getFORM("xAssociatedSend"));
		for ($f = 0; $f < count($associatedList)-1; $f++) {
			$thisAssoc = explode(":",$associatedList[$f]);
			$thisID = $thisAssoc[0];
			$thisBI = $thisAssoc[1];
			$dbA->query("replace into $tableAssociated (productID,assocID,position) VALUES($xProductID,$thisID,$f)");
			if ($thisBI == 1) {
				$dbA->query("replace into $tableAssociated (assocID,productID,position) VALUES($xProductID,$thisID,9999)");
			}
		}	
		$groupList = split(";",getFORM("xGroupSend"));
		for ($f = 0; $f < count($groupList)-1; $f++) {
			$thisGroup = explode(":",$groupList[$f]);
			$thisID = $thisGroup[0];
			$thisQty = $thisGroup[1];
			$dbA->query("replace into $tableProductsGrouped (productID,groupedID,qty,position) VALUES($xProductID,$thisID,$thisQty,$f)");
		}		

			updateQtyDisc();
			updatePriceComb();
			updateOneOff();
			updateAttComb();
		
		$xNewProduct = getFORM("xNewProduct");
		if ($xNewProduct == "Y") {
			$dbA->query("insert into $tableProductsOptions (productID,type) VALUES($xProductID,'N')");
		}
		$xTopProduct = getFORM("xTopProduct");
		if ($xTopProduct == "Y") {
			$dbA->query("insert into $tableProductsOptions (productID,type) VALUES($xProductID,'T')");
		}
		$xSpecialOffer = getFORM("xSpecialoffer");
		if ($xSpecialOffer == "Y") {
			$dbA->query("insert into $tableProductsOptions (productID,type) VALUES($xProductID,'S')");
		}
		$updateText = "";
		if (getFORM("xCode") != "") {
			$updateText = getFORM("xCode");
		} else {
			$updateText = getFORM("xName");
		}
		userLogActionAdd($recordType,$updateText);	

		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "delete") {
		$xProductID = getFORM("xProductID");
		if (!$dbA->doesIDExist($tableName,"productID",$xProductID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"productID",$xProductID);
			$dbA->deleteRecord($tableProductsTree,"productID",$xProductID);
			$dbA->deleteRecord($tableExtraFieldsValues,"productID",$xProductID);
			if ($uRecord["code"] != "") {
				$updateText = $uRecord["code"];
			} else {
				$updateText = $uRecord["name"];
			}			
			userLogActionDelete($recordType,$updateText);
			doRedirect($linkBackLink."&".userSessionGET());
		}	
	}
	if ($xAction == "update") {
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$xProductID = getFORM("xProductID");
		if (!$dbA->doesIDExist($tableName,"productID",$xProductID,$uRecord)) {
			$updateText = "";
			if (getFORM("xCode") != "") {
				$udpateText = getFORM("xCode");
			} else {
				$updateText = getFORM("xName");
			}
			setupProcessMessage($recordType,$updateText,"error_existance","BACK","");	
		} else {
			if (getFORM("xCode") != "" && getFORM("xOrigCode") != getFORM("xCode")) {
				if (!$dbA->isUnique($tableName,"productID",$xProductID,"code",getFORM("xCode"))) {
					setupProcessMessage($recordType,getFORM("xCode"),"error_duplicate_update","BACK","");					
				}
			}
			$rArray[] = array("code",getFORM("xCode"),"S");			
			$rArray[] = array("name",getFORM("xName"),"S");
			$rArray[] = array("shortdescription",getFORM("xShortDescription"),"S");	
			$rArray[] = array("description",getFORM("xDescription"),"S");
			$rArray[] = array("keywords",getFORM("xKeywords"),"S");		
			$rArray[] = array("metaDescription",getFORM("xMetaDescription"),"S");	
			$rArray[] = array("metaKeywords",getFORM("xMetaKeywords"),"S");	
			$rArray[] = array("visible",getFORM("xIsVisible"),"YN");	
			$rArray[] = array("templateFile",getFORM("xTemplateFile"),"S");	
			$rArray[] = array("scEnabled",getFORM("xScEnabled"),"YN");	
			$rArray[] = array("scLevel",getFORM("xScLevel"),"N");	
			$rArray[] = array("scWarningLevel",getFORM("xScWarningLevel"),"N");	
			$rArray[] = array("scActionZero",getFORM("xScActionZero"),"N");
			$rArray[] = array("categories",getFORM("xCategories"),"N");
			$rArray[] = array("weight",getFORM("xWeight"),"D");
			$rArray[] = array("minQty",getFORM("xMinQty"),"N");
			$rArray[] = array("maxQty",getFORM("xMaxQty"),"N");
			$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");
			$rArray[] = array("taxrate",getFORM("xTaxRate"),"N");
			$rArray[] = array("freeShipping",getFORM("xFreeShipping"),"YN");
			$rArray[] = array("ignoreDiscounts",getFORM("xIgnoreDiscounts"),"YN");
			$rArray[] = array("specialoffer",getFORM("xSpecialoffer"),"YN");
			$rArray[] = array("allowDirect",getFORM("xAllowDirect"),"YN");	
			$rArray[] = array("topproduct",getFORM("xTopProduct"),"YN");
			$rArray[] = array("newproduct",getFORM("xNewProduct"),"YN");	
			$rArray[] = array("isDigital",getFORM("xIsDigital"),"YN");	
			$rArray[] = array("digitalReg",getFORM("xDigitalReg"),"N");
			$rArray[] = array("combineQty",getFORM("xCombineQty"),"YN");
			$rArray[] = array("groupedProduct",getFORM("xGroupedProduct"),"YN");
			$rArray[] = array("supplierID",getFORM("xSupplierID"),"N");
			$rArray[] = array("suppliercode",getFORM("xSuppliercode"),"S");
			
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("name".$thisLanguage,getFORM("xName".$thisLanguage),"S");			
					$rArray[] = array("shortdescription".$thisLanguage,getFORM("xShortDescription".$thisLanguage),"S");	
					$rArray[] = array("description".$thisLanguage,getFORM("xDescription".$thisLanguage),"S");	
					$rArray[] = array("metaDescription".$thisLanguage,getFORM("xMetaDescription".$thisLanguage),"S");	
					$rArray[] = array("metaKeywords".$thisLanguage,getFORM("xMetaKeywords".$thisLanguage),"S");	
				}
			}		
			
			if (is_array($flagArray)) {
				for ($f = 0; $f < count($flagArray); $f++) {
					$rArray[] = array("flag".$flagArray[$f]["flagID"],getFORM("xFlag".$flagArray[$f]["flagID"]),"YN");
				}
			}
		
			
			$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"extraFieldID");
			$exFieldCurrent = "";
			if (is_array($extraFieldsArray)) {
				for ($f = 0; $f < count($extraFieldsArray); $f++) {
					switch ($extraFieldsArray[$f]["type"]) {
						case "USERINPUT":
							$rArray[] = array("extrafield".$extraFieldsArray[$f]["extraFieldID"],getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]),"N");	
							break;
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
							updateExtraField($extraFieldsArray[$f]["extraFieldID"],getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]."Send"),getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]."Delete"));
							break;
						case "IMAGE":
							addImageUpdate("xExtra".$extraFieldsArray[$f]["extraFieldID"],"extrafield".$extraFieldsArray[$f]["extraFieldID"],"products/extras/",$rArray);	
							break;
						default:
							$rArray[] = array("extrafield".$extraFieldsArray[$f]["extraFieldID"],getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]),"S");
							for ($g = 0; $g < count($languages); $g++) {
								$thisLanguage = $languages[$g]["languageID"];
								if ($thisLanguage != 1) {
									$rArray[] = array("extrafield".$extraFieldsArray[$f]["extraFieldID"]."_".$thisLanguage,getFORM("xExtra".$extraFieldsArray[$f]["extraFieldID"]."_".$thisLanguage),"S");
								}
							}
					}
				}
			}
			for ($f = 0; $f < count($currArray); $f++) {
				if ($currArray[$f]["useexchangerate"] != "Y") {
					$rArray[] = array("price".$currArray[$f]["currencyID"],getFORM("xPrice".$currArray[$f]["currencyID"]),"D");
					$rArray[] = array("rrp".$currArray[$f]["currencyID"],getFORM("xRRP".$currArray[$f]["currencyID"]),"D");
					$rArray[] = array("ooPrice".$currArray[$f]["currencyID"],getFORM("xOOPrice".$currArray[$f]["currencyID"]),"D");
				}
			}

			$sectionsList = split(";",getFORM("xSectionsDelete"));
			for ($f = 0; $f < count($sectionsList); $f++) {
				$dbA->query("delete from $tableProductsTree where productID=$xProductID and sectionID=$sectionsList[$f]");
			}			
			$sectionsList = split(";",getFORM("xSectionsSend"));
			for ($f = 0; $f < count($sectionsList); $f++) {
				$dbA->query("insert into $tableProductsTree (productID,sectionID,position) VALUES($xProductID,$sectionsList[$f],9999)");
			}	
			
			
			$associatedList = split(";",getFORM("xAssociatedDelete"));
			for ($f = 0; $f < count($associatedList)-1; $f++) {
				$assoc = split(":",$associatedList[$f]);
				$dbA->query("delete from $tableAssociated where productID=$xProductID and assocID=$assoc[0]");
			}			
			$associatedList = split(";",getFORM("xAssociatedSend"));
			for ($f = 0; $f < count($associatedList)-1; $f++) {
				$thisAssoc = explode(":",$associatedList[$f]);
				$thisID = $thisAssoc[0];
				$thisBI = $thisAssoc[1];
				$dbA->query("replace into $tableAssociated (productID,assocID,position) VALUES($xProductID,$thisID,$f)");
				if ($thisBI == 1) {
					$dbA->query("replace into $tableAssociated (assocID,productID,position) VALUES($xProductID,$thisID,9999)");
				}
			}	
			$groupList = split(";",getFORM("xGroupDelete"));
			for ($f = 0; $f < count($groupList)-1; $f++) {
				$group = split(":",$groupList[$f]);
				$dbA->query("delete from $tableProductsGrouped where productID=$xProductID and groupedID=$group[0]");
			}				
			$groupList = split(";",getFORM("xGroupSend"));
			for ($f = 0; $f < count($groupList)-1; $f++) {
				$thisGroup = explode(":",$groupList[$f]);
				$thisID = $thisGroup[0];
				$thisQty = $thisGroup[1];
				$dbA->query("replace into $tableProductsGrouped (productID,groupedID,qty,position) VALUES($xProductID,$thisID,$thisQty,$f)");
			}
				

			updateQtyDisc();
			updatePriceComb();
			updateOneOff();
			updateAttComb();
			
			$xNewProduct = getFORM("xNewProduct");
			if ($xNewProduct == "Y") {
				$dbA->query("insert into $tableProductsOptions (productID,type) VALUES($xProductID,'N')");
			} else {
				$dbA->query("delete from $tableProductsOptions where productID=$xProductID and type='N'");
			}	
			$xTopProduct = getFORM("xTopProduct");
			if ($xTopProduct == "Y") {
				$dbA->query("insert into $tableProductsOptions (productID,type) VALUES($xProductID,'T')");
			} else {
				$dbA->query("delete from $tableProductsOptions where productID=$xProductID and type='T'");
			}	
			$xSpecialOffer = getFORM("xSpecialoffer");
			if ($xSpecialOffer == "Y") {
				$dbA->query("insert into $tableProductsOptions (productID,type) VALUES($xProductID,'S')");
			} else {
				$dbA->query("delete from $tableProductsOptions where productID=$xProductID and type='S'");
			}					
			
			addImageUpdate("xThumbnail","thumbnail","products/thumbnails/",$rArray);
			addImageUpdate("xImage","mainimage","products/normal/",$rArray);
			addDigitalUpdate("xDigitalFile","digitalFile",$rArray);
			$dbA->updateRecord($tableName,"productID=$xProductID",$rArray,0);

			if (getFORM("xCode") != "") {
				$updateText = getFORM("xCode");
			} else {
				$updateText = getFORM("xName");
			}
			userLogActionUpdate($recordType,$updateText);
			doRedirect($linkBackLink."&".userSessionGET());
		}
	}
	if ($xAction == "reorder") {
		$xExtra = getFORM("xExtra");
		if ($xExtra == "products") {
			$xNewOrder = getFORM("xNewOrder");
			$newOrderBits = split(";",$xNewOrder);
			for ($f = 0; $f < count($newOrderBits)-1; $f++) {
				$g = $f+1;
				$dbA->query("update $tableProductsTree set position=$g where sectionID=$xSectionID and productID=$newOrderBits[$f]");
			}
			userLogAction("Sorted",$recordType,getFORM("xTitle"));
			doRedirect($linkBackLink."&".userSessionGET());
		}
		if ($xExtra == "newproducts") {
			$xNewOrder = getFORM("xNewOrder");
			$newOrderBits = split(";",$xNewOrder);
			for ($f = 0; $f < count($newOrderBits)-1; $f++) {
				$g = $f+1;
				$dbA->query("update $tableProductsOptions set position=$g where type='N' and productID=$newOrderBits[$f]");
			}
			userLogAction("Sorted","Products","New Products");
			doRedirect("sections_structure.php?".userSessionGET());
		}
		if ($xExtra == "topproducts") {
			$xNewOrder = getFORM("xNewOrder");
			$newOrderBits = split(";",$xNewOrder);
			for ($f = 0; $f < count($newOrderBits)-1; $f++) {
				$g = $f+1;
				$dbA->query("update $tableProductsOptions set position=$g where type='T' and productID=$newOrderBits[$f]");
			}
			userLogAction("Sorted","Products","Top Products");
			doRedirect("sections_structure.php?".userSessionGET());
		}
		if ($xExtra == "specialoffers") {
			$xNewOrder = getFORM("xNewOrder");
			$newOrderBits = split(";",$xNewOrder);
			for ($f = 0; $f < count($newOrderBits)-1; $f++) {
				$g = $f+1;
				$dbA->query("update $tableProductsOptions set position=$g where type='S' and productID=$newOrderBits[$f]");
			}
			userLogAction("Sorted","Products","Special Offers");
			doRedirect("sections_structure.php?".userSessionGET());
		}
	}		
	if ($xAction == "structure") {
		$xProductID = getFORM("xProductID");
		$xSectionID = getFORM("xSectionID");
		$dbA->query("insert into $tableProductsTree (sectionID,productID,position) VALUES($xSectionID,$xProductID,9999)");
		$linkBackLink = "sections_structure.php?xSectionID=$xSectionID&";
		$linkBackButton = "PARENT SECTION";
		userLogAction("Added to Section",$recordType,getFORM("xTitle"));
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "multistructure") {
		$xProductList = getFORM("xProductList");
		$xSectionID = getFORM("xSectionID");
		$xSplit = split(";",$xProductList);
		for ($f=0;$f < count($xSplit); $f++) {
			if ($xSplit[$f] != "") {
				$dbA->query("insert into $tableProductsTree (sectionID,productID,position) VALUES($xSectionID,".$xSplit[$f].",9999)");
			}
		}
		$linkBackLink = "sections_structure.php?xSectionID=$xSectionID&";
		$linkBackButton = "PARENT SECTION";
		userLogAction("Added to Section",$recordType,getFORM("xTitle"));
		doRedirect($linkBackLink."&".userSessionGET());
	}
	if ($xAction == "remove") {
		$xProductID = getFORM("xProductID");
		$dbA->query("delete from $tableProductsTree where productID=$xProductID and sectionID=$xSectionID");
		userLogAction("Delete from Section",$recordType,$xProductID);
		doRedirect($linkBackLink."&".userSessionGET());
	}
	
	function updateExtraField($extraFieldID,$extraFieldSend,$extraFieldDelete) {
		global $xProductID,$tableExtraFieldsValues,$tableExtraFieldsPrices,$dbA,$currArray,$exFieldCurrent,$myMode,$languages;
		$extraBits = explode("|",$extraFieldDelete);
		for ($f = 0; $f < count($extraBits); $f++) {
			if ($extraBits[$f] != "") {
				$thisExtra = explodeQueryString($extraBits[$f]);
				if ($thisExtra["exvalID"] != 0) {
					$dbA->query("delete from $tableExtraFieldsValues where exvalID=".$thisExtra["exvalID"]);
				}
			}
		}
		$extraBits = explode("|",$extraFieldSend);
		for ($f = 0; $f < count($extraBits); $f++) {
			if ($extraBits[$f] != "") {
				$thisExtra = explodeQueryString($extraBits[$f]);
				if ($myMode == "insert") {
					$thisExtra["exvalID"] = 0;
				}
				$zArray = null;
				$zArray[] = array("productID",$xProductID,"N");
				$zArray[] = array("extraFieldID",$extraFieldID,"N");
				$zArray[] = array("content",$thisExtra["content"],"S");
				for ($g = 0; $g < count($languages); $g++) {
					$thisLanguage = $languages[$g]["languageID"];
					if ($thisLanguage != 1) {
						$zArray[] = array("content".$thisLanguage,$thisExtra["content".$thisLanguage],"S");			
					}
				}
				$zArray[] = array("visible",$thisExtra["visible"],"S");
				$zArray[] = array("accTypeID",$thisExtra["accTypeID"],"N");
				$zArray[] = array("position",$f,"N");
				if ($thisExtra["exvalID"] == 0) {
					$dbA->insertRecord($tableExtraFieldsValues,$zArray,0);
					$exvalID = $dbA->lastID();
					$thisExtra["exvalID"] = $exvalID;
				} else {
					$dbA->updateRecord($tableExtraFieldsValues,"exvalID=".$thisExtra["exvalID"],$zArray,0);
					$exvalID = $thisExtra["exvalID"];
				}
				$exFieldCurrent[$extraFieldID][] = $thisExtra;
				$zArray = null;
				$zArray[] = array("exvalID",$exvalID,"N");
				$thePercent = makeDecimal($thisExtra["percent"]);
				if ($thisExtra["percent"] != 0) {
					$zArray[] = array("percent",$thePercent,"N");
					$dbA->replaceRecord($tableExtraFieldsPrices,$zArray,0);
				} else {
						for ($g = 0; $g < count($currArray); $g++) {
							$thisExtra["price".$currArray[$g]["currencyID"]] = makeDecimal($thisExtra["price".$currArray[$g]["currencyID"]]);
							$zArray[] = array("price".$currArray[$g]["currencyID"],$thisExtra["price".$currArray[$g]["currencyID"]],"D");
						}
						$dbA->replaceRecord($tableExtraFieldsPrices,$zArray,0);
				}
			}
		}
	}
	
	function updateQtyDisc() {
		global $xProductID,$tableAdvancedPricing,$dbA,$currArray,$myMode;
		$qtydiscList = explode("|",getFORM("xQtyDiscDelete"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				$advID = $entrySplit["advID"];
				if ($advID != 0) {
					$dbA->query("delete from $tableAdvancedPricing where advID=$advID");
				}
			}
		}
		$qtydiscList = explode("|",getFORM("xQtyDiscSend"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$zArray = null;
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				$advID = $entrySplit["advID"];
				$zArray[] = array("productID",$xProductID,"N");
				$zArray[] = array("accTypeID",$entrySplit["accTypeID"],"N");
				$zArray[] = array("priceType",2,"N");
				$zArray[] = array("percentage",$entrySplit["percentage"],"D");
				$counter = 5;
				for ($g = 0; $g < count($currArray); $g++) {
					$zArray[] = array("price".$currArray[$g]["currencyID"],$entrySplit["price".$currArray[$g]["currencyID"]],"D");
					$counter++;
				}
				$zArray[] = array("qtyfrom",$entrySplit["qtyfrom"],"N");
				$counter++;
				$zArray[] = array("qtyto",$entrySplit["qtyto"],"N");
				if ($myMode == "insert") {
					$advID = 0;
				}
				if ($advID != 0) {
					$dbA->updateRecord($tableAdvancedPricing,"advID=$advID",$zArray,0);
				} else {
					$dbA->insertRecord($tableAdvancedPricing,$zArray,0);
				}
			}
		}
	}
	
	function updatePriceComb() {
		global $xProductID,$tableAdvancedPricing,$dbA,$currArray,$extraFieldsArray,$exFieldCurrent,$myMode;
		$qtydiscList = explode("|",getFORM("xPriceCombDelete"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				$advID = $entrySplit["advID"];
				if ($advID != 0) {
					$dbA->query("delete from $tableAdvancedPricing where advID=$advID");
				}
			}
		}
		$qtydiscList = explode("|",getFORM("xPriceCombSend"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$zArray = null;
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				if ($myMode == "insert") {
					$entrySplit["advID"] = 0;
				}
				$advID = $entrySplit["advID"];
				$zArray[] = array("productID",$xProductID,"N");
				$zArray[] = array("accTypeID",$entrySplit["accTypeID"],"N");
				$zArray[] = array("priceType",0,"N");
				$zArray[] = array("percentage",$entrySplit["percentage"],"D");
				$counter = 5;
				for ($g = 0; $g < count($currArray); $g++) {
					$zArray[] = array("price".$currArray[$g]["currencyID"],$entrySplit["price".$currArray[$g]["currencyID"]],"D");
					$counter++;
				}
				$zArray[] = array("qtyfrom",$entrySplit["qtyfrom"],"N");
				$counter++;
				$zArray[] = array("qtyto",$entrySplit["qtyto"],"N");
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					switch ($extraFieldsArray[$g]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
							if ($entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]] < 0 || $myMode == "insert") {
								//got to grab the new one here.
								for ($h = 0; $h < @count(@$exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]]); $h++) {
									if ($exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"] == $entrySplit["textrafield".$extraFieldsArray[$g]["extraFieldID"]]) {
										$entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]][$h]["exvalID"];
										break;
									}
								}
							}
							$zArray[] = array("extrafield".$extraFieldsArray[$g]["extraFieldID"],$entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]],"N");
					}
				}
				if ($myMode == "insert") {
					$advID = 0;
				}
				if ($advID != 0) {
					$dbA->updateRecord($tableAdvancedPricing,"advID=$advID",$zArray,0);
				} else {
					$dbA->insertRecord($tableAdvancedPricing,$zArray,0);
				}
			}
		}
	}
?>
<?php
	function updateOneOff() {
		global $xProductID,$tableAdvancedPricing,$dbA,$currArray,$extraFieldsArray,$exFieldCurrent,$myMode;
		$qtydiscList = explode("|",getFORM("xOneOffDelete"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				$advID = $entrySplit["advID"];
				if ($advID != 0) {
					$dbA->query("delete from $tableAdvancedPricing where advID=$advID");
				}
			}
		}
		$qtydiscList = explode("|",getFORM("xOneOffSend"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$zArray = null;
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				if ($myMode == "insert") {
					$entrySplit["advID"] = 0;
				}
				$advID = $entrySplit["advID"];
				$zArray[] = array("productID",$xProductID,"N");
				$zArray[] = array("accTypeID",$entrySplit["accTypeID"],"N");
				$zArray[] = array("priceType",4,"N");
				$zArray[] = array("percentage",$entrySplit["percentage"],"D");
				$counter = 5;
				for ($g = 0; $g < count($currArray); $g++) {
					$zArray[] = array("price".$currArray[$g]["currencyID"],$entrySplit["price".$currArray[$g]["currencyID"]],"D");
					$counter++;
				}
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					switch ($extraFieldsArray[$g]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
							if ($entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]] < 0 || $myMode == "insert") {
								//got to grab the new one here.
								for ($h = 0; $h < @count(@$exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]]); $h++) {
									if ($exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"] == $entrySplit["textrafield".$extraFieldsArray[$g]["extraFieldID"]]) {
										$entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]][$h]["exvalID"];
										break;
									}
								}
							}
							$zArray[] = array("extrafield".$extraFieldsArray[$g]["extraFieldID"],$entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]],"N");
					}
				}
				if ($myMode == "insert") {
					$advID = 0;
				}
				if ($advID != 0) {
					$dbA->updateRecord($tableAdvancedPricing,"advID=$advID",$zArray,0);
				} else {
					$dbA->insertRecord($tableAdvancedPricing,$zArray,0);
				}
			}
		}
	}
?>
<?php
	
	function updateAttComb() {
		global $xProductID,$tableAdvancedPricing,$dbA,$currArray,$extraFieldsArray,$tableCombinations,$exFieldCurrent,$myMode;
		$qtydiscList = explode("|",getFORM("xAttCombDelete"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				$combID = $entrySplit["combID"];
				if ($combID != 0) {
					$dbA->query("delete from $tableCombinations where combID=$combID");
				}
			}
		}
		$qtydiscList = explode("|",getFORM("xAttCombSend"));
		for ($f = 0; $f < count($qtydiscList); $f++) {
			if ($qtydiscList[$f] != "") {
				$zArray = null;
				$entrySplit = explodeQueryString($qtydiscList[$f]);
				$combID = $entrySplit["combID"];
				$zArray[] = array("productID",$xProductID,"N");
				$zArray[] = array("type",$entrySplit["type"],"S");
				$zArray[] = array("content",@$entrySplit["content"],"S");
				$zArray[] = array("exclude",@$entrySplit["exclude"],"YN");
				for ($g = 0; $g < count($extraFieldsArray); $g++) {
					switch ($extraFieldsArray[$g]["type"]) {
						case "SELECT":
						case "CHECKBOXES":
						case "RADIOBUTTONS":
							if ($entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]] < 0 || $myMode == "insert") {
								//got to grab the new one here.
								for ($h = 0; $h < @count(@$exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]]); $h++) {
									if ($exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]][$h]["content"] == $entrySplit["textrafield".$extraFieldsArray[$g]["extraFieldID"]]) {
										$entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]] = $exFieldCurrent[$extraFieldsArray[$g]["extraFieldID"]][$h]["exvalID"];
										break;
									}
								}
							}						
							$zArray[] = array("extrafield".$extraFieldsArray[$g]["extraFieldID"],$entrySplit["extrafield".$extraFieldsArray[$g]["extraFieldID"]],"N");
					}
				}
				if ($myMode == "insert") {
					$combID = 0;
				}
				if ($combID != 0) {
					$dbA->updateRecord($tableCombinations,"combID=$combID",$zArray,0);
				} else {
					$dbA->insertRecord($tableCombinations,$zArray,0);
				}
			}
		}
	}
?>