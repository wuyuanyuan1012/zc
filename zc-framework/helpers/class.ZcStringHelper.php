<?php
/**
 * String的便捷方法
 * 
 * @author tangjianhui 2013-10-9 上午11:42:13
 *
 */
class ZcStringHelper {
	
	/**
	 * 生成指定长度和字母表的随机字符串，如果不指定字母表，默认用大小写字母加数字
	 * 
	 * @param 长度 $length
	 * @param 字母表 $chars
	 * @return string
	 */
	public static function genRandomStr($length = 7, $chars = '') {
		if (empty($chars)) {
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
		$len = strlen($chars) - 1;
	
		$str = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[mt_rand(0, $len)];
		}
		return $str;
	}
	
	/**
	 * 验证一个字符串是否是个合法的时间 <br/>
	 * http://www.php.net/manual/en/function.checkdate.php 给了个很棒的的方法来验证，但是需PHP 5.3以上
	 * 
	 * @param unknown $date
	 * @param string $format
	 * @return boolean
	 */
	public static function validateDate($date, $format = '%Y-%m-%d %H:%M:%S') {
		$dateArray = strptime($date, $format);
		return $dateArray
		&& checkdate($dateArray['tm_mon'] + 1, $dateArray['tm_mday'], $dateArray['tm_year'] + 1900)
		&& ($dateArray['tm_hour'] <= 23)
		&& ($dateArray['tm_min'] <= 59)
		&& ($dateArray['tm_sec'] <= 59);
	}
}