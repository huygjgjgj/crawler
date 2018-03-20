<?php

namespace App;

class Wordpress
{
    protected $wpClient;

	function __construct($server , $username , $password) {
		$this->wpClient = new WordpressClient();
//		$this->wpClient->onError(function($error){
//			logger($error);
//		});
		# Set the credentials for the next requests
		$this->wpClient->setCredentials($server, $username, $password);

	}

	public function getPost(){
		return $this->wpClient->getPosts();
	}

	public function submit($title, $content, $category = '', $photo = '', $keywords = '')
	{

		$post_thumbnail = $this->uploadMedia($title,$photo);
		$meta_data = [
			'mt_keywords'=>$keywords,
			'post_name' => $title,
			'terms_names' => [
				'post_tag' => explode(',', $keywords) ,
				'category' => explode(',', $category),
			],
			'post_thumbnail' => $post_thumbnail,
		];

		return $this->wpClient->newPost($title, $content, $meta_data);
	}

	public function uploadMedia($title, $photo){
		$content = file_get_contents($photo);
		$name = $title.'_'.rand(100, 999).'.jpg';
		$media_id = $this->wpClient->uploadFile($name , 'image/jpeg',$content);
		return $media_id ? $media_id['attachment_id'] : 0;
	}
}
