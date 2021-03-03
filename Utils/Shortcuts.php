<?php 

namespace Djaravel\Utils;

class Shortcuts
{

	static function redirect($url){
		$host = $_SERVER['SERVER_NAME'];
		$redirectUrl = 'http://'.$host.$url;
		header('Location: '.$redirectUrl);
		exit();
	}

	static function redirectBase($url){
		$host = $_SERVER['SERVER_NAME'];
		$redirectUrl = 'http://'.$host.'/'.$_ENV['BASE_DIR'].$url;
		header('Location: '.$redirectUrl);
		exit();
	}

}
