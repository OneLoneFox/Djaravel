<?php 

namespace Djaravel\Controllers\Generics;
use \Djaravel\Utils\Shortcuts;
use \Djaravel\Core\Exceptions\UnsuportedMethodException;

class DeleteController extends BaseController
{

	protected $template = 'generic_delete.html';
	protected $success_url;

	function __construct(){
		parent::__construct();
	}

	function getContextData(...$args){
		# fetch object the user wants to delete and render
		# a template with a form with the POST Method
		$object = $this->model::get(...$args);
		$context = parent::getContextData();
		$context['object'] = $object;
		return $context;
	}
	
	function post(...$args){
		# delete the object and redirect
		if($this->model::delete(...$args)){
			$success_url = $this->getSuccessUrl();
			Shortcuts::redirect($success_url);
		}else{
			# User could not delete the object
			http_response_code(403);
		}
	}

	function get(...$args){
		# render the remplate with the given context
		parent::dispatch(...$args);
	}

	function dispatch(...$args){
		# We override the dispatch method because this controller
		# will handle two different (http) methods to prevent accidental deletion

		// Note: If you want to override the functionality you just need to override the corresponding method
		// instead of overriding the dispatch method.
		switch($_SERVER['REQUEST_METHOD']){
			case 'POST':
				$this->post(...$args);
				break;
			case 'GET':
				$this->get(...$args);
				break;
			default:
				http_response_code(405);
				throw new UnsuportedMethodException('This controller must handle POST and GET requests only.');
				break;
		}
	}

	function getSuccessUrl(){
		if(isset($this->success_url)){
			return $this->success_url;
		}else{
			return $this->model::getListUrl();
		}
	}
}
