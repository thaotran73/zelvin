if (document.all) {    var browser_ie = true;} else {    if (document.layers) {        var browser_nn4 = true;    } else {        if (document.layers || (!document.all && document.getElementById)) {            var browser_nn6 = true;        }    }}if (window.navigator.userAgent.toUpperCase().indexOf("OPERA") >= 0) {    var browser_opera = true;}function getBrowserVersion(_4) {    var _5 = 0;    var _6 = navigator.userAgent.toLowerCase();    if (_4 == "firefox" && _6.indexOf("firefox") > -1) {        var _7 = /firefox[\/\s](\d+.\d+)/;        _6.match(_7);        _5 = RegExp.$1;    } else {        if (_4 = "chrome" && _6.indexOf("chrome") > -1) {            var _7 = /chrome[\/\s](\d+.\d+)/;            _6.match(_7);            _5 = RegExp.$1;        } else {            if (_4 == "ie" && _6.indexOf("msie") > -1) {                var _7 = /msie[\/\s](\d+.\d+)/;                _6.match(_7);                _5 = RegExp.$1;            }        }    }    return _5;}function getObj(n, d) {    var p, i, x;    if (!d) {        d = document;    }    if ((p = n.indexOf("?")) > 0 && parent.frames.length) {        if (n.substring(p + 1) != "" && n.substring(p + 1) != null && parent.frames[n.substring(p + 1)]) {            d = parent.frames[n.substring(p + 1)].document;            n = n.substring(0, p);        }    }    if (!(x = d[n]) && d.all) {        x = d.all[n];    }    for (i = 0; !x && i < d.forms.length; i++) {        x = d.forms[i][n];    }    for (i = 0; !x && d.layers && i < d.layers.length; i++) {        x = getObj(n, d.layers[i].document);    }    if (!x && d.getElementById) {        x = d.getElementById(n);    }    return x;}function selectAll(obj) {    if (obj) {		var count = 0;		if (obj.chk != undefined) {			noofentries = obj.chk.length;			checkstatus = obj.allcheck.checked;			var chk = "";			if (noofentries == undefined) {				obj.chk.checked = checkstatus;				chk = obj.chk;				if (checkstatus) {					chk.parentNode.parentNode.style.backgroundColor = "#FFC";				} else {					chk.parentNode.parentNode.style.backgroundColor = "";				}			}			for (i = 0; i < noofentries; i++) {				chk = obj.chk[i];				if (!chk.disabled) {					chk.checked = checkstatus;					if (checkstatus) {						chk.parentNode.parentNode.style.backgroundColor = "#FFC";						count++;					} else {						chk.parentNode.parentNode.style.backgroundColor = "";					}				}			}			editObj = document.getElementById("cvEdit");			if (editObj) {				if (noofentries == 1) {					editObj.disabled = false;				}				if (noofentries > 1) {					editObj.disabled = true;				}			}		}	}}function selectChecked(obj) {	var s="";    if (obj) {    	for (var i=0;i<obj.elements.length;i++){    		var e=obj.elements[i];    		if (e.checked && e.type=="checkbox" && e.name!="allcheck"){             	s += e.value + ",";    		}    	}    	s = s.substring(0,s.length-1);	}    return s;}function showChangeMenuTab() {    var _1c10 = getObj("module");    var _1c11 = document.getElementById("change_menu").getElementsByTagName("a");    var _1c12 = _1c11.length;    for (var i = 0; i < _1c12; i++) {        _1c11[i].className = "menuOn";    }    var _1c14 = (_1c10) ? _1c10.value : "Home";    var _1c15 = getObj("tab_" + _1c14);    if (_1c15 && _1c15.className != "subMenuLink") {        _1c15.className = "sel";    }}function callAdvSearch(name) {	var showpickId = "advancedSearch_showpickId_" + name;	var showtextId = "advancedSearch_showtextId_" + name;	var showbuttonId = "advancedSearch_showbuttonId" + name;    if (browser_ie) {        if (document.getElementById(showpickId)) {            if (document.getElementById(showpickId).style.display == "none") {                document.getElementById(showpickId).style.display = "block";            } else {                document.getElementById(showpickId).style.display = "none";            }        }        if (document.getElementById(showtextId)) {            if (document.getElementById(showtextId).style.display == "none") {                document.getElementById(showtextId).style.display = "block";            } else {                document.getElementById(showtextId).style.display = "none";            }        }        if (document.getElementById(showbuttonId)) {            if (document.getElementById(showbuttonId).style.display == "none") {                document.getElementById(showbuttonId).style.display = "block";            } else {                document.getElementById(showbuttonId).style.display = "none";            }        }    } else {        if (document.getElementById(showpickId)) {            if (document.getElementById(showpickId).style.display == "none") {                document.getElementById(showpickId).style.display = "table-row";            } else {                document.getElementById(showpickId).style.display = "none";            }        }        if (document.getElementById(showtextId)) {            if (document.getElementById(showtextId).style.display == "none") {                document.getElementById(showtextId).style.display = "table-row";            } else {                document.getElementById(showtextId).style.display = "none";            }        }        if (document.getElementById(showbuttonId)) {            if (document.getElementById(showbuttonId).style.display == "none") {                document.getElementById(showbuttonId).style.display = "table-row";            } else {                document.getElementById(showbuttonId).style.display = "none";            }        }    }}function bindMoT(lvId) {    var obj = document.getElementById(lvId);    if (obj) {        obj.onmouseover = lvTredFn;        obj.onmouseout = lvTredFn;    }}function lvTredFn(e) {    var evt = e || window.event;    var obj = evt.target || evt.srcElement;    while (obj.nodeName.toLowerCase() !== "tr") {        obj = obj.parentNode;    }    switch (evt.type) {        case "mouseover":            dropRofn(obj, obj.id);            break;        case "mouseout":            dropRoutfn(obj, obj.id);            break;    }}function dropRofn(rEle, nId) {    rEle.className = "tdhover";	if (document.getElementById("div" + nId)) {        document.getElementById("div" + nId).style.visibility = "visible";    }	    if (document.getElementById("div1" + nId)) {        document.getElementById("div1" + nId).style.visibility = "visible";    }		if (document.getElementById("div2" + nId)) {        document.getElementById("div2" + nId).style.visibility = "visible";    }}function dropRoutfn(rEle, nId) {    rEle.className = "tdout";	if (document.getElementById("div" + nId)) {        document.getElementById("div" + nId).style.visibility = "hidden";        if ($("#dropnoteDiv")) {            hide($("#dropnoteDiv"));            $("#dropnoteDiv").onmouseover = function() {                rEle.className = "tdhover";                document.getElementById("div" + nId).style.visibility = "visible";                show($("#dropnoteDiv"));            };            $("#dropnoteDiv").onmouseout = function() {                rEle.className = "tdout";                document.getElementById("div" + nId).style.visibility = "hidden";                hide($("#dropnoteDiv"));            };        }    }	    if (document.getElementById("div1" + nId)) {        document.getElementById("div1" + nId).style.visibility = "hidden";        if ($("#dropnoteDiv")) {            hide($("#dropnoteDiv"));            $("#dropnoteDiv").onmouseover = function() {                rEle.className = "tdhover";                document.getElementById("div1" + nId).style.visibility = "visible";                show($("#dropnoteDiv"));            };            $("#dropnoteDiv").onmouseout = function() {                rEle.className = "tdout";                document.getElementById("div1" + nId).style.visibility = "hidden";                hide($("#dropnoteDiv"));            };        }    }		if (document.getElementById("div2" + nId)) {        document.getElementById("div2" + nId).style.visibility = "hidden";        if ($("#dropnoteDiv")) {            hide($("#dropnoteDiv"));            $("#dropnoteDiv").onmouseover = function() {                rEle.className = "tdhover";                document.getElementById("div2" + nId).style.visibility = "visible";                show($("#dropnoteDiv"));            };            $("#dropnoteDiv").onmouseout = function() {                rEle.className = "tdout";                document.getElementById("div2" + nId).style.visibility = "hidden";                hide($("#dropnoteDiv"));            };        }    }}function showInLine(_f8c) {    var id = document.getElementById(_f8c);    id.style.display = "inline";}function show(_f8e, _f8f) {    var id = document.getElementById(_f8e);    if (_f8f == undefined) {        id.style.display = "block";    } else {        id.style.display = _f8f;    }}function showHide(_f91, _f92) {    show(_f91);    hide(_f92);}function hide(_f93, hide) {    var id = document.getElementById(_f93);    if (id != null) {        id.style.display = "none";    }    if (_f93 != "crmspanid" && (hide == undefined || hide == "")) {        if (document.getElementById("FreezeLayer")) {            document.body.removeChild(document.getElementById("FreezeLayer"));        }    }}/** * input munmeric only * @return true: ok; false: not ok * **/function inputNumericOnly(evt) {	var charCode = (evt.which) ? evt.which : event.keyCode    if (charCode > 31 && (charCode < 48 || charCode > 57))        return false;    return true;}