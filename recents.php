<html><head>
<meta charset="utf-8">
<title>WProfx</title>
<link rel="stylesheet" type="text/css" href="common.css">
<link rel="stylesheet" href="./assets/css/bootstrap.min.css">
<link rel="stylesheet" href="./assets/css/bootstrap-responsive.min.css">
<link href='http://fonts.googleapis.com/css?family=Ubuntu:400,300italic' rel='stylesheet' type='text/css'>
<script src="js/util.js"></script>
<style>
	.container-fluid { padding: 60px 10% 0 10%; }
	li.gray { background-color: whitesmoke; }
	li span { float: right; }
	#recents ul li { padding: 5px 10px 5px 10px; }
	#recents ul li:hover {
		background-color: rgb(244, 242, 255);
		border-top: solid white 1px;
		border-bottom: solid white 1px;
	}
	#recents {
		margin: 0px 2% 0px 2%;
		min-width: 500px;
		width: 96%;
		height: auto;
		position: static;
		color: gray;
	}
	#livesearch {
		left: 116px;
		top: 141px;
	}
</style>
</head>
<body>
	<div class="container-fluid">
		<div class="page-header">
			<span>
			<h1>Most Recently Analyzed Pages</h1>
			<form>
				<input name="searchJSON" id="searchJSON" type="text" placeholder="Search completed analyses" onkeyup="util.search(this.value)"></input>
				<div id="livesearch"><ul></ul></div>
    		</form>
            </span>
		</div>
        <div id="recents">
            <ul>
                <?php 
                ## CHANGE
                ## printf works only in linux
                $top = `find graphs/ -name "*json" | rev | cut -d '/' -f 1 | rev | sort -r`;
//                $top = `find graphs/ -type f -printf '%TY-%Tm-%Td %TT %p\n' | sort -r`;
                $eachtop = explode("\n", $top);
                for ($x = 1; $x < count($eachtop); $x++) {
                    $units = explode("\t", $eachtop[$x-1]);
                    $page = explode('_.', $units[2]);
                    if ($x % 2 == 0) {
                    ?><li><? } else { ?><li class="gray"><? } ?>
                        <a href=
                        <? echo 'http://wprofx.cs.stonybrook.edu/viz.php?p='.'/graphs/'.$units[2]; ?>
                        ><? echo $x.". ".$page[0]; ?></a><? echo $units[0]; ?></li>
                <? } ?>
            </ul>
	   </div>
	</div>
	<div id="header">
		<ul>
			<li><a href="http://wprofx.cs.stonybrook.edu/#mInfoPt">how it works</a></li>
			<li><a class="purple" href="http://wprofx.cs.stonybrook.edu/viz.php">wprofx</a></li>
			<li><a href="http://wprofx.cs.stonybrook.edu/recents.php">analyzed pages</a></li>
		</ul>
	</div>
	<div id="footer"><span>
		<a class="purple" href="http://wprof.cs.washington.edu/">wprof research</a> | 
		<a class="purple" href="https://netlab.cs.washington.edu/">uw networks</a> | 
		<a class="purple" href="http://cs.washington.edu/">uw cse</a> | 
		Aruna Balasubramanian, Xiao Sophia Wang, Ruhui Yan, Jeannette Yu, Michelle Lee
	</span></div>	
</body>
</html>
