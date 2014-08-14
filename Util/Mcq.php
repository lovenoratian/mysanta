<?php
/**
 * @author Ian
 *
 */
class Santa_Util_Mcq {
	
	const CHAR_ALT = 18; //ALT 键的ascii码用于标记时间信息
	/**
	 * 连接mcq
	 * @param string $mcq_name
	 * @return Memcached
	 */
	
	private static function connect($mcq_name) {
		$conf = Santa_Config::get ( "mcq.$mcq_name" );
		$mcq = new Memcached ();
		$mcq->setOption ( Memcached::OPT_COMPRESSION, false );
		$mcq->addServer ( $conf ['host'], $conf ['port'] );
		
		return $mcq;
	}
	
	/**
	 * 写入mcq
	 * @param string    $mcq_name        MCQ名称，必须是 self::MCQ_XX
	 * @param mixed     $value           要写入mcq的数据
	 * @param bool      $json_encode     是否自动json编码，默认为是，读取是会默认做json解码
	 * @return mixed
	 */
	public static function write($mcq_name, $value, $json_encode = true) {
		//写入数据处理
		$str = $json_encode ? json_encode ( $value ) : $value;
		$str = self::makeInputString ( $mcq_name, $str );
		
		//写入
		return self::connect ( $mcq_name )->set ( $mcq_name, $str );
	}
	
	/**
	 * 批量读取mcq（每次调用此方法是会重新链接mcq）
	 * @param string    $mcq_name        MCQ名称，必须是 self::MCQ_XX
	 * @param bool      $json_encode     是否自动json解码，默认是
	 * @param int       $max_mun         批量读取的最多个数，默认读取
	 * @return mixed
	 */
	public static function read($mcq_name, $json_decode = true, $max_num = 100) {
		//读取
		$arr_out = array ();
		$mcq = self::connect ( $mcq_name );
		for($i = 0; $i < $max_num; $i ++) {
			$str = $mcq->get ( $mcq_name );
			if (! $str) {
				break;
			}
			
			//数据处理
			$str = self::parseOutputString ( $mcq_name, $str );
			$str && $json_decode && $str = json_decode ( $str, true );
			$arr_out [] = $str;
		}
		
		//数据处理
		return $arr_out;
	}
	
	/**
	 * 处理写入mcq的字符串（处理为加入时间信息）
	 * @param string $mcq_name  mcq名称，结合配置信息用于区分是否处理写入字符串
	 * @param string $str       即将写入mcq的字符串
	 * @return string           处理过后的字符串
	 */
	private static function makeInputString($mcq_name, $str) {
		if (Santa_Config::get ( "mcq.{$mcq_name}.max_delay" )) {
			$str = chr ( self::CHAR_ALT ) . time () . $str;
		}
		
		return $str;
	}
	
	/**
	 * 解析mcq返回的数据,去除附加信息。并检查时间，考虑记录日志
	 * @param string $mcq_name mcq别名，记录错误日志时需要
	 * @param string $str   读取的mcq内容
	 * @return string       去除附加信息后的数据
	 */
	private static function parseOutputString($mcq_name, $str) {
		if (substr ( $str, 0, 1 ) == chr ( self::CHAR_ALT )) { //加入了时间信息
			$in_time = substr ( $str, 1, 10 ); //提取写入时间
			if (trim ( $in_time, '0123456789' ) == '') { //时间戳通过基本检测
				//去除附加的时间信息
				$str = substr ( $str, 11 );
				/* //报警检测
				$max_delay = Santa_Config::get ( "mcq.{$mcq_name}.max_delay" );
				if ($max_delay > 0) {
					$out_time = time (); //读取时间
					if ($out_time - $in_time > $max_delay) {
						//此处写报警代码
						$data = array (
							'mcq_name' => $mcq_name, 
							'max_delay' => $max_delay, 
							'real_delay' => $out_time - $in_time, 
							'mcq_content' => $str 
						);
						Santa_Util_Log::writeLog ( 'mcq_out_time', json_encode ( $data ) );
					}
				} */
			}
		}
		return $str;
	}
}