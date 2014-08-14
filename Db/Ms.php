<?php
/**
 * @author Ian
 * master/slave主从式数据库
 */
class Santa_Db_Ms {
	
	/**
	 * 主库
	 * @var unknown_type
	 */
	public $master = null;
	
	/**
	 * 从库
	 * @var unknown_type
	 */
	public $slaves = null;
	
	public function __construct($engine, $config) {
		//配置主库
		if (null === $this->master) {
			$masterConf = $config ['master'];
			$this->master = new $engine ( $masterConf );
		}
		//配置从库
		if (isset ( $config ['slaves'] ) && null === $this->slaves) {
			$slavesConf = $config ['slaves'];
			foreach ( $slavesConf as $conf ) {
				$this->slaves [] = new $engine ( $conf );
			}
		}
	}
	
	/**
	 * 主库实例
	 * @return unknown_type
	 */
	public function master() {
		return $this->master;
	}
	
	/**
	 * 随机在从库中取出一个对象，若从库未配置则返回主库对象
	 * @return NULL
	 */
	public function slave() {
		if (null === $this->slaves) {
			return $this->master;
		}
		$rand = array_rand ( $this->slaves );
		return $this->slaves [$rand];
	}
	
	/**
	 * 执行一条sql语句
	 * @param string $sql
	 * @param string $type
	 * @return PDOStatement
	 */
	public function query($sql, $type = "ASSOC") {
		$sqlType = explode ( ' ', $sql, 2 );
		switch (strtoupper ( $sqlType [0] )) {
			case 'SELECT' :
				$result = $this->slave ()->query ( $sql, $type );
			default :
				$result = $this->master->query ( $sql, $type );
		}
		return $result;
	}
	
	/**
	 * 获取一条记录
	 * @param string $sql
	 * @param string $type
	 * @return mixed
	 */
	public function findOne($sql, $type = "ASSOC") {
		return $this->slave ()->findOne ( $sql, $type );
	}
	
	/**
	 * 获取记录集
	 * @param string $sql
	 * @param string $type
	 * @return Ambigous <multitype:, multitype:>
	 */
	public function findAll($sql, $type = "ASSOC") {
		return $this->slave ()->findAll ( $sql, $type );
	}
	
	/**
	 * 获取
	 * @param string $table
	 * @param array $conditions
	 * @return PDOStatement
	 */
	public function select($table, array $conditions = array()) {
		return $this->slave ()->select ( $table, $conditions );
	}
	
	/**
	 * 插入
	 * @param string $table
	 * @param array $data
	 * @throws Exception
	 * @return PDOStatement
	 */
	public function insert($table, array $data) {
		return $this->master->insert ( $table, $data );
	}
	
	/**
	 * 更新
	 * @param string $table
	 * @param array $data
	 * @param string $where
	 * @throws Exception
	 * @return PDOStatement
	 */
	public function update($table, array $data, $where = '0') {
		return $this->master->update ( $table, $data, $where );
	}
	
	/**
	 * 删除
	 * @param string $table
	 * @param string $where
	 * @return PDOStatement
	 */
	public function delete($table, $where = '0') {
		return $this->master->delete ( $table, $where );
	}
	
	/**
	 * 获取记录行数
	 * @param string $table
	 * @param string $where
	 * @return Ambigous <number, mixed>
	 */
	public function count($table, $where = '1') {
		return $this->slave ()->count ( $table, $where );
	}
	
	/**
	 * @return boolean
	 */
	public function beginTransaction() {
		$this->master->beginTransaction ();
	}
	
	/**
	 * @return boolean
	 */
	public function commit() {
		return $this->master->commit ();
	}
	
	/**
	 * @return boolean
	 */
	public function rollBack() {
		return $this->master->rollBack ();
	}
	
	/**
	 * @param string $name
	 * @return string
	 */
	public function lastInsertId($name = null) {
		return $this->master->lastInsertId ( $name );
	}
	
	/**
	 * @param string $str
	 * @return string
	 */
	public static function escape($str) {
		return addslashes ( $str );
	}
	
	/**
	 * 释放数据库连接
	 */
	public function close() {
		$this->master->close ();
		if (null !== $this->slaves) {
			foreach ( $this->slaves as $slave ) {
				$slave->close ();
				unset ( $slave );
			}
		}
	}
	
	/**
	 * 释放PDOStatement
	 */
	public function free() {
		$this->master->free ();
		if (null !== $this->slaves) {
			foreach ( $this->slaves as $slave ) {
				$slave->free ();
				unset ( $slave );
			}
		}
	}
}