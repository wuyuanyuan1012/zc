<?php

class ZcNumberHelper {
	
	/**
	 * 返回当前时间的字符串，以支持BC的系列方法
	 * 
	 * @param integer $dec 精确到小数点后几位
	 * @return string
	 */
	public static function microtimeFloat($scale = 3) {
		list($usec, $sec) = explode(' ', microtime());
		return bcadd($usec, $sec, $scale);
	}
}