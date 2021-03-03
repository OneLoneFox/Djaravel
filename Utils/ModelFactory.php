<?php 

namespace Djaravel\Utils;

class ModelFactory
{
	static function fromArray($model, $array){
		$fields = $model::getFields();
		$_instance = new $model;
		# filter out any non matching key, we don't need those
		$values = array_filter(
			$array, 
			function($key) use ($fields) {
				return array_key_exists($key, $fields);
			},
			ARRAY_FILTER_USE_KEY
		);

		foreach ($values as $key => $value){
			$_instance->{$key} = $value;
		}
		# set all matching field values from the array to the object
		return $_instance;
	}
}