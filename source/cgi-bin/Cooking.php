<?php
	include_once('./simple_html_dom.php');

	$servername='localhost';
	$username='cse20151602';
	$password='cse20151602';
	$dbname='db_cse20151602';

	$conn=new mysqli($servername, $username, $password, $dbname);

	$conn->query("set session character_set_connection=utf8;");
	$conn->query("set session character_set_results=utf8;");
	$conn->query("set session character_set_client=utf8;");

	$title=$_REQUEST['title'];
	$author=$_REQUEST['author'];
	$publisher=$_REQUEST['publisher'];
	$isbn=$_REQUEST['isbn'];
	$point="0.0";
	$point_cnt=0;

	$query="SELECT point, point_cnt FROM book_info WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."';";

	$result=($conn->query($query))->fetch_assoc();

	if($result["point"]!=NULL)
		$point=$result["point"];
	if($result["point_cnt"]!=NULL)	
		$point_cnt=$result["point_cnt"];

	$query="SELECT point FROM script WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."';";

	$res=$conn->query($query);

	while($result=$res->fetch_assoc()){
		$point_cnt++;
		if($result["point"]!=NULL)
			$point+=$result["point"];
	}

	if($point_cnt==0)
		echo "";
	else
		echo $point/$point_cnt;
?>
