<?php
/**
 * A MySQLi DB Wrapper Class To Perform CRUD Operations
 */
class DB{
	
	protected $host = DB_HOST;
	protected $user = DB_USER;
	protected $pass = DB_PASS;
	protected $dbname = DB_NAME;
	public $link;
	public $error;
	public $error_log;

	public function __construct(){
		
		$this->connect();
	}

	public function connect(){
		
		$this->link = new mysqli( $this ->host, $this ->user, $this ->pass, $this ->dbname );
		
		$this->link->set_charset( 'utf8mb4' );
		
		if( ! $this->link || $this->link->connect_errno ){
			
			$this ->error_log = 'connection failed ' . $this->link->connect_error;

			$this->error = true; return;
		}

		$this->error = false;
	}
	
	public function select( $query ){
		
		return $this->query( $query );
	}

	public function create( $query ){
		
		return $this->query( $query );
	}

	public function insert( $query ){
	    
	    return $this->query( $query );
	}

	public function update( $query ){
	    
	    return $this->query( $query );
	}

	public function delete( $query ){
	    
	    return $this->query( $query );
	}

	public function rows_exists( $select_query ){

		if ( $select_query && mysqli_num_rows( $select_query ) > 0 ) {

			return true;
		}

		return false;
	}

	public function query( $query ){
		
		$result = $this->link->query( $query );
		
		if( $result !== false ){
		   
		   return $result;
		}

		$this ->error_log = $this->link->error . __LINE__;
		
		return false;
	}
}
?>