<?php

/**
 * @author Ian
 * router 路由器
 *
 */
abstract class Santa_Router {
	
	/**
	 * @var Santa_Router
	 */
	protected static $_instance = null;
	
	protected $_urlInfo = null;
	
	/**
	 * Constructor
	 */
	protected function __construct() {
	
	}
	
	/**
	 * @return Santa_Router
	 */
	final public static function getInstance() {
		if (null === static::$_instance) {
			static::$_instance = new static ();
		}
		return static::$_instance;
	}
	
	/**
	 * 返回urlInfo给Santa_App::$_urlInfo,
	 * 此方法主要用于router filter可能需要修改urlInfo的情况,
	 * 若应用不存在router filter修改Santa_App::$_urlInfo的情况则略显多余,以期改进
	 */
	abstract public function getUrlInfo();
	
	/**
	 * 由子类实现,其作用是将urlInfo转换成dispatchInfo,如/index/show/?id=333解析为array('index','show')
	 * @param String $urlInfo Santa_App::$_urlInfo
	 * @return array dispatchInfo,解析出来的controller,action信息
	 * 正确的返回值:
	 * array('',''),array('index',''),array('index','show')
	 * 错误的返回值:
	 * array(),array('')
	 * 即返回的数组必须包含两个元素，元素值可以为''，但不能不设置
	 */
	abstract public function route($urlInfo);
}