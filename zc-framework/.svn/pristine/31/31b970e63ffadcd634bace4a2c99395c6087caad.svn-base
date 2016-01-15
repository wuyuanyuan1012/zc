<?php

class ZcArrayHelper {

	public static function getSub(&$array, $keys) {
		if (is_string($keys)) {
			$keys = explode(',', $keys);
		}
		$kc = count($keys);
		$key = $keys[0];
		
		$ret = array ();
		foreach ($array as $arr) {
			if ($kc === 1) {
				$ret[] = $arr[$key];
			} else {
				foreach ($keys as $k) {
					$ret[$k] = $arr[$k];
				}
			}
		}
		
		return $ret;
	}

	public static function objectToArray($d) {
		if (is_object($d)) {
			$d = get_object_vars($d);
		}
		
		if (is_array($d)) {
			return array_map(array('ZcArrayHelper', 'objectToArray'), $d);
		} else {
			return $d;
		}
	}

	public static function arrayToObject($d) {
		if (is_array($d)) {
			return (object)array_map(array('ZcArrayHelper', 'arrayToObject'), $d);
		} else {
			return $d;
		}
	}
}

