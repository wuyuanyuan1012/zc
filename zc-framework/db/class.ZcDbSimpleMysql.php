<?php
/**
 * 对于MySQL的数据库非常简单的封装，正因为这个封装很简单，所以特别给名字加了个Simple
 * 
 * @author tangjianhui
 *
 */
class ZcDbSimpleMysql {
	private $connection;
	
	private $errorDisplay;
	
	public function __construct($hostname = '', $username = '', $password = '', $database = '') {
		if (empty($hostname)) {
			$hostname = Zc::C(ZcConfigConst::DbHostname);
			$username = Zc::C(ZcConfigConst::DbUsername);
			$password = Zc::C(ZcConfigConst::DbPassword);
			$database = Zc::C(ZcConfigConst::DbDatabase);
		}
		$this->errorDisplay = Zc::C(ZcConfigConst::MonitorExitOnDbError) == true ? true : false;
		
		if (!$this->connection = mysql_connect($hostname, $username, $password, true)) {
			if($this->errorDisplay) {
				exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
			}
			return false;
		}

		if (!mysql_select_db($database, $this->connection)) {
			if($this->errorDisplay) {
				exit('Error: Could not connect to database ' . $database);
			}
			return false;
		}

		mysql_query("SET NAMES 'utf8'", $this->connection);
		mysql_query("SET CHARACTER SET utf8", $this->connection);
		mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $this->connection);
		mysql_query("SET SQL_MODE = ''", $this->connection);
	}

	public function query($sql) {
		$resource = mysql_query($sql, $this->connection);

		if ($resource) {
			if (is_resource($resource)) {
				$i = 0;
					
				$data = array();

				while ($result = mysql_fetch_assoc($resource)) {
					$data[$i] = $result;

					$i++;
				}

				mysql_free_result($resource);

				$query = new stdClass();
				$query->row = isset($data[0]) ? $data[0] : array();
				$query->rows = $data;
				$query->num_rows = $i;

				unset($data);

				return $query;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
			//exit('Error: ' . mysql_error($this->connection) . '<br />Error No: ' . mysql_errno($this->connection) . '<br />' . $sql);
		}
	}

	public function escape($value) {
		return mysql_real_escape_string($value, $this->connection);
	}

	public function countAffected() {
		return mysql_affected_rows($this->connection);
	}

	public function getLastId() {
		return mysql_insert_id($this->connection);
	}

	public function autoBatchInsert($table, $data) {
		if (!isset($data[0])) {
			return false;
		}
		$firstRow = $data[0];

		$query = 'INSERT INTO `' . $table . '` (';
		foreach ( $firstRow as $key => $value ){
			$query .= '`' . $key . '`,';
		}
		$query = rtrim ( $query, ',' ) . ') VALUES ';

		foreach ($data as $row) {
			$query .= ' (';
			foreach ( $row as $key => $value ) {

				$query .= '\'' . (is_bool($value) ? (int)$value : $this->escape($value)) . '\',';
			}
			$query = rtrim($query, ',') . '),';
		}
		$query = rtrim($query, ',');

		return $this->query($query);
	}

	public function autoExecute($table, $values, $type, $where = false, $limit = false) {
		if (! count ( $values )){
			return true;
		}
			
		if (strtoupper ( $type ) == 'INSERT') {
			$query = 'INSERT INTO `' . $table . '` (';
			foreach ( $values as $key => $value ){
				$query .= '`' . $key . '`,';
			}
			$query = rtrim ( $query, ',' ) . ') VALUES (';
			foreach ( $values as $key => $value ){
				$query .= '\'' . (is_bool ( $value ) ? ( int ) $value : $this->escape($value)) . '\',';
			}
			$query = rtrim ( $query, ',' ) . ')';
			if ($limit){
				$query .= ' LIMIT ' . ( int ) $limit;
			}
			return $this->query ( $query );
		} elseif (strtoupper ( $type ) == 'UPDATE') {
			$query = 'UPDATE `' . $table . '` SET ';
			foreach ( $values as $key => $value ){
				$query .= '`' . $key . '` = \'' . (is_bool ( $value ) ? ( int ) $value : $this->escape($value)) . '\',';
			}
			$query = rtrim ( $query, ',' );
			if ($where){
				$query .= ' WHERE ' . $where;
			}
			if ($limit){
				$query .= ' LIMIT ' . ( int ) $limit;
			}
			return $this->query ( $query );
		}
			
		return false;
	}

	public function __destruct() {
		mysql_close($this->connection);
	}
}