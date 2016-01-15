<?php
class ZcDefaultUrlHandler implements ZcUrlHandler {

	/**
	 * @see ZcUrlHandler::rewrite()
	 */
	public function parseBack() {
		//echo 'do nothing';
		//Zc::dump($_SERVER); exit;
	}

	/**
	 * @see ZcUrlHandler::url()
	 */
	public function buildUrl($route, $params = '', $scheme = false, $host = false) {
		$scheme = ($scheme === false) ? ZcUrlHelper::getProtocol() : $scheme;
		$host = ($host === false) ? $_SERVER['HTTP_HOST'] : $host;
		$port = $_SERVER['SERVER_PORT'];
		 
		//使用$_SERVER['PHP_SELF']，要注意与$_SERVER['SCRIPT_NAME']的区别
		//http://stackoverflow.com/questions/279966/php-self-vs-path-info-vs-script-name-vs-request-uri
		$url = $scheme . '://' . $host . ($port == 80 ? '' : ':' . $port) . $_SERVER['PHP_SELF'] . '?route=' . $route;
		 
		if (is_array($params)) {
			$params = http_build_query($params, '', '&');
		}

		if ($params) {
			$url .= '&' . ltrim($params, '&');
		}
		return $url;
	}
}