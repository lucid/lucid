/**********************************\
|           Psych Desktop          |
|            API Library           |
|      (c) 2006 Psych Designs      |
| All functions here can be called |
| 	  via api.functionname();      |
\**********************************/
function api() {
this.createvar = function(name)
{
eval("var "+name+";");
}

// Registry API Start

this.registry = function() { }
this.registry = new this.registry();

this.registry.getValue = function(appid,varname,callback)  {
/*
ui_loadingIndicator(0);
//app_createRequest();
api.registry.appid = appid;
api.registry.varname = varname;
var url = "../backend/api.php?registry=load&appid="+appid+"&varname="+varname;
app_xmlHttp.open("GET", url, true);
app_xmlHttp.onreadystatechange = api.registry.processRegistryGet;
app_xmlHttp.send(null);
*/
api.registry.callback = callback;
ui_loadingIndicator(0);
var url = "../backend/api.php?registry=load&appid="+appid+"&varname="+varname;
dojo.io.bind({
    url: url,
    load: api.registry.processRegistryGet,
    error: sys_toastererr,
    mimetype: "text/plain"
});
}
this.registry.saveValue = function(appid,varname,value)  {
/*
ui_loadingIndicator(0);
//app_createRequest();
api.registry.appid = appid;
api.registry.varname = varname;
api.registry.value = value;
var url = "../backend/api.php?registry=save&appid="+appid+"&varname="+varname+"&value="+value;
app_xmlHttp.open("POST", url, true);
app_xmlHttp.send(null);
*/
ui_loadingIndicator(0);
var url = "../backend/api.php?registry=save&appid="+appid+"&varname="+varname+"&value="+value;
dojo.io.bind({
    url: url,
    error: sys_toastererr,
    mimetype: "text/plain"
});
ui_loadingIndicator(1);
}
this.registry.processRegistryGet = function(type, data, evt) {
api.registry.value = data;
api.registry.callback = callback;
eval(callback+"("+data+")");
ui_loadingIndicator(1);
}
// end of registry api

//start filesystem api
this.fs = function() { }
this.fs = new this.fs();
this.fs.getFile = function(file,directory) {
ui_loadingIndicator(0);
var url = "../backend/api.php?fs=load&file="+file+"&directory="+directory;
dojo.io.bind({
    url: url,
    error: sys_toastererr,
    mimetype: "text/plain",
	load: api.fs.getFileProcess
});
}
this.fs.getFileProcess = function(type, data, evt) {
api.fs.content = data;
ui_loadingIndicator(1);
api.toaster("Security Note: FileSystem was accessed.");
}
this.fs.listFiles = function(directory) {
ui_loadingIndicator(0);
var url = "../backend/api.php?fs=load&file="+file+"&directory="+directory;
dojo.io.bind({
    url: url,
    error: sys_toastererr,
    mimetype: "text/xml",
});
}

}
api = new api();

function sys_toastererr(type, error)
{
    api.toaster("Error in AJAX call: "+error);
}

