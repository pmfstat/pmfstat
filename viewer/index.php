<?php
/*
 * TODO: Actually we won't need this and we should do auth lateron.
 * For that we have to delay loading the datasource and building the tree
 */
include('./auth.php');
?>
<html>
    <head>
        <title>phpMyFAQ stats</title>
         <style type="text/css">
             @import "http://o.aolcdn.com/dojo/1.3/dijit/themes/tundra/tundra.css";
             @import "http://o.aolcdn.com/dojo/1.3/dojo/resources/dojo.css";
         </style>
    </head>
    <body class="tundra">
        <div dojoType="dojo.data.ItemFileReadStore" url="json.php?action=chartlist" jsid="chartListStore" />

        <div dojoType="dijit.MenuBar" id="mainMenu">
            <div dojoType="dijit.PopupMenuBarItem">
                <span>File</span>
                <div dojoType="dijit.Menu" id="fileMenu">
                    <div dojoType="dijit.MenuItem" onClick="alert('file 1')">File #1</div>
                    <div dojoType="dijit.MenuItem" onClick="alert('file 2')">File #2</div>
                </div>
            </div>
            <div dojoType="dijit.PopupMenuBarItem">
                <span>Edit</span>
                <div dojoType="dijit.Menu" id="helpMenu">
                    <div dojoType="dijit.MenuItem" onClick="alert('edit 1')">About</div>
                </div>
            </div>
        </div>

        <div id="mainTabContainer" dojoType="dijit.layout.TabContainer" style="width:100%;height:100%">
            <div id="chartTab" dojoType="dijit.layout.BorderContainer" title="Charts" design="sidebar">
                <div dojoType="dijit.layout.ContentPane" splitter="true" title="Table" region="left">
                     
                    <div dojoType="dijit.Tree" store="chartListStore" labelAttr="name" showRoot="false">
                        <script type="dojo/method" event="onClick" args="item">
                            var w = 800; //dojo.byId("chartFrame").innerWidth;
                            var h = 800; //dojo.byId("chartFrame").innerHeight;
                            dojo.byId("chartFrame").setAttribute("src",
                                "chart.php?d="+item.name +"&background=ffffff&filter=&width="+w+"&height="+h+"&pie_threshold=1&timeline_steps=50&format=svg");
                        </script>
                    </div>
                </div>
                <div dojoType="dijit.layout.ContentPane" splitter="true" title="Preview" region="center">
                    <iframe src="about:blank" id="chartFrame" style="border:0px; width:100%; height:100%;">
                    </iframe>
                </div>
            </div>
            <div id="aboutTab" dojoType="dijit.layout.ContentPane" title="About">
                This is a pmf stat viewer using Dojo
            </div>
        </div>
    </body>
<script type="text/javascript" src="dojo-release-1.3.0/dojo/dojo.js"
    djConfig="parseOnLoad:true, isDebug:false"></script>
 <script type="text/javascript">
       dojo.require("dojo.parser");
       dojo.require("dijit.Tree");
       dojo.require("dojo.data.ItemFileReadStore");
       dojo.require("dijit.layout.TabContainer");
       dojo.require("dijit.layout.SplitContainer");
       dojo.require("dijit.layout.ContentPane");
       dojo.require("dijit.layout.BorderContainer");
       dojo.require("dijit.Menu");
       dojo.require("dijit.MenuBar");
       dojo.require("dijit.MenuItem");
       dojo.require("dijit.PopupMenuBarItem");
     </script>
</html>