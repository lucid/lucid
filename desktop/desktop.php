	<?php	/*    Psych Desktop    Copyright (C) 2006 Psychiccyberfreak    This program is free software; you can redistribute it and/or modify    it under the terms of the GNU General Public License as published by    the Free Software Foundation; either version 2 of the License, or    (at your option) any later version.    This program is distributed in the hope that it will be useful,    but WITHOUT ANY WARRANTY; without even the implied warranty of    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    GNU General Public License for more details.    You should have received a copy of the GNU General Public License along    with this program; if not, write to the Free Software Foundation, Inc.,    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.	*/if($_SERVER['PHP_SELF']=="/desktop/desktop.php")   {   header("Location: /desktop/index.php");   exit;   }?><html><title>Psych Desktop</title><head><link rel="stylesheet" href="desktop.css" type="text/css" media="screen" /><script type="text/javascript" src="./dojo/dojo.js"></script><script type="text/javascript" language="javascript" src="psychdesktop.js"></script><link id="desktop_theme" rel="stylesheet" href="./themes/default/theme.css" type="text/css" media="screen" /></head><body><div id="wallpaper" name="wallpaper" align="center" style="position: absolute; top: 0; left: 0; zindex: -100; height: 100%; width: 100%;"></div><div id="wallpaperoverlay" style="position: absolute; top: 0; left: 0; zindex: -50; height: 100%; width: 100%;"></div><div id="windowcontainer" dojoType="ContentPane" style="position: absolute; top: 0px; left: 0px; zindex: 0; height: 100%; width: 100%;"></div><div ID='taskbar' style="zindex: 10000;"><center>loading...</center></div><div ID='taskbarhider' onClick="desktop.taskbar.hider()" style="zindex: 11000;"><img src="./icons/hidetask.gif"></div><div id="sysmenu" style="display: none;"><table border="0" cellpadding="0" cellspacing="0"><tr></tr><td><img src="./backgrounds/sysmenutop.gif"></td><tr><td style="background: url(./backgrounds/sysmenumiddle.gif);" align="center"><div id="menu_name"></div><div id="menu"></div></td></tr></table></div><div id="loadingIndicator" style="display: none; position: absolute; bottom: 50px; right: 15px; background-color: #444444; color: #FFFFFF; height: 25px; width: 130px; zindex: 1000;"><center><img style="vertical-align: middle;" src='../images/UI/loading.gif' /><span style="vertical-align: middle;"> <b>Loading...</b></span></center></div><div id="console" onClick = "dojo.byId('consoleinput').focus();">	<div id="consoleoutput" onClick = "dojo.byId('consoleinput').focus();"></div>	<form onSubmit="desktop.core.consolesubmit(); return false;">		<b>~$ </b><input type="text" id="consoleinput" />	</form></div><div dojoType="toaster" id="toaster" separator="<hr>" positionDirection="tr-down" duration="0" messageTopic="psychdesktop"></div></body></html>