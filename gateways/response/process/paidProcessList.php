<?php
	loopAlterStock($orderID);
	sendConfirmationEmails($orderID,0);
	sendOrderPaymentEmail($orderID,"MERCHPAYCONF");
	
	grabAllOptions();
	
	if (retrieveOption("affiliatesActivated") == 1 && retrieveOption("affiliatesCreatePayment") == "PAID" && $orderArray["affiliateID"] > 0) {
		include("../../routines/affiliateTracking.php");
		affiliatesCreatePayment($orderArray);
	}
	if (retrieveOption("downloadsActivate") == 1) {
		include("../../routines/dispatchRoutines.php");
		autoDispatchDigital($orderID);
	}
	if (retrieveOption("suppliersEnabled") == 1 && retrieveOption("suppliersEmailTiming") == 2) {
		include("../../routines/supplierRoutines.php");
		sendSupplierEmails($orderID);
	}
?>
