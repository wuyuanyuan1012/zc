<?php
/**
 * MeekroDB风格的构造SQL的工具类
 * 
 * @author tangjianhui 2013-8-19 下午12:43:41
 *
 */
class ZcSqlBuilder {
	public $argChar = '%';
	public $namedArgSeperator = ':';
	public $useNull = true;
	
	// ZcSqlBuilder依赖的连接
	/**
	 * @var ZcDbConnection
	 */
	private $connection;
	public function __construct($connection) {
		$this->connection = $connection;
	}
	
	/**
	 * array($limitSelectSql, $totalCountSql)
	 * 
	 * @param string $selectSql
	 * @param integer $pageNum
	 * @param integer $pageSize
	 * @return array
	 */
	public function buildQueryPage($selectSql, $pageNum, $pageSize) {
		$limitSelectSql = ltrim($selectSql . " LIMIT " . $pageSize * ($pageNum - 1) . ", " . $pageSize);
		$limitSelectSql = "SELECT SQL_CALC_FOUND_ROWS" . substr($limitSelectSql, 6);
		
		$totalCountSql = "SELECT FOUND_ROWS()";
		
		return array($limitSelectSql, $totalCountSql);
	}
	
	public function buildInsert($args) {
		$table = array_shift($args);
		$datas = array_shift($args);
		
		$datas = unserialize(serialize($datas));
		$keys = $values = array();
		
		if (isset($datas[0]) && is_array($datas[0])) {
			foreach ($datas as $datum) {
				ksort($datum);
				if (!$keys) {
					$keys = array_keys($datum);
				}
				$values[] = array_values($datum);
			}
		} else {
			$keys = array_keys($datas);
			$values = array_values($datas);
		}
		
		return $this->buildSql('INSERT INTO ' . $this->argChar . 'b ' . $this->argChar . 'lb VALUES ' . $this->argChar . '?', $table, $keys, $values);
	}
	
	public function buildUpdate($args) {
		$table = array_shift($args);
		$params = array_shift($args);
		$where = array_shift($args);
		
		$query = 'UPDATE ' . $this->argChar . 'b set ' . $this->argChar . '? WHERE ' . $where;
		
		array_unshift($args, $params);
		array_unshift($args, $table);
		array_unshift($args, $query);
		
		return call_user_func_array(array($this, 'buildSql'), $args);
	}
	
	public function buildDelete($args) {
		$table = $this->formatTableName(array_shift($args));
		$where = array_shift($args);
		
		$buildQuery = "DELETE FROM $table WHERE $where";
		array_unshift($args, $buildQuery);
		return call_user_func_array(array($this, 'buildSql'), $args);
	}
	
	public function buildQuery($args) {
		return call_user_func_array(array($this, 'buildSql'), $args);
	}

