<?php 

namespace Djaravel\Models;

use \Djaravel\Core\Exceptions\UnimplementedException;
use \Djaravel\Utils\DB;

class Model {
	private static $statement;
	private static $query;
	private static $queryParams = array();
	private static $_instance;

	static function all(){
		$connection = DB::getConnection();
		$query = $connection->query("select * from ".static::$table);
		$result = $query->fetchAll(\PDO::FETCH_CLASS, static::class);
		return $result;
	}

	static function exists($id){
		return static::get($id);
	}
	
	static function get($id){
		$connection = DB::getConnection();
		$query = $connection->prepare("SELECT * FROM ".static::$table." WHERE id = ?");
		$query->setFetchMode(\PDO::FETCH_CLASS, static::class);
		$query->execute([$id]);
		$result = $query->fetch();
		return $result;
	}

	static function select(...$args){
		$statement = 'SELECT ';
		$columns = implode(', ', $args);
		static::$statement = $statement . $columns . ' FROM ' . static::$table . ' WHERE ';
		if(!isset(static::$_instance)){
			static::$_instance = new static;
		}
		return self::$_instance;
	}

	static function where(...$args){
		# It's instantly instantiated bc we need to access the getFields method of the child class
		if(!isset(static::$_instance)){
			static::$_instance = new static;
		}
		self::validateQueryArgs(...$args);
		if(count($args) == 2){
			# if the number of arguments is 2 then we use the operator
			# parameter as the value and assume an equality operation
			$query = $args[0] . ' = ?';
			static::$queryParams[] = $args[1];
		}
		if(count($args) == 3){
			$query = $args[0] . ' ' . $args[1] . ' ?';
			static::$queryParams[] = $args[2];
		}
		if(isset(static::$query) and static::$query != ''){
			static::$query = static::$query . ' AND ' . $query;
		}else{
			# First call in the chain
			static::$query = $query;
		}
		return static::$_instance;
	}

	public function orWhere(...$args){
		# Is there any way to prevent doing this copy-paste bs?
		# oh right if only PHP had named arguments. Fuck PHP.
		if(!isset(static::$_instance)){
			static::$_instance = new static;
		}
		self::validateQueryArgs(...$args);
		if(count($args) == 2){
			# if the number of arguments is 2 then we use the operator
			# parameter as the value and assume an equality operation
			$query = $args[0] . " = " . $args[1];
		}
		if(count($args) == 3){
			$query = $args[0] . " " . $args[1] . " " . $args[2];
		}
		if(isset(static::$query) and static::$query != ''){
			static::$query = static::$query . " OR " . $query;
		}
		return static::$_instance;
	}

	private static function validateQueryArgs(...$args){
		if(count($args) == 1){ throw new \InvalidArgumentException("Expected 2 or more arguments, 1 given."); }
		if( !array_key_exists($args[0], static::$_instance->getFields()) ){
			throw new \InvalidArgumentException("The first argument must be a valid Field name");
		}
	}

	function getQuery(){
		$connection = DB::getConnection();
		if(!isset(static::$statement)){
			$statement = 'SELECT * FROM '.static::$table.' where ';
			static::$statement = $statement;
		}
		$query = $connection->prepare(static::$statement.static::$query);
		$query->setFetchMode(\PDO::FETCH_CLASS, static::class);
		$query->execute(static::$queryParams);
		$result = $query->fetchAll();
		return $result;
	}
	
	static function delete($id){
		$connection = DB::getConnection();
		$query = $connection->prepare('DELETE FROM '.static::$table.' WHERE id = ?');
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
		throw new UnimplementedException('You must implement the getFields() method in your model');
	}

	function __get($name){
		# If it's a defined field return the plain value
		if(array_key_exists($name, $this->getFields())){
			return $this->{$name};
		}
		# If it's a foreign property look for the correspongin model
		# and get the field from there
		if(strpos($name, '__') !== false){
			$foreignAccess = explode('__', $name);

			$foreignFieldName = $foreignAccess[0];
			$foreignAttribute = $foreignAccess[1];

			# gets the ForeignKeyField object 
			$foreignField = $this->getFields()[$foreignFieldName];

			# gets the raw foreign key as if it was returning $this->{$name} without all the __relatedAttribute stuff
			$fkValue = $this->{$foreignFieldName};

			# calls the get method on the "to" model (hard to explain AAAAAAA)
			$foreignValue = $foreignField->to::get($fkValue);

			return $foreignValue->{$foreignAttribute};
		}
		
		if(method_exists($this, $name)){
			$childClass = call_user_func([$this, $name]);
			return $childClass;
		}
		// # If it's a field_set make a reverse lookup and return all the child objects
		// if(strpos($name, '_set') !== false){
		// 	$childrenName = explode('_set', $name);

		// 	# how the fuck do I get the corresponding model?
		// 	# options:
		// 	# - first character to upper case and try to instance the result (dirty and still needs namespace)
		// 	# 
		// }
	}

	function __isset($name){
		# This is probably a bad idea
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

	function toJson(){
		$data = $this->serialize();
		return json_encode($data);
	}

}
