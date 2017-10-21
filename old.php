<html><head>
<meta charset="utf-8">
<title>WProf</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content>
<meta name="author" content>
<style>
/*#header {
	background-color: white;
	height: 170px;
	width: 100%;
	border-style: double;
	border-width: 7px;
	border-color: #7B7BFD;
	margin-left: -20px;
}*/
.form-inline {
	margin-left: 10px;
	margin-bottom: 10px;
	display: inline;
}
#header {
	position: relative;
	width: 100%;
}
#header div {
	position: absolute;
	height: 35px;
	width: 90%;
	left: 5%;
	border-bottom: solid rgb(240, 240, 240) 1px;
	z-index: -1;
	top: 20px;
	text-align: center;
}
#chooseFile {
	width: auto;
	margin-top: 70px;
}
#chooseFile form {
	margin-top: 10px;
}
#logoArea {
	margin-right: 5%;
	margin-top: 15px;
	width: 156px;
	height: 100px;
	float: right;
}
h1 {
	font-size: 18px !important;
	font-family: "Avant Garde", Avantgarde, "Century Gothic", CenturyGothic, "AppleGothic", sans-serif !important;
	font-weight: normal !important;
	color: gray !important;
}
#URLform input {
	height: auto;
	margin-bottom: 0;
}
#URLform {
	margin-bottom: 5px;
}

.highlight {
background-color: #e5e5e5;
}
.normal {
background-color: white;
}
.box {
position:absolute;
z-index:100;
}
.box_text {
position:absolute;
z-index:101;
font-size: 10px;
color: #555;
}
.hline {
position:absolute;
z-index:100;
height:2px;
}
.vline {
position:absolute;
z-index:100;
width:1px;
}
#parent0 {
	background-color: white;
	border: 2px solid #e5e5e5;
	padding-bottom: 10px;
}
#parent {
/*position:absolute;*/
/*z-index:999;*/
}

/* for d3canvas */

.bars {
	stroke-width: 1;
	stroke: black;
}
.critBars {
	stroke-width: 2;
	stroke: red;
}
.dependLines {
	stroke: black;
	stroke-width: 1;
	pointer-events: none;
	visibility: hidden;
}
#descr {
	pointer-events: none;
	font-size: 8px;
	width: 150px;
	height: 10px;
	position: relative;
}
.tick {
	font-size: 10px;
    stroke: lightgray;
    opacity: 0.7;
    shape-rendering: crispEdges;
}
.plus {
	font-size: 12px;
	pointer-events: none;
}
#bottomBar {
	position: fixed;
	z-index: 1030;
	top: 70%;
	width: 100%;
	bottom: 0px;
	left: 0px;
	right: 0px;
	background-color: whitesmoke;
	display: none;
	overflow: auto;
	/*padding: 15px 15px 15px 15px;*/
}
#dragLine {
	position: fixed;
	left: 0px;
	top: 70%;
	width:100%;
	height:3px;
	background-color: lightgray;
	z-index: 1031;
	display: none;
}
#dragLine.open {
	display: block;
}
#dragLine:hover {
	background-color: darkgray;
}
pre {
	position: relative;
	padding-left: 25px !important;
	border-top: white solid 2px;
	width: auto !important;
	height: auto !important;
	margin: 0px !important;
}

#pg {
	height: 100%;
	overflow: auto;
}
.slider {
	position: relative;
	top: 8px;
	display: inline;
	padding-right: 15px;
	margin-right: 15px;
	vertical-align: top;
}
.slider input{
	width: 150px;
	vertical-align: top;
	margin-bottom: 10px;
}
.slider span {
	vertical-align: top;
}
#legend {
	border: solid white 1px;
	border-radius: 10px;
	padding-top: 5px;
	display: inline-block;
	background-color: rgba(255,255,255,0.5);
	margin-top: 5px;
}
#legend div {
	display: inline-block;
	width: 15;
	height: 15;
	border: solid black 1px;
	border-radius: 5px;
	margin-left: 5px;
	margin-right: 5px;
	margin-bottom: 5px;
}
#legend span {
	color: gray;
	margin-right: 5px;
	font-size: 10px;
	vertical-align: top;
}
#tipgroup {
	position: relative;
	margin-left: 5px;
	display: inline-block;
}
.helptip {
	background-color: white;
}
#edithelp {
	opacity: 0;
	position: absolute;
	width:80px;
	border-radius: 10px;
	font-size: 8px;
	color: gray;
	padding: 5px;
	line-height: normal;
	left:50%;
	margin-left: -45px;
	top:-40px;
	text-align: center;
	z-index: 500;
}
.helptip.pointed {
	width:10px;
	height:10px;
}
.rotatePt {
	left:50%;
	overflow: hidden;
	margin-left: -5px;
	position: absolute;
	bottom:-5px;
	-webkit-transform: rotate(45deg);
	-moz-transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	-o-transform: rotate(45deg);
}


