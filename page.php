<?php
	include("static/includeBase_front.php");
	
	$xSec=1;
	$thisTemplate = "index.html";
	
	if (isset($_SERVER["REQUEST_URI"])) {
		$reqURI = $_SERVER["REQUEST_URI"];
	} else {
		$reqURI = $_SERVER["SCRIPT_NAME"];
		if (isset($_SERVER["QUERY_STRING"])) {
			$reqURI .= "?".$_SERVER["QUERY_STRING"];
		}
	}
	$reqURI = str_replace("%","",$reqURI);
	if (array_key_exists("xPage",$_GET)) {
		$xPage = makeSafe(getFORM("xPage"));
	} else {
		if (strpos($reqURI,"page.php/") === FALSE) {
			$xBits = explode("page/",$reqURI);
		} else {
			$xBits = explode("page.php/",$reqURI);
		}
		$xOptions = explode("/",@$xBits[1]);
		$_GET["xPage"] = makeSafe($xOptions[0]);
		$xPage = makeSafe($xOptions[0]);
	}
	$xPage = str_replace(chr(0),"",$xPage);
	$xPage = str_replace("../","",$xPage);
	$xPage = str_replace("./","",$xPage);
	$xPage = str_replace("%","",$xPage);
	if (strpos($xPage,".html") === FALSE) {
		$xPage .= ".html";
	}
	if ($xPage != "") {
		$thisTemplate = $xPage;
	}
	
	
	dbConnect($dbA);
	
	include("routines/cartOutputData.php");
	
	$tpl->showPage();
	$dbA->close();
?>