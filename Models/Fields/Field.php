<?php 

namespace Djaravel\Models\Fields;

class Field
{	
	/**
	 * primaryKey Wether or not the field is used as PRIMARY KEY
	 *
	 * @var Bool
	 */
	public $primaryKey;	
	/**
	 * nullable Wether or not the field can be NULL
	 *
	 * @var Bool
	 */
	public $nullable;	
	/**
	 * verboseName The readable name of the field. Will also be used for input labels.
	 *
	 * @var String
	 */
	public $verboseName;	
	/**
	 * choices An array containing value => name pairs.
	 * Used for limiting choices and allows the form builder to create a select element.
	 *
	 * @var Array[Mixed]String
	 */
	public $choices;	
	/**
	 * defaultSelected The default value selected for choices.
	 *
	 * @var Mixed
	 */
	public $defaultSelected;
	
	/**
	 * __construct
	 * 
	 * <code>
	 * const MALE = 'male_value'
	 * const FEMALE = 'female_value'
	 * VarcharField(1, ..., [MALE => 'Male', FEMALE => 'Female'], MALE|FEMALE|null);
	 * </code>
	 *
	 * @param  Bool $primaryKey
	 * @param  Bool $nullable
	 * @param  String $verboseName
	 * @param  Array[Mixed]String $choices
	 * @param  Mixed $defaultSelected
	 * 
	 * @return void
	 */
	function __construct($primaryKey = false, $nullable = false, $verboseName = null, $choices = null, $defaultSelected = null){
		$this->primaryKey = $primaryKey;
		$this->nullable = $nullable;
		if(!isset($verboseName)){
			$verboseName = get_class($this);
		}
		$this->verboseName = $verboseName;
		if(isset($choices) && is_array($choices)){
			$this->choices = $choices;
			if(isset($defaultSelected)){
				// Verify that the default selected is a valid choice value.
				if(!array_key_exists($defaultSelected, $this->choices)){
					throw new \InvalidArgumentException(
						"The property defaultSelected must be one of the defined choices' values"
					);
				}
				$this->defaultSelected = $defaultSelected;
			}
		}
	}

	function __toString(){
		return sprintf('%s (%s)', get_class($this), $this->verboseName);
	}
}