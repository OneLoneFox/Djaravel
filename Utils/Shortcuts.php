<?php 

namespace Djaravel\Utils;

class Shortcuts
{

	static function redirect($url){
		$host = $_SERVER['SERVER_NAME'];
		$redirectUrl = 'http://'.$host.$url;
		header('Location: '.$redirectUrl);
	}

}
