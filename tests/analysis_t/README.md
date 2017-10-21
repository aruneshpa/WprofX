 Here are the instructions to run the code (be sure to install corresponding perl modules upon errors).

1. We assume that we write the json logs of multiple web page loads to a single file, and we store many this kind of log files under a directory (e.g. wprof_logs/). First, we are gonna split these logs into per web page load for future analysis. The command is as follows.

$ perl slice.pl wprof_logs

Then, we got a folder named wprof_logs_pro/ with lots of files. The filename is formatted as url-timestamp-index where index=1 means the page load is a cold load while index>1 means the page load is a hot/warm load.

2. Move the folder with sliced files to data/. Configure it in ProcessMain.pm (search for TODO).

3. Generate the stats using the following command. Here {type} can be any one of min, max, median, or a number x between 0 and 1 where x means x-percentile. We assume that we have multiple page loads (files) for a given Web page. Here, we only analyze the page load with the min/max/median/... page load time.

$ perl analysis {type}

4. Understand the results

Results are stored in temp_files/*, results/*, and graphs/*. Each file in temp_files/ corresponds to a file in data/. Each line means a data point. Here are some explanations of the names:

load: total page load time in seconds
TTFB: time to first byte in milliseconds
Parse: the elapsed time between when the html starts to parse and the html finishes parsing in milliseconds
PostParse: the time after the html is parsed and before the page is loaded in milliseconds
time_*: the amount of time used in downloading objects, computation, or blocking on critical path in milliseconds
download_*: network time on critical path in milliseconds
parse_*: computation time on critical path in milliseconds
time_download_*: network time broken down by mime type in milliseconds
time_block_*: blocking time broken down by mime type in milliseconds
num_*_all: counts for the entire page
num_*_cp: counts on critical path

Results in results/* are used for generating the cumulative distributions which can be ignored for now.

Results in graphs/* are dependency graphs. Each file in graphs/* is in the json format that represents the dependency graph of a Web page. Here is some explanation of the dependency graph. "objs" means array of objects each of which contains a download activity and some computation activities. The i-th computation activity of an object depends on the (i-1)-th computation activity of an object and the 1st computation activity depends on the download activity. Besides these dependencies, "deps" means array of dependencies where "a2" depends on "a1". A complete explanation of the json graphs can be found in the "Dependency Graph" section at the link below.

http://wprof.cs.washington.edu/spdy/tool/
