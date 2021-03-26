<?php 

namespace Djaravel\Models;

use \Djaravel\Core\Exceptions\UnimplementedException;
use \Djaravel\Utils\DB;
use \Djaravel\Models\Fields\PrimaryKeyField;

class Model {
	private static $statement;
	private static $query = '';
	private static $queryParams = array();
	private static $orderBy = '';
	private static $_instance;

	static function all(){
		$connection = DB::getConnection();
		$query = $connection->query("SELECT * FROM ".static::$table);
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
		if(!isset(self::$_instance)){
			self::$_instance = new static;
		}
		return self::$_instance;
	}

	static function where(...$args){
		# It's instantly instantiated bc we need to access the getFields method of the child class
		if(!isset(self::$_instance)){
			self::$_instance = new static;
		}
		self::validateQueryArgs(...$args);
		if(count($args) == 2){
			# if the number of arguments is 2 then we use the operator
			# parameter as the value and assume an equality operation
			$query = $args[0] . ' = ?';
			self::$queryParams[] = $args[1];
		}
		if(count($args) == 3){
			# arg[0] is the column, arg[1] is the operator, arg[2] is the value
			$query = $args[0] . ' ' . $args[1] . ' ?';
			self::$queryParams[] = $args[2];
		}
		if(isset(self::$query) and self::$query != ''){
			self::$query = self::$query . ' AND ' . $query;
		}else{
			# First call in the chain
			self::$query = $query;
		}
		return self::$_instance;
	}

