<?php
/**
 * MySQL的mysql模块的驱动
 * 
 * @author tangjianhui 2013-8-16 上午9:02:48
 *
 */
class ZcMysqlDbConnection extends ZcDbConnection {
	const CLIENT_MULTI_RESULTS = 131072;

	private $dbVersion;
	
	public function __construct($config) {
		$this->config = $config;
		$this->connected = false;
	}

	public function connect() {
		if ($this->connected) {
			return;
		}
		$server = $this->config['hostname'] . ($this->config['port'] ? ":{$this->config['port']}" : '');
		$pconnect = $this->config['pconnect'];
		
		if ($pconnect) {
			$this->link = mysql_pconnect($server, $this->config['username'], $this->config['password'], self::CLIENT_MULTI_RESULTS);
		} else {
			$this->link = mysql_connect($server, $this->config['username'], $this->config['password'], true, self::CLIENT_MULTI_RESULTS);
		}
		
		if (!$this->link || (!empty($this->config['database']) && !mysql_select_db($this->config['database'], $this->link))) {
			$this->error('', 'connection');
		}
		
		// 标记连接成功
		$this->connected = true;

		$this->dbVersion = mysql_get_server_info($this->link);
		if ($this->dbVersion >= '4.1' && isset($this->config['charset'])) {
			//使用UTF8存取数据库 需要mysql 4.1.0以上支持
			$this->execute("SET NAMES '". $this->config['charset'] ."'", $this->link);
		}

		//设置 sql_model
		if($this->dbVersion >'5.0.1'){
			$this->execute("SET sql_mode=''", $this->link);
		}
		
		//MySQL要5.0.3以上才支持savepoints.http://dev.mysql.com/doc/refman/5.0/en/savepoint.html
		$this->savepointsSupported = ($this->dbVersion > '5.0.3');
	}

	public function getServerInfo() {
		$this->connect();
		return $this->dbVersion;
	}
	
	private function execute($sql) {
		$this->connect();
		
		$this->queryStr = $sql;
		$result = mysql_query($sql, $this->link);
		//将false的错误，转成Zc自己管理的异常
		if ($result === false) {
			$this->error();
		}
		return $result;
	}

	public function exec($sql) {
		$result = $this->execute($sql);
		
		$this->affectedRows = mysql_affected_rows($this->link);
		$this->lastInsertID = mysql_insert_id($this->link);
		return $this->affectedRows;
	}

	public function query($sql, $resultType = ZcDbConnection::RESULT_ASSOC) {
		$result = $this->execute($sql);
		if ($resultType == ZcDbConnection::RESULT_RAW) {
			return $result;
		}
		
		$rows = array();
		$rowNum = mysql_num_rows($result);
		if($rowNum > 0) {
			if (empty($resultType)) {
				$resultType = ZcDbConnection::RESULT_ASSOC;
			}
			
			while(true) {
				$row = false;
				
				if ($resultType === ZcDbConnection::RESULT_ASSOC) {
					$row = 	mysql_fetch_array($result, MYSQL_ASSOC);
				} else if ($resultType === ZcDbConnection::RESULT_NUM) {
					$row = mysql_fetch_array($result, MYSQL_NUM);
				} else if ($resultType === ZcDbConnection::RESULT_BOTH) {
					$row = mysql_fetch_array($result, MYSQL_BOTH);
				} else {
					$this->error("unsupport result type : $resultType");
				}
				
				if ($row === false) {
					break;
				} else {
					$rows[] = $row;
				}
			}
			mysql_free_result($result);
		}
		return $rows;
	}

	private function error($message = '', $type = 'db') {
		$info = $this->link ? mysql_error($this->link) : '';
		if (!empty($info)) {
			$message .= (empty($message) ? '' : ', ') . $info;
		}
		if ($type == 'connection') {
			throw new ZcDbConnectionException($message, $this->config['db_id'], $this->link ? mysql_errno($this->link) : 0);
		} else {
			throw new ZcDbException($message, $this->queryStr, $this->link ? mysql_errno($this->link) : 0);
		}
	}

	public function close() {
 		if ($this->link && !mysql_close($this->link)) {
 			return $this->error();
 		}
	}

	public function escape($str) {
		$this->connect();
		
		if ($this->link) {
			return mysql_real_escape_string($str, $this->link);
		} else {
			return mysql_escape_string($str);
		}
	}
	
	public function startTransaction($isolationLevel) {
		$this->clearTransactionInfo();
				
		$isolation = 'SET SESSION TRANSACTION ISOLATION LEVEL ';
		if ($isolationLevel == ZcTransactionDefinition::ISOLATION_READ_UNCOMMITTED) {
			$isolation .= 'READ UNCOMMITTED';
		} else if ($isolationLevel == ZcTransactionDefinition::ISOLATION_READ_COMMITTED) {
			$isolation .= 'READ COMMITTED';
		} else if ($isolationLevel == ZcTransactionDefinition::ISOLATION_REPEATABLE_READ) {
			$isolation .= 'REPEATABLE READ';
		} else if ($isolationLevel == ZcTransactionDefinition::ISOLATION_SERIALIZABLE) {
			$isolation .= 'SERIALIZABLE';
		} else {
			$this->error("unsupported isolation level $isolationLevel");
		}
		
		$this->exec('SET AUTOCOMMIT=0');
		$this->exec($isolation);
		$this->exec('START TRANSACTION');
		
		$this->transactionActive = true;
	}
	
	public function commit() {
		$this->exec('COMMIT');
		$this->exec('SET AUTOCOMMIT=1');
		$this->transactionActive = false;
	}

	public function rollback() {
		$this->exec('ROLLBACK');
		$this->exec('SET AUTOCOMMIT=1');
		$this->transactionActive = false;
	}

	public function supportsSavepoints() {
		$this->connect();
		return $this->connected && $this->savepointsSupported;
	}
	
	public function createSavepoint() {
		$this->connect();
		$this->savepointCounter++;
		$savepoint = 'SAVEPOINT_' . $this->savepointCounter;
		return $savepoint;
	}

	public function rollbackToSavepoint($savepoint) {
		$this->exec("ROLLBACK TO SAVEPOINT $savepoint");
		$this->savepointCounter--;
	}

	public function releaseSavepoint($savepoint) {
		$this->exec("RELEASE SAVEPOINT $savepoint");
		$this->savepointCounter--;
	}
}