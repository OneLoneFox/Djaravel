<?php 

namespace Djaravel\Models\Fields;

class IntegerField extends Field{
	function __construct($maxLength){
		if(!isset($maxLength)){
			throw new \InvalidArgumentException('The property maxLength cannot be empty');
		}
		if(!is_int($maxLength)){
			throw new \InvalidArgumentException('The property maxLength must be an integer');
		}
		parent::__construct();
	}
}