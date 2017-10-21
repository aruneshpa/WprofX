/*
 * Utility functions
 */

// Initialize util object
var util = {};

// The palette that defines the color of blocks
util.paletteBlock = {
  "": "9999dd",
  "h": "bbbbff",
  "none": "fff",
  "depended": "eee",
  "download": "EB5BC0",
  "download_h": "EB5BC0",
  "download_image": "c79efa",
  "download_image_h": "c79efa",
  "download_javascript": "E8AE61",
  "download_javascript_h": "E8AE61",
  "download_css": "8AE887",
  "download_css_h": "8AE887",
  "download_html": "A8C5F7",
  "download_html_h": "A8C5F7",
  "parse": "cd5c5c",
  "parse_h": "dd6c6c",
  "execute": "8a2be2",
  "execute_h": "9a3bf2",
  "evaljs": "C9780E",
  "evalcss": "13BD0D",
  "evalhtml": "2758B0",
  "null": "D93038",
};

// The palette that defines the color of lines
util.paletteLine = {
  "": "000000",
  "h": "555555",
  "axis": "bbb",
  "loadTime": "f00",
  "to": "FFA500",
  "from": "87CEEB",
};

/*
 * Gets the color of each block depends on the type and infro are given.
 * @param type - the type of the block have
 * @param info -  the information about the block
 * @return the color of the block
 */
util.getBlockColor = function(type, info) {
  if (type == "download"){	
	var a = info.split(":");
	if (a[0] == "image" || a[0] == "javascript" || a[0] == "css" || a[0] == "html")
		type += "_" + a[0];
	if (a[1] == "javascript")
		type += "_" + a[1];
	return util.paletteBlock[type];
  }else{
	return util.paletteBlock[info];
  }
}

/*
 * Gets the color of each line with given type
 * @param type - the type of the line
 * @return the color of the line
 */
util.getLineColor = function(type) {
  return util.paletteLine[type];
}

/*
 * Get KB from ginve bytes
 * @param bytes - KB needs to convert
 * @param n - number of decimal are needed
 * @return the bytes representation of KB
 */
util.getKBfromBytes = function(bytes, n) {
  m = 1;
  for (var i = 0; i < n; ++i)
    m *= 10;
  return Math.round(bytes / 1024 * m) / m;
}

/*
 * Converts a from a decimal number into a percentage number
 * @param a - number that needs to convert to percentage
 * @param n - number of decimal are needed
 * @return the percentage representation
 */
util.getPercentage = function(a, n) {
  m = 1;
  for (var i = 0; i < n; ++i)
    m *= 10;
  return Math.round(a * 100 * m) / m;
}

/*
 * Get the particular length of the url
 * @param url - the url needs to be trimed
 * @param allowed_strlen - the maximum length of url
 * @return the trimed url
 */
util.trimUrl = function(url, allowed_strlen) {
  if (!url)
    return "";
  if (url.length <= allowed_strlen)
    return url;

  var n = url.length;
  return url.substring(0, canvas.allowedStrLen - 13) + "..." + url.substring(n - 10, n);
}

/*
 * Get the domain in the url if there is a domain
 * @param url - the url needs to be modified
 * @return the domain of the url
 */
util.domain = function(url) {
  var a = url.split("/");
  if (a.length > 2)
    return a[2];
  return url;
}

/*
 * Gets the parameter in the url if there is a parameter
 * @param url - the url that need to be checked
 * @return the parameter of the url if there is one
 * @return empty string of there is no parameter
 */
util.param = function(url) {
  var a = url.split("?");
  if (a.length > 1)
    return "param";
  return "";
}

/*
 * Returns a message giving the estimate time to analyze the file
 * @param fileSize - the size of the file to be analyzed
 * @return a message giving the estimate time in minutes, accurate to
 *  0.5 minutes for small sizes, and no message for times close to 0.
 */
util.estimateTime = function(fileSize) {
  var size = parseInt(fileSize);
  if (size < 0)
    return "Error!<br/>Cannot estimate analysis time.";
  else if (size < 400)
    return "";
  else {
    // (6*10^-5)x^2 + 0.0256x - 6.6881
    var time = (6 * Math.pow(10, -5) * Math.pow(size, 2)) + (0.0256 * size) - 6.6881;
    var est = 0;
    if (size < 2500)
      est = (Math.round(time / 30) + 1) / 2;
    else
      est = Math.round(time / 60) + 1;
    return "Est. time:<br>" + est + " min<br>Check back then!";
  }
}

/*
 * Shows/hides the live search results for the given search string
 * @param str - the search string to find matching graphs with
 */
util.search = function(str) {
  if (str.length==0) {
    document.getElementById("livesearch").style.display="none";
    return;
  }
  if (window.XMLHttpRequest) {
    xmlhttp=new XMLHttpRequest();
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      document.getElementById("livesearch").style.display="inline-block";
      document.getElementById("livesearch").innerHTML="<ul>"+xmlhttp.responseText+"</ul>";
    }
  }
  xmlhttp.open("GET","livesearch.php?s="+str,true);
  xmlhttp.send();
}
