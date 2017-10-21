<html><head>
<meta charset="utf-8">
<title>WProfx</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content>
<meta name="author" content>
<link rel="stylesheet" type="text/css" href="common.css">
<link rel="stylesheet" type="text/css" href="index.css">
<link rel="stylesheet" href="./assets/css/bootstrap.min.css">
<link rel="stylesheet" href="./assets/css/bootstrap-responsive.min.css">
<link href='http://fonts.googleapis.com/css?family=Ubuntu:400,300italic' rel='stylesheet' type='text/css'>
<!-- Files for displaying pretty source code (see mapSource() in d3canvas.js)
<link href="prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="prettify.js"></script>-->

<script src="js/util.js"></script>
<script src="js/timeline.js"></script>
<script src="d3/d3.v3.js"></script>
    
<script>
var d_legacy = {},
    dataHash = {};
    javascript_type_list = new Set(['application/x-javascript', 'application/javascript', 'application/ecmascript', 'text/javascript', 'text/ecmascript', 'application/json', 'javascript/text']),
    css_type_list = new Set(['text/css', 'css/text']),
    text_type_list = new Set(['evalhtml', 'text/html', 'text/plain', 'text/xml']),
    colorMap = new Object({"ctext":"#2757ae", "dtext":"#a8c5f7", "cjs":"#c9780e", "djs":"#e8ae61", "ccss":"#13bd0d", "dcss":"#8ae887", "cother":"#eb5bc0", "dother":"#eb5bc0", "img":"#c79efa", "render":"#9b82e3", "paint":"#76b169"});
    deps = [];
    critPath = [];
    
function dataArrives(callback){
    var filename = (window.location.href).split('?p=')[1];
    if (filename === "") {
        filename = "/wprof/graphs/cnn_cold_new.json";
    }
    
    console.log(filename);
    var items = [];
    var timeBegin = 10000;
    var timeEnd = 0;
    $.getJSON(filename, function(data) {
        console.log(data);
        data.forEach(function(elem, index) {
            if (index === data.length - 1) {
                critPath = elem.criticalPath;
                return;
            }
            if (index === data.length - 2) return;
            elem.objs.forEach(function(objElem, objindex) {
                if (elem.id === "Deps") {
                    objElem.id = "Deps_" + objElem.a1;
                    // Store the dependencies for line drawing
                    deps.push(objElem);
                } else {
                    if (objElem.activityId.split('_')[0] == "Networking") {
                        var mimeType = objElem.mimeType;
                        if (javascript_type_list.has(mimeType)) {
                            objElem.color = colorMap["djs"];
                        } else if (text_type_list.has(mimeType)) {
                            objElem.color = colorMap["dtext"];
                        } else if (css_type_list.has(mimeType)) {
                            objElem.color = colorMap["dcss"];
                        } else if (mimeType.split('/')[0] === "image") {
                            objElem.color = colorMap["img"];
                        } else {
                            objElem.color = colorMap["dother"];
                        }
                    } else if (objElem.activityId.split('_')[0] == "Loading") {
                        if (objElem.name === "ParseHTML" && objElem.url != undefined && objElem.url != "") {
                            objElem.color = colorMap["ctext"];
                        } else if (objElem.name === "ParseAuthorStyleSheet" && objElem.styleSheetUrl != undefined &&
                                  objElem.styleSheetUrl != "") {
                            objElem.color = colorMap["ccss"];
                        }
                    } else if (objElem.activityId.split('_')[0] == "Scripting"){
                        objElem.color = colorMap["cjs"];
                    } else if (objElem.activityId.split('_')[0] == "Rendering") {
                        objElem.color = colorMap["render"];
                    } else if (objElem.activityId.split('_')[0] == "Painting") {
                        objElem.color = colorMap["paint"];
                    }
                    
                    // Create the Object
                    objElem.id = objElem.activityId;
                    objElem.activityId = elem.id + "##" + objElem.id;
                    timeBegin = Math.min(timeBegin, objElem.startTime);
                    timeEnd = Math.max(timeEnd, objElem.endTime);
                    // FIller
                    objElem.start = objElem.startTime;
                    objElem.end = objElem.endTime;
                    objElem.prev = []; // Previous elems. for dependencies.
                    objElem.offset = 0;
                    objElem.len = objElem.endTime - objElem.startTime;
                    // Row Number
                    objElem.download_group = index;
                    dataHash[objElem.id] = objElem;
                    items.push(objElem);
                    
                    // Push the prev activity
                    if (items.length >= 2) {
                        p = new Object();
                        p.id = "dep" + items[items.length - 1].id;
                        p.a1 = items[items.length - 2].id;
                        p.a2 = items[items.length - 1].id;
                        p.time = -1;
                        p.oTime = p.time;
                        items[items.length - 1].prev.push(p);
                    }
                }
            });
        });
        d_legacy["critPath"] = critPath;
        d_legacy["data"] = items;
        d_legacy["loadTime"] = timeEnd - timeBegin;
        d_legacy["htmlSource"] = filename.split(".json")[0];
        d_legacy["deps"] = deps;
        d_legacy["dataHash"] = dataHash;
        console.log(items);
        drawGraph();
    });
}

