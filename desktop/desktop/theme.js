/*
	Psych Desktop
	Copyright (C) 2006 HFLW

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
	51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/
/*
 * Class: desktop.theme
 * 
 * Takes care of themes
 */
desktop.theme = {
	/*
	 * Property: fileList
	 * 
	 * The different CSS files to load for each theme
	 */
	fileList: ["theme", "window", "icons", "dijit"],
	draw: function()
	{
		dojo.addClass(document.body, "dijit");
		dojo.forEach(this.fileList, function(e)
		{
			var element = document.createElement("link");
			element.rel = "stylesheet";
			element.type = "text/css";
			element.media = "screen";
			element.href = "./themes/"+(desktop.config.theme ? desktop.config.theme : "green")+"/"+e+".css";
			element.id = "desktop_theme_"+e;
			document.getElementsByTagName("head")[0].appendChild(element);
		});
		dojo.subscribe("configApply", this, function(conf) {
			desktop.theme.set(conf.theme);
		});
	},
	/*
	 * Method: set
	 * 
	 * Sets the theme
	 * 
	 * Arguments:
	 * 		theme - the theme to use
	 */
	set: function(/*String*/theme)
	{
		desktop.config.theme = theme;
		dojo.forEach(this.fileList, function(e) {
			dojo.byId("desktop_theme_"+e).href ="./themes/"+desktop.config.theme+"/"+e+".css";
		});
	},
	/*
	 * Method: list
	 * 
	 * Pases a list of the themes to the callback provided
	 * 
	 * Arguments:
	 * 		callback - a callback function
	 */
	list: function(/*Function*/callback)
	{
		api.xhr({
			backend: "core.theme.get.list",
			load: callback,
			handleAs: "json"
		});
	}
}
