<HTML>
<HEAD>
<TITLE>JShop Server v<?php print $versionNumber; ?> Administration: <?php print $userRecord["realname"]; ?> (<?php print $userRecord["username"]; ?>) <?php print $safeMode; ?></TITLE>
</HEAD>
<FRAMESET ROWS="65,*" FRAMESPACING="0" BORDER="0">
	<FRAME NAME="jssMenu" SRC="menu.php?<?php print userSessionGET(); ?>" FRAMEBORDER="0" FRAMESPACING="0" NORESIZE SCROLLING="no">
	<FRAME NAME="jssMain" SRC="main.php?<?php print userSessionGET(); ?>" FRAMEBORDER="0" FRAMESPACING="0" NORESIZE>
	<NOFRAMES>
		<BODY>
			A frames compatible browser is required to use the DoublePadlock system.
		</BODY>
	</NOFRAMES>
</FRAMESET>
</HTML>
