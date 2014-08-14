<?php

/**
 * @author Ian
 * Memcached类封装
 *
 */
class Santa_Cache_Memcached {
	
	protected $_connection = null;
	
	public function __construct($config) {
		if (empty ( $config )) {
			throw new Santa_Exception ( "config can't be empty" );
		}
		$this->_connection = new Memcached ();
		list ( $host, $port ) = $config;
		$this->_connection->addServer ( $host, $port );
		
	/* $this->setOption ( Memcached::OPT_LIBKETAMA_COMPATIBLE, true ); // 设置环形哈希算法
		$this->setOption ( Memcached::OPT_NO_BLOCK, true );
		$this->setOption ( Memcached::OPT_CONNECT_TIMEOUT, 200 );
		$this->setOption ( Memcached::OPT_POLL_TIMEOUT, 50 ); */
	}
	
	public function get($key) {
		if (empty ( $key )) {
			return false;
		}
		$result = $this->_connection->get ( $key );
		if (false === $result) {
			$result_code = $this->_connection->getResultCode ();
			if ($result_code != Memcached::RES_SUCCESS && $result_code != Memcached::RES_NOTFOUND) {
				return false;
			}
		}
		return $result;
	}
	
	public function set($key, $value, $expire = 60) {
		if (empty ( $key )) {
			return false;
		}
		$ret = $this->_connection->set ( $key, $value, $expire );
		if (false === $ret) {
			return false;
		}
		return true;
	}
	
	public function del($key) {
		if (empty ( $key )) {
			return false;
		}
		$ret = $this->_connection->delete ( $key );
		if (false === $ret) {
			$result_code = $this->_connection->getResultCode ();
			if ($result_code != Memcached::RES_SUCCESS && $result_code != Memcached::RES_NOTFOUND) {
				return false;
			}
		}
		return true;
	}
	
	public function mget(array $keys) {
		$ret = $this->_connection->getMulti ( $keys );
		if (false === $ret) {
			$ret = array ();
		}
		
		foreach ( $keys as $key ) {
			if (! isset ( $ret [$key] )) {
				$ret [$key] = false;
			}
		}
		return $ret;
	}
	
	public function mset(array $values, $expire = 60) {
		return $this->_connection->setMulti ( $values, $expire );
	}
	
	public function mdel(array $keys) {
		foreach ( $keys as $key ) {
			$this->_connection->delete ( $key );
		}
	}
}