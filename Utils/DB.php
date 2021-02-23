<?php 

namespace Djaravel\Utils;

class DB
{
	private $query;
	private $queryArray;
	
	static function getConnection()
	{
		// mysqli_report(MYSQLI_REPORT_STRICT);
		$database = $_ENV['DB_NAME'];
		$username = $_ENV['DB_USER'];
		$password = $_ENV['DB_PASSWORD'];
		try {
			// $conn = mysqli_connect($server,$username,$password,$database);
			$conn = new \PDO("mysql:host=localhost;dbname=$database", $username, $password, [
				\PDO::ATTR_PERSISTENT => true,
			]);
			return $conn;
		} catch (Exception $e) {
			echo "Error while connecting to database: <b>".$e->getMessage()."</b>";
			die();
		}
	}
	/**
	function entryexists($table, $column, $value)
	{
		$columnVerification = "select $column from $table where $column = '$value'";
		$conn = $this->getdbconn();
		$res = mysqli_query($conn, $columnVerification);
		$nr = mysqli_num_rows($res);
		if ($nr > 0) {
			return true;
		}else{
			return false;
		}
	}
	function buildquery($arr)
	{
		$this->query = ""; //initialize query
		$numFields = count($arr); //fields array lenght
		$i = 0; //index counter
		foreach ($arr as $key) {
			$this->query .= "'$_POST[$key]'"; //add value to the query eg. 'UTBB' for <<universidad>> field
			if (++$i !== $numFields) { //if not the last item in array
				$this->query .= ", "; //add a semicolon to the query eg. 'UTBB', 'Christian'
			}
		}
		return $this;
	}
	function buildqueryarray($arr, $count)
	{ 
		$this->queryArray = array(); //initialize query
		$numFields = count($arr); //fields array lenght
		for ($x=0; $x < $count; $x++) { 
			$temp = ""; //use a temporary variable to store the query
			$i = 0; //reset index counter
			foreach ($arr as $key) {
				$data = $_POST[$key][$x];
				$temp .= "'$data'";
				if (++$i !== $numFields) { //if not the last item in array
					$temp .= ", ";
				}
			}
			$this->queryArray[$x] = $temp;
		}
		return $this;
	}
	function attachfiles($arr, $colsarr, $path, $authorid)
	{
		$this->query = "";
		$numFields = count($arr);
		$i = 0;
		$pathname = $path."_".$authorid; //name folder as articlename_phoneNumber eg. miarticulo_3221234567;
		if(!is_dir("./resources/uploads/".$pathname."/")) //if the folder does not exist
		{
			#$this->query .= ", ";
			mkdir(__DIR__."/resources/uploads/".$pathname, 0755); //create folder on 'resources/uploads'
		}
		foreach ($arr as $fname) {
				$fileName = pathinfo($_FILES[$fname]['name'], PATHINFO_FILENAME); //name of the uploaded file
				$fileExtension = pathinfo($_FILES[$fname]['name'], PATHINFO_EXTENSION);
				$fileName .= $fileName.date('Y-m-d_H-i-s').".".$fileExtension; //add date and time to file name
				$sourceFile = $_FILES[$fname]['tmp_name']; //file stored on temp
				$target = __DIR__."/resources/uploads/".$pathname."/".$fileName; //target path and file
				if (move_uploaded_file($sourceFile, $target)) { //try to upload files
					$this->query .= "".$colsarr[$i]." = '$pathname/$fileName'";
					if (++$i !== $numFields) {
						$this->query .= ", ";
					}
				}else{ //if the process fails
					#$conn = $this->getdbconn();
					#mysqli_query($conn, "delete from autor where id = $authorid"); //delete the entry to try again
					header("location: error.php?type=upload&code=1");
					die();
				}
			}
			return $this;
		// else{ //if the folder exists
		// 	$conn = $this->getdbconn();
		// 	mysqli_query($conn, "delete from autor where id = $authorid"); //delete the entry to try again
		// 	header("location: error.php?type=upload&code=1");
		// 	die();
		// }
	}
	function getquery()
	{
		return $this->query;
	}
	function getqueryarray()
	{
		return $this->queryArray; //returns an array of queries
	}
	/**/
}
