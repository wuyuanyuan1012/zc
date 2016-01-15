<?php
/**
 * 配置类
 * 
 * @author tangjianhui
 *
 */
class ZcConfig {
	private $config = array();

	public function __construct() {
		$this->config = array (
				ZcConfigConst::DirFsApp => '',
				ZcConfigConst::LanguageDefault => 'english',
				ZcConfigConst::LanguageCurrent => 'chinese',
				ZcConfigConst::UrlHandler => array (
								'class' => 'ZcDefaultUrlHandler'
								),
				ZcConfigConst::DefaultRoute => 'home/main/index',
				ZcConfigConst::DefaultTimezone => 'Asia/Shanghai',
				ZcConfigConst::DefaultOutputEncoding => 'utf-8',
				ZcConfigConst::CleanQuotes => true,
				ZcConfigConst::Pagination => array(
												'pageSize' => 20,
												'pageParamName' => 'page',
												'defautUseGetParams' => true,
												'prevATitle' => 'Previous Page',
												'prevText' => '&lt;&lt;',
												'nextATitle' => 'Next Page',
												'nextText' => '&gt;&gt;',
												'pageATitle' => 'Page %u',
												'themeCssClass' => 'zc-pagination-light-theme',
												'edgeLen' => 2,
												'minSideLen' => 9,
												'maxSideLen' => 12,
												'midLen' => 9,
												'remainLen' => 4,
											),
				
				ZcConfigConst::MonitorAutostart => false,
				ZcConfigConst::MonitorDbServer => '192.168.10.251',
				ZcConfigConst::MonitorDbUsername => 'tmart_db_dev',
				ZcConfigConst::MonitorDbPassword => 'tmart_db_dev',
				ZcConfigConst::MonitorDbDatabase => 'tmart_dev',
				ZcConfigConst::MonitorExitOnDbError => true,
				
				ZcConfigConst::LogDir => '/tmp/zc/',
				ZcConfigConst::LogLevel => ZcLog::INFO,
                ZcConfigConst::LogHandlerLogstashredisEnable => true,
				ZcConfigConst::LogHandlerLogstashredisHost => '192.168.0.2',
				ZcConfigConst::LogHandlerLogstashredisPort => 6379,
				ZcConfigConst::LogHandlerLogstashredisKey => 'logstash',
				ZcConfigConst::DbConfig => array (
						'db_cache' => array(
									'biz_name' => 'zc_db_cache',
									'cache_type' => 'memcached',
									'timestamp' => '20130825',
									'options' => array(array('host' => '192.168.0.2', 'port' => '11211')),
								),
						'tx_def' => array(
									'isolation_level' => ZcTransactionDefinition::ISOLATION_READ_COMMITTED,
									'propagation' => ZcTransactionDefinition::PROPAGATION_NESTED,
								),
						'error_mode' => 'bool', // bool or exception
						'default_group' => 'zc',
						'connections' => array (
								'zc' => array (
										'master' => array (
												'db_id' => 'zc.master',
												'dbms' => 'mysql',
												'hostname' => 'localhost',
												'port' => '3306',
												'username' => 'root',
												'password' => 'tmart123',
												'pconnect' => false,
												'charset' => 'utf8',
												'database' => 'tmart',
												'read_weight' => 0 
										),
										'slaves' => array (
												array (
														'db_id' => 'zc.slave1',
														'dbms' => 'mysql',
														'hostname' => 'localhost',
														'port' => '3306',
														'username' => 'root',
														'password' => 'tmart123',
														'pconnect' => false,
														'charset' => 'utf8',
														'database' => 'tmart',
														'read_weight' => 30 
												),
												array (
														'db_id' => 'zc.slave2',
														'dbms' => 'mysql',
														'hostname' => 'localhost',
														'port' => '3306',
														'username' => 'root',
														'password' => 'tmart123',
														'pconnect' => false,
														'charset' => 'utf8',
														'database' => 'tmart',
														'read_weight' => 60 
												) 
										) 
								),
								'log' => array (
										'master' => array (
												'db_id' => 'log.master',
												'dbms' => 'mysql',
												'hostname' => '192.168.10.251',
												'port' => '3306',
												'username' => 'tmart_db_dev',
												'password' => 'tmart_db_dev',
												'pconnect' => false,
												'charset' => 'utf8',
												'database' => 'tmart_log',
												//'read_weight' => 100 
										) 
								),
						) 
				),
		);
	}

	public function mergeFromFile($file) {
		if(file_exists($file)) {
			$outConfig = require($file);
			$this->config = array_merge($this->config, $outConfig);
		}
	}

	public function set($key, $value) {
		$this->config[$key] = $value;
	}

	public function get($key = '') {
		return empty($key) ? $this->config : (isset($this->config[$key]) ? $this->config[$key] : null);
	}
}