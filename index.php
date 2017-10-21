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
<script src="js/d3canvas.js"></script>
<script src="d3/d3.v3.js"></script>

<script>
var loadTime = 0;
var initial_time = -1;
var d_legacy = {};
var start_obj = "";
var load_obj = "";
var data = new Array(); // Array contains all info about the boxes
var dataHash = {}; 		// Hashtable used to search for index of the id of boxes.

var str = window.location.search.substring(1);
//console.log("str: " + str);
var value = "test1.json";
if (str != ""){
	var keys = str.split('p=');
	var keys2 = keys[1].split('&q=');
	value = keys2[0];

//}

var ajax = new XMLHttpRequest();
ajax.onload = dataArrives;
ajax.open("GET", "graphs2/" + value, true);
ajax.send();
}
function dataArrives(){
  if (this.status == 200){ // If the json file is got successfully, then parse the json file.
	try{
	var d = JSON.parse(this.responseText);
	start_obj = d.start_activity; 
	load_obj = d.load_activity;
	console.log("original parse: ");
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
			if (d.objs[i].download.receiveLast < 0) {
				console.log(d.objs[i].download.id + " has negative time/length: "
				 + d.objs[i].download.receiveLast);
				obj_download.len = 0;
			} else
				obj_download.len = d.objs[i].download.receiveLast;// - d.objs[i].download.receiveFirst;
			obj_download.oLen = obj_download.len;
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
			if (d.objs[i].comps[j].time < 0) {
				console.log(d.objs[i].comps[j].id + " has negative time/length: "
				 + d.objs[i].comps[j].time);
				obj_comps.len = 0;
			} else
				obj_comps.len = d.objs[i].comps[j].time;
			obj_comps.oLen = obj_comps.len;
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
			p.id = "dep_line" + data[data.length - 1].id;
			p.a1 = data[data.length - 2].id;
			p.a2 = data[data.length - 1].id;
			p.time = -1;
			p.oTime = p.time;
			data[data.length - 1].prev.push(p);
		}
	}

	//console.log("data:");
	//console.log(data);
	// Puts id and index into an hash table. Sets id as key, and index as value.
	for (var i = 0; i < data.length; i++){
		var key_id = data[i].id;
		dataHash[key_id] = i;
	}
	//console.log("dataHash: ");
	//console.log(dataHash); // Hash table, for testing...

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
				//console.log("p.a1: " + p.a1);
				//console.log("dataHash[p.a1]: " + dataHash[p.a1]);
				//console.log("data[dataHash[p.a1]]: " + data[dataHash[p.a1]]);
				//console.log(".len: " + data[dataHash[p.a1]].len);
				p.time = data[dataHash[p.a1]].len; 
			}else{				
				p.time = d.deps[j].time; // The time that a2 should start after a1
			}
			p.oTime = p.time;
			var twice = false;
			for (var k = 0; k < data[index].prev.length; k++) {
				if (data[index].prev[k].a1 == p.a1 && data[index].prev[k].a2 == p.a2)
					twice = true;
			}
			if (!twice)
				data[index].prev.push(p); // Puts the deps into the pre array under "a2"
		}
	}
	
	// Create a next array to record the boxes depends on self.
	for (var i  = 0; i < data.length; i++){
		for (var j  = 0; j < data[i].prev.length; j++){
			data[dataHash[data[i].prev[j].a1]].next.push(data[i].prev[j].a2);
		}
	}

	// Find out the right start time
	var done = [];
	var queue = [];
	queue.push(data[dataHash[start_obj]]);
	while(queue.length > 0){
		var temp = queue.shift();
		//if(temp.prev.length < 0){
		if (temp.prev.length <= 0) {
			temp.start = 0;
			temp.oStart = temp.start;
			done.push(temp.id);
			for(var j = 0; j < temp.next.length; j++){
				queue.push(data[dataHash[temp.next[j]]]);
			}
		}else{
			var max = 0;
			var redo = false;
			for(var i = 0; i < temp.prev.length; i++){
				var p = temp.prev[i].a1;
				if (done.indexOf(p) == -1) {
					redo = true;
					break;
				}
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
			if (!redo) {
				temp.start = max;
				temp.oStart = temp.start;
				done.push(temp.id);
				//console.log(temp.id + "s start: " + temp.start + ", len: " + temp.len);
				for(var j = 0; j < temp.next.length; j++)
					if (done.indexOf(temp.next[j]) == -1)
						//console.log(temp.id + " is pushing " + temp.next[j]);    
						queue.push(data[dataHash[temp.next[j]]]);
			}
		}
	}

	// Find the end time of each box
	for (var i = 0; i < data.length; i++){
		data[i].end = data[i].start + data[i].len;
		data[i].oEnd = data[i].end;
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
				data_obj.download_group = i;
				order.push(data_obj);
				if (index < data.length - 1){
					index++;
					var current_obj = data[index];
					while (index < data.length && current_obj.same_group == data_obj.id){
						current_obj.download_group = i;
						order.push(current_obj);
						data_obj = current_obj;
						index++;
						current_obj = data[index];
					}
				}
			}
		}
	}
