<?php
	include_once('./simple_html_dom.php');

	function img_finder($y, $z){
		$img_link="";

                foreach($y->find('img') as $tmp)
                        if(strpos($tmp->src, $z)){
                                $img_link=$tmp->src;
				break;
			}

		return $img_link;
	};

	function kyobo($url_kyobo){
		$html_kyobo=file_get_html($url_kyobo);
		$pt_tmp="0.0";

                foreach($html_kyobo->find('span') as $tmp){
                        if($tmp->title=="ISBN-10" && $GLOBALS['isbn']=="")
                        	$GLOBALS['isbn']=strip_tags($tmp);
                        else if($tmp->title=="ISBN-13" && $GLOBALS['isbn']=="")
                                $GLOBALS['isbn']=strip_tags($tmp);
                        else if($tmp->title=="출판사")
                               $GLOBALS['publisher']=strip_tags($tmp->find('a')[0]);
                }

		foreach($html_kyobo->find('div[class="popup_load"]') as $tmp){
			$pt_tmp=strip_tags($tmp->find('em')[0]);
			break;
		}

		if($pt_tmp!="0.0" && $pt_tmp!=NULL){
			$GLOBALS['point']+=$pt_tmp;
			$GLOBALS['point_cnt']++;
		}

                $img_link=img_finder($html_kyobo, 'images/book/large');

		if($img_link!="")
			$GLOBALS['img']=$img_link;

        	if($GLOBALS['author']=="")
                	foreach($html_kyobo->find('a[class="detail_author"]') as $tmp)
				$GLOBALS['author']=strip_tags($tmp);
	};

	function aladin($url_aladin){
        	$html_aladin=file_get_html($url_aladin);
		$pt_tmp="0.0";

                if($GLOBALS['title']=="")
                	foreach($html_aladin->find('a[class="Ere_sub2_title"]') as $tmp){
                        	$GLOBALS['title']=strip_tags($tmp);
                                break;
                        }

		foreach($html_aladin->find('a[class="Ere_sub_pink Ere_fs16 Ere_str"]') as $tmp){
			$pt_tmp=strip_tags($tmp);
			break;
		}

		if($pt_tmp!="0.0" && $pt_tmp!=NULL){
			$GLOBALS['point']+=$pt_tmp;
			$GLOBALS['point_cnt']++;
		}

                if($GLOBALS['img']=="")
                        $GLOBALS['img']=img_finder($html_aladin, 'image.aladin.co.kr/product');
	};

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
	$script="";
	$img="";
	$point=0.0;
	$point_cnt=0;

	$query="SELECT * FROM book_info WHERE ";
	$chk=false;
	$arr=array();

	if($title!=NULL){
		$query.="title='".$title."' ";
		$chk=true;
	}
	if($author!=NULL){
		if($chk)
			$query.="AND ";

		$query.="author='".$author."' ";
		$chk=true;
	}
        if($publisher!=NULL){
                if($chk)
                        $query.="AND ";

                $query.="publisher='".$publisher."' ";
                $chk=true;
        }

        if($isbn!=NULL){
                if($chk)
                        $query.="AND ";

                $query.="isbn='".$isbn."'";
                $chk=true;
        }

	$query.=";";

	$res=$conn->query($query);

	if($res->num_rows==0){
		$t_title=str_replace(" ", "+", $title);
                $t_author=str_replace(" ", "+", $author);
                $t_publisher=str_replace(" ", "+", $publisher);
                $link="";
		$tmp_isbn="";

                $url="https://book.naver.com/search/search.nhn?query=+".$t_title."+".$t_author."+".$t_publisher."+".$isbn;
                $html=file_get_html($url);

	        foreach($html->find('a') as $a)
	                if(strpos($a->href, "book.naver.com/bookdb/book_detail")){
	                        $link=$a->href;
	                        break;
			}
		if($link!="")
			$html=file_get_html($link);

		if($link=="" || $html==""){
			$url="https://www.googleapis.com/books/v1/volumes?q=";

	                if($title!=NULL){
        	                $t_title=str_replace(" ", "%20", $title);
        	                $url.="+intitle:".$t_title;
       	        	}
                	if($author!=NULL){
                        	$t_author=str_replace(" ", "%20", $author);
                        	$url.="+inauthor:".$t_author;
                	}
                	if($publisher!=NULL){
                	        $t_publisher=str_replace(" ", "%20", $publisher);
                	        $url.="+inpublisher:".$t_publisher;
                	}
                	if($isbn!=NULL)
                	        $url.="+isbn:".$isbn;

	                $tmp_isbn=$isbn;

	                $html=file_get_html($url);
	                $crawl=json_decode($html);

			if($crawl->totalItems!=0){
	                        $crawl=$crawl->items[0]->volumeInfo;

	                        $img="";
	                        $isbn="";
	                        $title="";
	                        $publisher="";
	                        $author="";

	                        $img=$crawl->imageLinks->thumbnail;
	                        $author=$crawl->authors[0];
	                        $publisher=$crawl->publisher;
	                        $title=$crawl->title;
	                        $isbn=$crawl->industryIdentifiers[0]->identifier;

	                        $title=str_replace("%20", " ", $title);
	                        $author=str_replace("%20", " ", $author);
	                        $publisher=str_replace("%20", " ", $publisher);

	                        $info_link=$crawl->infoLink;
	                        if($info_link){
	                                $html=file_get_html($info_link);
					$url_kyobo="";
					$url_aladin="";					

					foreach($html->find('a') as $tmp){
						if(strpos($tmp->href, 'kyobobook.co.kr')){
							$url_kyobo=$tmp->href;
							break;
						}
						else if(strpos($tmp->href, 'aladdin.co.kr'))
							$url_aladin=$tmp->href;
					}

	                                if($url_kyobo!="")
						kyobo($url_kyobo);

					if($url_aladin!="")
						aladin($url_aladin);
        	                }

                	        $query="SELECT * FROM book_info WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."';";

	                        $res=$conn->query($query);

	                        if($res->num_rows==0){
	                                $query="INSERT INTO book_info (img, title, author, isbn, publisher, point, point_cnt) VALUES('".$img."', '".$title."', '".$author."', '".$isbn."', '".$publisher."', ".$point.", ".$point_cnt.");";

	                                $conn->query($query);
	                        }

	                        if($img==NULL)
	                                $img="";
	                        if($title==NULL)
	                                $title="";
	                        if($author==NULL)
	                                $author="";
	                        if($publisher==NULL)
	                                $publisher="";
	                        if($isbn==NULL)
	                                $isbn="";
	                }
			else{
				$title="";
				$author="";
				$publisher="";
				$isbn="";
			}

		}
		else{
			$url_kyobo="";
			$url_aladin="";
			$pt_tmp="0.0";

			$title="";
			$isbn="";
			$author="";
			$publisher="";

			foreach($html->find('a') as $a)
				if(strpos($a->href, "book.naver.com/search/search.nhn")){
					$link=$a->href;
					break;
				}

			foreach($html->find('a[id="txt_desc_point"]') as $tmp){
				$pt_tmp=substr(strip_tags($tmp->find('strong')[0]), 0, -3);
				break;
			}

			if($pt_tmp!="0.0" && $pt_tmp!=NULL){
				$point+=$pt_tmp;
				$point_cnt++;
			}

			foreach($html->find('a') as  $a)
				if(strpos($a->href, "kyobobook.co.kr")){
					$url_kyobo=$a->href;
					break;
				}

			foreach($html->find('a') as $a)
				if(strpos($a->href, "aladin.co.kr")){
					$url_aladin=$a->href;
					break;
				}

			foreach($html->find('a') as $a)
				if(strpos($a->href, "/bookdb/book_detail.nhn")){
					$title=strip_tags($a);
				}

			foreach($html->find('img') as $a)
				if(strpos($a->src, "bookthumb")){
					$img=$a->src;
					break;
				}

			$html=file_get_html($link);

			foreach($html->find('input') as $a)
				if($a->title=="검색"){
					$author=$a->value;
					break;
				}

			$url="https://search.kyobobook.co.kr/web/search?vPstrKeyWord=";
			if($title!="")
				$url.=str_replace(" ", "%20", $title)."%20";
			if($author!="")
				$url.=str_replace(" ", "%20", $author);
			$html_kyobo=file_get_html($url);
			foreach($html_kyobo->find('a') as $a)
				if(strpos($a->href, "kyobobook.co.kr/product/detailViewKor")){
					if(strip_tags($a->find('strong')[0])==$title){
						kyobo($a->href);
						break;
					}
				}

			if($url_aladin!="")
				aladin($url_aladin);
			if($url_kyobo!="")
				kyobo($url_kyobo);

			$query="SELECT * FROM book_info WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."';";

                        $res=$conn->query($query);

                        if($res->num_rows==0){
                                $query="INSERT INTO book_info (img, title, author, isbn, publisher, point, point_cnt) VALUES('".$img."', '".$title."', '".$author."', '".$isbn."', '".$publisher."', ".$point.", ".$point_cnt.");";

                                $conn->query($query);
                        }

		}

		$query="SELECT appr FROM script WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."' AND user_id='".$user_id."';";

                $res=$conn->query($query);

                if($res->num_rows==0)
                        $script="";
                else{
                        $result=$res->fetch_assoc();
                        $script=$result['appr'];
                }

	}
	else{
		$result=$res->fetch_assoc();
		$img=$result['img'];
		$author=$result['author'];
		$title=$result['title'];
		$publisher=$result['publisher'];
		$isbn=$result['isbn'];

		$query="SELECT appr FROM script WHERE title='".$title."' AND author='".$author."' AND publisher='".$publisher."' AND isbn='".$isbn."' AND user_id='".$user_id."';";

		$res=$conn->query($query);

		if($res->num_rows==0)
			$script="";
		else{
			$result=$res->fetch_assoc();
			$script=$result['appr'];
		}

	}

	$out=array("title" => $title, "author" => $author, "publisher" => $publisher, "isbn" => $isbn, "img" => $img, "script" => $script);

	echo json_encode($out, JSON_UNESCAPED_UNICODE);

	$conn->close();
?>
