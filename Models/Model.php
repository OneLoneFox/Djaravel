<?php 

namespace Djaravel\Models;

class Model {
	// private $query;
	static function all(){
		$connection = \Djaravel\Utils\DB::getConnection();
		$query = $connection->query("select * from ".static::$table);
		$result = $query->fetchAll(\PDO::FETCH_CLASS, static::class);
		return $result;
	}

	static function exists($id){
		return static::get($id);
	}
	
	static function get($id){
		$connection = \Djaravel\Utils\DB::getConnection();
		$query = $connection->prepare("select * from ".static::$table." where id = ?");
		$query->setFetchMode(\PDO::FETCH_CLASS, static::class);
		$query->execute([$id]);
		$result = $query->fetch();
		return $result;
	}

	/* nope, this won't work */
	static function where($filters, $operator = "and"){
		$filterString = implode(" $operator ", array_map(
			function($k, $v){ return "$k = $v"; },
			array_keys($filters),
			$filters)
		);
		return $filterString;
	}
	/**/
	
	static function delete($id){
		$connection = \Djaravel\Utils\DB::getConnection();
		$query = $connection->prepare("delete from ".static::$table." where id = ?");
		$query->execute([$id]);
		$count = $query->rowCount();
		return $count > 0;
	}
	
	public function save(){
		echo sprintf("saving object %s into %s table", get_class($this), $this->table);
		// Save any changes to the model into the database
	}

	static function getListUrl(){
		$url = sprintf("/%s/%s", $_ENV['BASE_DIR'], static::$baseRoute);
		return $url;
	}

	function getDetailUrl(){
		$url = sprintf("/%s/%s/%d",
			$_ENV['BASE_DIR'], static::$baseRoute, $this->id
		);
		return $url;
	}

	public function getDeleteUrl(){
		$url = sprintf("/%s/%s/%d/delete",
			$_ENV['BASE_DIR'], static::$baseRoute, $this->id
		);
		return $url;
	}

	public function __toString(){
		return sprintf('<%s object (%s)>', get_class($this), $this->id);
	}

	function getFields(){
		// This metod must return an array of Field objects that match the database Schema
		throw new \Djaravel\Core\Exceptions\UnimplementedException('You must implement the getFields() method in your model');
	}

	function __get($name){
		$foreignAccess = explode('__', $name);
		if(count($foreignAccess) > 1){
			$foreignFieldName = $foreignAccess[0];
			$foreignAttribute = $foreignAccess[1];

			# gets the ForeignKeyField object 
			$foreignField = $this->getFields()[$foreignFieldName];

			# gets the raw foreign key as if it was returning $this->{$name} without all the __relatedAttribute stuff
			$fkValue = $this->{$foreignFieldName};

			# calls the get method on the "to" model (hard to explain AAAAAAA)
			$foreignValue = $foreignField->to::get($fkValue);

			return $foreignValue->{$foreignAttribute};
		}else{
			return $this->{$name};
		}
	}

	function __isset($name){
		# Twig won't work without this...
		# W H A T ?
		// return isset($this->{$name});
		return true;
	}

	function __set($name, $value){
		$this->{$name} = $value;
		# some type check and shit to see if the value can be set to that TypeField
		# if it's a foreign key allow the user to set it like myModelB.my_model_a = myModelA; only if my_model_a "fieldToSet" foreign key relatedModel matches the one trying to set
	}

	function serialize(){
		$data = [];
		foreach($this->getFields() as $column => $field){
			$data[$column] = $this->{$column};
		}
		return $data;
	}

}
