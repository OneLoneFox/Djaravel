<?php 

require_once __DIR__.'/vendor/autoload.php';
// require_once 'Djaravel/dotenv/vendor/autoload.php';
// require_once 'Djaravel/twig/vendor/autoload.php';
// require_once 'Djaravel/router/vendor/autoload.php';
// require_once 'Djaravel/whoops/vendor/autoload.php';

# Initialize `whoops` Error Handler
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

# Initialize .env variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();
$dotenv->required(['BASE_DIR', 'DB_NAME', 'DB_USER', 'DB_PASSWORD']);

# Initialize session
session_start();


spl_autoload_register(function($class){
	$base_dir = __DIR__."/../".DIRECTORY_SEPARATOR;
	$file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

	if(file_exists($file)){
		require $file;
	}
});
