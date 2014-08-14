<?php

/**
 * @author Ian
 * 文件缓存
 *
 */
class Santa_Cache_File {
	
	/**
	 * 文件缓存目录
	 * @var unknown_type
	 */
	protected $directory;
	
	public function __construct($directory = null) {
		if (null === $directory) {
			$directory = sys_get_temp_dir ();
		}
		$this->directory = $directory;
	}
	
	protected function get_file_name($key) {
		return $this->directory . DIRECTORY_SEPARATOR . md5 ( $key );
	}
	
	public function get($key, $expire = 60) {
		if (! file_exists ( $this->get_file_name ( $key ) )) {
			return false;
		}
		$c = unserialize ( file_get_contents ( $this->get_file_name ( $key ) ) );
		if (! is_array ( $c )) {
			return false;
		}
		if ($c ['e']) {
			if ($c ['e'] < time ()) {
				$this->del ( $key );
				return false;
			}
		}
		return $c ['c'];
	}
	
	public function set($key, $value, $expire = 60) {
		$fname = $this->get_file_name ( $key );
		$c = serialize ( array (
			'e' => time () + $expire, 
			'c' => $value 
		) );
		file_put_contents ( $fname, $c );
		return true;
	}
	
	public function del($key) {
		$fname = $this->get_file_name ( $key );
		if (file_exists ( $fname )) {
			unlink ( $fname );
		}
		return true;
	}
	
	public function mget(array $keys) {
		$return = array ();
		foreach ( $keys as $key ) {
			$return [$key] = $this->get ( $key );
		}
		return $return;
	}
	
	public function mset(array $values, $expire = 60) {
		foreach ( $values as $k => $v ) {
			$this->set ( $k, $v, $expire );
		}
	}
	
	public function mdel(array $keys) {
		foreach ( $keys as $k ) {
			$this->del ( $k );
		}
	}
}