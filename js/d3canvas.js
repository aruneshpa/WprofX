// CONSTANTS
var margin = {top: 15, right: 20, bottom: 20, left: 20};
//var barHeight = 12;
var barHeight = 5;
var barSpace = 12;		// Vertical space between bars
var barSpace = 4;		// Vertical space between bars
var barCurve = 5;		// Radius curve at bar corners
var minBar = 7;			// minimum bar length on hover
var ttime = 800;		// duration for movement transitions

// Global variables
var w = 1000;			// default width--code should overwrite
var h = 1000;			// default height--code should overwrite
var max = 1;			// Maximum number of rows - not yet set
var xScale;				// Converts from time (ms?) to pixels
var yScale;				// Converts from row number to pixels
var critPath = [];		// bars and lines on the critical path
var oCritPath;
var critOn = false;		// critical path visibility
var allVisible = false;	// all dependency lines visibility
var edit = false;		// detail edit mode status
//var htmlTxt = "";
var jsdeps = [];		// asynchronized javascript dependencies

function svg(d, id) {
  	// Register canvas
  	svg.registercanvas(id, this);

  	//console.log("d[data]: " + d["data"]);
  	//console.log(d["loadTime"]);
  	this.dataset = d["data"];
  	this.loadTime = d["loadTime"];
  	this.htmlSource = d["htmlSource"] + ".html";
  	this.store = d["store"];
  	this.svgId = id;
  	this.svg = document.getElementById(id);
  	//this.c = this.svg.getContext("2d");
  	//this.c.font = "bold 12px sans-serif";
  	
  	// max : total number of rows : last row's download_group
  	max = this.dataset[this.dataset.length - 1].download_group;

  	// Width: Window width
  	// Height: maximum number of rows * space per bar
  	w = parseInt(d3.select("#parent").style("width"), 10) - margin.left - margin.right;
  	h = max * (barHeight + barSpace);

  	xScale = d3.scale.linear()
	    .domain([0, loadTime])
	    .range([0, w]);
  	yScale = d3.scale.linear()
		.domain([0, max])
		.range([0, h]);
}


// The hashmap of registered canvas
// Key: id, value: svg object
svg.__registeredCanvases = {};

/*
 * Register canvas
 *
 * @param id: the id of the svg
 * @param g: the svg object
 */
svg.registercanvas = function(id, g) {
  svg.__registeredCanvases[id] = g;
}

/*
 * Get a registered canvas
 *
 * @param id: the id of the svg
 * @return: the svg object
 */
svg.getRegisteredcanvas = function(id) {
  return svg.__registeredCanvases[id];
}

