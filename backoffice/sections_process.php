<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);

	$xSectionID = getFORM("xSectionID");
	if ($xSectionID != "") {
		//link back can be to the section
		$linkBackLink = "sections_structure.php?xSectionID=".getFORM("xParentReturn")."&";
	} else {
		$linkBackLink = "sections_structure.php?xSectionID=".getFORM("xParentReturn")."&";
	}
	if (getFORM("xReturn") != "") {
		$linkBackLink = urldecode(getFORM("xReturn"))."&";
	}	
		
	$recordType = "Section";
	$tableName = $tableSections;
	
	$languages = $dbA->retrieveAllRecords($tableLanguages,"languageID");
	
	$xAction=getFORM("xAction");
	if ($xAction == "insert") {
		$rArray[] = array("title",getFORM("xTitle"),"S");			
		$rArray[] = array("shortDescription",getFORM("xShortDescription"),"S");	
		$rArray[] = array("fullDescription",getFORM("xFullDescription"),"S");	
		$rArray[] = array("metaDescription",getFORM("xMetaDescription"),"S");	
		$rArray[] = array("metaKeywords",getFORM("xMetaKeywords"),"S");	
		$rArray[] = array("templateFile",getFORM("xTemplateFile"),"S");	
		$rArray[] = array("visible",getFORM("xIsVisible"),"YN");	
		$rArray[] = array("parent",getFORM("xParent"),"N");	
		$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");	
		for ($f = 0; $f < count($languages); $f++) {
			$thisLanguage = $languages[$f]["languageID"];
			if ($thisLanguage != 1) {
				$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
				$rArray[] = array("shortDescription".$thisLanguage,getFORM("xShortDescription".$thisLanguage),"S");	
				$rArray[] = array("fullDescription".$thisLanguage,getFORM("xFullDescription".$thisLanguage),"S");	
				$rArray[] = array("metaDescription".$thisLanguage,getFORM("xMetaDescription".$thisLanguage),"S");	
				$rArray[] = array("metaKeywords".$thisLanguage,getFORM("xMetaKeywords".$thisLanguage),"S");
			}
		}
		
		addImageUpdate("xThumbnail","thumbnail","sections/thumbnails/",$rArray);			
		addImageUpdate("xImage","image","sections/normal/",$rArray);
		$dbA->insertRecord($tableName,$rArray);
		userLogActionAdd($recordType,getFORM("xTitle"));		
		doRedirect("$linkBackLink".userSessionGET());
	}
	if ($xAction == "delete") {
		$xSectionID = getFORM("xSectionID");
		if (!$dbA->doesIDExist($tableName,"sectionID",$xSectionID,$uRecord)) {
			setupProcessMessage($recordType,"","error_existance","BACK","");
		} else {
			$dbA->deleteRecord($tableName,"sectionID",$xSectionID);
			$dbA->deleteRecord($tableProductsTree,"sectionID",$xSectionID);
			userLogActionDelete($recordType,$uRecord["title"]);
			doRedirect("$linkBackLink".userSessionGET());
		}	
	}
	if ($xAction == "update") {
		$xSectionID = getFORM("xSectionID");
		if (!$dbA->doesIDExist($tableName,"sectionID",$xSectionID,$uRecord)) {
			setupProcessMessage($recordType,getFORM("xTitle"),"error_existance","BACK","");	
		} else {
			$rArray[] = array("title",getFORM("xTitle"),"S");			
			$rArray[] = array("shortDescription",getFORM("xShortDescription"),"S");	
			$rArray[] = array("fullDescription",getFORM("xFullDescription"),"S");	
			$rArray[] = array("metaDescription",getFORM("xMetaDescription"),"S");	
			$rArray[] = array("metaKeywords",getFORM("xMetaKeywords"),"S");	
			$rArray[] = array("templateFile",getFORM("xTemplateFile"),"S");	
			$rArray[] = array("visible",getFORM("xIsVisible"),"YN");
			$rArray[] = array("accTypes",getFORM("xAccTypes"),"S");	
			$rArray[] = array("parent",getFORM("xParent"),"N");	
			for ($f = 0; $f < count($languages); $f++) {
				$thisLanguage = $languages[$f]["languageID"];
				if ($thisLanguage != 1) {
					$rArray[] = array("title".$thisLanguage,getFORM("xTitle".$thisLanguage),"S");			
					$rArray[] = array("shortDescription".$thisLanguage,getFORM("xShortDescription".$thisLanguage),"S");	
					$rArray[] = array("fullDescription".$thisLanguage,getFORM("xFullDescription".$thisLanguage),"S");	
					$rArray[] = array("metaDescription".$thisLanguage,getFORM("xMetaDescription".$thisLanguage),"S");	
					$rArray[] = array("metaKeywords".$thisLanguage,getFORM("xMetaKeywords".$thisLanguage),"S");
				}
			}

			
			addImageUpdate("xThumbnail","thumbnail","sections/thumbnails/",$rArray);			
			addImageUpdate("xImage","image","sections/normal/",$rArray);
			$dbA->updateRecord($tableName,"sectionID=$xSectionID",$rArray);
			userLogActionUpdate($recordType,getFORM("xTitle"));
			doRedirect("$linkBackLink".userSessionGET());
		}
	}
	if ($xAction == "reorder") {
		$xNewOrder = getFORM("xNewOrder");
		$newOrderBits = split(";",$xNewOrder);
		for ($f = 0; $f < count($newOrderBits)-1; $f++) {
			$g = $f+1;
			$dbA->query("update $tableSections set position=$g where sectionID=$newOrderBits[$f]");
		}
		userLogAction("Sorted",$recordType,getFORM("xTitle"));
		doRedirect("$linkBackLink".userSessionGET());
	}
	
?>
