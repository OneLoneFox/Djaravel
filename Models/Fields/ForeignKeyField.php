<?php 

namespace Djaravel\Models\Fields;

class ForeignKeyField extends Field{
	public $to;
	public $type = 'int';
	public $validate = FILTER_VALIDATE_INT;

	function __construct($to, $verboseName = null){
		if(!isset($to)){
			throw new \InvalidArgumentException('The property "to" cannot be empty');
		}
		if(!is_string($to)){
			throw new \InvalidArgumentException('The property "to" must be a string reference to the Model');
		}
		$choices = [];
		foreach($to::all() as $obj){
			$choices[$obj->id] = strval($obj);
		}
		parent::__construct(false, false, $verboseName, $choices);
		$this->to = $to;
	}
}