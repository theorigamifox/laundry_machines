<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
	include("routines/confirmMessage.php");
	createConfirmMessage("Remove Compiled Templates",
	"Remove Compiled Templates?",
	"Are you sure you wish to remove (delete) the compiled templates?<br>(This only works on compiled templates in the default <b>templates/compiled</b><Br>directory. If you have other template directories you must remove their compiled templates by hand.",
	"self.location.href='templates_process.php?xCommand=removecompiled&".userSessionGET()."';",
	"");
?>
