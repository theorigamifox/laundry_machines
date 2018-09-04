<?php
	function createConfirmMessage($confirmTitle,$confirmTitle2,$confirmMessage,$confirmButtonLinkYES,$confirmButtonLinkNO) {
		$myForm = new formElements;
?>
<HTML>
<HEAD>
<TITLE>Process Message</TITLE>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css"
</HEAD>
<BODY class="detail-body">
<center>
<table width="99%" cellpadding="2" cellspacing="0" class="table-outline">
	<tr>
		<td class="detail-title"><?php print $confirmTitle; ?></td>
	</tr>
</table>
<br>
<table cellpadding="2" cellspacing="0" class="table-list">
	<tr>
		<td class="table-list-title"><?php print $confirmTitle2; ?></td>
	</tr>
	<tr>
		<td class="table-list-entry1"><br><center><?php print $confirmMessage; ?></center><br></td>
	</tr>
	<tr>
		<td class="table-list-title" colspan="2" align="right"><?php $myForm->createNavBarButton("buttonYES","Yes",$confirmButtonLinkYES); ?>&nbsp;&nbsp;<?php $myForm->createNavBarButton("buttonNO","No",$confirmButtonLinkNO); ?></td>
	</tr>
</table>
</center>
</BODY>
</HTML>
<?php
		exit;
	}
?>