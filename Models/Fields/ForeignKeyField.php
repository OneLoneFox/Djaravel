<?php 

namespace Djaravel\Models\Fields;

class ForeignKeyField extends Field{
	public $to;

	function __construct($to){
		if(!isset($to)){
			throw new \InvalidArgumentException('The property "to" cannot be empty');
		}
		if(!is_string($to)){
			throw new \InvalidArgumentException('The property "to" must be a string reference to the Model');
		}
		parent::__construct();
		$this->to = $to;
	}
}