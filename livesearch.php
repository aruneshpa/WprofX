<?php
$results = `ls ./graphs -1`;
$sites = explode("\n", $results);
$response = "";
$term = $_GET["s"];

for ($i = 0; $i < count($sites); $i++) {
	$page = explode('_.', $sites[$i]);
	if (stristr($page[0], $term))
//		$response = $response . "<li><a href='http://wprofx.cs.stonybrook.edu/viz.php?p=/graphs/" . $sites[$i] . "'>" . $page[0] . "</a></li>";
		$response = $response . "<li><a href='http://localhost/wprof/viz.php?p=/wprof/graphs/" . $sites[$i] . "'>" . $page[0] . "</a></li>";
		$matches[] = $sites[$i];
}
echo $response;
print_r($matches);
?>
