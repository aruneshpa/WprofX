<?
$str = "old.php";
$temp = explode(".", $_FILES["uploadedfile"]["name"]);
$extension = end($temp);
if ($extension == "json"){
	$target_path = "graphs/";
	$target_path = $target_path . basename( $_FILES['uploadedfile']['name']); 
	if (file_exists("graphs/" . $_FILES["uploadedfile"]["name"])) {
		echo $_FILES["uploadedfile"]["name"] . " already exists. ";
    } else {
		move_uploaded_file($_FILES["uploadedfile"]["tmp_name"],
		$target_path);
		echo "Stored in: " . "upload/" . $_FILES["uploadedfile"]["name"];
    }
	$str = "index.php?p=".$_FILES["uploadedfile"]["name"];
	
}else{
	echo "Invalid file";
}
echo '<script type="text/javascript">
		setTimeout(function(){
			window.location = "'.$str.'";
		}, 1500);
	</script>';

?>
