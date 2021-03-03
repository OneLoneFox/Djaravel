<?php 

namespace Djaravel\Models\Fields;

class IntegerField extends Field{
	public $maxLength;
	public $inputType = 'number';
	public $type = 'int';
	public $validate = FILTER_VALIDATE_INT;
	public $invalidErrorMessage = 'error idk, fuck you.';

	function __construct($maxLength, $verboseName = null, $choices = null, $defaultSelected = null){
		if(!isset($maxLength)){
			throw new \InvalidArgumentException('The property maxLength cannot be empty');
		}
		if(!is_int($maxLength)){
			throw new \InvalidArgumentException('The property maxLength must be an integer');
		}
		parent::__construct(false, false, $verboseName, $choices, $defaultSelected);
		$this->maxLength = $maxLength;
	}
}