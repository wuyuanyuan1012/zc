<?php
class ZcSimplePathInfoUrlHandler implements ZcUrlHandler {

	public function buildUrl($route, $params = '', $scheme = false, $host = false) {
		$scheme = ($scheme === false) ? ZcUrlHelper::getProtocol() : $scheme;
		$host = ($host === false) ? $_SERVER['HTTP_HOST'] : $host;
		$port = $_SERVER['SERVER_PORT'];
		
		$route = trim($route, '/');
		$url = $scheme . '://' . $host . ($port == 80 ? '' : ':' . $port) . '/index.php/' . $route;

		if (is_array($params)) {
			$params = http_build_query($params, '', '&');
		}

		if ($params) {
			$url .= '?' . ltrim($params, '&');
		}
		return $url;
	}

	public function parseBack() {
		if (isset($_SERVER['PATH_INFO'])) {
			$_GET['route'] = trim($_SERVER['PATH_INFO'], '/');
		}
	}
}