	/**
	 * 返回指定位置的参数，如果参数数量不足的话，目前是直接返回NULL
	 * 
	 * @param SQL片段 $chunk
	 * @param 参数集合 $args
	 * @param 开始检查的位置 $testPos
	 * @param 返回命名结束的位置 $lastPos
	 * @throws ZcDbException
	 * @return mixed
	 */
	private function getArg(&$chunk, &$args, $testPos, &$lastPos) {
		if ($testPos >= strlen($chunk) || $chunk[$testPos] !== $this->namedArgSeperator) {
			$lastPos = $testPos;
			return array_shift($args);
		} else {
			$argNameLength = strspn($chunk, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_', $testPos + 1);
			$argName = substr($chunk, $testPos + 1, $argNameLength);
	
			if (count($args) != 1 || !is_array($args[0])) {
				throw new ZcDbException("If use named parameters, the second argument must be an array of parameters");
			}
			if (!array_key_exists($argName, $args[0])) {
				throw new ZcDbException("Non argument reference (arg $argName): $chunk");
			}
			
			$lastPos = $testPos + 1 + $argNameLength;
			return $args[0][$argName];
		}
	}
	
	/**
	 * 真正拼接SQL的方法
	 * 
	 * @return string
	 */
	public function buildSql() {
		$args = func_get_args();
		
		$sql = trim(array_shift($args));
		if (count($args) == 0) {
			return $sql;
		}
		
		$chunks = explode($this->argChar, $sql);
		
		$len = count($chunks);
		$isParamStart = ($sql[0] === $this->argChar);
		$lastPos = 0;
		
		for($i = 0; $i < $len; $i++) {
			if ($i === 0 && (!$isParamStart || empty($chunks[0]))) {
				continue;
			}
				
			$chunk = $chunks[$i];
			$chunkLen = strlen($chunk);
				
			$ch0 = ($chunkLen > 0) ? $chunk[0] : '';
			$ch1 = ($chunkLen > 1) ? $chunk[1] : '';
			$ch2 = ($chunkLen > 2) ? $chunk[2] : '';
				
			if ($ch0 === 'l') {
				if ($ch1 === 's') {
					if ($ch2 === 's') {
						$arg = $this->getArg($chunk, $args, 3, $lastPos);
						$ret =  $this->escape('%' . str_replace(array('%', '_'), array('\%', '\_'), $arg));
						$chunks[$i] = $ret . substr($chunk, $lastPos);
					} else {
						$arg = $this->getArg($chunk, $args, 2, $lastPos);
						$ret = array_map(array($this, 'escape'), $arg);
						$chunks[$i] = '(' . implode(',', $ret) . ')' . substr($chunk, $lastPos);
					}
				} else if ($ch1 === 'i') {
					$arg = $this->getArg($chunk, $args, 2, $lastPos);
					$ret = array_map(array($this, 'intval'), $arg);
					$chunks[$i] = '(' . implode(',', $ret) . ')' . substr($chunk, $lastPos);
				} else if ($ch1 === 'd') {
					$arg = $this->getArg($chunk, $args, 2, $lastPos);
					$ret = array_map('doubleval', $arg);
					$chunks[$i] = '(' . implode(',', $ret) . ')' . substr($chunk, $lastPos);
				} else if ($ch1 === 't') {
					$arg = $this->getArg($chunk, $args, 2, $lastPos);
					$ret = array_map(array($this, 'escape'), array_map(array($this, 'parseTS'), $arg));
					$chunks[$i] = '(' . implode(',', $ret) . ')' . substr($chunk, $lastPos);
				} else if ($ch1 === 'b') {
					$arg = $this->getArg($chunk, $args, 2, $lastPos);
					$ret = array_map(array($this, 'formatTableName'), $arg);
					$chunks[$i] = '(' . implode(',', $ret) . ')' . substr($chunk, $lastPos);
				} else if ($ch1 === 'l') {
					$arg = $this->getArg($chunk, $args, 2, $lastPos);
					$chunks[$i] = '(' . implode(',', $arg) . ')' . substr($chunk, $lastPos);
				} else {
					$arg = $this->getArg($chunk, $args, 1, $lastPos);
					$chunks[$i] = $arg . substr($chunk, $lastPos);
				}
			} else if ($ch0 === 's') {
				if ($ch1 === 's') {
					$arg = $this->getArg($chunk, $args, 2, $lastPos);
					$ret =  $this->escape('%' . str_replace(array('%', '_'), array('\%', '\_'), $arg) . '%');
					$chunks[$i] = $ret . substr($chunk, $lastPos);
				} else {
					$arg = $this->getArg($chunk, $args, 1, $lastPos);
					$chunks[$i] = $this->escape($arg) . substr($chunk, $lastPos);
				}
			} else if ($ch0 === 'i') {
				$arg = $this->getArg($chunk, $args, 1, $lastPos);
				$chunks[$i] = $this->intval($arg) . substr($chunk, $lastPos);
			} else if ($ch0 === 'd') {
				$arg = $this->getArg($chunk, $args, 1, $lastPos);
				$chunks[$i] = doubleval($arg) . substr($chunk, $lastPos);
			} else if ($ch0 === 'b') {
				$arg = $this->getArg($chunk, $args, 1, $lastPos);
				$chunks[$i] = $this->formatTableName($arg) . substr($chunk, $lastPos);
			} else if ($ch0 === 't') {
				$arg = $this->getArg($chunk, $args, 1, $lastPos);
				$chunks[$i] = $this->escape($this->parseTS($arg)) . substr($chunk, $lastPos);
			} else if ($ch0 === '?') {
				$arg = $this->getArg($chunk, $args, 1, $lastPos);
				$chunks[$i] = $this->sanitize($arg) . substr($chunk, $lastPos);
			} else if ($ch0 === 'r' && $ch1 === 's' && $ch2 === 's') {
				$arg = $this->getArg($chunk, $args, 3, $lastPos);
				$ret =  $this->escape(str_replace(array('%', '_'), array('\%', '\_'), $arg) . '%');
				$chunks[$i] = $ret . substr($chunk, $lastPos);
			} else {
				// 如果走到这里，说明被$argChar分割了，可是没有符合的替换
				$chunks[$i] = $this->argChar . $chunks[$i];
			}
		}
		
		return implode('', $chunks);
	}
	
	private function escape($arg) {
		return  "'" . ($this->connection ? $this->connection->escape($arg) : mysql_escape_string($arg)) . "'";
	}
	
	private function intval($var) {
		return PHP_INT_SIZE == 8 ? intval($var) : floor(doubleval($var));
	}
	
	private function formatTableName($table) {
		$table = trim($table, '`');
		if (strpos($table, '.')) {
			return implode('.', array_map(array($this, 'formatTableName'), explode('.', $table)));
		} else {
			return '`' . str_replace('`', '``', $table) . '`';
		}
	}
	
	private function parseTS($ts) {
		if (is_string($ts)) {
			return date('Y-m-d H:i:s', strtotime($ts));
		} else if (is_object($ts) && ($ts instanceof DateTime)) {
			return $ts->format('Y-m-d H:i:s');
		}
	}
	
	private function sanitize($value) {
		if (is_object($value)) {
			if ($value instanceof ZcDbEval) {
				return $this->buildQuery($value->getArgs());
			} else if ($value instanceof DateTime) {
				return $this->escape($value->format('Y-m-d H:i:s'));
			} else {
				return '';
			}
		}
		
		if (is_null($value)) {
			return $this->useNull ? "NULL" : "''";
		} else if (is_bool($value)) {
			return ($value ? 1 : 0);
		} else if (is_int($value) || is_float($value)) {
			return $value;
		} else if (is_array($value)) {
			if (array_values($value) === $value) {
				if (is_array($value[0])) {
					return implode(', ', array_map(array($this, 'sanitize'), $value));
				} else {
					return  '(' . implode(', ', array_map(array($this, 'sanitize'), $value)) . ')';
				}
			}
			
			$pairs = array();
			foreach ($value as $k => $v) {
				$pairs[] = $this->formatTableName($k) . '=' . $this->sanitize($v);
			}
			return implode(', ', $pairs);
		} else {
			return $this->escape($value);
		}
	}
	
}