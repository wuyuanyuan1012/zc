<?php
/**
 * 给%?认识literal的能力，在update、insert、delete等语句有特别的作用
 * 
 * @author tangjianhui 2013-8-29 上午11:07:48
 *
 */
class ZcDbEval {
	private $args;
	
	public function __construct($args) {
		$this->args = func_get_args();
	}
	
	public function getArgs() {
		return $this->args;
	}
}