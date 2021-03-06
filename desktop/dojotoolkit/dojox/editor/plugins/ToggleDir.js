dojo.provide("dojox.editor.plugins.ToggleDir");
dojo.experimental("dojox.editor.plugins.ToggleDir");

dojo.require("dojox.editor._Plugin");

dojo.declare("dojox.editor.plugins.ToggleDir",
	dojox.editor._Plugin,
	{
		//summary: This plugin is used to toggle direction of the edited document only,
		//		   no matter what direction the whole page is.
				
		useDefaultCommand: false,
		command: "toggleDir",

		_initButton: function(){
			this.inherited("_initButton", arguments);
			this.connect(this.button, "onClick", this._toggleDir);		
		},

		updateState: function(){},//overwrite

		_toggleDir: function(){
			var editDoc = this.editor.editorObject.contentWindow.document.documentElement;
			var isLtr = dojo.getComputedStyle(editDoc).direction == "ltr";
			editDoc.dir/*html node*/ = isLtr ? "rtl" : "ltr";
		}
	}
);

dojo.subscribe(dijit._scopeName + ".Editor.getPlugin",null,function(o){
	if(o.plugin){ return; }
	switch(o.args.name){
	case "toggleDir":
		o.plugin = new dojox.editor.plugins.ToggleDir({command: o.args.name});
	}
});
