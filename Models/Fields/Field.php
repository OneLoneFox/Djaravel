<?php 

namespace Djaravel\Models\Fields;

class Field{
	public $primaryKey;
	public $nullable;
	public $verboseName;
	public $choices;
	public $defaultSelected;

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