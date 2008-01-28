/** 
* An API for making an IDE
* TODO: plan this out better. After that write documentation for it.
* 
* @classDescription An API for making an IDE
* @memberOf api
*/
api.ide = new function()
{
	this.execute = function(code)
	{
		desktop.app._fetchApp(dojo.toJson({
			id: -666,
			code: code
		}));
		desktop.app.launch(-666);
	}
	this.save = function(app)
	{
		if(typeof app.id != "undefined" &&
	        typeof app.name != "undefined" &&
	        typeof app.author != "undefined" &&
	        typeof app.email != "undefined" &&
	        typeof app.version != "undefined" &&
	        typeof app.maturity != "undefined" &&
	        typeof app.category != "undefined" &&
	        typeof app.code != "undefined")
		{
			  api.log("IDE API: Saving application...");
	          dojo.xhrPost({
	               url: desktop.core.backend("api.ide.io.save"),
	               content : {
	                    id: app.id,
	                    name: app.name,
	                    author: app.author,
	                    email: app.email,
	                    version: app.version,
	                    maturity: app.maturity,
	                    category: app.category,
	                    code: app.code
	               },
	               load: function(data, ioArgs){
						app.callback(data.id);
						api.log("IDE API: Save Sucessful");
						delete desktop.app.apps[parseInt(data.id)];
				   },
				   handleAs: "json"
	          });
	     }
		 else
		 {
			api.log("IDE API: Error! Could not save. Not all strings in the object are defined.");
		 	return false;
		 }
	}
	this.load = function(appID, callback)
	{
		dojo.xhrPost({
			url: desktop.core.backend("core.app.fetch.full"),
			content: {
				id: appID
			},
			load: function(data, ioArgs)
			{
				if(callback) callback(data);
			},
			handleAs: "json"
		});
	}
	this.getAppList = function(callback) {
	dojo.xhrGet({
		url: desktop.core.backend("core.app.fetch.list"),
		load: function(data, ioArgs)
		{
			callback(apps);
		},
		handleAs: "json"
	});
	}
}
