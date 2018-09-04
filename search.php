<?php
	include("static/includeBase_front.php");
	
	$thisTemplate = "search.html";
	$pageType = "search";
	
	dbConnect($dbA);
	
	include("routines/cartOutputData.php");
	
	$tpl->showPage();
	$dbA->close();
?>