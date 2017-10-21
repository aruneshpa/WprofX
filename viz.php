<html>
<head>
    <meta charset="utf-8">
    <title>WebViz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content>
    <meta name="author" content>
    <link rel="stylesheet" type="text/css" href="common.css">
    <link rel="stylesheet" type="text/css" href="index.css">
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/css/bootstrap-responsive.min.css">
    <link rel="stylesheet" href="./assets/css/toggle.css">
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
            colorMap = new Object({
                "ctext": "#2757ae",
                "dtext": "#a8c5f7",
                "cjs": "#c9780e",
                "djs": "#e8ae61",
                "ccss": "#13bd0d",
                "dcss": "#8ae887",
                "cother": "#eb5bc0",
                "dother": "#eb5bc0",
                "img": "#c79efa",
                "render": "#9b82e3",
                "paint": "#76b169"
            });
        deps = [];
        critPath = [];
        compress = false;
        orig_data = null;

        function dataArrives(callback, taskname) {
            var filename = (window.location.href).split('?p=')[1];
            if (filename === "") {
                filename = "/graphs/cnn_cold_new.json";
            }
            var items = [];
            var timeBegin = 10000;
            var timeEnd = 0;
            var iscompress = document.getElementById('compress_text').checked;
            if (iscompress && taskname != undefined && taskname === "compress") {
                compress = !compress;
                if (compress === true) {
                    filename = "/graphs/cnn_cold_compressed.json";
                } else {
                    filename = (window.location.href).split('?p=')[1];
                }
            }
            $.getJSON(filename, function (data) {
                data.forEach(function (elem, index) {
                    if (index === data.length - 1) {
                        critPath = elem.criticalPath;
                        return;
                    }
                    if (index === data.length - 2) return;
                    elem.objs.forEach(function (objElem, objindex) {
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
                            } else if (objElem.activityId.split('_')[0] == "Scripting") {
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
                            // Filler
                            objElem.start = objElem.startTime;
                            objElem.end = objElem.endTime;
                            objElem.prev = []; // Previous elems. for dependencies.
                            objElem.offset = 0;
                            objElem.len = objElem.endTime - objElem.startTime;
                            // Row Number
                            objElem.download_group = index;
                            dataHash[objElem.id] = objElem;
                            objElem.domain = tld.getDomain(elem.id);
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
                drawGraph(compress);
            });
        }

        var drawGraph = function (compress) {
            var g = new svg(d_legacy, 'mySVG');
            g.draw(compress);
        };

        window.onload = function () {
            var url = (window.location.href).split('?p=');
            var filename = "";
            if (url.length != 1) {
                filename = url[1];
                console.log(filename);
            }
            if (filename != "") {
                console.log(filename);
                dataArrives(drawGraph);
            }
            // Set the filename on the panel.
            var webList = filename.split("/");
            var f = webList[webList.length - 1];
            f = f.replace("0_", "").replace(".json", "");
            document.getElementById("sitename").innerHTML = "Displaying Results for- " + f
        };
        /* Get the current URL and show the website name */
        var URL = window.location.href,
            siteName = "";
        <?php 
        $file = $_GET['p'];
        ?>
    </script>
</head>
<body>
<!-- 
FOR LOADING
Split the content of the page in load and content.   
-->
<div id="load"></div>
<div id="contents">
<!--   IF ELSE BEGINS     -->
<?php
if ($file == "") {
?>
    <div class="container-fluid" id="bg">
        <div id="inval" class="alert alert-danger" role="alert"><b>Invalid URL!</b> Try copy and pasting a url directly
            from the browser.
        </div>
        <div id="chooseFile">
            <!-- <a href="http://wprofx.cs.stonybrook.edu/index.php">
                <img src="images/wprof-logo-256.png" width="230" height="130">
            </a> -->
            <div>
                <h2>Analyze the page load performance of any website</h2>
                <form action="./viz.php" method="post" class="toRemove">
                    <span>
                        <input name="givenURL" id="givenURL" type="text" placeholder="http://www.yourwebsite.com">
                        <button class="btn btn-primary" type="submit" id="openURL">Analyze</button>
                    </span>
                </form>
                <span class="toRemove">
                    <h2 class="size14">-or-</h2>
                    <form>
                        <input name="searchJSON" id="searchJSON" type="text" placeholder="Search completed analyses"
                               onkeyup="util.search(this.value)">
                        <div id="livesearch"><ul></ul></div>
                    </form>

                    <h2 class="size14">-or-</h2>
                <form enctype="multipart/form-data" action="uploader.php" method="POST">
                    Or upload your own JSON file: <input name="uploadedfile" type="file"/><br/>
                    <button class="btn btn-primary" type="submit" value="Upload File">Upload File</button>
                </form>
                </span>
                <img src="images/load-get.gif" id="wheel">
            </div>
        </div>
        <div id="bottomdiv">
            <a name="mInfoPt" id="mInfoPt"></a>
            <a id="mInfoLink" onclick="$('#mInfoPt').animatescroll();">How it Works &darr;</a>
        </div>
        </div>
<?php
} else { ?>
    <div class="container-fluid" id="graphtop">
        <div class="page-header">
            <h1 id="sitename">Website Name</h1>
        </div>
        <div role="tabpanel" id="alltab">
            <ul class="nav nav-tabs" role="tablist">
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="home-t">
                    WebViz is a tool to analyze the web page load time and critical path of a web page with the help of
                    dependencies calculated.
                    <br><br>
                </div>
                <div role="tabpanel" class="tab-pane" id="ads-t">
                    Toggle the switch to remove ads from the page and check the visualization <br><br>
                    <label class="switch">
                        <input type="checkbox" id="remove_ads">
                        <div class="slider round"></div>
                    </label>
                    <br>
                    <div class="btn-group">
                        <button class="btn" id="remove_ads"
                                onclick="svg.getRegisteredcanvas('mySVG')._optimize('remove_ads', this);"><i
                                    class="icon-wrench"></i> Redraw
                        </button>
                    </div>
                    <div id="checks"></div>
                </div>
                <div role="tabpanel" class="tab-pane" id="compress-t">
                    This optimization compresses the HTML and CSS files so that the text download times are reduced by
                    half.
                    <label class="switch">
                        <input type="checkbox" id="compress_text">
                        <div class="slider round"></div>
                    </label>
                    <br>
                    <div class="btn-group">
                        <button class="btn" id="compress_text" onclick="dataArrives('drawGraph', 'compress');"><i
                                    class="icon-wrench"></i> Redraw
                        </button>
                    </div>
                    <div id="checks"></div>
                </div>
                <div role="tabpanel" class="tab-pane" id="cache-t">
                    Toggle the switch to turn on the cache optimizations. <br><br>
                    <label class="switch">
                        <input type="checkbox" id="cahce_opt">
                        <div class="slider round"></div>
                    </label>
                    <br>
                    <div class="btn-group">
                        <button class="btn" id="cache_button"><i class="icon-wrench"></i> Redraw</button>
                    </div>
                    <div id="checks"></div>
                </div>
                <div role="tabpanel" class="tab-pane" id="mobile-t">
                    Toggle the switch to turn on the mobile optimizations. <br><br>
                    <label class="switch">
                        <input type="checkbox" id="mobile_opt">
                        <div class="slider round"></div>
                    </label>
                    <br>
                    <div class="btn-group">
                        <button class="btn" id="mobile_button"><i class="icon-wrench"></i> Redraw</button>
                    </div>
                    <div id="checks"></div>
                </div>
            </div>
        </div>
        <div id="inval" class="alert alert-danger" role="alert"><b>Invalid URL!</b> Try copy and pasting a url directly
            from the browser.
        </div>
    </div>
    <div class="container-fluid" id="pg">
        <!--<div id="bottomBar"></div>-->
        <div id="legend"></div>
        <script>legend(d3.select("#legend"));</script>
        <div class="well">
            <div class="btn-toolbar">
                <div class="btn-group">
                    <button class="btn" onclick="svg.getRegisteredcanvas('mySVG').zoom(1);"><i class="icon-zoom-in"></i>
                    </button>
                    <button class="btn" onclick="svg.getRegisteredcanvas('mySVG').zoom(-1);"><i
                                class="icon-zoom-out"></i></button>
                </div>
                <div class="btn-group">
                    <button class="btn" id="crit_button"
                            onclick="svg.getRegisteredcanvas('mySVG').toggleCriticalPath(true);"><i
                                class="icon-eye-open"></i> Show critical path
                    </button>
                    <button class="btn" id="dep_button" onclick="svg.getRegisteredcanvas('mySVG').showAllLines();"><i class="icon-eye-open"></i> Show dependency lines
                    </button>
                </div>
<!--
                <div class="btn-group">
                    <button class="btn" onclick="svg.getRegisteredcanvas('mySVG').mapSource(this);"><i class="icon-edit"></i> View source</button>
                </div>
-->
                <div id="infoBox" data-spy="affix" data-offset-top="265">
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
        <div id="bottomdiv">
            <a name="mInfoPt" id="mInfoPt"></a>
            <a id="mInfoLink" onclick="$('#mInfoPt').animatescroll();">How it Works &darr;</a>
        </div>
        </div>
    <!--end of content-->
<?php 
} ?>
<div class="container-fluid" id="mInfo">
    <div class="page-header"><h1>How it Works</h1></div>
    <!--<h2>Overview</h2>-->
    <p>
    <ul style="list-style-type: circle">
        <li>WebViz is a tool that extracts dependencies of activities during a page load. For Web developers,
            WebViz can help identify the bottleneck activities of your Web pages. For browser architects,
            WebViz can relate page load bottlenecks to either Web standards or browser implementation choices.
        </li>
        <li>It can be used on any platform or browser, and given a URL, it analyzes that page’s performance.</li>
        <li>Start by typing in a URL of any website, or select a file from the analyzed pages tab.</li>
        <li>Click analyze, and WebViz draws the graph.</li>
    </ul>
    </p>
    <!-- <p>
    For questions or comments, contact <a href="http://www.cs.stonybrook.edu/~arunab" target="_blank"> Aruna Balasubramanian</a> at <a href="mailto:arunab@cs.stonybrook.edu">arunab@cs.stonybrook.edu</a>.
    </p>
-->
</div>

<div id="header">
    <ul>
        <li><a href="http://wprofx.cs.stonybrook.edu/index.html">Home</a></li>
        <li><a onclick="$('#mInfoPt').animatescroll();">How it works</a></li>
        <li><a class="purple" href="http://wprofx.cs.stonybrook.edu/viz.php">WebViz</a></li>
        <li><a href="http://wprofx.cs.stonybrook.edu/recents.php">analyzed pages</a></li>
    </ul>
</div>

<div id="footer"><span>
		<a class="purple" href="http://wprofx.cs.stonybrook.edu/">WebViz research</a> |
        <!-- <a class="purple" href="http://nrg.cs.stonybrook.edu/">NRG lab</a> |
        <a class="purple" href="https://www.cs.stonybrook.edu/">stony brook cs</a> |
        <a href= "mailto:arunab@cs.stonybrook.edu">Aruna Balasubramanian</a>
        -->
	</span></div>

<?php
$link = $_POST['givenURL'];

if (isset($link) and strlen($link) > 1 ) {
    $link_data = parse_url($link);
    $host = $link_data['host'];
    if(preg_match("/\.mp3$|\.zip$|\.tar$|\.mp4$|\.gif$|\.jpg$|\.png$|\.pdf$|\.exe$/", $link) || !filter_var($link, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {
        echo '<script type="text/javascript">
			d3.select("#inval").style("opacity", 1);
			d3.select("#inval").transition()
					.duration(10000)
					.style("opacity", 1);
			d3.select("#inval").transition()
					.duration(8000)
					.style("opacity", 0);
					</script>';
    } else {

        chdir('PLTSpeed/');
        shell_exec('echo '. $link . ' > live_test.txt');
        shell_exec("./main.py");
        shell_exec("./analyze.py");
        // CHANGE
        // $url = "http://wprofx.cs.stonybrook.edu/viz.php?p=/graphs/0_".$host .".json";
        $url = "http://localhost/wprof/viz.php?p=/wprof/graphs/0_".$host .".json";
        echo "<script> window.location.replace('$url') </script>";
    }
}
else {
    print( "Not set " );
    print( $link );
}
?>
</div>
<script src="./assets/js/jquery-3.1.1.min.js"></script>
<script src="./assets/js/bootstrap.min.js"></script>
<script src="./assets/js/affix.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/URI.js/1.18.9/URI.min.js"></script>
<script src="./assets/js/bootstrap-tooltip.js"></script>
    
<!--<script src="./assets/js/bootstrap-typeahead.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>-->
<script type="text/javascript" src="animatescroll.js"></script>
<script type="text/javascript" src="./js/tld.js"></script>
<script>
    document.onreadystatechange = function () {
    var state = document.readyState
    if (state == 'interactive') {
       document.getElementById('contents').style.visibility="hidden";
    } else if (state == 'complete') {
      setTimeout(function(){
         document.getElementById('interactive');
         document.getElementById('load').style.visibility="hidden";
         document.getElementById('contents').style.visibility="visible";
      },1000);
    }
    }
</script>  
</body>
</html>
