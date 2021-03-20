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
			// Validate value type
			if ($field->type !== 'string'){
				if(filter_var($value, $field->validate) === false){
					$this->errors[$name][] = 'Field '.$name.' must be of type '.$field->type;
				}
			}
			// Validate max length of field
			// ignore if maxLength is set to -1
			if(isset($field->maxLength) && $field->maxLength !== -1){
				if(strlen($value) > $field->maxLength){
					$this->errors[$name][] = sprintf('Exceeded maximum length (%s)',$field->maxLength);
				}
			}
			// Validate choices
			if (isset($field->choices)) {
				// Makes sure the selected value exists as a choice
				if (!array_key_exists($value, $field->choices)) {
					$this->errors[$name][] = 'The value is not a valid choice';
				}
			}
		}
		return $this->errors;
	}
}

/**
 * Here lies the reason the model validation has to be in a class
 * instead of being handled from within the parent class' (Djaravel\Models\Model) __set method
class A{
	static function set($name, $value){
		// A parent class can access the protected properties of $instance the same way it can
		// access them from $this
		// Which means it will never get to echo what's inside the __set function
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
	// The only way to get to the __set method is to access the protected
	// properties of $instance from anywhere outside the parent class
	static function set($class, $name, $value){
		$instance = new $class;
		$instance->{$name} = $value;
	}
}

$b = new B();

$b::set('foo', 'bar');
$b->set2('foo', 'bar');

C::set(B::class, 'foo', 'baz');
// Thanks PHP, fuck you.
/**/