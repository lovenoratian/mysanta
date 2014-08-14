<?php

/**
 * @author Ian
 * 缓存池
 */
class Santa_Cache {
	
	public static $_pool = array ();
	
	/**
	 * @param string $cnf 默认缓存配置文件,不带扩展名cache.php
	 */
	static public function init($cnf) {
		$configs = Santa_Config::get ( $cnf );
		foreach ( $configs as $engine => $caches ) {
			foreach ( $caches as $alias => $config ) {
				self::$_pool [$alias] = new $engine ( $config );
			}
		}
	}
	
	/**
	 * 从池中获取缓存资源
	 * @param string $alias
	 * @throws Exception
	 * @return multitype:
	 */
	static public function pool($alias) {
		if (empty ( self::$_pool [$alias] )) {
			throw new Santa_Exception ( 'cache alias "' . $alias . '" not exist' );
		}
		return self::$_pool [$alias];
	}
	
	/**
	 * 释放池
	 */
	static public function release() {
		self::$_pool = null;
	}
}