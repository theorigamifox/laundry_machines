<?php
	function sendSupplierEmails($orderID) {
		global $dbA,$tableOrdersHeaders,$tableOrdersLines,$tableSuppliers,$tableSuppliersEmails,$tableOrdersExtraFields,$tablePaymentOptions,$orderArray,$extraFieldsArray,$extraFieldList,$tableCurrencies,$tableLanguages,$tableExtraFields,$currArray,$langArray;
		$currArray = $dbA->retrieveAllRecords($tableCurrencies,"currencyID");
		$langArray = $dbA->retrieveAllRecords($tableLanguages,"languageID");
		$extraFieldsArray = $dbA->retrieveAllRecords($tableExtraFields,"position,name");
		$result =  $dbA->query("select * from $tableOrdersHeaders where orderID=$orderID");
		$orderArray = $dbA->fetch($result);
		$orderArray["ordernumber"] = $orderArray["orderID"]+retrieveOption("orderNumberOffset");
		$orderArray["orderdate"] = formatDate($orderArray["datetime"]);
		$orderArray["ordertime"] = formatTime(substr($orderArray["datetime"],-6));
		$orderArrayTemp = $orderArray;
		$result = $dbA->query("select supplierID from $tableOrdersLines where orderID=$orderID group by supplierID");
		$count = $dbA->count($result);
		if ($count > 0) {
			for ($f = 0; $f < $count; $f++) {
				$record = $dbA->fetch($result);
				if ($record["supplierID"] != 0) {
					$orderArray = $orderArrayTemp;
					$orderProducts = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersLines where orderID=$orderID and supplierID=".$record["supplierID"]." order by lineID");
					$orderArray["products"] = $orderProducts;
					$orderArray["supplierID"] = $record["supplierID"];
					$extraFieldList = $dbA->retrieveAllRecordsFromQuery("select * from $tableOrdersExtraFields where orderID=$orderID order by lineID,extraFieldID");
					@sendEmail("SUPPLIER","","SUPPLIER");
				}
			}
		}
	}
?>