var drawGraph = function() {
    var g = new svg(d_legacy, 'mySVG');
	g.draw();
}
    
window.onload = function(){
    var url = (window.location.href).split('?p=');
    var filename = "";
    if (url.length != 1) {
        filename = url[1];
    }
    if (filename != "") {
        dataArrives(drawGraph);
    }
}
/* Get the current URL and show the website name */
var URL = window.location.href,
    siteName = "";
if (URL.split('?p=').length > 1)
    siteName = URL.split('?p=')[1].split('.json')[0];
<?php $file = $_GET['p']; ?>
</script>
</head>
<body>
    <?php
    if ($file == "") {
    ?>
    <div class="container-fluid" id="bg">
    <div id="inval" class="alert alert-danger" role="alert"><b>Invalid URL!</b> Try copy and pasting a url directly from the browser.</div>
    <div id="chooseFile">
        <a href="http://wprofx.cs.stonybrook.edu/index.php">
            <img src="images/wprof-logo-256.png" width="230" height="130">
        </a>
        <div>
            <h2>Analyze the page load performance of any website</h2>
            <form action="./index.php" method="post" class="toRemove">
                <span>
                    <input name="givenURL" id="givenURL" type="text" placeholder="http://www.yourwebsite.com"></input>
                    <button class="btn btn-primary" type="submit" id="openURL">Analyze</button>
                </span>
            </form>
            <span class="toRemove">
                <h2 class="size14">-or-</h2>
                <form>
                    <input name="searchJSON" id="searchJSON" type="text" placeholder="Search completed analyses" onkeyup="util.search(this.value)"></input>
                    <div id="livesearch"><ul></ul></div>
                </form>

                <h2 class="size14">-or-</h2>
            <form enctype="multipart/form-data" action="uploader.php" method="POST">
                Or upload your own JSON file: <input name="uploadedfile" type="file" /><br/>
                <button class="btn btn-primary" type="submit" value="Upload File">Upload File</button>
            </form>
            </span>
            <img src="images/load-get.gif" id="wheel">
        </div>
    </div>
    <a name="mInfoPt" id="mInfoPt"></a>
    <a id="mInfoLink" onclick="$('#mInfoPt').animatescroll();">How it Works &darr;</a>
</div>
    <?php
    } else {
    ?>
    <div class="container-fluid" id="graphtop"> 
    <div class="page-header">
    <h1 id = "sitename">Website Name</h1>
    <script>document.getElementById("sitename").innerHTML = "Displaying Results for- " + siteName</script> 
    </div>
    <div role="tabpanel" id="alltab" >
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#speed-t" aria-controls="speed" role="tab" data-toggle="tab">Modify Speed</a>
            </li>
            <li role="presentation">
                <a href="#js-t" aria-controls="js-mod" role="tab" data-toggle="tab">Modify Javascript</a>
            </li>
            <li role="presentation">
                <a href="#edit-t" aria-controls="edit-t" role="tab" data-toggle="tab">Detail Edit Mode</a>
            </li>
        </ul>
        <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="speed-t">
                    Adjust the lengths of computations or downloads by a constant factor.<br><br>
                    <div class="slider">
                        CPU Speed: 
                        <input type="range" min="0" max="4" value="2" id="CPU" oninput="svg.getRegisteredcanvas('mySVG').drawToSpeed();">
                        <span id="cpuVal">1x</span>
                    </div><br>
                    <div class="slider">
                        Network Speed: 
                        <input type="range" min="0" max="4" value="2" id="Network" oninput="svg.getRegisteredcanvas('mySVG').drawToSpeed();">
                        <span id="netVal">1x</span>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="js-t">
                    Asynchronize or set inline each javascript component, or select specific bars from the list or the graph. <br><br>
                    <div class="btn-group">
                        <button class="btn" id="asynch" onclick="svg.getRegisteredcanvas('mySVG').asynchJS(this);"><i class="icon-wrench"></i> Asynchronize</button>
                        <button class="btn" id="injs" onclick="svg.getRegisteredcanvas('mySVG').inlineJS(this);"><i class="icon-wrench"></i> Make Inline</button>  
                    </div>
                    <div id="checks"></div>
                </div>
                <div role="tabpanel" class="tab-pane" id="edit-t">
                    In Detail Edit Mode, click and drag the bars to adjust their lengths and see the effects on the rest of the graph.
                    <br>While in editing, the CPU and Network speed cannot be modified.<br><br>
                    <button class="btn" id="editButton" onclick="svg.getRegisteredcanvas('mySVG').detailEdit(this);"><i class="icon-wrench"></i> Start Editing</button>
                </div>
            </div>
    </div>
    <div id="inval" class="alert alert-danger" role="alert"><b>Invalid URL!</b> Try copy and pasting a url directly from the browser.</div>
    </div>
    <div class="container-fluid" id="pg">
    <!--<div id="bottomBar"></div>-->
    <div id="legend"></div>
    <script>legend(d3.select("#legend"));</script>
    <div class="well">  	
        <div class="btn-toolbar">
            <div class="btn-group">
                <button class="btn" onclick="svg.getRegisteredcanvas('mySVG').zoom(1);"><i class="icon-zoom-in"></i> </button>
                <button class="btn" onclick="svg.getRegisteredcanvas('mySVG').zoom(-1);"><i class="icon-zoom-out"></i> </button>
            </div>
            <div class="btn-group">
                <button class="btn" id="crit_button" onclick="svg.getRegisteredcanvas('mySVG').toggleCriticalPath(true);"><i class="icon-eye-open"></i> Show critical path</button>
                <button class="btn" id="dep_button" onclick="svg.getRegisteredcanvas('mySVG').showAllLines();"><i class="icon-eye-open"></i> Show dependency lines</button>
            </div>
            <!--<div class="btn-group">
                <button class="btn" onclick="svg.getRegisteredcanvas('mySVG').mapSource(this);"><i class="icon-edit"></i> View source</button>
            </div>-->
            <div id="infoBox" data-spy="affix" data-offset-top="265">
                <span><b>100%</b> of original speed</span><br/>
                <a data-toggle="collapse" href="#tooltip" aria-expanded="false" aria-controls="tooltip"></a>
                <ul class="collapse" id="tooltip"></ul>
            </div>
        </div>
        <div id="parent0" style="overflow: auto;">
            <div id="parent">
                <svg id="mySVG"></svg>
            </div>
        </div>
    </div>
    <a name="mInfoPt" id="mInfoPt"></a>
    <a id="mInfoLink" onclick="$('#mInfoPt').animatescroll();">How it Works &darr;</a>
</div><!--end of container--> 
    <?php
    }
    ?>
