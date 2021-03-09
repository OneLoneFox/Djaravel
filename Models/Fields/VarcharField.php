<?php 

namespace Djaravel\Models\Fields;

class VarcharField extends Field
{	
	/**
	 * maxLength The maximum allowed length. Used for validation.
	 *
	 * @var Int
	 */
	public $maxLength;	
	/**
	 * inputType The type of html input element used
	 *
	 * @var String
	 */
	public $inputType = 'text';	
	/**
	 * type The actual data type. Used for validation.
	 *
	 * @var String
	 */
	public $type = 'string';

	/**
	 * __construct
	 * 
	 * @param Bool $nullable
	 * @param Int $maxLength 
	 * @param String|null $verboseName 
	 * @param Array[Mixed]String|null $choices 
	 * @param Mixed|null $defaultSelected 
	 * 
	 * @return void
	 */
	function __construct($nullable = false, $maxLength, $verboseName = null, $choices = null, $defaultSelected = null){
		if(!isset($maxLength)){
			throw new \InvalidArgumentException('The property "maxLength" cannot be empty');
		}
		if(!is_int($maxLength)){
			throw new \InvalidArgumentException('The property "maxLength" must be an integer');
		}
		parent::__construct(false, $nullable, $verboseName, $choices, $defaultSelected);
		$this->maxLength = $maxLength;
	}
}