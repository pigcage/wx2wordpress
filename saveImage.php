<?php
	//主函数，参数为文章内容，返回修改好的内容
	function post_save_images( $content ){
		set_time_limit(240);	//时间限制
		$preg=preg_match_all('/<img.*?data-src="(.*?)"/',stripslashes($content),$matches);//匹配所有图片,$preg为匹配数量
		if($preg){
			$i = 1;
			foreach($matches[1] as $image_url){
				if(empty($image_url)) continue;
				$pos=strpos($image_url,get_bloginfo('url'));	//检查是否为本地图片，这里参数二可以改为域名
				if($pos===false){
					$res=save_images($image_url,$i);	//储存该图片，返回储存后的结果
					$replace=$res['url'];						//新的本地路径
					$src = $image_url . '" src="'. $replace;
					$content=str_replace($image_url,$src,$content);	//替换到原文本中
				}
				$i++;
			}
		}
	return $content;
	}
	//获取url参数，由于未处理url中的'&'符号，返回的键名前带'amp;'
	function convertUrlQuery($query){
		$queryParts = explode('&', $query);
		$params = array();
		foreach ($queryParts as $param)
		{
			$item = explode('=', $param);
			$params[$item[0]] = $item[1];
		}
		return $params;
	}
	
	//替换视频链接
	function post_save_video( $content ){
		set_time_limit(240);	//时间限制
		$preg=preg_match_all('/<iframe.*?data-src="(.*?)"/',stripslashes($content),$matches);//匹配所有图片,$preg为匹配数量
		if($preg){
			foreach($matches[1] as $video_url){
				if(empty($video_url)) continue;
				$arr = convertUrlQuery($video_url);
				$width = $arr['amp;width'];
				$height = $arr['amp;height'];
				$player_url=str_replace('preview.html','player.html',$video_url);
				$src = $video_url . '" width="'. $width.'" height="' . $height .'" src="'. $player_url;
				$content=str_replace($video_url,$src,$content);	//替换到原文本中
			}
		}
		return $content;
	}

	//背景图片储存
	function post_save_bg( $content ){
		set_time_limit(240);	//时间限制
		$bg = "background-image: url(";
		if(stripos($content, $bg)){
			echo "!!<br>";
			$content = str_replace($bg, $bg . 'http://read.html5.qq.com/image?src=forum&q=5&r=0&imgflag=7&imageUrl=', $content);
		}
		return $content;
	}
	
	//单一图片储存，参数为原图片地址，文章id，是否设为题图（是则为1，其它均否），返回array(file,type,url,error)
	function save_images($image_url,$i){
		//读取原图片内容、文件名
		$file=file_get_contents($image_url);
		// $filename=basename($image_url);	//basename()的形式不能读取正确格式
		$filetype=get_wx_file_type($image_url);
		$filename = date("YmHis").rand(100,999).(".".$filetype['ext']."");
		
		$res=wp_upload_bits($filename,'',$file);//上传到uploads文件夹
		$attach_id = insert_attachment($res['file'], $filename, $filetype,false);//添加到媒体库
		
		return $res;
	}
	
	//图片添加到媒体库，参数为：图片路径（无域名），图片类型（array），是否为封面，返回图片id
	function insert_attachment($file,$filename,$filetype,$isCover){
		// if($isCover){
			// $dirs=array(
				// "url"=>"http://www.gdutchoir.cn/postCover",
			// );
			// echo "wpdir:";
			// print_r(wp_upload_dir());
			// echo "<br>dirs:";
			// print_r($dirs);
		// } else {
			$dirs=wp_upload_dir();
		// }
		$attachment=array(
			'guid'=>$dirs['url'].'/'.$filename,
			'post_mime_type'=>$filetype['type'],
			'post_title'=>preg_replace('/\.[^.]+$/','',$filename),
			'post_content'=>'',
			'post_status'=>'inherit'
		);
		//添加到媒体库，并为其生成、绑定预览图
		$attach_id=wp_insert_attachment($attachment,$file);
		$attach_data=wp_generate_attachment_metadata($attach_id,$file);
		wp_update_attachment_metadata($attach_id,$attach_data);
		return $attach_id;
	}
	
	//获取微信图片的格式，以wp_check_filetype()的形式返回
	function get_wx_file_type($image_url){
		$preg = preg_match_all('/wx_fmt=jpeg/',$image_url,$match_arr);
		if($preg) {
			$ext = 'jpg';
			$type = 'image/jpeg';
		} 
		$preg = preg_match_all('/wx_fmt=png/',$image_url,$match_arr);
		if($preg) {
			$ext = 'png';
			$type = 'image/png';
		} 
		$preg = preg_match_all('/wx_fmt=gif/',$image_url,$match_arr);
		if($preg) {
			$ext = 'gif';
			$type = 'image/gif';
		} 
		return compact( 'ext', 'type' );
	}
?>