<!--
<script>
if (siteName === "" || siteName == undefined) {
    document.getElementById("pg").style.display = 'none';
    document.getElementById("graphtop").style.display = 'none';
} else {
    document.getElementById("bg").style.display = 'none';
}
</script>
-->

<div class="container-fluid" id="mInfo">
    <div class="page-header"><h1>How it Works</h1></div>
        <!--<h2>Overview</h2>-->
        <p>
            <ul style="list-style-type: circle">
            <li>WProfx is a tool that extracts dependencies of activities during a page load. For Web developers, 
                WProfx can help identify the bottleneck activities of your Web pages. For browser architects, 
                WProfx can relate page load bottlenecks to either Web standards or browser implementation choices. </li>
            <li>It can be used on any platform or browser, and given a URL, it analyzes that page’s performance. </li>
            <li>Start by typing in a URL of any website, or select a file from the analyzed pages tab. </li>
            <li>Click analyze, and WProfx draws the graph.</li>
            </ul>
        </p>
        <p>
        For questions or comments, contact <a href="http://www.cs.stonybrook.edu/~arunab" target="_blank"> Aruna Balasubramanian</a> at <a href="mailto:arunab@cs.stonybrook.edu">arunab@cs.stonybrook.edu</a>.
        </p>
</div>    
    
<div id="header">
    <ul>
        <li><a onclick="$('#mInfoPt').animatescroll();">How it works</a></li>
        <li><a class="purple" href="http://wprofx.cs.stonybrook.edu/index.php">wprofx</a></li>
        <li><a href="http://wprofx.cs.stonybrook.edu/recents.php">analyzed pages</a></li>
    </ul>
</div>    
    
<div id="footer"><span>
		<a class="purple" href="http://wprofx.cs.stonybrook.edu/">wprof research</a> | 
		<a class="purple" href="http://nrg.cs.stonybrook.edu/">NRG lab</a> | 
		<a class="purple" href="https://www.cs.stonybrook.edu/">stony brook cs</a> | 
		<a href= "mailto:arunab@cs.stonybrook.edu">Aruna Balasubramanian</a>
	</span></div>	
<script src="./assets/js/jquery-3.1.1.min.js"></script>
<script src="./assets/js/bootstrap.min.js"></script>
<script src="./assets/js/affix.js"></script>
<!--<script src="./assets/js/bootstrap-typeahead.js"></script>
<script src="./assets/js/bootstrap-tooltip.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>-->
<script src="animatescroll.js"></script>
</body>
</html>