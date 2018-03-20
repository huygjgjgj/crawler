<?php

namespace App\Http\Controllers;
use DB;
use App;

class TestController extends Controller
{

	protected $wpClient;

	function __construct(){
		$endpoint = "http://localhost/blog-dulich/xmlrpc.php";
		$this->wpClient = new WordpressClient();
		# Log error
		$this->wpClient->onError(function($error, $event){
		    logger()->error($error , $event);
		});

		$this->wpClient->setCredentials($endpoint, 'thaihuy', 'crossgame54321');
		
	}

    public function PostWP(){
    	

 		$data = App\wppost::get();
		foreach ($data as $key => $value) {
			$id = $value ->id;
			$title = $value->title;
			$content = $value ->content;
			$dis = $value ->description;
			$photo = $value ->photo;
			$status = $value ->status;
			$category = $value ->category;
		}

		$file = $this->wpClient->uploadFile('foo image.jpg',$photo, $photo);
		
		$termId = (int)$this->wpClient->newTerm($category, 'category');
		$term = $this->wpClient->getTerm($termId, 'category');

		$media = $this->wpClient->getMediaItem($file['attachment_id']);
			$thumbnail_image =  $media['link'];  
			$thumbnail_id = $media['attachment_id'];

		$postId = $this->wpClient->newPost($title, $content, 
			array(
					'terms_names' => array(
						'category' => array($term['name']),
					),
						'post_thumbnail' => $thumbnail_id,
					
				)
			);
		
    }
}