/* end d3canvas */

#tooltip {
font-family: "Avant Garde", Avantgarde, "Century Gothic", CenturyGothic, "AppleGothic", sans-serif !important;
font-size: 12px;
line-height: normal;
color: gray;
overflow: auto;
padding-left: 5px;
border-radius: 5px;
word-wrap: break-word;
}
#tooltip span {
	display: inline-block;
	width: 170px;
	margin-right: 30px;
	border-right: solid lightgray 1px;
	padding-right: 20px;
}

</style>
<link rel="stylesheet" href="./assets/css/bootstrap.min.css">
<link rel="stylesheet" href="./assets/css/bootstrap-responsive.min.css">

<link href="prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="prettify.js"></script>

<script src="js/util.js"></script>
<script src="js/d3canvas.js"></script>
<script src="d3/d3.v3.js"></script>
<!--script src="js/graph.js"></script-->

<script>
var loadTime = 0;
var initial_time = -1;
var d_legacy = {};
var start_obj = "";
var load_obj = "";
var data = new Array(); // Array contains all info about the boxes
var dataHash = {}; // Hashtable used to search for index of the id of boxes.

var str = window.location.search.substring(1);
var value = "test1.json";
if (str != ""){
	var keys = str.split('=');
	value = keys[1];
}

var ajax = new XMLHttpRequest();
ajax.onload = dataArrives;
ajax.open("GET", "./graphs/" + value, true);
ajax.send();

