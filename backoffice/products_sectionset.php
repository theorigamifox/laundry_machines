<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/processMessage.php");

	dbConnect($dbA);
	
	$result = $dbA->query("select* from $tableProducts where extrafield1 != '' order by name");
	$count = $dbA->count($result);
	for ($f = 0; $f < $count; $f++) {
		@set_time_limit(30);
		$record = $dbA->fetch($result);
		$productID = $record["productID"];
		$sectionID = makeInteger($record["extrafield1"]);
		$dbA->query("insert into $tableProductsTree (productID,sectionID) VALUES($productID,$sectionID)");
		echo "Product: ".$record["name"]." placed in section ".$sectionID."<BR>";
	}
	echo "<B>Total of ".$count." products placed in sections</b>";
?>
