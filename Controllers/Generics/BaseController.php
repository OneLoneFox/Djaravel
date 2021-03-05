<?php 

namespace Djaravel\Controllers\Generics;

class BaseController
{
	protected $loader;
	protected $twig;
	protected $model;
	protected $template;

	function __construct(){
		$this->loader = new \Twig\Loader\FilesystemLoader(['templates', 'Djaravel/templates']);
		$this->twig = new \Twig\Environment($this->loader, [
			'debug' => true,
		    // 'cache' => 'cache',
		]);
		$this->twig->addExtension(new \Twig\Extension\DebugExtension());
	}

	function getContextData(...$args){
		$context = [
			'user' => $_SESSION['user'] ?? null,
		];
		return $context;
	}

	function dispatch(...$args){
		if(!isset($this->template)){
			throw new \Djaravel\Core\Exceptions\ImproperlyConfiguredException('You must set the template to use');
		}
		$context = $this->getContextData(...$args);
		echo $this->twig->render($this->template, $context);
	}
}
