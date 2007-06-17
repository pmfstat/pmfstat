window.onload = initStatViewer;

function initStatViewer()
{
    getDiagramList();

    window.onload = null;
}


function getFilterAndSettingsURLString()
{
    return 'filter=' + escape(document.getElementById('filtertext').value) + '&' + 'width='  + escape(document.getElementById('setting_chart_x').value) + '&' + 'height=' + escape(document.getElementById('setting_chart_y').value);
}

function doXHRequest(url, success)
{
    var httpRequest = new XMLHttpRequest();
    
    document.getElementById("statmainwindow").setAttribute("wait-cursor", "true");
    document.getElementById("filterbutton").setAttribute("disabled", "true");

    httpRequest.onreadystatechange = function() {
        if (httpRequest.readyState == 4) {
            if (httpRequest.status == 200) {
                try {
                    success(httpRequest.responseText);
                } catch(e) {

                }

                document.getElementById("filterbutton").setAttribute("disabled", "false");
                document.getElementById("statmainwindow").removeAttribute("wait-cursor");
            } else {
                alert('There was a problem with the request.');
            }
        }
    };

    httpRequest.open('GET', url, true);
    httpRequest.send(null);
}

function addToFilter(newcondition)
{
    var element = document.getElementById('filtertext');
    element.value = (element.value == "") ? newcondition : "("+element.value+") AND "+newcondition;
}

function applyFilter()
{
    showSVGFromSelection(document.getElementById("diagramlist"));

    if (document.getElementById('dumptab') == document.getElementById("maintabbox").selectedTab) {
        getDump(true);
    } else {
        var list = document.getElementById('dumptree');
    
        while (list.hasChildNodes()) {
            list.removeChild(list.firstChild);
       }
    }
}

function exportChart(format, obj)
{
    if (!document.getElementById("diagramlist").selectedItem) {
        return;
    }

    var url = document.getElementById("diagramlist").selectedItem.value;
    showSVG(url, format);
}

function loadedDigramList(json)
{
    var all, diagrams;
    var list = document.getElementById("diagramlist");

    eval("all = "+json);
    diagrams = all['dat'];

    while (list.hasChildNodes()) {
        list.removeChild(list.firstChild);
    }

    for (var key in diagrams) {
        var li = document.createElement('listitem');
        li.setAttribute('label', diagrams[key][0]);
        li.setAttribute('value', key);
        li.setAttribute('tooltiptext', diagrams[key][1]);
        li.setAttribute('context', 'svgmenu');
        list.appendChild(li);
    }
}

function getDiagramList()
{
    doXHRequest('json.php?action=chartlist', loadedDigramList);
}

function exportDump(format, sender)
{
    var url = 'dump.php?export=1&mode=' + escape(format) +'&'+getFilterAndSettingsURLString();
    document.getElementById('diagramframe').setAttribute('src', url);
}

function showSVG(chart, exportformat)
{
    var url = 'chart.php?d='+escape(chart)+'&'+getFilterAndSettingsURLString();

    if (exportformat != null) {
        url += "&export=1&format=" + escape(exportformat);
    } else {
        url += "&format=" + escape(document.getElementById("setting_chart_format").getAttribute('label'));
    }
    document.getElementById('diagramframe').setAttribute('src', url);
}

function showSVGFromSelection(obj)
{
    if (!obj.selectedItem) {
        return;
    }

    var url = obj.selectedItem.value;
    showSVG(url, null);
}

function loadedDump(dat)
{
    var d;
    var tree = document.getElementById('dumptree');
    var cols = document.createElement('treecols');
    var children = document.createElement('treechildren');
    
    while (tree.hasChildNodes()) {
        tree.removeChild(tree.firstChild);
    }

    eval('d = '+dat);

    for (var key in d.header) {
        var tmp = document.createElement('treecol');
        var splitter = document.createElement('splitter');
        splitter.setAttribute('class', 'tree-splitter');
        tmp.setAttribute('label', d.header[key]);
        tmp.setAttribute('flex', '1');
        cols.appendChild(tmp);
        cols.appendChild(splitter);
    }
    tree.appendChild(cols);

    for (var key in d.data) {
        var item = document.createElement('treeitem');
        var row = document.createElement('treerow');

        for (var content in d.data[key]) {
           var tmp = document.createElement('treecell');
           tmp.setAttribute('label', d.data[key][content]);
           row.appendChild(tmp);
       }
       item.appendChild(row);
       children.appendChild(item);
    }
    tree.appendChild(children);

    document.getElementById("filterbutton").setAttribute("disabled", "false");
    document.getElementById("statmainwindow").removeAttribute("wait-cursor"); //, "true");
}

function getDump(force)
{
    if (!force && document.getElementById('dumptree').hasChildNodes()) {
        return;
    }

    doXHRequest('json.php?action=dump&filter='+escape(document.getElementById('filtertext').value), loadedDump);
}


function exportToServer(format, event)
{
    var url = 'chart.php?' + 'format=' + escape(format) + '&export=server&d=' + escape(document.getElementById("diagramlist").selectedItem.value) + '&' + getFilterAndSettingsURLString();

    doXHRequest(url, window.open);
}

function showSVGFromSelection(obj)
{
    if (!obj.selectedItem) {
        return;
    }

    var url = obj.selectedItem.value;
    showSVG(url, null);
}

function loadedQueryList(text)
{
    var tmp;
    eval('tmp='+text);
    document.getElementById("querybox").value = tmp;
}

function getQueryList()
{
    doXHRequest('json.php?action=querylist', loadedQueryList);
}
