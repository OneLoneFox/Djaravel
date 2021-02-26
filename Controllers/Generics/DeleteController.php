<?php 

namespace Djaravel\Controllers\Generics;

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

	function dispatch(...$args){
		# We override the dispatch method because this controller
		# will handle two different (http) methods to prevent accidental deletion
		switch($_SERVER['REQUEST_METHOD']){
			case 'POST':
				# delete the object and redirect
				$success_url = $this->getSuccessUrl();

				if($this->model::delete(...$args)){
					\Djaravel\Utils\Shortcuts::redirect($success_url);
				}else{
					# User could not delete the object
					http_response_code(403);
				}
				break;
			case 'GET':
				# render the remplate with the given context
				parent::dispatch(...$args);
				break;
			default:
				http_response_code(405);
				throw \Djaravel\Core\Exceptions\UnsuportedMethodException('This controller must handle POST and GET requests only.');
				break;
		}
	}

	function getSuccessUrl(...$args){
		if(isset($this->success_url)){
			return $this->success_url;
		}else{
			return $this->model::getListUrl();
		}
	}
}
