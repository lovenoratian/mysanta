<?php
class PinStore {
    const MC_LIVETIME = 300;
    const CACHE_NAME = 'l1';
    private static $MC_KEY_PINCODE = '%s_1_%s';
    
    function __construct() {
        $this->CACHE_KEY_PINCODE = Comm_Util::conf('cache_key.pincode');
    }
    
    /**
     * 添加验证码到缓存
     * @param string $key
     * @param string $code
     */
    public function add_pin($key, $code) {
        $mc_key = sprintf ( self::$MC_KEY_PINCODE, $this->CACHE_KEY_PINCODE, $key);
        $re = Comm_Cache::pool (self::CACHE_NAME)->set($mc_key, $code, self::MC_LIVETIME);
        return $re;
    }
    
    /**
     * 从缓存中获取验证码
     * @param string $key
     */
    public function get_pin($key) {
        $mc_key = sprintf ( self::$MC_KEY_PINCODE, $this->CACHE_KEY_PINCODE, $key);
        $code = Comm_Cache::pool(self::CACHE_NAME)->get($mc_key, self::MC_LIVETIME);
        return $code;
    }
    
    /**
     * 从缓存中删除验证码
     * @param string $key
     */
    public function del_pin($key) {
        $mc_key = sprintf ( self::$MC_KEY_PINCODE, $this->CACHE_KEY_PINCODE, $key);
        $re = Comm_Cache::pool(self::CACHE_NAME)->del($mc_key);
        return $re;
    }
    
}
?>