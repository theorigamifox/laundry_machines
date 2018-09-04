<?php
	include("static/includeBase_front.php");
	
	$thisTemplate = "advsearch.html";
	$pageType = "advsearch";
	
	dbConnect($dbA);
	
	include("routines/cartOutputData.php");
	
	$tpl->showPage();
	$dbA->close();
?>