function dataArrives(){
  if (this.status == 200){ // If the json file is got successfully, then parse the json file.
	var d = JSON.parse(this.responseText);
	start_obj = d.start_activity; 
	load_obj = d.load_activity;
	console.log(d);
	for (var i = 0; i < d.objs.length; i++){
		obj = new Object();
		obj.tag = d.objs[i].id;
		obj.same_group = "";
		obj.url = d.objs[i].url;
		obj.same_with = "";
		obj.prev = [];
		obj.next = [];
		obj.end = initial_time;
		if (d.objs[i].download){
			obj_download = JSON.parse(JSON.stringify(obj));
			obj_download.id = d.objs[i].download.id; // ID for the object
			obj_download.len = d.objs[i].download.receiveLast;// - d.objs[i].download.receiveFirst;
			obj_download.start = initial_time;
			obj_download.event = "download";
			obj_download.bytes = d.objs[i].download.len;
			var r = obj_download.id.split("_");
			//obj_download.download_group = r[1];
			var info = d.objs[i].download.type;
			var s = info.split("/");
			if (s[0] == "text"){           
				obj_download.info = s[1] + ":" + s[0];
			}else{
				obj_download.info = s[0] + ":" + s[1];
			}
			data.push(obj_download); // Puts it into the array
		}
		for (var j = 0; j < d.objs[i].comps.length; j++){ // Goes through all of the comps
			obj_comps = JSON.parse(JSON.stringify(obj));
			obj_comps.start = initial_time;
			obj_comps.len = d.objs[i].comps[j].time;
			obj_comps.id = d.objs[i].comps[j].id;
			obj_comps.event = "";
			//obj_comps.download_group = "";
			var info = d.objs[i].comps[j].type;
			if (info == "1" || info == "2" || info == "3"){
				obj_comps.info = "evaljs";
			}else if (info == "4"){
				obj_comps.info = "evalcss";
			}else{
				obj_comps.info = info;
			}
			data.push(obj_comps);
			p = new Object();
			p.id = "dep_line";
			p.a1 = data[data.length - 2].id;
			p.a2 = data[data.length - 1].id;
			p.time = -1;
			data[data.length - 1].prev.push(p);
		}
	}

	console.log(data);
	// Puts id and index into an hash table. Sets id as key, and index as value.
	for (var i = 0; i < data.length; i++){
		var key_id = data[i].id;
		dataHash[key_id] = i;
	}
	console.log(dataHash); // Hash table, for testing...

	// Goes through all the deps
	for (var j = 0; j < d.deps.length; j++){
		var a2 = d.deps[j].a2;
		if (dataHash[a2] != null){
			var index = dataHash[a2]; // Gets the index of a2 in the data_obj
			p = new Object(); // Creates a new object to store deps
			p.id = d.deps[j].id;
			p.a1 = d.deps[j].a1;
			p.a2 = a2;
			if (d.deps[j].time == -1){
				// if time = -1, then a2 starts when a1 finishes
				p.time = data[dataHash[p.a1]].len; 
			}else{				
				p.time = d.deps[j].time; // The time that a2 should start after a1
			}
			data[index].prev.push(p); // Puts the deps into the pre array under "a2"
			}
		}
	}

	// Create a next array to record the boxes depends on self.
	for (var i  = 0; i < data.length; i++){
		for (var j  = 0; j < data[i].prev.length; j++){
			data[dataHash[data[i].prev[j].a1]].next.push(data[i].prev[j].a2);
		}
	}

	// Find out the right start time
	var queue = [];
	queue.push(data[dataHash[start_obj]]);
	while(queue.length > 0){
		var temp = queue.shift();
		if(temp.prev.length < 0){
			temp.start = 0;
			queue.push(data[dataHash[temp.next]]);
		}else{
			var max = 0;
			for(var i = 0; i < temp.prev.length; i++){
				var p = temp.prev[i].a1;
				var time = data[dataHash[p]].start;
				if(time == -1){
					break;
				}
				if(temp.prev[i].time == -1){
					time = time + data[dataHash[p]].len;
				}else{
					time = time + temp.prev[i].time;
				}
				if(time > max){
					max = time;
				}
			}
			temp.start = max;
		}
		for(var j = 0; j < temp.next.length; j++){	    
			queue.push(data[dataHash[temp.next[j]]]);
		}
	}

	// Find the end time of each box
	for (var i = 0; i < data.length; i++){
		data[i].end = data[i].start + data[i].len;
	}

	// Finds the same_group for each box
	for (var i = 0; i < data.length; i++){
		if(i != 0){
			var tag = data[i-1].tag;
			if(tag == data[i].tag){
				//data[i].download_group = data[i-1].download_group;
				data[i].same_group = data[i - 1].id;
				
			}
		}
	}
	
	//Reorder lines of boxes depend on start time
	var key = new Array(); // Store the start boxes
	var orderhash = {}; // Key - box id, value - start time
	var order = new Array(); // Array stores the order of boxes
	for (var i = 0; i < data.length; i++){
		if (data[i].same_group == ""){
			var time = data[i].start;
			if (key.indexOf(time) == -1){
				key.push(time);
			}
			orderhash[data[i].id] = time;
		}
	}
	key.sort(function(a,b){return a-b});
	for (var i = 0; i < key.length; i++){
		var k = key[i];
		for (var id in orderhash){
			if (orderhash[id] == k){
				var index = dataHash[id];
				var data_obj = data[index];
				order.push(data_obj);
				if (index < data.length - 1){
					index++;
					var current_obj = data[index];
					while (index < data.length && current_obj.same_group == data_obj.id){
						order.push(current_obj);
						data_obj = current_obj;
						index++;
						current_obj = data[index];
					}
				}
			}
		}
	}
	console.log("order");
	console.log(order);
	
	for (var i = 0; i < order.length; i++){
		var current = order[i];
		if (i != 0){
			var previous = order[i - 1];
			var dif = Math.abs(previous.start - current.start);
			if (dif <= 10){
				var c_arr = current.info.split(":");
				var r_arr = previous.info.split(":");
				if ((c_arr[0] == r_arr[0] || c_arr[1] == r_arr[1]) && (current.next.length == 0)){
					if (previous.same_with == ""){
						current.same_with = previous.id;
					}else{
						current.same_with = previous.same_with;
					}
					
				}
			}
		}
	}
	
	
	
	if (i != 0){
		if (d.same_group == "" && d.next.length == 0){
			var previous = this.data[i - 1];
			
		}
	}
	
	var group = new Array(); // Stores boxes with different time
	var store = new Array(); // Stores boxes with the same time.
	var record_time = {}; // the key will be the start time, the value will be the index in group
	for (var i = 0; i < order.length; i++){
		var current = order[i];
		if (group.length == 0){
			record_time[current.start] = group.length; 
			group.push(current);
			var arr = new Array();
			arr.push(current);
			store.push(arr);
		}else if (current.same_group != ""){
			group.push(current);
			var arr = new Array();
			arr.push(current);
			store.push(arr);
		}else{
			if (i == order.length - 1){
				var record = false;
				for (var s_time in record_time){
					if (current.start < s_time + 10 && current.start > s_time - 10){
						var c_arr = current.info.split(":");
						var r_arr = group[record_time[s_time]].info.split(":");
						if (c_arr[0] == r_arr[0] || c_arr[1] == r_arr[1]){
							var s_time = group[record_time[s_time]].start;
							var e_time = group[record_time[s_time]].end;
							group[record_time[s_time]].start = Math.min(s_time, current.start);
							group[record_time[s_time]].end = Math.max(e_time, current.end);
							var index = record_time[s_time];
							store[index].push(current);
							record = true;
							break;
						}
					}
				}
				if (!record){
					record_time[current.start] = group.length;
					group.push(current);
					var arr = new Array();
					arr.push(current);
					store.push(arr);
				}
			}else{
				if (order[i + 1].same_group == current.id){
					group.push(current);
					var arr = new Array();
					arr.push(current);
					store.push(arr);
				}else{
					var record = false;
					for (var s_time in record_time){
						if (current.start < parseInt(s_time) + 10 && current.start > parseInt(s_time) - 10){
							var c_arr = current.info.split(":");
							var r_arr = group[record_time[s_time]].info.split(":");
							if (c_arr[0] == r_arr[0] || c_arr[1] == r_arr[1]){
								var s_time = group[record_time[s_time]].start;
								var e_time = group[record_time[s_time]].end;
								group[record_time[s_time]].start = Math.min(s_time, current.start);
								group[record_time[s_time]].end = Math.max(e_time, current.end);
								var index = record_time[s_time];
								store[index].push(current);
								record = true;
								break;
								
							}
						}
					}
					if (!record){
						record_time[current.start] = group.length;
						group.push(current);
						var arr = new Array();
						arr.push(current);
						store.push(arr);
					}
				}
			}
		}
	}
	console.log("record_time: ");
	console.log(record_time);
	console.log("group: ");
	console.log(group);
	console.log("store: ");
	console.log(store);
	
	// Add the store infomation to the group array
	for (var i = 0; i < store.length; i++){
		var current = group[i];
		current.num = store[i].length;
	}
	
	// Counts time for each line or box
	var line_length = 0;
	var max_length = 0;
	for (var i = 0; i < order.length; i++){
		if(order[i].event == "download" && i != 0){
			line_length = order[i].start + order[i].len;
		}else{
			if (line_length >= order[i].start){
				line_length = line_length + order[i].len;
			}else{
				line_length = order[i].start + order[i].len;
			}
		}
		max_length = Math.max(line_length, max_length);
		if (i == order.length - 1){
			loadTime = Math.max(line_length, max_length);
		}
		
	}

	d_legacy['data'] = order;
	d_legacy['loadTime'] = loadTime;
	d_legacy['htmlSource'] = value.split(".com_.json")[0];
	console.log(d_legacy);
	drawGraph();
}
function drawGraph() {
	var g = new svg(d_legacy, 'mySVG');
	g.draw();
}
</script>


