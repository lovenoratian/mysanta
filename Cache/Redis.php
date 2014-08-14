<?php
/**
 * @author Ian
 * redis
 * 安装phpredis扩展：https://github.com/owlient/phpredis
 */
class Santa_Cache_Redis {
	
	protected $_connection = null;
	
	public function __construct($config = array()) {
		if (empty ( $config )) {
			throw new Santa_Exception ( "config can't be empty" );
		}
		$this->_connection = new Redis ();
		list ( $host, $port ) = $config;
		$this->_connection->connect ( $host, $port );
	}
	
	public function get($name) {
		return $this->_connection->get ( $name );
	}
	
	public function set($name, $value, $expire = 60) {
		if (is_int ( $expire )) {
			$result = $this->_connection->setex ( $name, $expire, $value );
		} else {
			$result = $this->_connection->set ( $name, $value );
		}
		return $result;
	}
	
	public function del($name) {
		return $this->_connection->delete ( $name );
	}
	
	public function clear() {
		return $this->_connection->flushDB ();
	}
}