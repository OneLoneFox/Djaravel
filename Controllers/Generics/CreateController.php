<?php

namespace Djaravel\Controllers\Generics;
use \Djaravel\Core\Exceptions\UnimplementedException;
use \Symfony\Bridge\Twig\Extension\FormExtension;
use \Symfony\Bridge\Twig\Form\FormRenderer;

class CreateController extends BaseController
{
	protected $template = 'generic_create.html';
	protected $success_url;

	function __construct(){
		parent::__construct();
	}

	function getContextData(...$args){
		$form = $this->getForm();
		$context = parent::getContextData(...$args);
		$context['form'] = $form;
		return $context;
	}

	// function dispatch(...$args){}

	function getForm(){
		throw new UnimplementedException(
			sprintf('The getForm() method is missing on %s', static::class)
		);
	}
}