//	console.log("order");
//	console.log(order);
	
	for (var i = 0; i < order.length; i++){
		var current = order[i];
		if (i != 0){
			var previous = order[i - 1];
			var dif = Math.abs(previous.start - current.start);
			if (dif <= 50){
				if (current.info == null)
					current.info = "unknown";
				if (previous.info == null)
					previous.info = "unknown";
				var c_arr = current.info.split(":");
				var r_arr = previous.info.split(":");
				if ((c_arr[0] == r_arr[0]/* || c_arr[1] == r_arr[1]*/) && (current.next.length == 0)){
					if (previous.same_with == ""){
						current.same_with = previous.id;
					}else{
						current.same_with = previous.same_with;
					}
					
				}
			}
		}
	}
	
	/* if (i != 0){
		if (d.same_group == "" && d.next.length == 0){
			var previous = this.data[i - 1];
			
		}
	}*/
	
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
						if (c_arr[0] == r_arr[0] && c_arr[1] == r_arr[1]){
							/*var s_time = group[record_time[s_time]].start;
							var e_time = group[record_time[s_time]].end;
							group[record_time[s_time]].start = Math.min(s_time, current.start);
							group[record_time[s_time]].end = Math.max(e_time, current.end);
							*/var index = record_time[s_time];
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
								/*var s_time = group[record_time[s_time]].start;
								var e_time = group[record_time[s_time]].end;
								group[record_time[s_time]].start = Math.min(s_time, current.start);
								group[record_time[s_time]].end = Math.max(e_time, current.end);
								*/var index = record_time[s_time];
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
/*	console.log("record_time: ");
	console.log(record_time);
	console.log("group: ");
	console.log(group);
	console.log("store: ");
	console.log(store);
*/	
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
	} catch(err) {
		console.log(err);
		console.log(err.stack);
		d3.select("#graphtop").append("br");
		d3.select("#graphtop").append("div").classed("alert alert-danger", true)
			.attr("role", "alert")
			.style("width", "80%")
			.text("JSON data error! Please try analyzing again.");
	}
  }
}
function drawGraph() {
	var g = new svg(d_legacy, 'mySVG');
	g.draw();
}
function loading(stage, size) {
	if (stage == 1) {
		d3.selectAll(".toRemove").remove();
		d3.select("#wheel").style("display", "inline-block");
	}
	else if (stage == 2) {
		var url = "http://wprofx.cs.stonybrook.edu/images/load-analyze.gif";
		if (d3.select("#wheel").classed("-b"))
			url = "http://wprofx.cs.stonybrook.edu/images/load-analyze-b.gif";
		d3.select("#wheel").attr("src", url);
		var fileSize;
		if (size.indexOf("M") != -1) {
			var num = size.substring(0, size.indexOf("M"));
			fileSize = parseInt(num) * 1000;
		} else if (size.indexOf("K") != -1) {
			var num = size.substring(0, size.indexOf("K"));
			fileSize = parseInt(num);
		} else
			fileSize = -1;
		d3.select("#wheel").select(function() {
			return this.parentNode;
		}).insert("p", "#wheel")
			.attr("id", "timeEst");
		document.getElementById("timeEst").innerHTML = util.estimateTime(fileSize);
	}
	else if (stage == 3) {
		var url = "http://wprofx.cs.stonybrook.edu/images/load-graph.gif";
		if (d3.select("#wheel").classed("-b"))
			url = "http://wprofx.cs.stonybrook.edu/images/load-graph-b.gif";
		d3.select("#wheel").attr("src", url);
	}
}
function legend(selection) {
	var colorKey = [{keyl: "HTML", clr: util.getBlockColor("download", "html:text")}, 
		{keyl: "Javascript", clr: util.getBlockColor("download", "application:javascript")},
		{keyl: "CSS", clr: util.getBlockColor("download", "css:text")},
		{keyl: "Image", clr: util.getBlockColor("download", "image:gif")},
		{keyl: "Other App", clr: util.getBlockColor("download", "application:x-javascript")},
		{keyl: "Unknown", clr: util.getBlockColor("", "null")}];

	selection.selectAll("g")
		.data(colorKey)
		.enter()
		.append("g")
		.append("div")
			.style("border-radius", "5px")
			.style("height", 15)
			.style("background-color", function(d) {
				return "#" + d.clr;
			});
	selection.selectAll("g").append("span").text(function(d) {
		return d.keyl;
	});
	var from = selection.append("g");
	from.append("div")
		.style("height", 2)
		.style("background-color", "#" + util.getLineColor("from"));
	from.append("span").text("Parent dep.");
	var to = selection.append("g");
	to.append("div")
		.style("height", 2)
		.style("background-color", "#" + util.getLineColor("to"));
	to.append("span").text("Child dep.");
}
</script>
</head>
<body>

