<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	
	$pageTitle = "Email Templates";
	$submitButton = "";
	$hiddenFields = "";
	
	$myForm = new formElements;
	dbConnect($dbA);
?>
<HTML>
<HEAD>
<TITLE></TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $pageTitle; ?></td>
	</tr>
</table>
<p>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Emails To Merchant</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email to merchant when order placed</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit1" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHORDER&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email to merchant when payment confirmed<br><i>(only applicable if using payment gateway)</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit2" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHPAYCONF&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email to merchant when payment failed<br><i>(only applicable if using payment gateway)</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit3" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHPAYFAIL&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email to merchant when product stock warning level reached<br><i>(only applicable if using stock control)</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit4" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=STOCKWARN&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>		
	<tr>
		<td class="table-list-entry1" valign="top">Email to merchant when product stock zero level reached<br><i>(only applicable if using stock control)</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit5" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=STOCKZERO&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Contact Form Email</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit6" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=CONTACTFORM&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email Sent When New Affiliate Signs Up</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit6" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHAFFNEW&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>		
	<tr>
		<td class="table-list-entry1" valign="top">Email When Customer Opens Account</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit6" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHACCOPEN&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email When Somebody Joins Newsletter List</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit6" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHNEWSLETTER&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email When New Review Is Added</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit6" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=MERCHREVIEW&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>	
	<tr>
		<td class="table-list-title" valign="top" colspan="2"></td>
	</tr>	
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Emails To Customers</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email when customer opens an account</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit9" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=CUSTACCOPEN&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email when customer changes their password</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit9" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=NEWPASSWORD&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email when customer sends their wish list</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit10" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=WISHLIST&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email to customer when order placed<br><i>(timing of this email is set in your Payment Options)</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit7" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=CUSTORDER&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email to customer when order dispatched<br><i>(only applicable if using the dispatch option)</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit8" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=CUSTDESPATCH&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email Gift Certificate</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit8" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=GIFTCERT&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email When Adding To Newsletter List</i></td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit8" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=CUSTNEWSLETTER&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
<?php
	if (retrieveOption("affiliatesActivated") == 1) {
?>	
	<tr>
		<td class="table-list-entry0" valign="top" colspan="2"><center><B>Emails To Affiliates</b></center></td>
	</tr>	
	<tr>
		<td class="table-list-entry1" valign="top">Email when affiliate account is accepted</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit9" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=AFFACCEPTED&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email when affiliate account is declined</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit9" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=AFFDECLINED&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
	<tr>
		<td class="table-list-entry1" valign="top">Email when payment is made to affiliate</td>
		<td class="table-list-entry1" valign="top" align="right">
			<button id="buttonEdit9" class="button-edit" onClick="self.location.href='emails_templates_edit.php?xTemplate=AFFPAYMENT&<?php print userSessionGET(); ?>';">Edit</button>
		</td>
	</tr>
<?php
	}
?>		
</table>
</center>
</BODY>
</HTML>
<?php
	$dbA->close();
?>
