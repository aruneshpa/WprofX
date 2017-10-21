#!/usr/bin/perl
# ------------------------------------------------------
# This script slices and analyzes log files into json files,
# removing temporary directories and copying into graphs folder.
#
# See config to change directory paths in analyze.pl
# Generally should be run after log.pl, works on any .log files
# created in ./tests/analysis_t/pre_log/
# ------------------------------------------------------

use IO::Handle;
use strict;
my $moveup = "cd ./tests/analysis_t";
my $com1 = "perl ./slice.pl ./pre_log";
my $com2 = "cp -r ./pre_log_pro/ ./data";
my $com3 = "perl ./analyze.pl";
`$moveup`;
`$com1`;
`$com2`;
`$com3`;

# Get the name of the log file
#my $get_name = "cd pre_log_pro";
#my $store_name = `ls`;
#my @tokens = split(/_/, $store_name);
#my $json_name = @tokens[0]."_.json";
#my $cd = "cd ..";

# `$get_name`;
# `$store_name`;
# `$cd`;
 
my $get_files = "ls ./graphs";
my @file_name = split(/\n/, `$get_files`);
my $files = @file_name;
while ($files > 0) {
	$files = $files - 1;
	my $newfile = "";
	# Handle cases where '#' is in the file name
	if (index(@file_name[$files], '#') != -1) {
		$newfile = join('', split('#', @file_name[$files]));
		my $rename = "mv ./graphs/" . @file_name[$files] . " ./graphs/" . $newfile;
		`$rename`;
	}
	my $move_graph = "cp ./graphs/" . @file_name[$files] . " /var/www/wprofx.cs.stonybrook.edu/public_html/graphs/";
	`$move_graph`;
}

my $delete_log_file = "rm -r ./pre_log_pro/";
my $delete_temp_file = "rm -r ./temp_files/pre_log_pro/";
my $delete_data_file = "rm -r ./data/pre_log_pro";
# delete_file and recreate make sure that there is only one passed in log file.
# So we can know which json file is needed to be displayed.
my $delete_file = "rm -r ./pre_log";
my $recreate = "mkdir pre_log";
 `$delete_log_file`;
 `$delete_temp_file`;
 `$delete_data_file`;
 `$delete_file`;
 `$recreate`;