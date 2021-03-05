<?php

namespace Djaravel\Models;

use \Djaravel\Models\Fields\PrimaryKeyField;

class Validator
{
	public $errors = array();
	/**
	 * Provides validation for models and returns errors if any.
	 * 
	 * @param Object $model an instance of \Djaravel\Models\Model
	 * @return Array[fieldName][] returns a 2d array containing the fields that have errors and the errors themselves
	 */
	public function validate($model)
	{
		$fields = $model::getFields();
		$fields = array_filter($fields, function($field){
			return !$field instanceof PrimaryKeyField;
		});
		foreach ($fields as $name => $field){
			$value = $model->{$name};
			if ($field->type !== 'string'){
				if(filter_var($value, $field->validate) === false){
					$this->errors[$name][] = 'Field '.$name.' must be of type '.$field->type;
				}
			}
			if(isset($field->maxLength)){
				if(strlen($value) > $field->maxLength){
					$this->errors[$name][] = sprintf('Exceeded maximum length (%s)',$field->maxLength);
				}
			}
		}
		return $this->errors;
	}
}

/**
class A{
	static function set($name, $value){
		$instance = new static;
		$instance->{$name} = $value;
	}

	public function set2($name, $value){
		$this->{$name} = $value;
	}

	public function __set($name, $value){
		echo "tried to access a protected property, bypassing.";
		$this->{$name} = $value;
	}
}

class B extends A{
	protected $foo;
}

class C{
	static function set($class, $name, $value){
		$instance = new $class;
		$instance->{$name} = $value;
	}
}

$b = new B();

$b::set('foo', 'bar');
$b->set2('foo', 'bar');

C::set(B::class, 'foo', 'baz');
/**/