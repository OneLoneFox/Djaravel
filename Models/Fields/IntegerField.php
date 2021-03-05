<?php 

namespace Djaravel\Models\Fields;

class IntegerField extends Field
{	
	/**
	 * inputType The type of html input element used
	 *
	 * @var String
	 */
	public $maxLength;	
	/**
	 * inputType The type of html input element used
	 *
	 * @var String
	 */
	public $inputType = 'number';	
	/**
	 * type The actual data type. Used for validation.
	 *
	 * @var String
	 */
	public $type = 'int';
	/**
	 * validate The filter const used for filter_var
	 *
	 * @var Mixed
	 */
	public $validate = FILTER_VALIDATE_INT;
	
	/**
	 * __construct
	 *
	 * @param  Int $maxLength
	 * @param  String $verboseName
	 * @param  Array[Mixed]String $choices
	 * @param  Mixed $defaultSelected
	 * 
	 * @return void
	 */
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