<?php

/**
 * @author Ian
 * config 配置读取类
 */
class Santa_Config {
	
	public static $_config = array ();
	
	public static function loadConf($file, $dir = CONF_DIR) {
		$filename = $dir . DIRECTORY_SEPARATOR . $file . '.php';
		if (! is_file ( $filename )) {
			throw new Santa_Exception ( "file $filename not exists or something" );
		}
		self::$_config [$file] = include ($filename);
	}
	
	public static function get($key = null, $default = null, $dir = CONF_DIR) {
		if (! $key) {
			throw new Santa_Exception ( 'parameter $key is empty' );
		}
		if (false !== strpos ( $key, '.' )) {
			list ( $file, $path ) = explode ( '.', $key, 2 );
			unset ( $path );
		} else {
			$file = $key;
		}
		if (! isset ( self::$_config [$file] )) {
			self::loadConf ( $file, $dir );
		}
		$config = self::$_config;
		$keys = explode ( '.', $key );
		foreach ( $keys as $key ) {
			if (! isset ( $config [$key] )) {
				throw new Santa_Exception ( __FILE__ . __METHOD__ . __LINE__ . "key:{$key} is not set in config" );
			}
			//isset("$string") bug still here
			/* if (! is_array ( $res [$key] )) {
				$res = $res [$key];
				break;
			} */
			$config = $config [$key];
		}
		return $config;
	}
}