<?php 
	//var_dump($_POST);
	$date = explode(",",$_POST['date']);
	$title = explode("|", $_POST['title']);
	$content = explode("|", $_POST['content']);
	$img = explode(",", $_POST['img']);
	$read = explode(",", $_POST['read']);

	$con = mysqli_connect("localhost","root","root","ccit");
	$count = count($date);
	for($i=0;$i<$count;$i++) {
		$sql = "insert into news(title,content,img,`read`,time) values('$title[$i]','$content[$i]','$img[$i]','$read[$i]','$date[$i]')";
		mysqli_query($con,$sql);
	}
	mysqli_close($con);
 ?>