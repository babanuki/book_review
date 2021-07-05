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

	$title=array();
	$publisher=array();
	$isbn=array();
	$author=array();

	$query="SELECT * FROM book_info;";
	
	$res=$conn->query($query);

	while($result=$res->fetch_assoc()){
		if($result["title"])
			array_push($title, $result["title"]);
		else
			array_push($title, "");
		if($result["author"])
			array_push($author, $result["author"]);
		else
			array_push($author, "");
		if($result["publisher"])
			array_push($publisher, $result["publisher"]);
		else
			array_push($publisher, "");
		if($result["isbn"])
			array_push($isbn, $result["isbn"]);
		else
			array_push($isbn, "");
	}

	$type=$_REQUEST["type"];

	$hint="";

	switch($type){
		case 0:
			$arr=$title;
			break;
		case 1:
			$arr=$author;
			break;
		case 2:
			$arr=$publisher;
			break;
		default:
			$arr=$isbn;
			
	}

	$q_title=$_REQUEST["title"];
	$q_author=$_REQUEST["author"];
	$q_publisher=$_REQUEST["publisher"];
	$q_isbn=$_REQUEST["isbn"];

	for($i=0; $i<$res->num_rows; $i++) {
		$cnt=0;
		$name="";
		$t_title=$title[$i];
		$t_author=$author[$i];
		$t_publisher=$publisher[$i];
		$t_isbn=$isbn[$i];

		$len=strlen($q_title);
		if ($q_title=="" || stristr($q_title, substr($t_title, 0, $len)))
			$cnt++;
		else
			continue;
		$len=strlen($q_author);
		if($q_author=="" || stristr($q_author, substr($t_author, 0, $len)))
			$cnt++;
		else
			continue;
		$len=strlen($q_publisher);
                if($q_publisher=="" || stristr($q_publisher, substr($t_publisher, 0, $len)))
                        $cnt++;
		else
			continue;
		$len=strlen($q_isbn);
                if($q_isbn=="" || stristr($q_isbn, substr($t_isbn, 0, $len)))
                        $cnt++;
		else
			continue;

		switch($type){
			case 0:
				$name=$t_title;
				break;
			case 1:
				$name=$t_author;
				break;
			case 2:
				$name=$t_publisher;
				break;
			default:
				$name=$t_isbn;
		}

		if ($hint === "")
                        $hint = $arr[array_search($name, $arr)];
                else
                        $hint .= ", " . $arr[array_search($name, $arr)];
	}

	echo $hint;
?>
