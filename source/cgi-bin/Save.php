<?php
	include_once('./simple_html_dom.php');

	$servername="localhost";
        $username="cse20151602";
        $password="cse20151602";
        $dbname="db_cse20151602";

        $conn=new mysqli($servername, $username, $password, $dbname);

        $conn->query("set session character_set_connection=utf8;");
        $conn->query("set session character_set_results=utf8;");
        $conn->query("set session character_set_client=utf8;");

        if($conn->connect_error)
                die("Connection failed: ".$conn->connect_error);

	$user_id=$_REQUEST["user_id"];
        $author=$_REQUEST["author"];
        $title=$_REQUEST["title"];
        $publisher=$_REQUEST["publisher"];
        $isbn=$_REQUEST["isbn"];
	$script=$_REQUEST["script"];
	$img=$_REQUEST["dlalwl"];
	$point=$_REQUEST["point"];

	$query="DELETE FROM script WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."' AND user_id='".$user_id."';";
	
	$conn->query($query);

	$query="INSERT INTO script (user_id, img, title, author, isbn, publisher, appr, point) VALUES('".$user_id."', '".$img."', '".$title."', '".$author."', '".$isbn."', '".$publisher."', '".$script."', ".$point.");";

	$conn->query($query);

	if($conn->connect_error)
		echo "";
	else
		echo "ok";
?>
