<?php
/**
 * @author wuyuanyuan
 */
class HomeController extends ZcController {
	/**
	 * 构造函数
	 *
	 * @param string $route
	 *        	router
	 */
	public function __construct($route) {
		parent::__construct ( $route );
		
		echo 'wyy';
	}
	
	public function index () {
		$this->render ();
	}
	
}

?>