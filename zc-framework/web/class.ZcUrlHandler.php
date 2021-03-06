<?php
interface ZcUrlHandler {

	/**
	 * 重写url
	 * @param string $url
	 */
	public function parseBack();

	/**
	 * 生成url
	 * @param string $route
	 * @param array | string $params
	 * @param boolean $ssl
	*/
	public function buildUrl($route, $params = '', $scheme = false, $host = false);
}