</head>
<!--<body onload="g = new svg(d_legacy, 'mySVG');g.draw();">-->

	<div class="container-fluid" id="pg"><a href="http://wprof.cs.washington.edu/">
		<img src="http://wprof.cs.washington.edu/wprof-logo-256.png" id="logoArea"></a>
		<!--<div id="title">
			<h1>WProf: Demystifying page load performance</h1>
		</div>-->
		<div id="header">
			<div>
				<h1>WProf: Demystifying page load performance</h1>
			</div>
		</div>

<!--navbar-->
    <!--div id="display">debugging info
    </div-->
<div id="bottomBar">
</div>



<div class="span12" id="chooseFile">
	To begin, choose a JSON graph:
  <form class="form-inline">
    <select name="p" id="s">
<?php
$s = `ls ./graphs`;
$arr = explode("\n", $s);
$file = $_GET['p'];
if (!file)
  $file = "test1.json";
for ($i = 0; $i < count($arr) - 1; $i++) {
	if ($arr[$i] == $file){ 
		echo <<<EOF
<option value="$arr[$i]" selected="selected">$arr[$i]</option>
EOF;
	}else{
		echo <<<EOF
<option value="$arr[$i]">$arr[$i]</option>
EOF;
	}
}
?>
    </select>
    <button class="btn btn-primary" type="submit" id="button" onclick="dataChange();">Show</button>
  	</form>
	<form enctype="multipart/form-data" action="uploader.php" method="POST">
		Or upload your own JSON file: <input name="uploadedfile" type="file" /><br/>
		<button class="btn btn-primary" type="submit" value="Upload File">Upload File</button>
	</form><!--
	<form action="runjava.php" method="post">
	<span id="URLform">URL: <input name="givenURL" id="givenURL" type="text" placeholder="http://www.example.com"></input>
	<button class="btn btn-primary" type="submit" id="openURL">Open</button></span></form>--><br />

	<!--
	<p><i>Mouse over a bar to see its dependency lines</i></p>
	<p><i><b>Blue</b> lines show that a bar depends on other bars.
		<b>Orange</b> lines show that a bar is depended on by other bars.</i></p>-->
	<!--<div>
	<button class="btn btn-primary" id="show_dep" type="summit" onclick="svg.getRegisteredcanvas('mySVG').show_allLines();">Show/Hide all dependency lines </button>
	</div>-->
	<br>
