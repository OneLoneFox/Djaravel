<?php 

namespace Djaravel\Controllers\Generics;

class DetailController extends BaseController
{

	protected $template = 'generic_detail.html';

	function __construct(){
		parent::__construct();
	}

	function getContextData(...$args){
		$object = $this->model::get(...$args);
		$context = parent::getContextData();
		$context['object'] = $object;
		return $context;
	}
}
