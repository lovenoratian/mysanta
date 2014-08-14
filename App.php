<?php

/**
 * @author Ian
 * App核心类
 * 
 */
class Santa_App {
	
	/**
	 * @var Santa_Router
	 * must be an instance of Santa_Router
	 */
	public static $_router = null;
	
	/**
	 * @var unknown_type
	 */
	public static $_filters = array ();
	
	/**
	 * @var $_urlInfo
	 * 
	 */
	public static $_urlInfo = null;
	
	/**
	 * @var array
	 * router_class=>默认路由器
	 * controller=>默认控制器
	 * action=>默认控制器方法
	 * enable_router_filters=>是否开启路由筛选器
	 * enable_action_filters=>是否开启控制器筛选器
	 * view_engine=>默认视图引擎
	 * enable_db_ms=>数据库池是否启用主从式
	 * db_cnf=>默认数据库配置文件,不带扩展名,对应CONF_DIR/db.php
	 * cache_cnf=>默认缓存配置文件,不带扩展名,对应CONF_DIR/cache.php
	 */
	public static $_settings = array (
		'router_class' => 'Santa_Router_Pathinfo', 
		'controller' => 'Index', 
		'action' => 'index', 
		'enable_router_filters' => 1, 
		'enable_action_filters' => 1, 
		'view_engine' => 'Santa_View_Php', 
		'enable_db_ms' => 0, 
		'db_cnf' => 'db', 
		'cache_cnf' => 'cache' 
	);
	
	/**
	 * @var array
	 */
	public static $_dispatchInfo = null;
	
	/**
	 * @param array $settings
	 */
	public static function init(array $settings = array()) {
		/* 
		 * init $_settings
		 */
		if (! empty ( $settings )) {
			self::$_settings = array_merge ( self::$_settings, $settings );
		}
		/* 
		 * register autoload function,
		 */
		self::regAutoload ();
		/* 
		 * set router,which will parse $_urlInfo to $_dispatchInfo
		 * e.g. 'Santa_Router_Test'
		 */
		$routerClass = self::$_settings ['router_class'];
		if (empty ( $routerClass ) || ! (self::$_router = $routerClass::getInstance ()) instanceof Santa_Router) {
			throw new Santa_Exception ( 'router_class empty ,or not subclass of Santa_Router' );
		}
		/* 
		 * init db and cache pool
		 */
		Santa_Db::init ( self::$_settings ['db_cnf'], self::$_settings ['enable_db_ms'] );
		Santa_Cache::init ( self::$_settings ['cache_cnf'] );
	}
	
	/**
	 * wrap route and dispatch
	 */
	public static function run() {
		self::route (); //parse $_urlInfo to $_dispatchInfo
		self::dispatch (); //execute $_dispatchInfo
	}
	
	/**
	 * 把$_urlInfo传给router，router经过处理后返回给$_dispatchInfo
	 */
	public static function route() {
		//set $_urlInfo
		if (null === self::$_urlInfo) {
			self::$_urlInfo = self::$_router->getUrlInfo ();
		}
		//run pre_router filters路由器解析URL之前执行
		if (self::$_settings ['enable_router_filters'] && (! empty ( self::$_filters ['router'] ))) {
			$filters = self::$_filters ['router'];
			foreach ( $filters as $filter ) {
				if (! ($cls = new $filter ()) instanceof Santa_Filter) {
					throw new Santa_Exception ( "filter $filter must be subclass of Santa_Filter" );
				}
				$cls->filt ();
			}
		}
		//set $_dispatchInfo
		if (null === self::$_dispatchInfo) {
			self::$_dispatchInfo = self::$_router->route ( self::$_urlInfo );
		}
	}
	
	/**
	 * 分发执行action
	 * @throws Santa_Exception_404
	 * @throws Exception
	 */
	public static function dispatch() {
		/* 
		 * self::$_dispatchInfo有格式限制，参见Santa_Router::route方法的说明
		 */
		list ( $controller, $action ) = self::$_dispatchInfo;
		/* 
		 * 创建控制器controller对象
		 */
		$controller = $controller !== '' ? $controller : self::$_settings ['controller'];
		define ( '__CONTROLLER__', $controller );
		$controller = 'Controller_' . $controller;
		if (! self::autoload ( $controller )) {
			throw new Santa_Exception_404 ( "404 : can't locate controller $controller" );
		}
		$cls = new $controller ();
		/* 
		 * 判断控制器中相应的action方法是否存在
		 */
		$func = $action !== '' ? array (
			$cls, 
			$action 
		) : array (
			$cls, 
			self::$_settings ['action'] 
		);
		define ( '__ACTION__', $func [1] );
		if (! is_callable ( $func, false )) {
			throw new Santa_Exception_404 ( "404 : can't locate action $controller:$action,or $controller:$action not a public method" );
		}
		/*
		 * 在执行controller的action之前执行某些筛选行为，无参数filter
		*/
		if (self::$_settings ['enable_action_filters']) {
			$filters = $cls->filters ( $func [1] );
			if (! empty ( $filters )) {
				foreach ( $filters as $filter ) {
					if (! is_callable ( array (
						$cls, 
						$filter 
					), false )) {
						throw new Santa_Exception ( "filter $controller:$filter not exists or not a public method" );
					}
					call_user_func ( array (
						$cls, 
						$filter 
					) );
				}
			}
		}
		/* 
		 * 执行action方法
		 */
		call_user_func ( $func );
	}
	
	/**
	 * @param string $class
	 */
	public static function autoload($class) {
		$dir = ('Santa' == substr ( $class, 0, 5 )) ? ROOT_DIR : APP_DIR;
		$file = $dir . DIRECTORY_SEPARATOR . strtr ( $class, '_', DIRECTORY_SEPARATOR ) . '.php';
		if (file_exists ( $file )) {
			include $file;
		}
		
		return (class_exists ( $class, false ) || interface_exists ( $class, false ));
	}
	
	/**
	 * @param string/array $func
	 */
	public static function regAutoload($loader = 'Santa_App::autoload') {
		spl_autoload_register ( $loader );
	}
}