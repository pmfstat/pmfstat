<?php
include('./auth.php');

header('Content-Type: application/vnd.mozilla.xul+xml');

echo '<?xml version="1.0"?>';
echo '<?xml-stylesheet href="chrome://global/skin/" type="text/css"?>';
?>

<window
    id="statmainwindow"
    title="phpMyFAQ Statistics"
    orient="vertical"
    xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">

    <script src="xul.js" />
<script>
function _addToFilter(event)
{

var treeElement = event.rangeParent;  // returns the reference to tree xul element

   var row = new Object();
   var col = new Object();
   var childElement = new Object();

   treeElement.treeBoxObject.getCellAt(event.clientX, event.clientY, row, col, childElement); 

    var tree=document.getElementById("dumptree");

    alert(tree);
}
</script>

<popupset>
  <popup id="svgmenu" oncommand="allert(event.x);">
    <menuitem label="Export as SVG" oncommand="exportChart('svg', this);" />
    <menuitem label="Export as PNG" oncommand="exportChart('png', this);" />
  </popup>
</popupset>

<popupset>
  <popup id="dumpmenu">
    <menuitem label="Add to filter" oncommand="addToFilter(event)" />
    <menuitem label="Export as HTML" oncommand="exportDump('html', this);" />
    <menuitem label="Export as CVS" oncommand="exportDump('cvs', this);" />
    <menuitem label="Export as SQL" oncommand="exportDump('sql', this);" />
  </popup>
</popupset>

    <tabbox flex="1" id="maintabbox">
        <tabs>
            <tab label="Charts" />
            <tab id="dumptab" label="Dump"  oncommand="getDump(false);" />
        </tabs>
        <tabpanels flex="1">
            <tabpanel flex="1">
                <hbox flex="1">
                    <listbox id="diagramlist" onselect="showSVGFromSelection(this);">
                    </listbox>
                    <splitter state="open" collapse="before" resizebefore="closest" resizeafter="closest"><grippy /></splitter>
                    <vbox flex="3">
                        <iframe id="diagramframe" src="about:blank"  context="svgmenu" flex="4" />
                        <splitter state="open" collapse="after" resizebefore="closest" resizeafter="closest"><grippy /></splitter>
                        <textbox id="querybox" multiline="true" value="" flex="1"/>
                    </vbox>
                </hbox>
            </tabpanel>
            <tabpanel flex="1" context="dumpmenu">
                <tree id="dumptree" flex="1">
                </tree>
<!--
                <listbox id="dumplist" flex="1">
                </listbox>
-->
            </tabpanel>
        </tabpanels>
    </tabbox>

    <hbox>
        <label>Filter:</label>
        <textbox id="filtertext" flex="1" />
        <button label="Filter!" id="filterbutton" oncommand="applyFilter();" />
    </hbox>
</window>
