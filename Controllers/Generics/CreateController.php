<?php

namespace Djaravel\Controllers\Generics;

use \Djaravel\Models\Validator;
use \Djaravel\Models\Fields\PrimaryKeyField;
use \Djaravel\Core\Exceptions\UnimplementedException;
use \Djaravel\Core\Exceptions\UnsuportedMethodException;
use \Djaravel\Utils\Shortcuts;
use \Djaravel\Utils\ModelFactory;

class CreateController extends BaseController
{
	protected $template = 'generic_create.html';
	protected $success_url;
	protected $validator;

	function __construct(){
		parent::__construct();
		$this->validator = new Validator();
	}

	function getContextData(...$args){
		$modelForm = $this->getForm();
		$form = $modelForm['form'];
		$formData = $modelForm['form_data'];
		$formErrors = $this->validator->errors;
		$context = parent::getContextData(...$args);
		$context['form'] = $form;
		$context['form_data'] = $formData;
		$context['form_errors'] = $formErrors;
		return $context;
	}

	function post(...$args){
		# create a new object from the post data, save and redirect
		// $newObject = $this->model::fromArray($_POST);
		$newObject = ModelFactory::fromArray($this->model, $_POST);
		$errors = $this->validator->validate($newObject);
		if (count($errors) > 0){
			// If there are any errors go back to the form and
			parent::dispatch(...$args);
			die();
		}
		if($newObject->save()){
			$success_url = $this->getSuccessUrl();
			Shortcuts::redirect($success_url);
		}else{
			throw new \Exception('I fucked up didn\'t I?');
		}

		# 403 if save fails
	}

	function get(...$args){
		parent::dispatch(...$args);		
	}

	/**/
	function dispatch(...$args){
		# We override the dispatch method because this controller
		# will handle two different (http) methods to prevent accidental deletion
		switch($_SERVER['REQUEST_METHOD']){
			case 'POST':
				$this->post(...$args);
				break;
			case 'GET':
				# render the remplate with the given context
				$this->get(...$args);
				break;
			default:
				http_response_code(405);
				throw new UnsuportedMethodException('This controller must handle POST and GET requests only.');
				break;
		}
	}
	/**/

	function getForm(){
		$form = new \Formr\Formr();
		$form->action = '';
		// build a form from the defined fields in the model
		$fields = $this->model::getFields();
		$data = array();
		$required = array();
		# formr is a really really badly designed library
		$selectCount = 0;
		foreach ($fields as $name => $field){
			// If it's a primary key we just don't add the id field
			if(!$field instanceof PrimaryKeyField){
				if(!$field->nullable){
					// Field is not nullable
					$required[] = $name;
				}
				if(isset($field->choices)){
					// If choices are set, use a <select> instead of the defined type
					$inputType = 'select'.$selectCount++;
					// The choices are an associative array of value => name pairs
					$options = $field->choices;
					$selected = $field->defaultSelected;
					$data[$inputType] = [
						'id' => $name,
						'name' => $name,
						'label' => $field->verboseName,
						'placeholder' => $field->verboseName,
						'options' => $options,
					];
					if($selected !== null){
						$data[$inputType]['selected'] = $selected;
					}
				}else{
					$inputType = $field->inputType;
					$data[$name] = [
						'type' => $inputType,
						'id' => $name,
						'name' => $name,
						'label' => $field->verboseName,
						'placeholder' => $field->verboseName,
					];
				}
			}
		}
		if(count($required) > 0){
			$form->required = implode(',', $required);
		}
		return [
			'form' => $form,
			'form_data' => $data,
		];
	}

	function getSuccessUrl(...$args){
		if(isset($this->success_url)){
			return $this->success_url;
		}else{
			return $this->model::getListUrl();
		}
	}
}