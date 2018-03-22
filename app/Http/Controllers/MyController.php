<?php

namespace App\Http\Controllers;
use App\Data;
use App\Wordpress;


class MyController extends Controller
{
	public function Getmetadata(){
		$endpoint = "http://localhost/blog-dulich/xmlrpc.php";
		$userwp = 'thaihuy';
		$password = 'crossgame54321';

		$Wordpress = new Wordpress($endpoint, $userwp, $password);

		$Data = Data::where('status',1)->get();
		foreach ($Data as $key => $data) {
			$id = $data->id;
			$title = $data->title;
			$content = $data->content;
			$photo = $data->photo;
			$category = $data->category;
			$keyword = $data->tags;

			$postID = $Wordpress->submit($title,$content,$category,$photo,$keyword);

			echo 'Post bài thành công với postid = ' . $postID. '<br>';

			if($postID) Data::where('id',$id)->update(['status' => 2]);
		}		
	}
}