</div>

<?
function upload(){
	$temp = explode(".", $_FILES["uploadedfile"]["name"]);
	$extension = end($temp);
	if ($extension == "json"){
		$target_path = "./graphs/";
		$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
		if (file_exists("./graphs/" . $_FILES["uploadedfile"]["name"])) {
		  echo $_FILES["uploadedfile"]["name"] . " already exists. ";
		} else {
		  move_uploaded_file($_FILES["uploadedfile"]["tmp_name"],
		  $target_path . $_FILES["uploadedfile"]["name"]);
		  echo "Stored in: " . "./upload/" . $_FILES["uploadedfile"]["name"];
		  $_GET['p'] = $_FILES["uploadedfile"]["name"];
		}
	}else{
		echo "Invalid file";
	}
}
?>

<div class="span12">
  	<div class="well">
	      	<div id="tooltip">
	      		<b>[Type]</b> <br/>
	      		<b>Info:</b> <br/><span>
	      		<b>Length:</b> </span>
	      		<b>% / Total Time:</b> <br>
	      	</div>
   		<div class="btn-toolbar">
   			<div class="btn-group">
		      	<button class="btn" onclick="svg.getRegisteredcanvas('mySVG').zoom(1);"><i class="icon-zoom-in"></i> Zoom in</button>
		      	<button class="btn" onclick="svg.getRegisteredcanvas('mySVG').zoom(-1);"><i class="icon-zoom-out"></i> Zoom out</button>
		    </div>
    		<div class="btn-group">
      			<button class="btn" id="crit_button" onclick="svg.getRegisteredcanvas('mySVG').toggleCriticalPath(true);"><i class="icon-eye-open"></i> Show critical path</button>
      			<button class="btn" id="dep_button" onclick="svg.getRegisteredcanvas('mySVG').showAllLines();"><i class="icon-eye-open"></i> Show all dependency lines</button>
    		</div>
    		<div class="btn-group">
      			<button class="btn" onclick="svg.getRegisteredcanvas('mySVG').mapSource(this);"><i class="icon-edit"></i> View source</button>
    		</div>
    		<div id="tipgroup">
    		<div class="btn-group">
      			<button class="btn" id="editButton" onclick="svg.getRegisteredcanvas('mySVG').detailEdit(this);"><i class="icon-wrench"></i> Detail Edit Mode</button>
      		</div>
      		      		<div id="edithelp" class="helptip">
				Click and drag to adjust bar lengths
				<span class="rotatePt">
					<div class="helptip pointed"></div>
				</span>
			</div>
		</div>

      		<br>
    		<div class="slider">
				CPU Speed: 
				<input type="range" min="0" max="4" value="2" id="CPU" oninput="svg.getRegisteredcanvas('mySVG').redraw(document.getElementById('CPU'), document.getElementById('Network').value, 'cpuVal');">
				<span id="cpuVal">1x</span>
			</div>
			<div class="slider">
				Network Speed: 
				<input type="range" min="0" max="4" value="2" id="Network" oninput="svg.getRegisteredcanvas('mySVG').redraw(document.getElementById('Network'), document.getElementById('CPU').value, 'netVal');">
				<span id="netVal">1x</span>
   			</div>
   			<div id="legend">
   			</div>
   		</div>
	    <p></p>
	    <div id="parent0" style="overflow: auto;">
	      	<div id="parent">
		      	<svg id="mySVG"></svg>
	      	</div>
	    </div>
  	</div>
</div>
<!--span8-->
<!--div class="span5">
  <table class="table table-bordered table-condensed">
    <thead><tr>
      <th>Task</th>
      <th width="200px;">Info</th>
      <th>URL</th>
      <th>Start</th>
      <th>End</th>
    </tr></thead>
    <tbody id="results">
    </tbody>
  </table>
</div-->
</div><!--container-->
<script src="./assets/js/jquery-1.7.2.min.js"></script>
<script src="./assets/js/bootstrap.min.js"></script>
<script src="./assets/js/bootstrap-typeahead.js"></script>
<script src="./assets/js/bootstrap-tooltip.js"></script>
<script>
//$("#tooltip").hide();
</script>
</body>
