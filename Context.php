<?php
/**
 * @author Ian
 * 上下文资源类
 */
class Santa_Context {
	
	/**
	 * @var array
	 */
	public static $_data = array ();
	
	/**
	 * 根据指定的上下文键名获取一个已经设置过的上下文键值
	 * 
	 * @param string|int|float $key 键名
	 * @param mixed $if_not_exist 当键值未设置的时候的默认返回值。可选，默认是Null。如果该值是Null,当键值未设置则会抛出一个异常；否则，返回该值。
	 * @return mixed 如果指定的$key不存在，根据 $if_not_exist 的值，会抛出一个异常或者 $if_not_exist 本身。
	 */
	public static function get($key, $default = null) {
		if (! array_key_exists ( $key, self::$_data )) {
			if (null === $default) {
				throw new Santa_Exception ( "key $key not exists in context" );
			} else {
				return $default;
			}
		}
		return self::$_data [$key];
	}
	
	/**
	 * 往一个指定的上下文键名中设置键值。如果该键值已经被设置，则会抛出异常。
	 * 
	 * @param string|int|float $key
	 * @param mixed $value
	 * @param array $rule
	 * @throws Comm_Exception_Program
	 */
	public static function set($key, $value) {
		if (array_key_exists ( $key, self::$_data )) {
			throw new Santa_Exception ( "key $key already exists in context" );
		}
		self::$_data [$key] = $value;
	}
	
	/**
	 * 从$_GET中获取指定参数的值。
	 * 如果指定参数未找到，则会返回默认值$default的值。
	 * @param string $name 参数名。
	 * @param mixed $default 若指定的$name的值不存在的情况下返回的默认值
	 * @param boolean $filt 是否执行过滤操作
	 * @return string
	 */
	public static function param($name, $default = null) {
		return isset ( $_GET [$name] ) ? $_GET [$name] : $default;
	}
	
	/**
	 * 从$_COOKIE中获取指定参数的值。
	 * 如果指定参数未找到，则会返回默认值$default的值。
	 * @param string $name 参数名称
	 * @param mixed $default 若指定的$name的值不存在的情况下返回的默认值。
	 * @return string
	 */
	public static function cookie($name, $default = null) {
		return isset ( $_COOKIE [$name] ) ? $_COOKIE [$name] : $default;
	}
	
	/**
	 * 从$_POST中获取指定参数的值。如果指定参数未找到，则会返回默认值$default的值。
	 *
	 * @param string $name 参数名。
	 * @param mixed $default 若指定的$name的值不存在的情况下返回的默认值。
	 * @return string
	 */
	public static function form($name, $default = null) {
		return isset ( $_POST [$name] ) ? $_POST [$name] : $default;
	}
	
	/**
	 * 获取当前的$_SERVER变量
	 *
	 * @param string $name
	 * @return
	 */
	public static function server($name) {
		return isset ( $_SERVER [$name] ) ? $_SERVER [$name] : null;
	}
	
	/**
	 * 判断是否ajax请求
	 * @return boolean
	 */
	public static function isAjax() {
		return (self::server ( 'HTTP_X_REQUESTED_WITH' ) == 'XMLHttpRequest') ? true : false;
	}
	
	/**
	 * 获取客户端IP
	 * @return NULL|Ambigous <string, unknown>
	 */
	public static function getClientIp() {
		static $ip = null;
		if ($ip !== null)
			return $ip;
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			$pos = array_search ( 'unknown', $arr );
			if (false !== $pos)
				unset ( $arr [$pos] );
			$ip = trim ( $arr [0] );
		} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$ip = (false !== ip2long ( $ip )) ? $ip : '0.0.0.0';
		return $ip;
	}
	
	/**
	 * 清除context中的所有内容
	 */
	public static function clear() {
		foreach ( self::$_data as $key => $value ) {
			self::$_data [$key] = null;
			$value = null;
		}
		self::$_data = array ();
	}
}