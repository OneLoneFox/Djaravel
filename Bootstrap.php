<?php 

require_once 'Djaravel/dotenv/vendor/autoload.php';
require_once 'Djaravel/twig/vendor/autoload.php';
require_once 'Djaravel/router/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();
$dotenv->required(['BASE_DIR', 'DB_NAME', 'DB_USER', 'DB_PASSWORD']);


spl_autoload_register(function($class){
	$base_dir = $_SERVER['DOCUMENT_ROOT'].'/'.$_ENV['BASE_DIR'].DIRECTORY_SEPARATOR;
	$file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

	if(file_exists($file)){
		require $file;
	}
});
