<?php

/**
 * @author Ian
 * router
 *
 */
class Santa_Router_Rewrite extends Santa_Router {
	
	protected static $_instance = null;
	protected function __construct() {
	}
	
	public function getUrlInfo() {
		$this->_urlInfo = isset ( $_SERVER ['PATH_INFO'] ) ? $_SERVER ['PATH_INFO'] : '';
		return $this->_urlInfo;
	}
	public function route($urlInfo) {
		echo __METHOD__;
	}
}