<?
$file = $_GET['p'];
$used = $_GET['q'];
if($file != "") {
?>
	<div class="container-fluid" id="graphtop">
		
<?php
$page = explode('_.', $file);
$s = `ls tests/analysis_t/graphs2`;
$arr = explode("\n", $s);
$op = 0;
if ($used != "" && count($arr) > 2) { ?>
		<form class="form-inline">
		    <select name="p" id="s" onchange="if (this.selectedIndex > -1) this.form.submit();">
		    	<!--<option value="" disabled selected>Select a website</option>-->
<?
//$file = $_GET['p'];
//if (!file)
//  $file = "test1.json";
for ($i = 0; $i < count($arr) - 1; $i++) {
	if ($arr[$i] == $file){ 
		$op = $i + 1;
		echo <<<EOF
<option value="$arr[$i]" selected="selected">$arr[$i]</option>
EOF;
	}else{
		echo <<<EOF
<option value="$arr[$i]">$arr[$i]</option>
EOF;
	}
}?>
	    	</select>
	    	<input type="hidden" name="q" value=<? echo $used; ?> />
	  	</form>
	  	<br />
	  	<div class="page-header">
	  	<h1>Displaying <? echo $op.' of '.(count($arr) - 1).': '.$page[0] ?></h1>
<? } else { ?>
	<div class="page-header">
	<h1>Displaying <? echo $page[0] ?></h1>
<? } ?>
		</div>
		<span class="right">
			<form action="./index.php" method="post" class="toRemove">
				<span>
					<input name="givenURL" id="givenURL" type="text" 
						<?	if ($used != "") { ?>
						value=<? echo $used; ?> >
						<?	} else { ?>
						placeholder="http://www.anotherwebsite.com">
						<? } ?></input>
					<button class="btn btn-primary" type="submit" id="openURL">Analyze</button>
				</span>
			</form>
			<form class="toRemove" onkeypress="return event.keyCode != 13;">
				<input name="searchJSON" id="searchJSON" type="text" placeholder="Search completed analyses" onkeyup="util.search(this.value)"></input>
				<div id="livesearch"><ul></ul></div>
			</form>
			<img src="images/load-get-b.gif" id="wheel" class="-b">
		</span>
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
	</div><!--end of container-->
	<div id="header">
		<ul>
			<li><a href="http://wprofx.cs.stonybrook.edu/index.php#mInfoPt">how it works</a></li>
<? 
} else {
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
			<!--<h2>Graph Elements</h2>
			<p>Horizontal Bars - As a bar graph, each bar represents a page load activity, such as an image or html activity,
			 and the horizontal axis represents the time in milliseconds. You’ll notice darker colors, which represent evaluation activities.</p>-->
	</div>
	<div id="header">
		<ul>
			<li><a onclick="$('#mInfoPt').animatescroll();">how it works</a></li>
<? } ?>
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
<?
if(isset($_POST['givenURL'])) {
	$link = $_POST['givenURL'];
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
    	echo '<script type="text/javascript">
			loading(1);
			</script>';
    	echo str_repeat(' ',1024*64);
		flush();
		chdir('tests/analysis_t/');
		$go = "perl log.pl " . $link;
		shell_exec($go);
		$sizeinfo = `du -h pre_log`;
		$fsize = explode("\t", $sizeinfo);
		echo '<script type="text/javascript">
			loading(2, "' . $fsize[0] . '");
			</script>';
		echo str_repeat(' ',1024*64);
		flush();
    	$analyze = "perl /var/www/wprofx.cs.stonybrook.edu/public_html/tests/analysis_t/convert.pl";
    	shell_exec($analyze);
    	echo '<script type="text/javascript">
			loading(3);
			</script>';
		echo str_repeat(' ',1024*64);
		flush();
    	$graphs = `ls graphs2`;
		$file_name = explode("\n", $graphs);
	  	echo '<script type="text/javascript">
			setTimeout(function(){
			location.href = "../../viz.php?p=' . $file_name[0] . '&q=' . $link . '"}, 500);
			</script>';
    }	
}
?>
<script src="./assets/js/jquery-1.11.2.min.js"></script>
<script src="./assets/js/bootstrap.min.js"></script>
<script src="./assets/js/affix.js"></script>
<!--<script src="./assets/js/bootstrap-typeahead.js"></script>
<script src="./assets/js/bootstrap-tooltip.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>-->
<script src="animatescroll.js"></script>
</body>
