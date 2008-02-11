this.currentFeed = false;
this.init = function(args)
 {
    dojo.require("dijit.layout.LayoutContainer");
    dojo.require("dijit.layout.SplitContainer");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dojox.widget.SortList");
    dojo.require("dijit.Toolbar");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.Button");

    api.addDojoCss("dojox/widget/SortList/SortList.css");

    this.win = new api.window({
        title: "RSS Reader",
        onClose: dojo.hitch(this, this.kill)
    });

    this.toolbar = new dijit.Toolbar({
        layoutAlign: "top"
    });
    var button = new dijit.form.Button({
        label: "Add Feed",
        iconClass: "icon-22-actions-list-add",
        onClick: dojo.hitch(this, this.addFeedDialog)

    });
    this.toolbar.addChild(button);
    var button = new dijit.form.Button({
        label: "Remove Feed",
        iconClass: "icon-22-actions-list-remove",
        onClick: dojo.hitch(this, this.removeFeed)

    });
    this.toolbar.addChild(button);
    this.win.addChild(this.toolbar);

    this.hiddenBar = new dijit.layout.ContentPane({
        layoutAlign: "bottom",
        style: "display: none; height: 0px;"

    },
    document.createElement("div"));

    var client = new dijit.layout.SplitContainer({
        orientation: "horizontal",
        layoutAlign: "client"
    },
    document.createElement("div"));

    this.left = new dojox.widget.SortList({
        title: "Feeds",
        sortable: true
    });

    this.feeds = [
    {
        title: "Psych Desktop",
        url: "http://www.psychdeskop.net/rss.xml"
    },
    {
        title: "Dojo Toolkit",
        url: "http://dojotoolkit.org/rss.xml"
    },
    {
        title: "Ajaxian",
        url: "http://feeds.feedburner.com/ajaxian"
    },
    {
        title: "Slashdot",
        url: "http://rss.slashdot.org/Slashdot/slashdot"
    },
    {
        title: "xkcd",
        url: "http://www.xkcd.com/rss.xml"
    },
    {
        title: "Psychcf's blog",
        url: "http://psychdesigns.net/psych/rss.xml"
    },
	{
        title: "Jay's blog",
        url: "http://www.jaymacdesigns.net/feed/"
    }
    ];
    dojo.forEach(this.feeds, dojo.hitch(this, 
    function(e)
    {
        this.addFeed(e.title, e.url);

    }));
    this.left.startup();
    client.addChild(this.left);

    this.right = new dijit.layout.ContentPane({
        style: "overflow: auto;",
        minsize: 50,
        sizeShare: 30

    },
    document.createElement("div"));
    client.addChild(this.right);

    this.win.addChild(client);
    this.win.onClose = dojo.hitch(this, this.kill);
    this.win.show();
    this.win.startup();
}

this.changeFeeds = function(e)
 {
    this.fetchFeed(e.target.title);
    this.currentFeed = e.target;

}

this.removeFeed = function(t) {
    console.log(this.currentFeed);
    if (this.currentFeed)
    {
        dojo.forEach(this.feeds, dojo.hitch(this, 
        function(e) {
            if (typeof e != "undefined")
            {
                if (e.url == this.currentFeed.title && e.title == this.currentFeed.textContent && this.currentFeed)
                {
                    try
                    {
                        this.currentFeed.parentNode.removeChild(this.currentFeed);
                        for (var i = 0; i < this.feeds.length; i++) {
                            var c = this.feeds[i];
                            if (c.title == this.currentFeed.textContent && c.url == this.currentFeed.title)
                            {
                                delete this.feeds[i];

                            }

                        }

                    } catch(exception) {}
                    this.currentFeed = false;

                }

            }

        }));

    }

}

this.addFeedDialog = function()
 {
    if (typeof(this.addfeedwin) != "undefined") {
        this.addfeedwin.close();
    }
    this.addfeedwin = new api.window({
        title: "Add Feed",
        width: "300px",
        height: "200px"

    });
    this._form = {
        title: new dijit.form.TextBox({}),
        url: new dijit.form.TextBox({})

    };
    var line = document.createElement("div");
    var p = document.createElement("span");
    p.innerHTML = "Title: ";
    line.appendChild(p);
    line.appendChild(this._form.title.domNode);
    var line2 = document.createElement("div");
    var p = document.createElement("span");
    p.innerHTML = "URL: ";
    line2.appendChild(p);
    line2.appendChild(this._form.url.domNode);
    var button = new dijit.form.Button({
        label: "Add Feed"

    });
    dojo.connect(button, "onClick", this, 
    function(e) {
        console.log("test");
        this.addFeed(this._form.title.getValue(), this._form.url.getValue());
        this.addfeedwin.close();

    })
    this.addfeedwin.show();
    this.addfeedwin.body.domNode.appendChild(line);
    this.addfeedwin.body.domNode.appendChild(line2);
    this.addfeedwin.body.domNode.appendChild(button.domNode);
    this.addfeedwin.startup();

}

this.addFeed = function(title, url)
 {
    var p = document.createElement("li");
    p.innerHTML = title;
    p.title = url;
    dojo.connect(p, "onclick", this, this.changeFeeds);
    this.left.containerNode.appendChild(p);
    this.feeds.push({
        url: url,
        title: title
    });

}
this.kill = function()
 {
    if (typeof(this.addfeedwin) != "undefined") {
        this.addfeedwin.close();
    }
    if (typeof(this.win) != "undefined") {
        this.win.close();
    }
}
this.fetchFeed = function(url)
 {
    api.xhr({
        url: url,
        preventCache: true,
		xsite: true,
        load: dojo.hitch(this, 
        function(data, ioArgs) {
			if(data == "9") { api.ui.alertDialog({title: "Psych Desktop internal error", message: "cURL not supported by this server<br>enchanced web features disabled"}); }
            var items = data.getElementsByTagName("item");
            var text = "";
            dojo.forEach(items, 
            function(item) {
                var title = item.getElementsByTagName("title")[0].textContent;
                var content = item.getElementsByTagName("description")[0].textContent;
                var url = item.getElementsByTagName("link")[0].textContent;
                text += "<div style='border: 1px solid black;'><h4><a href='javascript:desktop.app.launch(2, {url: \"" + escape(url) + "\"})'>" + title + "</a></h4><p>" + content + "</p></div>";

            });
            this.right.setContent(text);

        }),
        handleAs: "xml"

    });

}