Santa
======

一个参考了其它N个框架，且仍处于开发中的PHP框架

### 1 推荐应用目录结构
	Apache htdocs---服务器www根目录
		App---应用根目录
			Conf---配置文件目录
			Controller---
			Filter---路由器筛选器目录
			Model---
			Static---js/css/image等静态资源
			View---
			Tool---工具类目录
			...{任意自定义目录}
			index.php---入口文件
		Santa---框架
		
**在应用根目录中可以自定义添加任意目录，只要符合以下的<类命名规则>即可**

### 2 类命名规则
<pre>
根据类文件路径名确定类名，如：
Controller_Index类对应Controller/Index.php文件
Santa_Config类对应Santa/Config.php类
自定义的类名和类文件路径名大小写应一致，严格区分大小写，否则*nix平台可能找不到文件
Santa_Config-------Santa/Config.php,正确(建议)
Santa_config-------Santa/config.php,正确
Santa_Config-------Santa/config.php,错误
Santa_config-------Santa/Config.php,错误
</pre>

### 3 入口文件index.php

````php
	//时区设置
	date_default_timezone_set ( 'Asia/Shanghai' );
	//定义关键目录
	define ( 'ROOT_DIR', dirname ( __DIR__ ) ); //根目录，必需
	define ( 'SANTA_DIR', ROOT_DIR . '/Santa' ); //Santa框架目录，必需
	define ( 'APP_DIR', __DIR__ ); //应用目录，必需
	define ( 'CONF_DIR', APP_DIR . '/Conf' ); //配置文件目录，必需
	define ( 'VIEW_DIR', APP_DIR . '/View' ); //模板视图目录，必需
	define ( 'VENDOR_DIR', SANTA_DIR . '/Vendor' ); //第三方库目录，不用时可注释
	//引入核心类
	require SANTA_DIR . '/App.php';
	
	try {
		Santa_App::$_filters = array ('router' => array ('Filter_Test', 'Filter_Test2'));//配置URL筛选器，不需要时可去掉
		Santa_App::init ( array ('enable_router_filters' => 0, 'enable_action_filters' => 0 ) );//初始化应用配置
		Santa_App::run ();//应用执行
	} catch ( Santa_Exception_404 $e ) {
		//404找不到页面情况的处理
		var_dump ( $e );
	} catch ( Exception $e ) {
		//其它异常情况处理
		var_dump ( $e );
	}
````

