<?php 

namespace Djaravel\Models\Fields;

class VarcharField extends Field{
	public $maxLength;
	public $inputType = 'text';
	public $type = 'string';

	function __construct($maxLength, $verboseName = null, $choices = null, $defaultSelected = null){
		if(!isset($maxLength)){
			throw new \InvalidArgumentException('The property "maxLength" cannot be empty');
		}
		if(!is_int($maxLength)){
			throw new \InvalidArgumentException('The property "maxLength" must be an integer');
		}
		parent::__construct(false, false, $verboseName, $choices, $defaultSelected);
		$this->maxLength = $maxLength;
	}
}