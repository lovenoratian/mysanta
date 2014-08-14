<?php

/**
 * @author Ian
 * Memcache类封装
 *
 */
class Santa_Cache_Memcache {
	
	/**
	 * @var unknown_type
	 */
	protected $_connection = null;
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (empty ( $config )) {
			throw new Santa_Exception ( "config can't be empty" );
		}
		$this->_connection = new Memcache ();
		list ( $host, $port ) = $config;
		$this->_connection->addServer ( $host, $port );
	}
	
	/**
	 * Get Cache
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->_connection->get ( $key );
	}
	
	/**
	 * @param unknown_type $key 键
	 * @param unknown_type $data 值
	 * @param unknown_type $expire 过期时间
	 * @param unknown_type $compress 是否压缩,默认启用压缩MEMCACHE_COMPRESSED
	 * @return boolean
	 */
	public function set($key, $data, $expire = 60, $compress = MEMCACHE_COMPRESSED) {
		if (empty ( $key ) || empty ( $data )) {
			return false;
		}
		return $this->_connection->set ( $key, $data, $compress, $expire );
	}
	
	/**
	 * Delete cache
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return $this->_connection->delete ( $key );
	}
	
	/**
	 * Increment value
	 *
	 * @param string $key
	 * @param int $value
	 */
	public function increment($key, $value = 1) {
		return $this->_connection->increment ( $key, $value );
	}
	
	/**
	 * Decrement value
	 *
	 * @param string $key
	 * @param int $value
	 */
	public function decrement($key, $value = 1) {
		return $this->_connection->decrement ( $key, $value );
	}
	
	/**
	 * clear cache
	 */
	public function flush() {
		$this->_connection->flush ();
	}
	
	protected function close() {
		$this->_connection->close ();
	}
	
	public function stats() {
		return $this->_connection->getStats ();
	}
}