### 4 配置
#### 4.1 核心类配置(Santa_App)
核心类Santa_App的配置存储于其成员$_settings中，其中各项配置及意义如下
````php
	/**
	 * @var array
	 * router_class=>默认路由器
	 * controller=>默认控制器
	 * action=>默认控制器方法
	 * enable_router_filters=>是否开启路由器筛选器
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
````
配置此变量应统一可以通过Santa_App::init(array(...))实现
````php
	Santa_App::init ( array (
		'router_class' => 'Santa_Router_Pathinfo', 
		'controller' => 'Index', 
		'action' => 'index', 
		'enable_router_filters' => 1, 
		'enable_action_filters' => 1, 
		'view_engine' => 'Santa_View_Php', 
		'enable_db_ms' => 0, 
		'db_cnf' => 'db', 
		'cache_cnf' => 'cache' 
	) );
````
<pre>
以上初始设置框架都含默认值，若不需要自定义则可不设置，如可以设置为
	Santa_App::init ( array (
		'enable_action_filters' => 0, //表示关闭控制器筛选器
		'enable_db_ms' => 1, //启用主从式数据库池
	) );
</pre>

#### 4.2 应用配置
应用的配置通过CONF_DIR(Conf目录)下的各个配置文件实现,以数组形式返回，如:env.php
````php
<?php
	return array (
		"a" => array (
			'b' => 'cbcdefg', 
			'c' => array (
				'd' => 'ddd' 
			) 
		), 
		"domain" => 'http://xxx.yyy.cn/', 
	);
````
<pre>
使用Santa_Config::get('file.key1.key2...')可以读取配置文件中对应key的值,如:
$domain = Santa_Config::get('env.domain');
$c = Santa_Config::get('env.a.c');
$d = Santa_Config::get('env.a.c.d');
</pre>

### 5 上下文Santa_Context
<pre>
应用上下文，存储一些在请求中共用的数据，有点儿全局变量的意思，同时提供$_GET/$_POST/$_SERVER的封装，示例:
Santa_Context::set('user',$user);//将用户信息设置进上下文
Santa_Context::get('user');//可以在其它地方进行访问
Santa_Context::param('t');//$_GET['t']
Santa_Context::form('t');//$_POST['t']
Santa_Context::server('t');//$_SERVER['t']
具体参见类代码和示例
</pre>

### 6 控制器
控制器都由类Santa_Controller继承而来，子类可以通过实现init()方法进行一些初始化操作
### 7 视图
<pre>
视图引擎类需实现Santa_View接口，框架默认支持php原生视图引擎、smarty2/smarty3三种引擎
用户可以通过配置$_settings['view_engine']来使用自定义的视图引擎，参照代码和示例
smarty引擎示例参见smarty.php入口.../smarty.php/index/smarty?..
</pre>
### 8 筛选器
<pre>
筛选器分为路由器筛选器和控制器筛选器两种
Santa_App类中设置了两个开关$_settings['enable_router_filters']和$_settings['enable_action_filters']
设置其值为1/0来设置启用/关闭相应的筛选器功能
</pre>
#### 8.1 路由器筛选器
**在路由器解析URL信息之前执行，可以通过它来进行环境检测，URL控制等行为**
<pre>
所有的路由器筛选器必须继承Santa_Filter类,存储于Santa_App的成员变量$_filters数组中，通过以下语句可以设置
Santa_App::$_filters = array ('router' => array ('Filter_Test', 'Filter_Test2'));
Filter_Test,Filter_Test2为筛选器类名，建议置于APP_DIR/Filter目录下，也可自定义目录
</pre>
**参考<入口文件>**
#### 8.2 控制器筛选器
**在执行控制器里具体的action方法前执行，可用于各种验证、条件判断等**
<pre>
配置方法---
所有继承自Santa_Controller的控制器都包含一个$filters数组成员，此成员设置了当前控制器各个action方法的筛选器
控制器筛选器方法一般也是控制器中的某个方法
一般在init()方法中进行设置,以array('筛选器名'=>开/关)形式实现，开/关(执行/不执行)以1/0表示
以下Controller_Index控制器为例，

//$this->filters ['controller']表示控制器的全局筛选器，设置的筛选器在执行每个action方法时都会生效
//如，当用户请求Index/index、Index/test、Index/show时都会执行checkLogin、isChinaMobile筛选器
$this->filters ['controller'] = array ('checkLogin' => 1, 'isChinaMobile' => 1, 't' => 0 );
//$this->filters ['action'] [具体控制器方法]表示控制器中某个方法的筛选器，只在执行当前action方法时才会生效
//用户可以通过这来开关某个action方法的筛选器行为，如
//index方法不需要isChinaMobile筛选器，可以设置为0
$this->filters ['action'] ['index'] = array ('checkLogin' => 1, 'isChinaMobile' => 0 );
//test方法需要t筛选器，可以设置为1
$this->filters ['action'] ['test'] = array ('t' => 1 );
//show方法不需要checkLogin、isChinaMobile，可以设置为0
$this->filters ['action'] ['show'] = array ('checkLogin' => 0, 'isChinaMobile' => 0 );

</pre>
用户可以通过覆盖控制器的filters()方法来实现自己的筛选器处理规则，只要返回形式如array('checkLogin','isChinaMobile',...)的数组即可
````php
	//参数$currentAction为当前要执行的action方法
	public function filters($currentAction = null) {
		//TODO实现自己的筛选器处理逻辑
		//返回值$filters形式为array('checkLogin','isChinaMobile',...)
		return $filters;
	}
````
### 9 路由器
<pre>
框架的路由通过单独的路由器类实现(Santa_App::$_router成员)，可以通过
Santa_App::init(array('router_class'=>'...'));
来配置使用的路由器类，默认为Santa_Router_Pathinfo
使用者可以根据需要自行开发路由器类，需继承自Santa_Router类
默认的pathinfo模式的格式为.../index.php/controller/actoin?id=123...
如用户访问.../index.php/index/test?id=333，则对应的控制器类为Controller_Index，方法为test(尚待改进...)
</pre>

### 10 数据库
数据库采用池模式
#### 10.1 配置
<pre>
首先配置数据库资源，默认的配置文件为CONF_DIR/db.php
数据库可以配置为使用主从式数据库池或普通数据库池两种，两种配置文件的实现方式参照以下代码中示例
</pre>
**注意：使用主从式数据库池模式时需要在入口文件中配置启用(默认为普通池模式)，Santa_App::init ( array ('enable_db_ms' => 1) )**
````php
<?php
/**
 * 使用非主从式数据库池的配置示例
 */
