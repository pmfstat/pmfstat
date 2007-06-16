window.onload = getDiagramList;

var selectedcol = null;

function addToFilter(event)
{
    alert(selectedcol);
}

function applyFilter()
{
    showSVGFromSelection(document.getElementById("diagramlist"));

    if (document.getElementById('dumptab') == document.getElementById("maintabbox").selectedTab) {
        getDump(true);
    } else {
//        var list = document.getElementById('dumplist');
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
    document.getElementById("querybox").setAttribute("value", all["queries"]);

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
    var httpRequest = new XMLHttpRequest();
    
    document.getElementById("filterbutton").setAttribute("disabled", "true");

    httpRequest.onreadystatechange = function() {
        if (httpRequest.readyState == 4) {
            if (httpRequest.status == 200) {
                loadedDigramList(httpRequest.responseText);
                document.getElementById("filterbutton").setAttribute("disabled", "false");
            } else {
                alert('There was a problem with the request.');
            }
        }
    };

    httpRequest.open('GET', 'json.php?action=chartlist', true);
    httpRequest.send(null);

    window.onload = null;
}

function showSVG(chart, exportformat)
{
    var url = 'chart.php?d='+escape(chart)+'&filter='+escape(document.getElementById('filtertext').value)
    if (exportformat != null) {
        url += "&export=" + escape(exportformat);
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
        tree.removeChild(list.firstChild);
    }

    eval('d = '+dat);

    for (var key in d.header) {
        var tmp = document.createElement('treecol');
        tmp.setAttribute('label', d.header[key]);
        tmp.setAttribute('flex', '1');
        cols.appendChild(tmp);
    }
    tree.appendChild(cols);

    for (var key in d.data) {
        var item = document.createElement('treeitem');
        var row = document.createElement('treerow');

        for (var content in d.data[key]) {
           var tmp = document.createElement('treecell');
           tmp.setAttribute('label', d.data[key][content]);
           tmp.setAttribute('oncontextmenu', 'selectedcol="'+content+'";');
           row.appendChild(tmp);
       }
       item.appendChild(row);
       children.appendChild(item);
    }
    tree.appendChild(children);

    document.getElementById("filterbutton").setAttribute("disabled", "false");
    document.getElementById("statmainwindow").removeAttribute("wait-cursor"); //, "true");
/*
    var d;
    var list = document.getElementById('dumplist');
    var cols = document.createElement('listcols');
    var head = document.createElement('listhead');
    
    while (list.hasChildNodes()) {
        list.removeChild(list.firstChild);
    }

    eval('d = '+dat);

    for (var key in d.header) {
        var tmp = document.createElement('listheader');
        tmp.setAttribute('label', d.header[key]);
        head.appendChild(tmp);

        tmp = document.createElement('listcol');
        tmp.setAttribute('flex', '1');
        cols.appendChild(tmp);
    }
    list.appendChild(head);
    list.appendChild(cols);

    for (var key in d.data) {
        var row = document.createElement('listitem');

        for (var content in d.data[key]) {
           var tmp = document.createElement('listcell');
           tmp.setAttribute('label', d.data[key][content]);
           row.appendChild(tmp);
       }    
       list.appendChild(row);
    }

    document.getElementById("filterbutton").setAttribute("disabled", "false");
    document.getElementById("statmainwindow").removeAttribute("wait-cursor"); //, "true");
*/
}

function getDump(force)
{
//    if (!force && document.getElementById('dumplist').hasChildNodes()) {
    if (!force && document.getElementById('dumptree').hasChildNodes()) {
        return;
    }

    var httpRequest = new XMLHttpRequest();
    
    document.getElementById("filterbutton").setAttribute("disabled", "true");
    document.getElementById("statmainwindow").setAttribute("wait-cursor", "false");

    httpRequest.onreadystatechange = function() {
        if (httpRequest.readyState == 4) {
            if (httpRequest.status == 200) {
                loadedDump(httpRequest.responseText);
                document.getElementById("filterbutton").setAttribute("disabled", "false");
            } else {
                alert('There was a problem with the request.');
            }
        }
    };

    httpRequest.open('GET', 'json.php?action=dump&filter='+escape(document.getElementById('filtertext').value), true);
    httpRequest.send(null);

    window.onload = null;
}
