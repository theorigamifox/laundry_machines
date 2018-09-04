<?php
	include("resources/includeBase.php");
	
	$f = new formElements;
	
	if ($xBrowserLong == "" || $xBrowserLong == "IE4") {
		exitError("Browser Error","The administration system is designed to be used in Internet Explorer 5 and above or Mozilla Firefox 0.9 and anove.<br>Please login using a compatible browser.");
	}
?>
<HTML>
<HEAD>
<TITLE>JShop Server Administration Login</TITLE>
<META NAME="Author" CONTENT="Unknown">
</HEAD>
<script language="JavaScript1.2" src="resources/js/design.js" type="text/javascript"></script>
<script language="JavaScript1.2" src="resources/js/validation.js" type="text/javascript"></script>
<link rel="stylesheet" href="resources/css/admin.css" type="text/css">
<?php
	$f->addCheck("loginForm","xUsername","Username","!empty");
	$f->addCheck("loginForm","xPassword","Password","!empty");
	$f->createCheck();
?>
<BODY class="detail-body">
<?php $f->createForm("loginForm","login.php",""); ?>
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td valign="center" align="center">
		<table width="236" cellpadding="0" cellspacing="0" class="table-login">
			<tr class="tr-navbar">
				<td align="left" valign="center" height="51" width="400"><img src="images/spacer.gif" border="0" width="5" height="51"><img src="images/logo.gif" border="0" width="47" height="48" alt="JShop Server">
				</td>
			</tr>
			<tr>
				<td class="td-navbar-fade" height="7"><img src="images/spacer.gif" border="0" width="5" height="7"></td>
			</tr>
			<tr>
				<td>
					<table width=100% cellpadding=2 cellspacing=0 border=0>
						<tr>
							<td colspan=2>
								<font class="text-normal"><center>Please login by entering your username and password below and clicking 'Login'.<br></center></font>
							</td>
						</tr>
<?php
	if (array_key_exists("error",$_GET)) {
		$errorCode = $_GET["error"];
		if ($errorCode == "notfound") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> Username and / or password could not be found in the database.</center></font>
							</td>
						</tr>
<?php
		}
		if ($errorCode == "unauthorised") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> You tried to access the administration system without logging in.</font></center>
							</td>
						</tr>
<?php
		}
		if ($errorCode == "timeout") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> Session has timed out. Please login again.</font></center>
							</td>
						</tr>
<?php
		}
		if ($errorCode == "outside") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> You have attempted to access this system from an unauthorised location. Access has been denied.</font></center>
							</td>
						</tr>
<?php
		}
		if ($errorCode == "iperror") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> Your IP address doesn't match IP address saved at login. Access has been denied. Please login again.</font></center>
							</td>
						</tr>
<?php
		}
		if ($errorCode == "disabled") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> User accounts are currently disabled from logging into the system. Please see the system administrator.</font></center>
							</td>
						</tr>
<?php
		}
		if ($errorCode == "accdisabled") {
?>
						<tr>
							<td colspan=2 class="td-error">
								<font class="text-error"><center><B>ERROR:</B> Your account is currently disabled so you may not login. Please see the system administrator.</font></center>
							</td>
						</tr>
<?php
		}
	}
?>
						<tr>
							<td align="right">
								<font class="text-field-title">Username</font>
							</td>
							<td>
								<?php $f->createText("xUsername",20,15,getFORM("xUsername"),"alpha-numeric"); ?>
							</td>
						</tr>
						<tr>
							<td align="right">
								<font class="text-field-title">Password</font>
							</td>
							<td>
								<?php $f->createPassword("xPassword",20,15,"",""); ?>
							</td>
						</tr>
						<tr>
							<td align="right">
								<font class="text-field-title">&nbsp;</font>
							</td>
							<td>
								<?php $f->createSubmit("submit","LOGIN"); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
					<br><font class="text-normal">The Administration System is designed for use
					with<br>Internet Explorer 5 and abov, Mozilla Firefox 0.9 and above and Safari<br>and a screen resolution
					of 800 x 600.
	</td>
</tr>

</table>
<?php $f->closeForm("xUsername"); ?>
</BODY>
</HTML>
