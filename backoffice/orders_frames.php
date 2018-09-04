<?php
	include("resources/includeBase.php");
	include("routines/checkaccess.php");
?>
<HTML>
<HEAD>
</HEAD>
<FRAMESET ROWS="*,100">
	<FRAME NAME="jssOrdersList" SRC="orders.php?<?php print userSessionGET(); ?>" FRAMEBORDER="0" NORESIZE>
	<FRAME NAME="jssOrdersSearch" SRC="orders_search.php?<?php print userSessionGET(); ?>" FRAMEBORDER="0" NORESIZE SCROLLING="no">
</FRAMESET>
</HTML>
