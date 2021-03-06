<?php

class ZcAction {
	protected $route;
	protected $file;
	protected $class;
	protected $method;
	protected $args = array();

	public function __construct($route, $args = array()) {
		$dirController = Zc::C(ZcConfigConst::DirFsLibsController);

		$this->route = $route;

		$path = '';

		$parts = explode('/', $route);

		foreach ($parts as $part) {
				
			$tryDir = $path . $part . '/';
			if (is_dir($dirController . $tryDir)) {
				$path = $tryDir;
				array_shift($parts);
				continue;
			}

			$tryClass = str_replace(' ', '', ucwords(strtolower(str_replace(array('-', '_'), ' ', $part)))) . 'Controller';
			$tryFile = $path . 'class.' . $tryClass . '.php';
				
			if (is_file($dirController . $tryFile)) {

				$this->file = $dirController . $tryFile;
				$this->class = $tryClass;

				array_shift($parts);

				break;
			}
		}

		if ($args) {
			$this->args = $args;
		}
			
		$method = array_shift($parts);

		if ($method) {
			//$method = preg_replace ( '/(?:^|_)(.?)/e', "strtoupper('$1')", $method );
		    $method = preg_replace_callback ( '/(?:^|_)(.?)/', function($matches) {
		        return strtolower($matches[1]);
		    }, $method );
			$method{0} = strtolower($method{0});
			$this->method = $method;
		} else {
			$this->method = 'index';
			$this->route = $this->route . (substr($this->route, -1) == '/' ? 'index' : '/index');
		}
	}

	public function getFile() {
		return $this->file;
	}

	public function getClass() {
		return $this->class;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getArgs() {
		return $this->args;
	}

	public function getRoute() {
		return $this->route;
	}
}
