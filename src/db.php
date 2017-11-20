<?php
	namespace noware;
	
	////include_once ("lib/kint/Kint.class.php");
	//require_once (__DIR__ . "/kint/Kint.class.php");
	//require_once (__DIR__ . "/../required.php");
	//require_once (__DIR__ . "/../functions.php");
	
	//\Kint::trace ();
	
	class db
	{
		//protected static $TABLE = "Users";
		//protected static $TABLE_FIELDS = "Fields";
		// public $message /* The error's message. */;
		// protected $error /* The last error. */, $identifier, $key, $link /* Database connection. */, $connection /* Database connection information. */, $statement, $database, $table, $table_fields;
		
		// protected $error /* The last error. */, $message /* The error's message. */, $identifier, $key, $link /* Database connection. */, $connection /* Database connection information. */, $statement, $database, $table, $table_fields, $is_sqlite;
		// public $current_dir;
		//protected $dsn, $connx;
		protected $dsn, $usr, $key, $cfg;
		protected $db;
		
		public function __construct ()
		{
			$this -> init ();
		}
		
		public function __destruct ()
		{
			//$this -> disconnect ();
			$this -> fin ();
		}
		
		public function __get ($name)
		{
			//switch ($name)
			//{
			//	case 'database';
					return $this -> $name;
			//}
		}
		
		/*
		public function __set ($name, $value)
		{
			if (!self::reserved ($name))
				$this -> $name = $value;
		}
		*/
		
		public function __sleep ()
		{
			return array ('dsn', 'usr', 'key', 'cfg');
		}
		
		public function __wakeup ()
		{
			if ($this -> db != null)
			//$this -> message = $this -> error = "";
				$this -> connect ($this -> dsn, $this -> usr, $this -> key, $this -> cfg);
			//var_dump ($this -> link -> getAttribute(PDO::ATTR_SERVER_INFO));
		}
		
		/*
		// reserved restricted
		public static function reserved ($name)
		{
			switch ($name)
			{
				case 'database':
				case 'connection':
					return true;
				default:
					return false;
			}
		}
		*/
		
		public function begin ()
		{
			return $this -> db -> beginTransaction ();
		}
		
		public function rollback ()
		{
			return $this -> db -> rollBack ();
		}
		
		public function commit ()
		{
			return $this -> db -> commit ();
		}
		
		public function in_transaction (/*null void*/)
		{
			return $this -> db -> inTransaction ();
		}
		
		public function query (&$exception, &$result, $sql, $arg = array (), $cfg = array (), $fetch_mode = /*\PDO::FETCH_NUM*/null)
		{
			if (!$this -> connected ())
				return false;
			
			try
			{
				$stmt = $this -> db -> prepare ($sql);
				
				if ($stmt === false)
					return false;
				
				foreach ($cfg as $name => $value)
					if (!$stmt -> setAttribute ($name, $value))
						return false;
				
				if ($fetch_mode != null)
					if (!$stmt -> setFetchMode ($fetch_mode))
						return false;
				
				
				$success = $stmt -> execute ($data);
				
				
				//$result = array ();
				
				//$result ['err'] ['code'] = $stmt -> errorCode ();
				//$result ['err'] ['msg'] = $stmt -> errorInfo ();
				
				//$result ['statement'] = $_statement -> debugDumpParams ();	// Prints to screen!
				//$_statement -> debugDumpParams ();	// Print to screen.
				//$result ['query'] ['stmt'] = $stmt;
				//$result ['query'] ['arg'] = $arg;
				
				//if ($success)
				//{
					//$result /*['entries'] */[/*'affected:' .*/ 'count'] = $stmt -> rowCount ();
					//$result /*['entries'] */['result'] = $_statement -> fetchAll ();
					//$result /*['entries'] */['data'] = $result ['result'];
					//$result /*['entries'] */['data'] = $stmt -> fetchAll ();
				//}
				$result = $stmt -> fetchAll ();
				
				// Disconnect/Finalize.
				$stmt = null;
				
				return $success;
			}
			catch (\PDOException $_exception)
			{
				//$result = $exception;
				$exception = $_exception;
				
				//$result ['statement'] = $_statement -> debugDumpParams ();
				//$result ['error'] ['code'] = $_statement -> errorCode ();
				//$result ['error'] ['message'] = $_statement -> erroInfo ();
				
				//var_dump ($exception);
				
				//if ($statement == 'update "entity" set "content: type" = ?, "content" = ? where "id" = ? and "key" = ?')
				//{
					//\Kint::trace ();
					//var_dump ($result);
				//}
				
				return false;
			}
		}
		
		//public function connect ($user = "root", $password = "", $settings = "host=localhost", $type = "mysql")
		public function connect ($dsn, $usr = '', $key = '', $cfg = array ())
		{
			if (!$this -> disconnect ())
				return false;
			
			// Save the connection details for reconnecting upon waking up for resuming a session.
			$this -> dsn = $dsn;
			$this -> usr = $usr;
			$this -> key = $key;
			$this -> cfg = $cfg;
			
			try
			{
				$this -> db = new \PDO ($this -> dsn, $this -> usr, $this -> key, $this -> cfg);
				
				$this -> db -> setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
				$this -> db -> setAttribute (\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
				$this -> db -> setAttribute (\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_NATURAL);
				$this -> db -> setAttribute (\PDO::ATTR_STRINGIFY_FETCHES, false);
				// $this -> db -> setAttribute (\PDO::ATTR_AUTOCOMMIT, true);
				//$this -> db -> setAttribute (\PDO::ATTR_EMULATE_PREPARES, true);
				//$this -> db -> setAttribute (\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
				$this -> db -> setAttribute (\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_NUM);
				
				// Apply any user-provided configuration options.
				foreach ($cfg as $name => $value)
					if (!$this -> db -> setAttribute ($name, $value))
						return false;
				
				return true;
			}
			catch (\PDOException $exception)
			{
				//return false;
				return $exception;
			}
		}
		
		public function disconnect ()
		{
			// Reset member variables.
			return $this -> init ();
		}
		
		protected function init ()
		{
			$this -> dsn = '';
			$this -> usr = '';
			$this -> key = '';
			$this -> cfg = array ();
			$this -> db = null;
			
			return true;
		}
		
		protected function inited ()
		{
			//return $this -> connected ();
			return true;
		}
		
		protected function fin ()
		{
			return $this -> disconnect ();
		}
		
		public function connected ()
		{
			return $this -> db != null;
		}
		
		public function autocommit ($value = true)
		{
			if (!$this -> connected ())
				return false;
			
			return $this -> database -> setAttribute (\PDO::ATTR_AUTOCOMMIT, $value);
		}
	}