svg.prototype = {

	/*  Sets the svg dimensions, plus margins
		transitions smoothly when bars are expanded/condensed	*/
	init: function() {
		d3.select(this.svg).transition().duration(ttime)
			.attr("width", w + margin.left + margin.right)
			.attr("height", yScale(max - data[data.length - 1].barsHidden + 1) + margin.top + margin.bottom);
	},

	/* 	draws bars and sets hover/click event functions			*/
	_drawBars: function() {
		var me = this;			// this: the svg
		var hiddenBars = 0;		// count of merged bars
		var dragging = false;

		// Draw and color rectangles
		d3.select("#graph").selectAll("rect")
			.data(me.dataset)
			.enter()
			.append("g")
			.append("rect")
				.attr("id", function(d) {
					return d.id;
				})
				.attr("class", "bars")
				.attr("width", function(d) {
					// set initial offset to 0 (see _miniExpand)
					d['offset'] = 0;
					return xScale(d.end - d.start);
				})
				.attr("height", barHeight)
				.attr("fill", function(d) {
					return "#" + util.getBlockColor(d.event, d.info);
				})
				.attr("rx", barCurve)
				.attr("ry", barCurve);

		/*  Set rectangles in the same group to be transparent
			Mark the top rectangle in each group with "+"		*/
		var last = "";
		d3.select("#graph").selectAll("rect")
			.each(function(d, i) {
				// d.same_with: id of the top rectangle, "" if none
				if (d.same_with != "") {
					hiddenBars++;
					d3.select(this)
						.attr("opacity", 0.3)					// transparent
						.attr("pointer-events", "none");	// cannot be clicked

					// if this is the top rectangle in the group...
					if (d.same_with != last) {
						last = d.same_with;
						// mark this rectangle as expandable
						d3.select("#" + d.same_with).data()[0]['canExpand'] = true;
						d3.select("#" + d.same_with).select(function() {
							return this.parentNode;
						}).append("text")
							.attr("class", "plus")
							.text("+")
							.attr("font-size", 12)
							.attr("pointer-events", "none")
							.attr("x", 2)
							.attr("y", 10);
					}
				}
				d['barsHidden'] = hiddenBars;
				// Positions this rectangle
				d3.select(this.parentNode)
					.attr("transform", "translate(" + xScale(d.start) + "," + (yScale(d.download_group - d.barsHidden) + margin.top) + ")");
			})
			/* 	On hover, shows bar label and dep lines;
				brightens and lengthens bars 					*/
			.on("mouseover", function(d) {
				if (!dragging) {
					var graph = document.getElementById("graph");
					var matrix = this.getTransformToElement(graph);

					// positions label and makes visible	
					d3.select("#descr").transition()
						.duration(200)
						.style("opacity", 1);
					d3.select("#descr")
						.text(d.event + " " + d.info)
						.style("left", (matrix.e + 5) + "px")
	                    .style("top", (matrix.f + 10) + "px");
	                // brightens bar on hover
	                d3.select(this).style("fill", d3.rgb(d3.select(this).attr("fill")).brighter());
	                // show this bar's dep lines
	                me._colorLines(true, d);
	                // lengthen any short bars on this row
	                me._miniExpand(true, d3.select(this).data()[0].download_group);
				}
			})
			.on("mouseout", function(d) {
				if (!dragging) {
					// hide description tooltip
					d3.select("#descr").transition()
						.duration(1000)
						.style("opacity", 0);
					// return to normal color
					if (!d3.select(this).classed("selected"))
	                	d3.select(this).style("fill", d3.rgb(d3.select(this).attr("fill")));
	                // hide dep lines, reset critical path setting
	                me._colorLines(false, d);
	                me.toggleCriticalPath(false);
	            }
			})
			// Expand bar group and show tooltip on click
			.on("click", function(d) {
				// writes tooltip info
				me._tooltip(d); 
				if (d.canExpand != null)
					d.canExpand = me._expand(this, d.canExpand);
				if ((d3.select("#asynch").classed("btn-primary") || d3.select("#injs").classed("btn-primary"))
				 && (d.info == "javascript:text" || d.info == "application:javascript")) {
					document.getElementById("check_" + d.id).checked = !(document.getElementById("check_" + d.id).checked);
					me._onCheckChange(d, document.getElementById("check_" + d.id), this);
				}
			});

		var drag = d3.behavior.drag()
			.on("dragstart", function() {
				if (edit)
					dragging = true;
			})
			.on("drag", function(d) {
				if (edit) {
					d.end += xScale.invert(d3.event.dx);
					if (d.end - d.start < 5)
						d.end = d.start + 5;
					d.len = d.end - d.start;
					d3.select(this).attr("width", function() {
						return xScale(d.end - d.start);
					});
				}
			})
			.on("dragend", function(d) {
				if (edit) {
					dragging = false;
					d3.select(this).style("fill", d3.rgb(d3.select(this).attr("fill")));

					for (var j = 0; j < d.next.length; j++) {
						var deps = d3.select("#" + d.next[j]).data()[0].prev;
						var index = -1;
			    		for (var k = 0; k < deps.length && index == -1; k++)
			        		if (deps[k].a1 == d.id)
			        			index = k;
			        	if (deps[index].time != -1)
			        		deps[index].time = (deps[index].oTime / d.oLen) * d.len;
					}
					me._adjustEnd(true);
				}
			});
		d3.select("#graph").selectAll("rect").call(drag);
	},

	/* draw a shadow of every bar, runs only once				*/
	_drawShadow: function() {
		d3.select("#graph").selectAll("rect")
			.each(function(d) {
				// draws below the other bars
				d3.select("#graph").insert("g", "#lineLabel")
					.attr("transform", "translate(" + xScale(d.oStart) + "," + 
						(yScale(d.download_group - d.barsHidden) + margin.top) + ")")
					.append("rect")
						.attr("class", "shadow")
						.attr("width", xScale(d.oEnd - d.oStart))
						.attr("height", barHeight)
						.attr("fill", "lightgray")
						.attr("opacity", 0.6)
						.attr("rx", barCurve)
						.attr("ry", barCurve)
						.attr("display", "none")
						// start, end, height
						.data([{st: d.oStart, en: d.oEnd, h: d.download_group - d.barsHidden}]);
			});
	},
	
	/*	draws loadline #'trail' with 'color', runs only once	*/
	_drawLoadLines: function(trail, color) {
		d3.select("#graph").append("text")
			.text(Math.round(loadTime).toLocaleString())
			.attr("id", "lineLabel" + trail)
			.attr("fill", color)
			.attr("font-size", "10px")
			.attr("x", w - 13)
			.attr("dy", "-0.3em");
		d3.select("#graph").append("line")
			.attr("id", "loadLine" + trail)
			.style("stroke", color)
			.attr("x1", w)
			.attr("x2", w)
			.attr("y1", 0)
			.attr("y2", h + margin.top + margin.bottom);
	},

	/* 	draws axis and grid, ticks: # of ticks					*/
	_drawAxis: function(ticks) {
		//define x-axis
		var xAxis = d3.svg.axis()
			.scale(xScale)
			.orient("top")
			.ticks(ticks)
			.tickSize(-h - margin.top - margin.bottom, 0);

		d3.select("#mySVG").insert("text", "#graph")
			.attr("x", 25)
			.attr("y", 12)
			.text("(ms)")
			.style("fill", "darkgray")
			.style("font-size", "10px");
		//draw x-axis beneath the bars
		d3.select("#graph").insert("g", "#lineLabel")
			.attr("id", "topAxis")
			.call(xAxis);
	},

	/* 	draws new dependency lines if create, then transitions		*/
	_drawLines: function(create) {
		d3.select("#graph").selectAll("rect")
			.each(function(d) {
				// for non-shadow bars
				if (d.id)
					for (var j = 0; j < d.prev.length; j++) {
						var depData = d3.select("#" + d.prev[j].a1).data()[0];	// other bar's data
						var yVal = yScale(d.download_group - d.barsHidden) + 
							margin.top + (barHeight / 2);						// vertical center of this bar
						var ptTime;												// relative time of dependency to the bar
						if (d.prev[j].time == -1)
							ptTime = xScale(depData.end - depData.start);	// end of bar
						else
							ptTime = xScale(d.prev[j].time);					

						// if no lines are drawn yet:
						if (create) {
							d3.select("#graph").append("line")		// Horizontal line
								.attr("class", "dependLines")
								.attr("id", d.prev[j].id);
							d3.select("#graph").append("line")		// Vertical line
								.attr("class", "dependLines")
								.attr("id", d.prev[j].id + "v");
						}

						d3.select("#" + d.prev[j].id).transition()			// Hori.
							.duration(ttime)
							.attr("x1", xScale(depData.start) + ptTime + depData.offset)	// offset: see _miniExpand
							.attr("x2", xScale(d.start) + d.offset)
							.attr("y1", yVal)
							.attr("y2", yVal);
						d3.select("#" + d.prev[j].id + "v").transition()	// Vert.
							.duration(ttime)
							.attr("x1", xScale(depData.start) + ptTime + depData.offset)
							.attr("x2", xScale(depData.start) + ptTime + depData.offset)
							.attr("y1", yScale(depData.download_group - depData.barsHidden) + 
								margin.top + (barHeight / 2))
							.attr("y2", yVal);
					}
			})
	},

	/* 	colors prev lines blue, next lines orange 				*/
	_colorLines: function(mouseOn, d) {
		for (var j = 0; j < d.prev.length; j++) {
        	this._setColor(mouseOn, "#" + util.getLineColor("from"), d.prev[j].id);
        	this._setColor(mouseOn, "#" + util.getLineColor("from"), d.prev[j].id + "v");
        }
        for (var j = 0; j < d.next.length; j++) {
        	var arr = d3.select("#" + d.next[j]).data()[0].prev;
        	var lineId = "";
        	// finds the right depLine
    		for (var k = 0; k < arr.length && lineId == ""; k++)
        		if (arr[k].a1 == d.id)
        			lineId = arr[k].id;
			this._setColor(mouseOn, "#" + util.getLineColor("to"), lineId);
			this._setColor(mouseOn, "#" + util.getLineColor("to"), lineId + "v");
        }
	},

	/* 	helper function; colors lines							*/
	_setColor: function(active, color, lineId) {
		var size, state;
		if (active) {
			size = "2px";
			state = "visible";
			// bring line to the front
    		document.getElementById("graph").appendChild(document.getElementById(lineId));
		} else {
			color = "black";
			size = "1px";
			state = "hidden";
		}

    	d3.select("#" + lineId)
    		.style("stroke-width", size)
    		.style("stroke", color);
    	// if dep lines are not toggled all on, changes visibility
    	if (!allVisible)
    		d3.select("#" + lineId).style("visibility", state);
	},

	/* 	recursively finds the critical path, runs only once,
		arr: list of bar's dependencies 						*/
	_findCriticalPath: function(arr) {
		if (arr.length > 0) {
			var most = 0;	// latest end time
			var index = 0;
			// finds latest dependent bar
			for (var i = 0; i < arr.length; i++) {
				var temp = d3.select("#" + arr[i].a1).data()[0].end;
				if (temp > most) {
					most = temp;
					index = i;
				}
			}
			// adds bar and lines to the critical path list
			critPath.push(d3.select("#" + arr[index].a1));
			critPath.push(d3.select("#" + arr[index].id));
			critPath.push(d3.select("#" + arr[index].id + "v"));
			// recurses
			this._findCriticalPath(d3.select("#" + arr[index].a1).data()[0].prev);
		}
	},

	/* shows, repositions, and writes tooltip info using d data	*/
	_tooltip: function(d) {
		var html = "";		// the html text inside the tooltip
		try {
			var url = "<a href='" + d.url + "'>";
			if (d.url.length > 100)
				url += d.url.substr(0, 100) + "...</a>";
			else
				url += d.url + "</a>";

			if (d.event == "download") {
				d3.select("#infoBox").select("a")
					.html("<b>[" + d.event + "]</b> " + d.info + " &darr;");
				html += "<li><b>URL:</b> " + url + "</li>";
				html += "<li><b>Size:</b> " + util.getKBfromBytes(d.bytes, 2) + "KB</li>";
			}
			else if (d.event == "parse")
			{
				d3.select("#infoBox").select("a")
					.html("<b>Selected Bar: [" + d.event + "] " + d.info + " &darr;</b>");
				html += "<li><b>URL:</b> " + url + "</li>";
			}
			else if (d.info == "execScript" || d.info == "recalcStyle")
			{
				d3.select("#infoBox").select("a")
					.html("<b>Selected Bar: [" + d.info + "] " + d.urlRecalcStyle + " &darr;</b>");
			} else {
				d3.select("#infoBox").select("a")
					.html("<b>Selected Bar: [Info] " + d.info + " &darr;</b>");
					html += "<li><b>URL:</b> " + url + "</li>";
			}

			html += "<li><b>Length:</b> " + Math.round(d.len * 1000) / 1000.0 + "</li>"
			html += "<li><b>% / Total Time:</b> " + Math.round(d.len / loadTime * 10000) / 100.0 + "%</li>";
			if (d.canExpand != null)
				html += "<li><b>Merged Bars:</b> " + d.num + "<li>";
		} catch(err) {
			console.log(err);
			html = "Data Unavailable";
		}
		d3.select("#tooltip")
			.html(html);
	},

	/* 	vertically expands/condenses merged bars and dep lines	*/
	_expand: function(parentRect, expand) {
		d3.select(parentRect).attr("class", "bars");
		var parent = d3.select(parentRect).attr("id");
		var bars = 0;		// count of expanding/condensing bars

		d3.select("#graph").selectAll("rect").each(function(d) {
			// only non-shadow bars
			if (d.id) {
				if (d.same_with == parent) {
					d3.select(this)
						.attr("opacity", function() {
							if (!expand)
								return 0.3;
							else
								return +expand;		// +expand: 0 || 1
						}).attr("pointer-events", function() {
							if (!expand) {
								bars--;
								return "none";
							} else {
								bars++;
								return "visiblePainted";
							}
						});
				}
				d.barsHidden -= bars;

				// repositions the bars
				d3.select(this.parentNode).transition().duration(ttime)
					.attr("transform", "translate(" + (xScale(d.start) + d.offset) + 
						"," + (yScale(d.download_group - d.barsHidden) + margin.top) + ")");
			}
		})
		// repositions dep lines
		this._drawLines(false);
		// resizes the svg
		this.init();
		// returns that this group is now expanded or condensed
		return !expand;
	},

	/* 	shows horizontally expanded bars per barNum row			*/
	_miniExpand: function(show, barNum) {
		var offset = 0;
		d3.select("#graph").selectAll("rect").each(function(d) {
			if (d.id) {
				d3.select(this).transition().duration(ttime)
					.attr("width", function() {
						if (show && d.download_group == barNum) {
							d.offset = offset;
							// if short
							if (xScale(d.end - d.start) < minBar) {
								offset += (minBar - xScale(d.end - d.start));
								return minBar;
							}
						} else
							d.offset = 0;
						return xScale(d.end - d.start);
					})
					// reposition bars
					.select(function() {
						return this.parentNode;
					}).attr("transform", "translate(" + (xScale(d.start) + d.offset) + "," + 
							(yScale(d.download_group - d.barsHidden) + margin.top) + ")");
			}
		});
		// repositions dep lines
		this._drawLines(false);
	},

	/*	toggles show/hide all dependency lines 					*/
	showAllLines: function(){
		allVisible = !allVisible;
		// highlights button
		document.getElementById("dep_button").classList.toggle("btn-primary");

		d3.selectAll(".dependLines")
			.style("visibility", function() {
				if (allVisible) {
					document.getElementById("dep_button").innerHTML = "<i class='icon-eye-close'></i> Hide all dependency lines";
					return "visible";
				} else {
					document.getElementById("dep_button").innerHTML = "<i class='icon-eye-open'></i> Show all dependency lines";
					return "hidden";
				}
			});
		// resets crit path display
		this.toggleCriticalPath(false);
	},

	/* 	if !toggle, resets crit path, else toggles display		*/
	toggleCriticalPath: function(toggle) {
		if (toggle) {
			critOn = !critOn;
			// highlights button
			document.getElementById("crit_button").classList.toggle("btn-primary");
			if (critOn)
				document.getElementById("crit_button").innerHTML = "<i class='icon-eye-close'></i> Hide critical path";
			else	
				document.getElementById("crit_button").innerHTML = "<i class='icon-eye-open'></i> Show critical path";
		}		
		var cls = "bars";
		if (critOn)
			cls = "critBars";	// sets rectangle class style

		for (var i = 0; i < critPath.length; i++) {
			// sets lines red
			if (critPath[i].attr("class") == "dependLines")
				this._setColor(critOn, "red", critPath[i].attr("id"));
			// sets top merged rectangle red
			else if (critPath[i].data()[0].same_with != "" && d3.select("#" + 
				critPath[i].data()[0].same_with).data()[0].canExpand) {
				if (d3.select("#" + critPath[i].data()[0].same_with).classed("selected")) {
					d3.select("#" + critPath[i].data()[0].same_with).attr("class", cls + " selected");
					critPath[i].attr("class", cls + " selected");				
				} else {
					d3.select("#" + critPath[i].data()[0].same_with).attr("class", cls);
					critPath[i].attr("class", cls);				
				}
			} else 	// sets rectangles red
				critPath[i].attr("class", function() {
					if (critPath[i].classed("selected"))
						return cls + " selected";
					else
						return cls;
				}, true);
		}
	},

	/* 	horizontally zooms the graph by m
		min w: 500, max w: 2000			 						*/
	zoom: function(m) {
		if ((w > 500 && w < 2000) || (w <= 500 && m > 0) || (w >= 2000 && m < 0)) {
			w += 250 * m;		// increments by 250

		    this.init();		// adjust svg size

			// if larger than original size
			if (loadTime > this.dataset[this.dataset.length - 1].oEnd)
				// adjust axis size
				xScale.domain([0, loadTime])
				    .range([0, w]);
			else
				// maintain original axis size
				xScale.domain([0, this.dataset[this.dataset.length - 1].oEnd])
				    .range([0, w]);

		    d3.select("#topAxis").remove();
		    if (w > 1500)
		    	this._drawAxis(15);		// _drawAxis(number of ticks)
		    else
		    	this._drawAxis(10);

		    // reposition the loadtime lines and labels
		    d3.select("#lineLabel").transition().duration(ttime)
		    	.attr("x", xScale(loadTime) - 13)
		    	.text(Math.round(loadTime).toLocaleString());
		    d3.select("#loadLine").transition().duration(ttime)
		    	.attr("x1", xScale(loadTime))
				.attr("x2", xScale(loadTime));
			d3.select("#lineLabel0").transition().duration(ttime)
				.attr("x", xScale(this.dataset[this.dataset.length - 1].oEnd) - 13);
			d3.select("#loadLine0").transition().duration(ttime)
		    	.attr("x1", xScale(this.dataset[this.dataset.length - 1].oEnd))
				.attr("x2", xScale(this.dataset[this.dataset.length - 1].oEnd));

			// adjust rectangles
		    d3.selectAll("rect").transition().duration(ttime)
				.attr("width", function(d) {
					if (d.id) {
						d.offset = 0;
						return xScale(d.end - d.start);
					} else
						return xScale(d.en - d.st);
				})
				.select(function() {
					return this.parentNode;
				})
					.attr("transform", function(d) {
						if (d.id)
							return "translate(" + xScale(d.start) + "," + (yScale(d.download_group - 
								d.barsHidden) + margin.top) + ")";
						else
							return "translate(" + xScale(d.st) + "," + (yScale(d.h) + margin.top) + ")";
					});
			// adjust lines
			this._drawLines(false);
		}
	},

	/*	redraws bars and lines to selected speed multipliers
		inp: ref to slider, type: 'cpuVal' or 'netVal'			*/
	drawToSpeed: function() {
		var slideScale = [0.25, 0.5, 1, 2, 4];	// multiply scale
		var val;								// slider value
		var cpuV = +(document.getElementById("CPU").value);
		var netV = +(document.getElementById("Network").value);
		// write current speed
		d3.select("#cpuVal").text(slideScale[cpuV] + "x");
		d3.select("#netVal").text(slideScale[netV] + "x");
		d3.select("#graph").selectAll("rect")
			.each(function(d) {
				// show shadow bars if not original speed
				if (d3.select(this).attr("class") == "shadow")
					d3.select(this).attr("display", function() {
						if (cpuV != 2 || netV != 2 || edit)
							return "inline";
						else
							return "none";
					});
				else {		// find each rectangles' (new) width
					if (d.event == "")
						val = cpuV;
					else
						val = netV;
					
					d.len = (d.oEnd - d.oStart) * (1 / slideScale[val]);
					d.end = d.start + d.len;
					for (var j = 0; j < d.next.length; j++) {
			        	var arr = d3.select("#" + d.next[j]).data()[0].prev;
			        	var index = -1;
			        	// finds the right depLine
			    		for (var k = 0; k < arr.length && index == -1; k++)
			        		if (arr[k].a1 == d.id)
			        			index = k;
			        	// adjust dep lines' time
			        	if (arr[index].time != -1)
		        			arr[index].time = arr[index].oTime * (1 / slideScale[val]);
					}
				}

			})	// transition to new width
			.transition().duration(ttime)
				.attr("width", function(d) {
					if (d.id) {
						d.offset = 0;
						return xScale(d.end - d.start);
					} else
						return xScale(d.en - d.st);
				});
		this._adjustEnd(false);
	},

	/*	helper function; redraws the axis if greater than
		original values, critPath, moves bars and loadlines 	*/
	_adjustEnd: function(reCrit) {
		this._moveBars(this.dataset[0].id, []);
		d3.select("#graph").selectAll("rect")
		.select(function() {
			return this.parentNode;
		}).transition()
			.duration(ttime)
			.attr("transform", function(d) {
				if (d.id)
					return "translate(" + xScale(d.start) + "," + (yScale(d.download_group - 
						d.barsHidden) + margin.top) + ")";
				else
					return d3.select(this).attr("transform");
			});
		var re;
		if (critOn) {
			this.toggleCriticalPath(true);
			re = true;
		}
		// find new loadtime
		loadTime = this.dataset[this.dataset.length - 1].end;
		if (!reCrit)
			critPath = oCritPath.slice(0);
		else {
			loadTime = 0;
			var tempid;
			for (var i = 0; i < this.dataset.length; i++) {
				if (this.dataset[i].end > loadTime) {
					loadTime = this.dataset[i].end;
					tempid = i;
				}
			}
			critPath = [];
			critPath.push(d3.select("#" + this.dataset[tempid].id));
			this._findCriticalPath(this.dataset[tempid].prev);
		}
		// adjust scale and axis if larger than original
		if (loadTime > this.dataset[this.dataset.length - 1].oEnd) {
			w = xScale(loadTime);
			xScale.domain([0, loadTime])
		    	.range([0, w]);
		    d3.select("#topAxis").remove();
		    if (w > 1000)
	   			this._drawAxis(15);		// _drawAxis(# of ticks)
	   		else
	   			this._drawAxis(10);				
		} else {
			w = xScale(this.dataset[this.dataset.length - 1].oEnd)
			xScale.domain([0, this.dataset[this.dataset.length - 1].oEnd])
		    	.range([0, w]);
		    d3.select("#topAxis").remove();
	   		this._drawAxis(10);
		}
		// move loadline and label
		d3.select("#lineLabel").transition().duration(ttime)
			.attr("x", xScale(loadTime) - 13)
			.text(Math.round(loadTime).toLocaleString());
	    d3.select("#loadLine").transition().duration(ttime)
	    	.attr("x1", xScale(loadTime))
			.attr("x2", xScale(loadTime));
		
		var plt = Math.round(this.dataset[this.dataset.length - 1].oEnd / loadTime * 10000) / 100.0;
		d3.select("#infoBox").select("span")
			.html("<b>" + plt + "%</b> of original speed");

		this._drawLines(false);	// adjusts dep lines
		this.init();
		if (re)
			this.toggleCriticalPath(true);
	},

	/*	recursively moves this bar and the bars dependent on it */
	_moveBars: function(bar, done) {
		var d = d3.select("#" + bar).data()[0];
		var latest = 0;		// the latest dep time
		var redo = false;
		// if this bar is dependent on other bars, move it
		if (d.prev.length > 0) {
			// find the latest bar
			for (var i = 0; i < d.prev.length; i++) {
				if (done.indexOf(d.prev[i].a1) == -1) {
					redo = true;
					break;
				}
				var temp;
				var prevRect = d3.select("#" + d.prev[i].a1).data()[0];
				if (d.prev[i].oTime == -1)
					temp = prevRect.start + prevRect.len;
				else
					temp = prevRect.start + d.prev[i].time;
				if (temp > latest) {
					latest = temp;
				}
			}
		}
		if (!redo) {
			d.start = latest;
			d.end = d.start + d.len;
			done.push(d.id);
			// recurse with all bars that are dependent on this bar
			for (var i = 0; i < d.next.length; i++) {
				if (done.indexOf(d.next[i]) == -1)
					this._moveBars(d.next[i], done);
			}
		}
	},

	/*	toggles info bar containing source code -- INCOMPLETE	*/
	/*mapSource: function(button) {
		// highlights button
		button.classList.toggle("btn-primary");
		// Shows bottom bar and shortens pg height when on
		if (button.classList.contains("btn-primary")) {
			d3.select("#bottomBar").style("display", "block");
			d3.select("#dragLine").style("display", "block");
			d3.select("#pg").style("height", function() {
				return parseInt(d3.select(this).style("height")) - 
					(parseInt(d3.select("#bottomBar").style("height")) + 20);
			});
			document.getElementById("bottomBar").innerHTML = htmlTxt;
		} else {
			d3.select("#bottomBar").style("display", "none");
			d3.select("#dragLine").style("display", "none");
			d3.select("#pg").style("height", "100%");
			document.getElementById("bottomBar").innerHTML = "HTML file could not be found";
		}
	},*/

	/*	toggles ability to edit bars by dragging				*/
	detailEdit: function(button) {
		// highlights button
		button.classList.toggle("btn-primary");
		edit = !edit;
		if (edit) {
			// show shadow bars
			d3.select("#graph").selectAll("rect").attr("display", "inline");
			button.innerHTML = "<i class='icon-remove-circle'></i> Reset Edits";
			// disable speed adjustment sliders
			d3.select("#CPU").attr("disabled", "disabled");
			d3.select("#Network").attr("disabled", "disabled");
		} else {	// resets all detail edits
			this.drawToSpeed();
			button.innerHTML = "<i class='icon-wrench'></i> Detail Edit Mode";
			d3.select("#CPU").attr("disabled", null);
			d3.select("#Network").attr("disabled", null);
		}
	},

	/*	loads source code, runs prettyprint, sets up dragLine 	*/
	/*_setSource: function() {
		d3.select("#pg").append("div")
			.attr("id", "dragLine");
		var drag = d3.behavior.drag()
			.on("drag", function() {
				d3.select("#dragLine").style("top", d3.event.y + "px");
			})
			.on("dragend", function() {
				d3.select("#bottomBar").style("top", function() {
					return d3.select("#dragLine").style("top");
				});
				d3.select("#pg").style("height", function() {
					return parseInt(d3.select("#bottomBar").style("top")) - 20;
				});
			});
		d3.select("#dragLine").call(drag);

		d3.text(this.htmlSource, function(error, htmlText) {
			if (error)
				console.log(error);
			if (htmlText != null) {
				htmlText = htmlText.replace(/^\s*\n/gm, "");
				document.getElementById("bottomBar")
					.innerHTML = "<xmp class='prettyprint linenums lang-html'>" + htmlText + "</xmp>";
			} else
				htmlTxt = "HTML file could not be found";
			var saveHTML = function() {
				if (htmlText != null && htmlTxt != "HTML file could not be found") {
					htmlTxt = document.getElementById("bottomBar").innerHTML;
					htmlTxt = htmlTxt.split('<ol class')[1];
					htmlTxt = htmlTxt.split("</xmp")[0];
					htmlTxt = "<pre class='prettyprinted'><ol class" + htmlTxt + "</pre>";
					document.getElementById("bottomBar").innerHTML = "HTML file could not be found";
				}
			}
			prettyPrint(saveHTML);
		});
	},*/

	inlineJS: function(button) {
		button.classList.toggle("btn-primary");
		if (d3.select(button).classed("btn-primary")) {
			button.innerHTML = "<i class='icon-wrench'></i> Reset Changes";
			// show shadow bars
			d3.select("#graph").selectAll("rect").attr("display", "inline");
			d3.select("#checks").style("display", "block");
			var lis = document.getElementById("checks").getElementsByTagName("input");
			for (var i = 0; i < lis.length; i++) {
				lis[i].checked=true;
			}
			d3.select("#graph").selectAll("rect").each(function(d) {
				if (d.info == "javascript:text" || d.info == "application:javascript") {
					d3.select(this).classed("selected", true);
					d.len = 0;
					d.end = d.start;
					d3.select(this).transition().duration(ttime)
						.attr("width", xScale(d.len));
				}
			});
			this._adjustEnd(true);
		} else {
			button.innerHTML = "<i class='icon-wrench'></i> Make Inline";
			d3.selectAll(".selected").each(function(d) {
				d.len = d.oLen;
				d.start = d.oStart;
				d.end = d.oEnd;
				d3.select(this).transition().duration(ttime)
					.attr("width", xScale(d.len));
			});
			if (d3.select("#asynch").classed("btn-primary", false)) {
				d3.selectAll(".selected").each(function() {
					d3.select(this).classed("selected", false)
				});
				d3.select("#checks").style("display", "none");
			}

			this.drawToSpeed();
		}
	},

	_makeList: function() {
		var canv = this;
		var list = d3.select("#checks");
		list.style("display", "none");
		d3.select("#graph").selectAll("rect").each(function(d) {
			if (d.info == "javascript:text" || d.info == "application:javascript") {
				var thisrect = this;
				list.append("span")
					.text(function() {
						var str = d.url;
						if (d.url.length > 53) {
							str = d.url.substr(0, 30);
							str += "...";
							str += d.url.substr(d.url.length-20);
						}
						return str + " : ";
					}).select(function() {
						return this.parentNode;
					}).append("input").attr("type", "checkbox")
					.attr("id", "check_" + d.id)
					.attr("checked", "checked")
					.on("change", function() {
						canv._onCheckChange(d, this, thisrect);
					});
				list.append("br");
			}
		});
	},

	_onCheckChange: function(d, inp, thisrect) {
		var asynch = d3.select("#asynch").classed("btn-primary");
		var injs = d3.select("#injs").classed("btn-primary");
		if (inp.checked) {
			d3.select(thisrect).classed("selected", true);
			if (asynch) {
				d3.select(thisrect).style("fill", d3.rgb(d3.select(thisrect).attr("fill")).brighter());
				if (d.next.length > 0 && d.download_group == d3.select("#"+d.next[0]).data()[0].download_group) {
					d3.select("#"+d.next[0]).classed("selected", true);
					d3.select("#"+d.next[0]).style("fill", d3.rgb(d3.select("#"+d.next[0]).attr("fill")).brighter());
					this._dropDep(d3.select("#"+d.next[0]).data()[0]);
				}
			}
			if (injs) {
				d.len = 0;
				d.end = d.start;
				d3.select(thisrect).transition().duration(ttime)
					.attr("width", xScale(d.len));
			}
		} else {
			d3.select(thisrect).classed("selected", false);
			if (asynch) {
				d3.select(thisrect).style("fill", d3.rgb(d3.select(thisrect).attr("fill")));
				if (d.next.length > 0 && d.download_group == d3.select("#"+d.next[0]).data()[0].download_group) {
					d3.select("#"+d.next[0]).classed("selected", false);
					d3.select("#"+d.next[0]).style("fill", d3.rgb(d3.select("#"+d.next[0]).attr("fill")));
				}
				for (var i = 0; i < jsdeps.length; i++) {
					//console.log("in for " + d.id);
					//console.log("checking " + jsdeps[i].a1);
					if (jsdeps[i].a1==d.next[0]) {
						//console.log("return to normal: " + d3.select("#" + d.next[0]).data()[0]);
						d3.select("#" + d.next[0]).data()[0].next.push(jsdeps[i].a2);
						d3.select("#" + jsdeps[i].a2).data()[0].prev.push(jsdeps[i]);
						jsdeps.splice(i, 1);
					}
				}
			}
			if (injs) {
				d.len = d.oLen;
				d.start = d.oStart;
				d.end = d.oEnd;
				d3.select(thisrect).transition().duration(ttime)
					.attr("width", xScale(d.len));
			}
		}
		this._adjustEnd(true);
	},

	asynchJS: function(button) {
		var canv = this;
		button.classList.toggle("btn-primary");
		if (d3.select(button).classed("btn-primary")) {
			button.innerHTML = "<i class='icon-wrench'></i> Reset Changes";
			// show shadow bars
			d3.select("#graph").selectAll("rect").attr("display", "inline");
			d3.select("#checks").style("display", "block");
			var lis = document.getElementById("checks").getElementsByTagName("input");
			for (var i = 0; i < lis.length; i++) {
				lis[i].checked=true;
			}

			d3.select("#graph").selectAll("rect").each(function(d) {
				if (d.info == "javascript:text" || d.info == "application:javascript") {
					d3.select(this).classed("selected", true);
					d3.select(this).style("fill", d3.rgb(d3.select(this).attr("fill")).brighter());
					if (d.next.length > 0 && d.download_group == d3.select("#"+d.next[0]).data()[0].download_group) {
						d3.select("#"+d.next[0]).classed("selected", true);
						d3.select("#"+d.next[0]).style("fill", d3.rgb(d3.select("#"+d.next[0]).attr("fill")).brighter());
						canv._dropDep(d3.select("#"+d.next[0]).data()[0]);
					}
				}
			});
			canv._adjustEnd(true);
		} else {
			button.innerHTML = "<i class='icon-wrench'></i> Asynchronize";
			if (d3.select("#injs").classed("btn-primary", false)) {
				d3.selectAll(".selected").each(function(d) {
					d3.select(this).classed("selected", false);
					d3.select(this).style("fill", d3.rgb(d3.select(this).attr("fill")));
				});
				d3.select("#checks").style("display", "none");
			}
			for (var i = 0; i < jsdeps.length; i++) {
				d3.select("#" + jsdeps[i].a1).data()[0].next.push(jsdeps[i].a2);
				d3.select("#" + jsdeps[i].a2).data()[0].prev.push(jsdeps[i]);
			}
			jsdeps = [];

			this.drawToSpeed();
		}
	},

	// remove dependency, save in jsdeps
	_dropDep: function(d) {
		for (var i = 0; i < d.next.length; i++) {
			for (var j = 0; j < d3.select("#" + d.next[i]).data()[0].prev.length; j++) {
				if (d3.select("#" + d.next[i]).data()[0].prev[j].a1 == d.id) {
					jsdeps.push(d3.select("#" + d.next[i]).data()[0].prev[j]);
					d3.select("#" + d.next[i]).data()[0].prev.splice(j, 1);
					if (d3.select("#" + d.next[i]).data()[0].prev.length == 0) {
						d3.select("#" + d.next[i]).data()[0].start = 0;
						d3.select("#" + d.next[i]).data()[0].end = d3.select("#" + d.next[i]).data()[0].len;
					}
				}
			}
		}
		d.next = [];
	},

	/* draws the entire graph									*/
	draw: function() {		
		// bar label, starts hidden
		d3.select("#parent").insert("div", "svg")
			.attr("id", "descr")
			.style("opacity", 0);
		d3.select("#mySVG").append("g")		// offset by margins
			.attr("id", "graph")
			.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		this._drawLoadLines("0", "gray");
		this._drawLoadLines("", "red");
		this._drawAxis(10);
		this._drawBars();
		this._drawShadow();
		this._drawLines(true);

		critPath.push(d3.select("#" + this.dataset[this.dataset.length - 1].id));
		this._findCriticalPath(this.dataset[this.dataset.length - 1].prev);
		oCritPath = critPath.slice(0);

		this.init();
		this._makeList();
		//this._setSource();
	}
}
