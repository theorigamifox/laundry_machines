<?php
	include("static/includeBase_front.php");
	
	$xSec=1;
	$thisTemplate = "index.html";
	
	dbConnect($dbA);
	
	include("routines/cartOutputData.php");
	
	$tpl->showPage();
	$dbA->close();
?>