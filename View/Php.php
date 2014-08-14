<?php
/**
 * @author Ian
 * Santa_View_Php native php view engine 原生PHP模板引擎
 */
class Santa_View_Php implements Santa_View {
	
	public function __construct() {
	}
	
	/**
	 * render view
	 * @param unknown_type $tpl
	 * @return string
	 */
	protected function _render($tpl) {
		if (null === $tpl) {
			$tpl = __CONTROLLER__ . DIRECTORY_SEPARATOR . __ACTION__ . '.html';
		}
		ob_start ();
		ob_implicit_flush ( 0 );
		$file = VIEW_DIR . DIRECTORY_SEPARATOR . $tpl;
		include $file;
		return ob_get_clean ();
	}
	
	public function assign($key, $value = null) {
		$this->$key = $value;
	}
	
	public function fetch($tpl) {
		return $this->_render ( $tpl );
	}
	
	public function display($tpl) {
		echo $this->fetch ( $tpl );
	}
	
	/**
	 * 在模板中引入其它模板
	 * @param unknown_type $tpl
	 */
	public function import($tpl) {
		include VIEW_DIR . DIRECTORY_SEPARATOR . $tpl;
	}
	
	/* public function __set($key, $value = null) {
		$this->$key = $value;
	} */
	
	public function __get($key) {
		if (! isset ( $this->$key )) {
			throw new Santa_Exception ( "Undefined property:Santa_View_Php::\${$key}" );
		}
		return $this->$key;
	}
}