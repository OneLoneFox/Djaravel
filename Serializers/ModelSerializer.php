<?php

namespace Djaravel\Serializers;
use \Djaravel\Models\Model;

class ModelSerializer{
	public $data;
	function __construct($objects){
		if (is_array($objects)) {
			$data = json_encode(
				array_map(
					function($obj){
						return $obj->serialize();
					},
					$objects
				)
			);
			$this->data = $data;
		}elseif ($objects instanceof Model) {
			$this->data = $objects->toJson();
		}else{
			throw new \InvalidArgumentException('The constructor only accepts instances of Model or an array of instances.');
			
		}
	}
}