<?php

namespace App\Http\Controllers;
use App\Data;
use App\Wordpress;


class MyController extends Controller
{
	public function Getmetadata(){

		$data = Data::get();
		foreach ($data as $key => $value) {
			$id = $value ->id;
			$title = $value->title;
			$content = $value ->content;
			$dis = $value ->description;
			$photo = $value ->photo;
			$status = $value ->status;
			$category = $value ->category;
		}

		$endpoint = "http://localhost/blog-dulich/xmlrpc.php";

		$photo = 'C:\Users\DORE\Desktop\caocao.jpg';

		$Wordpress = new Wordpress($endpoint, 'thaihuy', 'crossgame54321');

		$post = $Wordpress->submit($title,$content,$category,$photo,'1');

		$uploadfile = $Wordpress->uploadMedia($title,$photo);
	}
}