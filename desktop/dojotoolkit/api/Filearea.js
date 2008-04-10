dojo.provide("api.Filearea");
dojo.require("dijit.layout._LayoutWidget");
dojo.require("dijit.Menu");

dojo.declare("api.Filearea", dijit.layout._LayoutWidget, {
	//	path: String
	//		the path that the filearea should start at
	path: "file://",
	//	textShadow: boolean
	//		Should the items have text shadows
	textShadow: false,
	//	vertical: boolean
	//		should the icons be arranged vertically? if not, then they are placed horizontally.
	vertical: false,
	//	subdirs: boolean
	//		should the desktop navigate through subdirs?
	subdirs: true,
	menu: null,
	postCreate: function() {
		this.menu = new dijit.Menu();
		dojo.connect(this.domNode, "onclick", this, "_onClick");
		
		if(dojo.isIE){
			dojo.connect(this.domNode,'onresize', this,"layout");
		}
		dojo.connect(window,'onresize',this,"layout");
	},
	onItem: function(/*String*/path)
	{
		//	summary:
		//		Called when an item is open
		//		You can overwrite this with your own function.
		//		Defaults to opening the file
		//	path:
		//		the path to the file
		desktop.app.launchHandler(path);
	},
	setPath: function(/*String*/path) {
		//	summary:
		//		sets the path of the filearea and shows that path's contents
		//	path:
		//		the path to display
		if (this.subdirs) {
			this.path = path;
			this.refresh();
			this.onPathChange(path);
		}
		else desktop.app.launchHandler(path);
	},
	onPathChange: function(path) {
		//	summary:
		//		Called when the path changes
	},
	up: function() {
		//	summary:
		//		make the filearea go up one directory
		var path = this.path.split("://");
		if(typeof path[1] == "undefined") {
			path = path[0];
			var protocol = "file";
		}
		else {
			path = path[1];
			var protocol = this.path.split("://")[0];
		} 
		if (path != "/") {
			dirs = path.split("/");
			if(path.charAt(path.length-1) == "/") dirs.pop();
			if(path.charAt(0) == "/") dirs.shift();
			dirs.pop();
			if(dirs.length == 0) this.setPath("/");
			else this.setPath(protocol+"://"+dirs.join("/")+"/");
		}
	},
	_onClick: function(e)
	{
		var w = dijit.getEnclosingWidget(e.target);
		if (w.declaredClass == "api.Filearea._Icon") {
			if (dojo.hasClass(e.target, "fileIcon")) 
				w._onIconClick();
			else 
				if (dojo.hasClass(e.target, "shadowFront") ||
				dojo.hasClass(e.target, "shadowBack") ||
				dojo.hasClass(e.target, "iconLabel")) 
					w.rename();
				else 
					w._onIconClick();
		}
		else {
			//we could put a dragbox selection hook here
			dojo.forEach(this.getChildren(), function(item) {
				item.unhighlight();
			})
		}
	},
	refresh: function() {
		//	summary:
		//		refreshes the area
		
		//clear the area
		dojo.forEach(this.getChildren(), function(item){
			item.destroy();
		});
		//list the path
		api.fs.ls({
			path: this.path,
			callback: dojo.hitch(this, function(array) {
				//make a new widget for each item returned
				dojo.forEach(array, function(item) {
					var name = item.name;
					var p = name.lastIndexOf(".");
					var ext = name.substring(p+1, name.length);
					var icon = desktop.config.filesystem.icons[ext.toLowerCase()];
					if(desktop.config.filesystem.hideExt && item.type!="text/directory" && p != -1) {
						var label = name.substring(0, p-1);
					}
					var wid = new api.Filearea._Icon({
						label: label || name,
						name: name,
						path: item.path,
						type: item.type,
						iconClass: (item.type=="text/directory" ? "icon-32-places-folder" : (icon || "icon-32-mimetypes-text-x-generic"))
					});
					this.addChild(wid);
				}, this);
				//invoke a layout so that everything is positioned correctly
				this.layout();
			})
		});
	},
	layout: function() {
		//	summary:
		//		Lays out the icons vertically or horizontally depending on the value of the 'vertical' property
		var width = this.domNode.offsetWidth;
		var height = this.domNode.offsetHeight;
		var spacing = 100;
		var wc = 0; //width counter
		var hc = 0; //height counter
		var children = this.getChildren();
		for(key in children) {
			var w = children[key];
			if(!w.declaredClass) continue;
			dojo.style(w.domNode, {
				position: "absolute",
				top: (this.vertical ? wc : hc)+"px",
				left: (!this.vertical ? wc : hc)+"px"
			});
			wc += spacing;
			if(wc >= (this.vertical ? height : width)-spacing) {
				wc = 0;
				hc += spacing;
			}
		};
	}
})

dojo.declare("api.Filearea._Icon", [dijit._Widget, dijit._Templated, dijit._Contained], {
	templatePath: dojo.moduleUrl("api", "templates/Filearea_Item.html"),
	//	label: string
	//		The label shown underneath the icon
	label: "File",
	//	path: string
	//		the full path to the file this icon represents
	path: "file://File",
	//	type: string
	//		the mimetype of this file
	type: "text/directory",
	//	iconClass: string
	//		the CSS class of the icon displayed
	iconClass: "icon-32-places-folder",
	//	highlighted: boolean
	//		is the file currently highlighted? (read-only)
	highlighted: false,
	//	name: string
	//		the file's full name
	name: "File.txt",
	postCreate: function() {
		dojo.connect(this.iconNode, "ondblclick", this, "_onDblClick");
	},
	_onDblClick: function() {
		if(this.type=="text/directory") {
			this.getParent().setPath(this.getParent().path+"/"+this.name);
		}
		else {
			this.getParent().onItem(this.getParent().path+"/"+this.name);
		}
	},
	highlight: function() {
		//	summary:
		//		highlights the icon
		this.highlighted = true;
		dojo.addClass(this.labelNode, "selectedItem");
		dojo.addClass(this.iconNode, "fileIconSelected");
	},
	_onIconClick: function() {
		dojo.forEach(this.getParent().getChildren(), function(item) {
			item.unhighlight();
		});
		this.highlight();
	},
	rename: function() {
		//	summary:
		//		show a textbox that renames the file
	},
	unhighlight: function() {
		//	summary:
		//		unhighlights the icon
		this.highlighted = false;
		dojo.removeClass(this.labelNode, "selectedItem");
		dojo.removeClass(this.iconNode, "fileIconSelected");
	},
	startup: function() {
		if(!this.getParent().textShadow) {
			dojo.removeClass(this.textFront, "shadowFront");
			dojo.addClass(this.textFront, "iconLabel");
			dojo.style(this.textBack, "display", "none");
			dojo.style(this.textHidden, "display", "none");
		}
	}
})