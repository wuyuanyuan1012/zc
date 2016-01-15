<?php
/**
 * 这个依赖于ZenCart自带的数据库类。所以Zc框架剥离出来后，已经不保证可用了，仅供参考，自己写handler
 * 
 * @author tangjianhui 2013-6-29 下午5:33:48
 *
 */
class ZcDbSessionHandler extends ZcSessionHandler {
	private $db;
	private $useSelfDb = false;

	public function __construct($options = array()) {
		parent::__construct();

		$this->useSelfDb = false;

		global $db;
		if (!is_object($db)) {
			//PHP 5.2.0 bug workaround。这是ZenCart原来的注释，Jianhui迁移Session的时候，迁移过来的。
			$db = new queryFactory();
			$db->connect($options['host'], $options['username'], $options['password'], $options['database'], $options['use_pconnect'], false);
			$this->useSelfDb = true;
		}
		$this->db = $db;
	}

	public function __destruct() {
		$this->sessionLog->debug('in db session __destruct');
	}

	public function open($save_path, $session_name) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in db session open, save_path is $save_path, and session_name is $session_name");
		}
		return true;
	}

	public function close() {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug('in db session close');
		}

		if ($this->useSelfDb) {
			$this->db->close();
		}
		return true;
	}

	public function read($key) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in db session read, the key is $key");
		}

		$qid = "select value
              from " . TABLE_SESSIONS . "
              where sesskey = '" . $this->db->prepare_input($key) . "'
              and expiry > '" . time() . "'";

		$value = $this->db->Execute($qid, false, false, 0, 'db');

		if (isset($value->fields['value']) && $value->fields['value']) {
			return $value->fields['value'];
		}

		return ("");
	}

	public function write($key, $val) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in db session write $key $val");
		}

		$expiry = time() + $this->lifeTime;
		$value = $val;

		$qid = "select count(*) as total
              from " . TABLE_SESSIONS . "
              where sesskey = '" . $this->db->prepare_input($key) . "'";
		$total = $this->db->Execute($qid, false, false, 0, 'db');

		if ($total->fields['total'] > 0) {
			$sql = "update " . TABLE_SESSIONS . "
                set expiry = '" . $this->db->prepare_input($expiry) . "', value = '" . $this->db->prepare_input($value) . "'
                where sesskey = '" . $this->db->prepare_input($key) . "'";

			return $this->db->Execute($sql);

		} else {
			$sql = "insert into " . TABLE_SESSIONS . "
                values ('" . $this->db->prepare_input($key) . "', '" . $this->db->prepare_input($expiry) . "', '" .
                $this->db->prepare_input($value) . "')";

			return $this->db->Execute($sql);
		}
	}

	public function destroy($key) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in db session destroy, the key is $key");
		}

		$sql = "delete from " . TABLE_SESSIONS . " where sesskey = '" . $this->db->prepare_input($key) . "'";
		return $this->db->Execute($sql);
	}

	public function gc($maxlifetime) {
		if ($this->sessionLog->isEnableLogLevel(ZcLog::DEBUG)) {
			$this->sessionLog->debug("in db session gc, gc = $maxlifetime");
		}

		$sql = "delete from " . TABLE_SESSIONS . " where expiry < " . time();
		$this->db->Execute($sql);
		return true;
	}
}