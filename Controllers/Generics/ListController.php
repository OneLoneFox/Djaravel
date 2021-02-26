<?php 

namespace Djaravel\Controllers\Generics;

class ListController extends BaseController
{

	protected $template = 'generic_list.html';

	
	function __construct(){
		parent::__construct();
	}

	function getContextData(...$args){
		$objectList = $this->model::all();
		$context = parent::getContextData();
		$context['object_list'] = $objectList;
		return $context;
	}
}