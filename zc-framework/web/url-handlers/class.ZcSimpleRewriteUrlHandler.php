<?php
class ZcSimpleRewriteUrlHandler implements  ZcUrlHandler {

	public function buildUrl($route, $params = '', $scheme = false, $host = false) {
		$scheme = ($scheme === false) ? ZcUrlHelper::getProtocol() : $scheme;
		$host = ($host === false) ? $_SERVER['HTTP_HOST'] : $host;
		$port = $_SERVER['SERVER_PORT'];

		$route = trim($route, '/');
			
		$url = $scheme . '://' . $host . ($port == 80 ? '' : ':' . $port) . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], 'index.php')) . $route;

		if (is_array($params)) {
			$params = http_build_query($params, '', '&');
		}

		if ($params) {
			$url .= '?' . ltrim($params, '&');
		}
		return $url;
	}

	public function parseBack() {
		if (isset($_GET['_route_'])) {
			$_GET['route'] = trim($_GET['_route_'], '/');
			unset($_GET['_route_']);
		}
	}
}