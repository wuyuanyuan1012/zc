<?php
/**
 * 自动加载类
 * 
 * @author tangjianhui
 *
 */
class ZcAutoloader {
	private static $autoloadDirs = array();
	private static $autoloadClassFileMapping = array();
	private static $includeFiles = array();
	
	private static function initConfig() {
		$config = ZcFactory::getConfig();
		
		//获取绝对路径的自动加载目录
		$dirsFs = $config->get(ZcConfigConst::AutoloadDirsFs);
		if (!empty($dirsFs)) {
			foreach($dirsFs as $dir) {
				self::$autoloadDirs[] = rtrim(trim($dir), '/') . '/';		
			}
		}
		
		//获取应用路径的自动加载目录
		$dirsWs = $config->get(ZcConfigConst::AutoloadDirsWs);
		$dirApp = $config->get(ZcConfigConst::DirFsApp);
		if (!empty($dirsWs)) {
			foreach($dirsWs as $dir) {
				self::$autoloadDirs[] = $dirApp . trim(trim($dir), '/') . '/';
			}
		}
		
		self::$autoloadClassFileMapping = $config->get(ZcConfigConst::AutoloadClassFileMapping);
		
		//自动加载文件
		self::$includeFiles = $config->get(ZcConfigConst::AutoloadIncludeFiles);
	}
	
	private static function includeFiles() {
		if (empty(self::$includeFiles)) {
			return ;
		}
		foreach(self::$includeFiles as $file) {
			if (file_exists($file)) {
				include_once $file;
			}
		}
	}
	
	public static function init() {
		self::initConfig();
		spl_autoload_register(array('ZcAutoloader', "autoload"));
		self::includeFiles();
	}
	
	public static function autoload($class) {
		if (isset(self::$autoloadClassFileMapping[$class])) {
			include_once self::$autoloadClassFileMapping[$class];
			return ;
		}
		
		foreach (self::$autoloadDirs as $autoLoadDir) {
			$classFile = $autoLoadDir . 'class.' . $class . '.php';
			if (file_exists($classFile)) {
				include_once $classFile;
				continue;
			}
			
			$classFile = $autoLoadDir . $class . '.class.php';
			if (file_exists($classFile)) {
				include_once $classFile;
				continue;
			}
			
			$classFile = $autoLoadDir . $class . '.php';
			if (file_exists($classFile)) {
				include_once $classFile;
			}
		}
	}
}