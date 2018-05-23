<?php

//基础配置
require('../wp-blog-header.php');  
$xmlrpcurl='http://**************';  //xmlrpc.php文件的地址
$blogid='1';  	//第几个wordpress博客，一般为1
$username='*********';  //wordpress用户名
$password='**********'; //wordpress用户密码
$tips = '<p style = "color:#888;width:100%;text-align:center;">（本文系后台自动转发自微信公众平台，可能存在格式错乱，阅读原文请关注公众号：******）</p>';
$postBody = $postBody . $tips;
$postContent='<title>' . $postTitle . '</title>'.'<category>' . $postCategory . '</category>' . '<!--more-->' .$postBody;

//图片本地化
require('./saveImage.php');
echo "<p>正在转存文章图片到服务器..</p>";
$postContent=post_save_images($postContent);

$postContent=post_save_bg($postContent);

$postContent=post_save_video($postContent);
//发布文章
echo "<p>开始发布文章..</p>";
require('../wp-includes/class-IXR.php');
$client = new IXR_Client($xmlrpcurl);
$params=array(
	'',
	'blog_ID'=>$blogid,
	'user_login'=>$username,
	'user_pass'=>$password,
	'post_content'=>'' . $postContent,
	'publish'=>true  
);
$params=array_values($params);
$client->query("blogger.newPost",$params);  
$response=$client->getResponse(); 

if ($response['faultCode'] != 0){  
	echo '<p>文章发布失败，请联系管理员:' . $response['faultString'] ."</p>";  
} else {  
	echo "<p>文章发布成功！文章ID为：".$response."，正在设置封面图片..</p>";
	
	//设置封面
	$coverFilePath = "../wp-content/uploads/".date("Y")."/".date("m")."/". $coverFileName;
	$coverID = insert_attachment($coverFilePath,$coverFileName,array(
		"ext"=>"jpg",
		"type"=>"image/jpeg",
	),true);
	if(set_post_thumbnail( $response, $coverID )){
		echo "<p>设置封面图片成功</p>";
	} else {
		echo "<p>设置封面图片失败，请登录网站后台手动设置</p>";
	}
	echo "<p>文章链接：</p>";
	// ..............链接地址
} 
?>  