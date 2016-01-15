<?php
/**
 * URL 帮助相关类
 * @author yunliang.huang 2013/03/18
 */
class ZcUrlHelper {
	/**
	 * 获取协议类型
	 * @return string
	 */
	public static function getProtocol(){
		$protocol = ((! empty ( $_SERVER ['HTTPS'] ) && strtolower ( $_SERVER ['HTTPS'] ) != 'off')) ? 'https' : 'http';
		return $protocol;
	}
}
