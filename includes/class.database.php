<?php 
    class Database 
    { 
        // Singleton object. Leave $me alone. 
        private static $me; 

        public $readDB; 
        public $writeDB; 
         
        public $dbType; 
         
        public $readHost; 
        public $writeHost; 

        public $name; 

        public $readUsername; 
        public $writeUsername; 

        public $readPassword; 
        public $writePassword; 

        public $onError; // Can be '', 'die', or 'redirect' 
        public $emailOnError; 
        public $queries; 
        public $result; 

        public $emailTo; // Where to send an error report 
        public $emailSubject; 

        public $errorUrl; // Where to redirect the user on error 

        // Singleton constructor 
        private function __construct() 
        { 
            $this->dbType        = Config::get('dbType'); 
            $this->name          = Config::get('dbName'); 
            $this->onError       = Config::get('dbOnError'); 
            $this->emailOnError  = Config::get('dbEmailOnError'); 
            $this->queries = array(); 
            // MySQL specific stuff 
            if($this->dbType=='mysql') { 
                $this->readHost      = Config::get('dbReadHost'); 
                $this->writeHost     = Config::get('dbWriteHost'); 
                $this->readUsername  = Config::get('dbReadUsername'); 
                $this->writeUsername = Config::get('dbWriteUsername'); 
                $this->readPassword  = Config::get('dbReadPassword'); 
                $this->writePassword = Config::get('dbWritePassword'); 
                $this->readDB  = false; 
                $this->writeDB = false; 
            } else if($this->dbType=='sqlite') {
				//$this->readDB = new SQLite3(DOC_ROOT.DS.'db'.DS.$this->dbName.'db', (SQLITE3_OPEN_READONLY | SQLITE3_OPEN_CREATE) );
				//$this->writeDB = new SQLite3(DOC_ROOT.DS.'db'.DS.$this->dbName.'db');
				die('SQLite support has not been completed yet.');
			} else {
				die('Unsupported database type specified in class.config.php');
			}
        } 

        // Get Singleton object 
        public static function getDatabase() 
        { 
            if(is_null(self::$me)) 
                self::$me = new Database(); 
            return self::$me; 
        } 

        // Do we have a valid read-only database connection? 
        public function isReadConnected() 
        { 
            return is_object($this->readDB); 
        } 

        // Do we have a valid read/write database connection? 
        public function isWriteConnected() 
        { 
            return is_object($this->writeDB); 
        } 
		
		// Are we using MySQL?
		public function isMySQL()
		{
			if($this->dbType=='mysql') return true;
			else return false;
		}
		
        // Do we have a valid database connection and have we selected a database? 
        public function databaseSelected() 
        { 
            if(isMySQL()) {
				if(!$this->isReadConnected()) return false; 
				$result = mysqli_query( $this->readDB, "SHOW TABLES FROM $this->name"); 
            } else {
				$this->readDB->query( "SHOW TABLES FROM $this->name");
			}
			return is_object($result); 
        } 

        public function readConnect() 
        { 
            $this->readDB = mysqli_connect($this->readHost, $this->readUsername, $this->readPassword) or $this->notify(); 
            if($this->readDB === false) return false; 

            if(!empty($this->name)) 
                mysqli_select_db($this->readDB, $this->name) or $this->notify(); 

            return $this->isReadConnected(); 
        } 

        public function writeConnect() 
        { 
            $this->writeDB = mysqli_connect($this->writeHost, $this->writeUsername, $this->writePassword) or $this->notify(); 
            if($this->writeDB === false) return false; 

            if(!empty($this->name)) 
                mysqli_select_db($this->writeDB, $this->name) or $this->notify(); 

            return $this->isWriteConnected(); 
        } 

        public function query($sql, $args_to_prepare = null, $exception_on_missing_args = true) 
        { 
            // Read or Write connection? 
            $sql = trim($sql); 
            if(preg_match('/^(INSERT|UPDATE|REPLACE|DELETE)/i', $sql) == 0) 
            { 
                if(!$this->isReadConnected()) 
                    $this->readConnect(); 

                $the_db = $this->readDB; 
            } 
            else 
            { 
                if(!$this->isWriteConnected()) 
                    $this->writeConnect(); 

                $the_db = $this->writeDB; 
            } 

            // Allow for prepared arguments. Example: 
            // query("SELECT * FROM table WHERE id = :id:", array('id' => $some_val)); 
            if(is_array($args_to_prepare)) 
            { 
                foreach($args_to_prepare as $name => $val) 
                { 
                    $val = $this->quote($val); 
                    $sql = str_replace(":$name:", $val, $sql, $count); 
                    if($exception_on_missing_args && (0 == $count)) 
                        throw new Exception(":$name: was not found in prepared SQL query."); 
                } 
            } 

            $this->queries[] = $sql; 
            $this->result = mysqli_query( $the_db, $sql) or $this->notify(); 
            return $this->result; 
        } 

        // Returns the number of rows. 
        // You can pass in nothing, a string, or a db result 
        public function numRows($arg = null) 
        { 
            $result = $this->resulter($arg); 
            return ($result !== false) ? mysqli_num_rows($result) : false; 
        } 

        // Returns true / false if the result has one or more rows 
        public function hasRows($arg = null) 
        { 
            $result = $this->resulter($arg); 
            return is_object($result) && (mysqli_num_rows($result) > 0); 
        } 

        // Returns the number of rows affected by the previous WRITE operation 
        public function affectedRows() 
        { 
            if(!$this->isWriteConnected()) return false; 
            return mysqli_affected_rows($this->writeDB); 
        } 
		function mysqli_result($result, $pos, $field) { 
			$i=0; 
			$retval=''; 
			while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) { 
				if ($i==$pos) $retval=$row[$field]; 
				$i++; 
			} 
			return $retval; 
		} 
        // Returns the auto increment ID generated by the previous insert statement 
        public function insertId() 
        { 
            if(!$this->isWriteConnected()) return false; 
            return ((is_null($___mysqli_res = mysqli_insert_id($this->writeDB))) ? false : $___mysqli_res); 
        } 

        // Returns a single value. 
        // You can pass in nothing, a string, or a db result 
        public function getValue($arg = null) 
        { 
            $result = $this->resulter($arg); 
            return $this->hasRows($result) ? $this->mysqli_result($result, 0, 0) : false; 
        } 

        // Returns an array of the first value in each row. 
        // You can pass in nothing, a string, or a db result 
        public function getValues($arg = null) 
        { 
            $result = $this->resulter($arg); 
            if(!$this->hasRows($result)) return array(); 

            $values = array(); 
            mysqli_data_seek($result,  0); 
            while($row = mysqli_fetch_array($result,  MYSQLI_ASSOC)) 
                $values[] = array_pop($row); 
            return $values; 
        } 

        // Returns the first row. 
        // You can pass in nothing, a string, or a db result 
        public function getRow($arg = null) 
        { 
            $result = $this->resulter($arg); 
            return $this->hasRows($result) ? mysqli_fetch_array($result,  MYSQLI_ASSOC) : false; 
        } 

        // Returns an array of all the rows. 
        // You can pass in nothing, a string, or a db result 
        public function getRows($arg = null) 
        { 
            $result = $this->resulter($arg); 
            if(!$this->hasRows($result)) return array(); 

            $rows = array(); 
            mysqli_data_seek($result,  0); 
            while($row = mysqli_fetch_array($result,  MYSQLI_ASSOC)) 
                $rows[] = $row; 
            return $rows; 
        } 

        // Escapes a value and wraps it in single quotes. 
        public function quote($var) 
        { 
            return "'" . $this->escape($var) . "'"; 
        } 

        // Escapes a value. 
        public function escape($var) 
        { 
            if(!$this->isReadConnected()) $this->readConnect(); 
            return mysqli_real_escape_string( $this->readDB, $var); 
        } 

        public function numQueries() 
        { 
            return count($this->queries); 
        } 

        public function lastQuery() 
        { 
            if($this->numQueries() > 0) 
                return $this->queries[$this->numQueries() - 1]; 
            else 
                return false; 
        } 

        private function notify() 
        { 
            if($this->emailOnError === true) 
            { 
                $globals = print_r($GLOBALS, true); 

                $msg = ''; 
                $msg .= "Url: " . full_url() . "\n"; 
                $msg .= "Date: " . dater() . "\n"; 
                $msg .= "Server: " . $_SERVER['SERVER_NAME'] . "\n"; 

                $msg .= "ReadDB Error:\n" . ((is_object($this->readDB)) ? mysqli_error($this->readDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "\n\n"; 
                $msg .= "WriteDB Error:\n" . ((is_object($this->writeDB)) ? mysqli_error($this->writeDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "\n\n"; 

                ob_start(); 
                debug_print_backtrace(); 
                $trace = ob_get_contents(); 
                ob_end_clean(); 

                $msg .= $trace . "\n\n"; 

                $msg .= $globals; 

                mail($this->emailTo, $this->emailSubject, $msg); 
            } 

            if($this->onError == 'die') 
            { 
                echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Read Database Error:</strong><br/>" . ((is_object($this->readDB)) ? mysqli_error($this->readDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "</p>"; 
                echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Write Database Error:</strong><br/>" . ((is_object($this->writeDB)) ? mysqli_error($this->writeDB) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . "</p>"; 
                echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Last Query:</strong><br/>" . $this->lastQuery() . "</p>"; 
                echo "<pre>"; 
                debug_print_backtrace(); 
                echo "</pre>"; 
                exit; 
            } 

            if($this->onError == 'redirect') 
            { 
                redirect($this->errorUrl); 
            } 
        } 

        // Takes nothing, a MySQL result, or a query string and returns 
        // the correspsonding MySQL result resource or false if none available. 
        private function resulter($arg = null) 
        { 
            if(is_null($arg) && is_object($this->result)) 
                return $this->result; 
            elseif(is_object($arg)) 
                return $arg; 
            elseif(is_string($arg)) 
            { 
                $this->query($arg); 
                if(is_object($this->result)) 
                    return $this->result; 
                else 
                    return false; 
            } 
            else 
                return false; 
        } 
    } 
?>
