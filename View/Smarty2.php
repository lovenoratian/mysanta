<?php

require_once VENDOR_DIR . '/Smarty-2.6.26/libs/Smarty.class.php';

class Santa_View_Smarty2 extends Smarty implements Santa_View {
	
	public function __construct() {
		$this->smarty = new Smarty ();
		
		$this->compile_dir = Santa_Config::get ( "env.compile_dir" );
		$this->cache_dir = Santa_Config::get ( "env.cache_dir" );
		$this->template_dir = VIEW_DIR;
		
		$this->left_delimiter = "<?";
		$this->right_delimiter = "?>";
	}
}