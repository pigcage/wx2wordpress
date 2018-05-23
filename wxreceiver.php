<?php
	if(!empty($_FILES)){
		if ($_FILES["pic"]["error"] > 0) {
			echo "图片上传出错，请联系管理员。错误码：" . $_FILES["pic"]["error"] . ".";
			return;
		} else {
			if($_FILES["pic"]["type"] == "image/jpeg"){
				if($_FILES["pic"]["size"] / 1024 > 700) { //图片文件大小小于700k
					echo "上传的图片过大，请返回重新选择";
					return;
				} else {
					if(!($_POST["link"]) || !($_POST["password"])){
						echo "请填写正确的链接和密码";
						return;
					} else {
						if($_POST["password"] != "***************"){
							echo "密码错误";
							return;
						} else {
							$link = $_POST["link"];
							$password = $_POST["password"];
							$coverFileName = date("YmHis").rand(100,999).".jpg"; 
							//若储存目录不存在则创建
							if(!is_dir('../wp-content/uploads/".date("Y")."/".date("m")'))
								mkdir('../wp-content/uploads/'.date("Y").'/'.date("m"),0777,true) or die("创建目录失败");
							
							move_uploaded_file($_FILES["pic"]["tmp_name"], "../wp-content/uploads/".date("Y")."/".date("m")."/". $coverFileName) or die("上传文件出错");
							echo "发布请求提交成功，开始读取链接内容..";
							
							//根据链接读取文章内容
							$html = file_get_contents($link);  
							//文章标题、内容
							preg_match('/<div[^>]*id="js_content"[^>]*>(.*?) <\/div>/si',$html,$content_arr);  
							preg_match("/<span class=\'rich_media_title_ios\'>(.*)<\/span>/i",$html,$title_arr);
							$postTitle=$title_arr[1];
							$postBody=$content_arr[1];

							if(!($postBody) || !($postTitle)){
								echo "无法正确读取链接中的内容！请联系管理员";
								return;
							} else {
								//文章分类，“所有文章（9）是固定的”
								$cat = $_POST['cat'];
								$postCategory=$cat;
								require("./poster.php");
							}
						}
					}
				}
			} else {
				echo "仅支持jpg格式的封面！";
				return;
			}
		}
	} else {
		echo "未选择封面图片，请返回重新选择";
	}
?>