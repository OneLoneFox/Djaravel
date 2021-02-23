<?php 

namespace Djaravel\Models\Fields;

class Field{
	public $primaryKey;
	public $nullable;

	function __construct($primaryKey = false, $nullable = false){
		$this->primaryKey = $primaryKey;
		$this->nullable = $nullable;
	}
}