return array (
	'Santa_Db_Pdo' => array (
		'test' => array (
			'host' => 'localhost', 
			'port' => 3306, 
			'database' => 'test', 
			'user' => 'root', 
			'password' => '', 
			'charset' => 'UTF8', 
			'persistent' => true, 
			'options' => array () 
		), 
		'test1' => array (
			'host' => 'localhost', 
			'port' => 3306, 
			'database' => 'test', 
			'user' => 'root', 
			'password' => '', 
			'charset' => 'UTF8', 
			'persistent' => true, 
			'options' => array () 
		) 
	) 
);
/**
 * 使用主从式数据库池的配置示例
 * master/slave
 */
/* return array (
	'Santa_Db_Pdo' => array (
		'test' => array (
			'master' => array (
				'host' => 'localhost', 
				'port' => 3306, 
				'database' => 'test', 
				'user' => 'root', 
				'password' => '', 
				'charset' => 'UTF8', 
				'persistent' => true, 
				'options' => array () 
			), 
			'slaves' => array (
				array (
					'host' => 'localhost', 
					'port' => 3306, 
					'database' => 'test', 
					'user' => 'root', 
					'password' => '', 
					'charset' => 'UTF8', 
					'persistent' => true, 
					'options' => array () 
				), 
				array (
					'host' => 'localhost', 
					'port' => 3306, 
					'database' => 'test', 
					'user' => 'root', 
					'password' => '', 
					'charset' => 'UTF8', 
					'persistent' => true, 
					'options' => array () 
				) 
			) 
		), 
		'test1' => array (
			'master' => array (
				'host' => 'localhost', 
				'port' => 3306, 
				'database' => 'test', 
				'user' => 'root', 
				'password' => '', 
				'charset' => 'UTF8', 
				'persistent' => true, 
				'options' => array () 
			) 
		) 
	) 
); */
````
<pre>
Santa_Db_Pdo：数据库驱动类，默认提供PDO，用户可以根据需要自己实现，也可封装第三方数据库驱动
test/test1/...数据库别名
</pre>
#### 10.2 使用
<pre>
数据库资源实例都以Santa_Db::$_pools元素形式，访问方式为Santa_Db::pool ( '数据库别名' )如，
$db = Santa_Db::pool ( 'test' );
$db = Santa_Db::pool ( 'test1' );
$db->query($sql);
具体用法参见示例
</pre>
### 11 缓存
缓存也采用池模式
#### 11.1 配置
默认的配置文件为CONF_DIR/cache.php
````php
<?php
return array (
	'Santa_Cache_Memcache' => array (
		'userinfo' => array (
			'10.10.21.12', 
			11211 
		), 
		'topic' => array (
			'10.10.21.12', 
			11211 
		) 
	), 
	'Santa_Cache_File' => array (
		'file1' => APP_DIR . '/tmp' 
	) 
);
````
<pre>
Santa_Cache_Memcache/Santa_Cache_File为缓存驱动
userinof/topic/file1为缓存资源别名
</pre>
#### 11.2 使用
<pre>
缓存资源实例以Santa_Cache::$_pools元素形式，访问方式为Santa_Db::pool ( '缓存别名' )如，
$userinfo = Santa_Cache::pool ( 'test' );
$topic = Santa_Cache::pool ( 'test1' );
具体用法参见示例
</pre>
### 12 Util
Util目录中的类多为辅助工具类
