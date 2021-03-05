<?php 

namespace Djaravel\Models\Fields;

class ForeignKeyField extends Field
{	
	/**
	 * to A reference to a Model class that extends \Djaravel\Models\Model which MUST be the same as the foreign key in the database
	 *
	 * @var String
	 */
	public $to;	
	/**
	 * type The actual data type. Used for validation.
	 *
	 * @var string
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
	 * @param  String $to
	 * @param  String $verboseName
	 * 
	 * @return void
	 */
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