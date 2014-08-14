<?php

/**
 * @author Ian
 * 数据库池
 *
 */
class Santa_Db {
	
	public static $_pool = array ();
	
	/**
	 * 初始化池
	 * @param string $cnf 默认数据库配置文件,不带扩展名db.php
	 * @param boolean $isMasterSlaves 是否使用主从式数据库池
	 */
	static public function init($cnf, $isMasterSlaves) {
		$configs = Santa_Config::get ( $cnf );
		if ($isMasterSlaves) {
			foreach ( $configs as $engine => $dbs ) {
				foreach ( $dbs as $alias => $config ) {
					self::$_pool [$alias] = new Santa_Db_Ms ( $engine, $config );
				}
			}
		} else {
			foreach ( $configs as $engine => $dbs ) {
				foreach ( $dbs as $alias => $config ) {
					self::$_pool [$alias] = new $engine ( $config );
				}
			}
		}
	}
	
	/**
	 * 从池中获取数据库资源
	 * @param string $alias
	 * @throws Exception
	 * @return multitype:
	 */
	static public function pool($alias) {
		if (empty ( self::$_pool [$alias] )) {
			throw new Santa_Exception ( 'Db alias "' . $alias . '" not exist' );
		}
		return self::$_pool [$alias];
	}
	
	/**
	 * 释放池
	 */
	static public function release() {
		foreach ( self::$_pool as $conns ) {
			$conns->master->free ();
			$conns->master->close ();
			if ($conns->slaves) {
				$slaves = $conns->slaves;
				foreach ( $slaves as $slave ) {
					$slave->free ();
					$slave->close ();
				}
			}
		}
		self::$_pool = null;
	}
}