	public function orWhere(...$args){
		# Is there any way to prevent doing this copy-paste bs?
		# oh right if only PHP had named arguments. Fuck PHP.
		if(!isset(self::$_instance)){
			self::$_instance = new static;
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
		if(isset(self::$query) and self::$query != ''){
			self::$query = self::$query . " OR " . $query;
		}
		return self::$_instance;
	}

	protected static function validateQueryArgs(...$args){
		if(count($args) == 1){ throw new \InvalidArgumentException("Expected 2 or more arguments, 1 given."); }
		if( !array_key_exists($args[0], static::getFields()) ){
			throw new \InvalidArgumentException("The first argument must be a valid Field name");
		}
	}

	static function orderBy($column, $direction = 'asc'){
		if (!in_array($direction, ['asc', 'desc'])) {
			throw new InvalidArgumentException("The direction must be either 'asc' or 'desc'");
		}
		if (!isset(self::$_instance)) {
			self::$_instance = new static;
		}
		$orderQuery = $column . ' ' . $direction;
		if(isset(self::$orderBy) && self::$orderBy != ''){
			self::$orderBy = self::$orderBy . ', ' . $orderQuery;
		}else{
			// first orderBy call
			self::$orderBy = ' ORDER BY '.$orderQuery;
		}
		return self::$_instance;
	}

	function getQuery(){
		$connection = DB::getConnection();
		if(!isset(self::$statement)){
			$statement = 'SELECT * FROM '.static::$table;
			if (isset(self::$query) && self::$query != '') {
				$statement .= ' where ';
			}
			self::$statement = $statement;
		}
		$query = $connection->prepare(self::$statement.self::$query.self::$orderBy);
		$query->setFetchMode(\PDO::FETCH_CLASS, static::class);
		$query->execute(self::$queryParams);
		$result = $query->fetchAll();

		// clear afterwards to allow making a new query
		self::$_instance = null;
		self::$statement = null;
		self::$query = '';
		self::$orderBy = '';
		self::$queryParams = null;

		return $result;
	}

	function first(){
		return self::getQuery()[0] ?? null;
	}
	
	static function delete($id){
		$connection = DB::getConnection();
		$query = $connection->prepare('DELETE FROM '.static::$table.' WHERE id = ?');
		$query->execute([$id]);
		$count = $query->rowCount();
		return $count > 0;
	}
	
	public function save(){
		// Check if the object already exists. ($this->id !== null)
		if($this->id !== null){
			if(static::exists($this->id)){
				return $this->update();
			}
			throw new \Exception('Could not find object with id '.$this->id);
		}
		// If it doesn't, make an insert query.
		return $this->create();
	}
	
	/**
	 * create
	 *
	 * @return Bool
	 */
	public function create(){
		$connection = DB::getConnection();
		$columns = array_filter(
			static::getFields(),
			function($item){
				return !$item instanceof PrimaryKeyField;
			}
		);
		$valuesQuery = array_map(
			function($c){
				// prepare as :column for VALUES (:column1, :column2, :column3, ...)
				return ":".$c;
			},
			array_keys($columns)
		);
		$valuesQuery = implode(', ', $valuesQuery);
		$data = $this->serialize();
		// We need to remove the id to prevent pdo errors
		unset($data["id"]);
		$insertColumns = implode(', ', array_keys($columns));
		$prep = "INSERT INTO ".static::$table." (".$insertColumns.") VALUES (".$valuesQuery.")";
		$query = $connection->prepare($prep);
		$result = $query->execute($data);

		if ($result) {
			$this->id = $connection->lastInsertId();
		}
		return $result;
	}

	public function update(){
		// If it does then make an update query.
		$connection = DB::getConnection();
		// Get all columns except the primary key.
		$columns = array_filter(
			static::getFields(),
			function($item){
				return !$item instanceof PrimaryKeyField;
			}
		);
		$setQuery = array_map(
			function($c){
				// prepare as column = :column
				return $c." = :".$c;
			},
			array_keys($columns)
		);
		$setQuery = implode(', ', $setQuery);
		$prep = "UPDATE ".static::$table." SET ".$setQuery." WHERE id = :id";
		$query = $connection->prepare($prep);
		// Use the serialized data which is already in a [$k => $v] format
		$data = $this->serialize();
		$result = $query->execute($data);
		$count = $query->rowCount();
		return $count > 0;
	}
	
	/**
	 * Returns the default list url for the model
	 *
	 * @return String
	 */
	static function getListUrl(){
		$url = sprintf("/%s/%s", $_ENV['BASE_DIR'], static::$baseRoute);
		return $url;
	}
	
	/**
	 * Returns the default detail url for the model
	 *
	 * @return String
	 */
	function getDetailUrl(){
		$url = sprintf("/%s/%s/%d",
			$_ENV['BASE_DIR'], static::$baseRoute, $this->id
		);
		return $url;
	}
	
	/**
	 * Returns the default delete url for the model
	 *
	 * @return String
	 */
	public function getDeleteUrl(){
		$url = sprintf("/%s/%s/%d/delete",
			$_ENV['BASE_DIR'], static::$baseRoute, $this->id
		);
		return $url;
	}

	public function __toString(){
		return sprintf('%s object (%s)', get_class($this), $this->id);
	}
	
	/**
	 * Returns an assoiciative array containing the table column names and Field instances
	 *
	 * @return Array[String]Object
	 */
	static function getFields(){
		// This metod must return an array of Field objects that match the database Schema
		throw new UnimplementedException(
			sprintf('The getFields() method is missing on %s', static::class)
		);
	}

	function __get($name){
		# If it's a defined field return the plain value
		if(array_key_exists($name, static::getFields())){
			return $this->{$name};
		}
		# If it's a foreign property look for the correspongin model
		# and get the field from there
		if(strpos($name, '__') !== false){
			$foreignAccess = explode('__', $name);

			$foreignFieldName = $foreignAccess[0];
			$foreignAttribute = $foreignAccess[1];

			# gets the ForeignKeyField object 
			$foreignField = static::getFields()[$foreignFieldName];

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
	}

	function __isset($name){
		# This is probably a bad idea
		// return isset($this->{$name});
		return true;
	}

	function __set($name, $value){
		# some type check and shit to see if the value can be set to that Field->type
		// $field = $this->getFields()[$name];
		// static::validateTypeValue($field, $value);
		
		# ToDo: if it's a foreign key allow the user to set it like myModelB.my_model_a = myModelA; only if my_model_a "fieldToSet" foreign key relatedModel matches the one trying to set

		$this->{$name} = $value;
	}

	/**
	protected static function validateTypeValue($field, $value){
		// Everything is a string so no need to validate that
		if ($field->type !== 'string'){
			if(filter_var($value, $field->validate) === false){
				throw new \InvalidArgumentException(
					sprintf('Validation failed on %s',
						// $value, $type, static::class
					)
				);
			}
		}else{
			// We just validate lenght
			if(strlen($value) > $field->maxLength){
				throw new \InvalidArgumentException(
					sprintf(
						'Value %s(%s) exceeds maximum length (%s) of field (%s)',
						$value, strlen($value), $field->maxLength, $field,
					)
				);
			}
		}
	}
	/**/

	// static function fromArray($array){
	// 	$fields = static::getFields();
	// 	$_instance = new static;
	// 	# filter out any non matching key, we don't need those
	// 	$values = array_filter(
	// 		$array, 
	// 		function($key) use ($fields) {
	// 			return array_key_exists($key, $fields);
	// 		},
	// 		ARRAY_FILTER_USE_KEY
	// 	);

	// 	foreach ($values as $key => $value){
	// 		// The __set() method will handle validation
	// 		$_instance->{$key} = $value;
	// 	}
	// 	# set all matching field values from the array to the object
	// 	return $_instance;
	// }
	
	/**
	 * Converts the object and it's properties (defined on the getFields method) to an associative array
	 *
	 * @return Array[String]String
	 */
	function serialize(){
		$data = [];
		foreach(static::getFields() as $column => $field){
			$data[$column] = $this->{$column};
		}
		return $data;
	}
	
	/**
	 * Converts the object to a JSON string representation
	 *
	 * @return String
	 */
	function toJson(){
		$data = $this->serialize();
		return json_encode($data);
	}

}
