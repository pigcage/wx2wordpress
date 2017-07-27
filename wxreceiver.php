<?php
	if(!empty($_FILES)){
		if ($_FILES["pic"]["error"] > 0) {
			echo "图片上传出错，请联系管理员。错误码：" . $_FILES["pic"]["error"] . ".";
			return;
		} else {
			if($_FILES["pic"]["type"] == "image/jpeg"){
				if($_FILES["pic"]["size"] / 1024 > 10000) {
					echo "上传的图片过大，请返回重新选择";
					return;
				} else {
					if(!($_POST["link"]) || !($_POST["password"])){
						echo "请填写正确的链接和密码";
						return;
					} else {
						if($_POST["password"] != 发布密码){
							echo "密码错误";
							return;
						} else {
							$link = $_POST["link"];
							$password = $_POST["password"];
							$coverFileName = date("YmHis").rand(100,999).".jpg"; 
							move_uploaded_file($_FILES["pic"]["tmp_name"], "./wp-content/uploads/".date("Y")."/".date("m")."/". $coverFileName);
							echo "发布请求提交成功，开始读取链接内容..";
							
							//根据链接读取文章内容
							$html = file_get_contents($link);  
							//文章标题、内容
							preg_match('/<title>(.*)<\/title>/i',$html,$title_arr);
							preg_match('/<div[^>]*id="js_content"[^>]*>(.*?) <\/div>/si',$html,$content_arr);  
							$postTitle=$title_arr[1];
							$postBody=$content_arr[1];
							
							if(!($postBody) || !($postTitle)){
								echo "无法正确读取链接中的内容！请联系管理员";
								return;
							} else {
								//文章分类
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