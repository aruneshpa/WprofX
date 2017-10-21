#!/usr/bin/perl
# ------------------------------------------------------
# Opens given website in Chromium and saves log file.
# ------------------------------------------------------

use strict;
use IO::Handle;
use threads;
use threads qw(stringify);

my $arg = @ARGV;
if ($arg < 1){
    print "Usage: " . $0 . " [command website]\n";
    exit 0;
}
my $web = $ARGV[0];
my $name = $web;
if (index($web, ".com") != -1){
  my @tokens = split(/.com/, $web);
  $name = @tokens[0];
} elsif (index($web, ".net") != -1){
    my @tokens = split(/.net/, $web);
    $name = @tokens[0];
} elsif (index($web, ".org") != -1){
    my @tokens = split(/.org/, $web);
    $name = @tokens[0];
}
if (index($web, "www.") != -1){
  my @token = split(/www./, $name);
  $name = @token[1];
}

my $log_file = "/var/www/wprofx.cs.stonybrook.edu/public_html/tests/analysis_t/pre_log/" . $name . ".log";
print "$log_file\n";

my $t1 = threads->create(\&run_chrome);

check_if_log_complete();

sub check_if_log_complete{
    # The thread will test if the log file is completed
    while (! -e $log_file) {
        sleep(2);
    }
    print "file exists\n";
    my $com = "grep \"Complete\" ". $log_file;
    print "$com\n";
    my $complete = `$com`;
    print "$complete\n";
    while($complete eq ""){
        print "file is not completed\n";
        sleep(2);
        $complete = `$com`;
    }
    print "file is completed\n";
    `pkill chrome`;
    #`ps ux|grep chrome|cut -d'' -f 2|xargs kill`;
    threads->exit();
}

sub run_chrome{
    # The thread will run the chrome application to get the log file
    my $c = "DISPLAY=:7 /var/www/wprofx.cs.stonybrook.edu/public_html/Release/chrome --no-sandbox " . $web;
    print "$c\n";
    open OUTPUT, '>', "$log_file" or die "$log_file not available \n";
    print "Write to file\n";
    STDERR->fdopen(\*OUTPUT, 'w') or die $!;
    `$c`;
    threads->exit();
}
