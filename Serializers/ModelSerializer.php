<?php

namespace Djaravel\Serializers;

class ModelSerializer{
	public $data;
	function __construct($objects){
		$data = json_encode(
			array_map(
				function($obj){
					return $obj->serialize();
				},
				$objects
			)
		);
		$this->data = $data;
	}
}