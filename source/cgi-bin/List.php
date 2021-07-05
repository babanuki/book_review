<?php
	include_once("./simple_html_dom.php");

	$servername="localhost";
	$username="cse20151602";
	$password="cse20151602";
	$dbname="db_cse20151602";

	$conn=new mysqli($servername, $username, $password, $dbname);

	$conn->query("set session character_set_connection=utf8;");
	$conn->query("set session character_set_results=utf8;");
	$conn->query("set session character_set_client=utf8;");

	$user_id=$_REQUEST["user_id"];
	$author=$_REQUEST["author"];
	$title=$_REQUEST["title"];
	$publisher=$_REQUEST["publisher"];
	$isbn=$_REQUEST["isbn"];

	$query="SELECT * FROM script WHERE user_id='".$user_id."'";
	if($author!="")
		$query.=" AND author LIKE '%".$author."%'";
	if($title!="")
		$query.=" AND title LIKE '%".$title."%'";
	if($publisher!="")
		$query.=" AND publisher LIKE '%".$publisher."%'";
	if($isbn!="")
		$query.=" AND isbn LIKE '%".$isbn."%'";
	$query.=";";

	$res=$conn->query($query);

	$cnt=0;
	$a="";

	while($result=$res->fetch_assoc()){
		if(!$result["title"])
			$result["title"]="";
		if(!$result["author"])
                        $result["author"]="";
                if(!$result["publisher"])
                        $result["publiser"]="";
                if(!$result["isbn"])
                        $result["isbn"]="";
                if(!$result["appr"])
                        $result["appr"]="";

		$a.=json_encode(array("title"=>$result["title"], "author"=>$result["author"], "publisher"=>$result["publisher"], "isbn"=>$result["isbn"], "script"=>$result["appr"], "time"=>$result["date"], "point"=>$result["point"]), JSON_UNESCAPED_UNICODE).",";
	}

	if($a!="")
		$a=substr($a, 0, $a.length-1);

	echo "{\"items\":[".$a."]}";;
?>
