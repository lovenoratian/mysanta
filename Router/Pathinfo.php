<?php

/**
 * @author Ian
 * router
 *
 */
class Santa_Router_Pathinfo extends Santa_Router {
	
	protected static $_instance = null;
	
	protected function __construct() {
	
	}
	
	public function getUrlInfo() {
		$this->_urlInfo = isset ( $_SERVER ['PATH_INFO'] ) ? $_SERVER ['PATH_INFO'] : '';
		return $this->_urlInfo;
	}
	
	public function route($urlInfo) {
		/* 
		 * 
		 */
		if (false !== strpos ( $urlInfo, '_' )) {
			throw new Santa_Exception ( 'pathinfo can\'t include character "_"' );
		}
		$dispatchInfo = array ();
		$pathInfo = explode ( '/', trim ( strtolower ( $urlInfo ), '/' ) );
		$dispatchInfo [0] = isset ( $pathInfo [0] ) ? $pathInfo [0] : '';
		$dispatchInfo [1] = isset ( $pathInfo [1] ) ? $pathInfo [1] : '';
		return $dispatchInfo;
	}
}