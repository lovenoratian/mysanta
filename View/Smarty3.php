<?php

require_once VENDOR_DIR . '/Smarty-3.0.7/libs/Smarty.class.php';

class Santa_View_Smarty3 extends Smarty implements Santa_View {
	public function __construct() {
		parent::__construct ();
		
		$this->compile_dir = Santa_Config::get ( "env.compile_dir" );
		$this->cache_dir = Santa_Config::get ( "env.cache_dir" );
		$this->template_dir = VIEW_DIR;
		
		$this->left_delimiter = "<?";
		$this->right_delimiter = "?>";
	}
}