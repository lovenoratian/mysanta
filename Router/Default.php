<?php

/**
 * @author Ian
 * router
 *
 */
class Santa_Router_Default extends Santa_Router {
	
	protected static $_instance = null;
	
	protected function __construct() {
	
	}
	
	public function getUrlInfo() {
		$this->_urlInfo = $_SERVER ['QUERY_STRING'];
		return $this->_urlInfo;
	}
	
	public function route($urlInfo) {
		$dispatchInfo = array ();
		$dispatchInfo [0] = isset ( $_GET ['c'] ) ? $_GET ['c'] : '';
		$dispatchInfo [1] = isset ( $_GET ['a'] ) ? $_GET ['a'] : '';
		return $dispatchInfo;
	}
}