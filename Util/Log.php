<?php
/**
 * 记录日志处理.
 *
 * 记录位置为: (/data/logs/{appname})
 */
class Santa_Util_Log {
	
	/**
	 * 记录应用日志
	 * @param string $file
	 * @param string $content
	 * @return mixed
	 */
	static public function writeLog($file, $content, $appname = 'default') {
		$file = "/data/logs/{$appname}" . '/runtime/' . date ( 'Y/md/' ) . $file . '.log';
		$dir = dirname ( $file );
		if (! is_dir ( $dir )) {
			mkdir ( $dir, 0777, true );
			chmod ( $dir, 0777 );
		}
		$content = '[' . date ( 'Y-m-d H:i:s' ) . ']' . $content . "\r\n";
		return file_put_contents ( $file, $content, FILE_APPEND );
	}

}