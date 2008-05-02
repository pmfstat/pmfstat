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
    xmlns:html="http://www.w3.org/1999/xhtml/"
    xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">
    <script src="xul.js" />

<popupset>
  <popup id="svgmenu">
    <menuitem label="Export as SVG" oncommand="exportChart('svg', this);" />
    <menuitem label="Export as PNG" oncommand="exportChart('png', this);" />
    <menuitem label="Save SVG on server" oncommand="exportToServer('svg', this);" />
    <menuitem label="Save PNG on server" oncommand="exportToServer('png', this);" />
  </popup>
</popupset>

<popupset>
  <popup id="dumpmenu">
    <menuitem label="Add to filter" oncommand="" />
    <menuitem label="Export as HTML" oncommand="exportDump('html', this);" />
    <menuitem label="Export as CSV" oncommand="exportDump('csv', this);" />
    <menuitem label="Export as SQL" oncommand="exportDump('sql', this);" />
  </popup>
</popupset>

<popupset>
  <popup id="filtermenu">
    <menuitem label="Add filter for PMF 2.0.0" oncommand="addToFilter('`phpMyFAQ_main.currentVersion` = &quot;2.0.0&quot;');" />
    <menuitem label="Add filter for PMF 2.0.1" oncommand="addToFilter('`phpMyFAQ_main.currentVersion` = &quot;2.0.1&quot;');" />
    <menuitem label="Add filter for host != 127.0.0.1" oncommand="addToFilter('System_ip NOT LIKE &quot;127.0.%&quot;');" />
    <menuitem label="Add filter for OS = Windows" oncommand="addToFilter('System_OS = &quot;WINNT&quot;');" />
  </popup>
</popupset>

    <hbox>
      <image src="pmf_logo.png" width="32" height="32" />
      <label style="font-size: 18px; border-bottom: 1px solid 6699cc;">phpMyFAQ statistics viewer</label>
    </hbox>

    <tabbox flex="1" id="maintabbox">
        <tabs>
            <tab label="Charts" />
            <tab id="dumptab" label="Dump"  oncommand="getDump(false);" />
            <tab id="dumptab" label="Query log"  oncommand="getQueryList();" />
            <tab label="Configuration" />
            <tab label="About" />
        </tabs>
        <tabpanels flex="1">
            <tabpanel flex="1">
                <hbox flex="1">
                    <listbox id="diagramlist" onselect="showSVGFromSelection(this);">
                    </listbox>
                    <splitter state="open" collapse="before" resizebefore="closest" resizeafter="closest"><grippy /></splitter>
                    <vbox flex="3">
                        <iframe id="diagramframe" src="about:blank"  context="svgmenu" flex="4" />
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


            <tabpanel flex="1">
                <textbox id="querybox" multiline="true" value="" flex="1"/>
            </tabpanel>

            <tabpanel flex="1">
              <grid flex="1">
                <columns>
                  <column/>
                  <column/>
                </columns>

                <rows>
                  <row>
                    <label>Chart Size X-orientation</label>
                    <textbox id="setting_chart_x" min="100" max="3000" type="number" value="800" hidespinbuttons="false" />
                  </row>
                  <row>
                    <label>Chart Size Y-orientation</label>
                    <textbox id="setting_chart_y" min="100" max="3000" type="number" value="600" hidespinbuttons="false" />
                  </row>
                  <row>
                    <label>Chart file format</label>
                    <menulist id="setting_chart_format" label="svg">
                      <menupopup>
                        <menuitem label="svg" selected="true"/>
                        <menuitem label="png"/>
                      </menupopup>
                    </menulist>
                  </row>
                </rows>
              </grid>
            </tabpanel>

            <tabpanel flex="1">
               <label>You should know - else leave this site!</label>
               <html:h1>I want to see</html:h1>
               <html:h2>Sieben, sieben, eins, zwei, sieben, sieben</html:h2>
            </tabpanel>
        </tabpanels>
    </tabbox>

    <hbox>
        <label>Filter:</label>
        <textbox id="filtertext" flex="1" />
        <button label="..." popup="filtermenu" />
        <button label="Filter!" id="filterbutton" oncommand="applyFilter();" />
    </hbox>
</window>
