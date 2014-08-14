<?php
/**
 * @author Ian
 * PDO封装
 */
class Santa_Db_Pdo {
	
	/**
	 * @var PDO instance
	 */
	protected $_connection = null;
	
	/**
	 * @var PDOStatement instance
	 */
	protected $_statement = null;
	
	/**
	 * @return Santa_Util_Db_Pdo
	 */
	public function __construct($config = null) {
		if (null !== $config) {
			if (null === $this->_connection) {
				$this->_connect ( $config );
			}
		} else {
			throw new Santa_Exception ( "param \$config is null" );
		}
	}
	
	/**
	 * @throws Exception
	 * @return PDO
	 */
	protected function _connect($config) {
		if ($config ['persistent']) {
			$config ['options'] [PDO::ATTR_PERSISTENT] = true;
		}
		$dsn = 'mysql:host=' . $config ['host'] . ';port=' . $config ['port'] . ';dbname=' . $config ['database'];
		try {
			$this->_connection = new PDO ( $dsn, $config ['user'], $config ['password'], $config ['options'] );
		} catch ( Exception $e ) {
			throw $e;
		}
		$this->query ( "SET NAMES '" . $config ['charset'] . "';" );
		return $this->_connection;
	}
	
	/**
	 * @param string $sql
	 * @throws Exception
	 * @return Santa_Util_Db_Pdo
	 */
	protected function _statement($sql) {
		$this->_statement = $this->_connection->query ( $sql );
		if (false === $this->_statement) {
			throw new Santa_Exception ( "\$_statement can not be created as PDOStatement instance,check your params or config" );
		}
		return $this;
	}
	
	/**
	 * @param string $style
	 * @return number
	 */
	protected static function _getFetchStyle($style) {
		switch ($style) {
			case 'ASSOC' :
				$style = PDO::FETCH_ASSOC;
				break;
			case 'BOTH' :
				$style = PDO::FETCH_BOTH;
				break;
			case 'NUM' :
				$style = PDO::FETCH_NUM;
				break;
			case 'OBJECT' :
				$style = PDO::FETCH_OBJECT;
				break;
			default :
				$style = PDO::FETCH_ASSOC;
		}
		return $style;
	}
	
	/**
	 * 获取一条记录
	 * @param string $type
	 * @return mixed
	 */
	protected function _fetch($type = 'ASSOC') {
		$type = strtoupper ( $type );
		$result = $this->_statement->fetch ( self::_getFetchStyle ( $type ) );
		$this->free ();
		return $result;
	}
	
	/**
	 * 获取记录集
	 * @param string $type
	 * @return multitype:
	 */
	protected function _fetchAll($type = 'ASSOC') {
		$type = strtoupper ( $type );
		$result = $this->_statement->fetchAll ( self::_getFetchStyle ( $type ) );
		$this->free ();
		return $result;
	}
	
	/**
	 * @return number
	 */
	protected function _affectedRows() {
		return $this->_statement->rowCount ();
	}
	
	/**
	 * 执行一条sql语句
	 * @param unknown_type $sql
	 * @param unknown_type $type
	 * @return PDOStatement
	 */
	public function query($sql, $type = "ASSOC") {
		$this->_statement ( $sql );
		$sqlType = explode ( ' ', $sql, 2 );
		switch (strtoupper ( $sqlType [0] )) {
			case 'SELECT' :
				($result = $this->_fetchAll ( $type )) || ($result = array ());
				break;
			case 'INSERT' :
				$result = $this->lastInsertId ();
				break;
			case 'UPDATE' :
			case 'DELETE' :
				$result = $this->_affectedRows ();
				break;
			default :
				$result = $this->_statement;
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
		$this->_statement ( $sql );
		return $this->_fetch ( $type );
	}
	
	/**
	 * 获取记录集
	 * @param string $sql
	 * @param string $type
	 * @return Ambigous <multitype:, multitype:>
	 */
	public function findAll($sql, $type = "ASSOC") {
		$this->_statement ( $sql );
		return $this->_fetchAll ( $type );
	}
	
	/**
	 * 获取
	 * @param string $table
	 * @param array $conditions
	 * @return PDOStatement
	 */
	public function select($table, array $conditions = array()) {
		$result = array ();
		$conditions = $conditions + array (
			'fields' => '*', 
			'where' => 1, 
			'order' => null, 
			'limit' => null 
		);
		extract ( $conditions );
		unset ( $conditions );
		$sql = "select {$fields} from `$table` where $where";
		if ($order) {
			$sql .= " order by {$order}";
		}
		if ($limit) {
			$sql .= " limit {$limit}";
		}
		$result = $this->query ( $sql );
		return $result;
	}
	
	/**
	 * 插入
	 * @param string $table
	 * @param array $data
	 * @throws Exception
	 * @return PDOStatement
	 */
	public function insert($table, array $data) {
		if (empty ( $data )) {
			throw new Santa_Exception ( "param \$data is empty array" );
		}
		$keys = '';
		$values = '';
		foreach ( $data as $key => $value ) {
			$keys .= "`$key`,";
			$values .= "'" . $value . "',";
		}
		$sql = "insert into `$table` (" . substr ( $keys, 0, - 1 ) . ") values (" . substr ( $values, 0, - 1 ) . ");";
		return $this->query ( $sql );
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
		if (empty ( $data )) {
			throw new Santa_Exception ( "param \$data is empty array" );
		}
		$tmp = array ();
		foreach ( $data as $key => $value ) {
			$tmp [] = "`$key`='" . $value . "'";
		}
		$str = implode ( ',', $tmp );
		$sql = "update `$table` set " . $str . " where $where";
		return $this->query ( $sql );
	
	}
	
	/**
	 * 删除
	 * @param string $table
	 * @param string $where
	 * @return PDOStatement
	 */
	public function delete($table, $where = '0') {
		$sql = "delete from `$table` where $where";
		return $this->query ( $sql );
	}
	
	/**
	 * 获取记录行数
	 * @param string $table
	 * @param string $where
	 * @return Ambigous <number, mixed>
	 */
	public function count($table, $where = '1') {
		$sql = "select count(1) as cnt from `$table` where $where";
		$result = $this->findOne ( $sql );
		return empty ( $result ['cnt'] ) ? 0 : $result ['cnt'];
	}
	
	/**
	 * 切换数据库
	 * @param string $database
	 * @return PDOStatement
	 */
	public function selectDb($database) {
		return $this->query ( "use $database;" );
	}
	
	/**
	 * @return boolean
	 */
	public function beginTransaction() {
		return $this->_connection->beginTransaction ();
	}
	
	/**
	 * @return boolean
	 */
	public function commit() {
		return $this->_connection->commit ();
	}
	
	/**
	 * @return boolean
	 */
	public function rollBack() {
		return $this->_connection->rollBack ();
	}
	
	/**
	 * @param string $name
	 * @return string
	 */
	public function lastInsertId($name = null) {
		return $this->_connection->lastInsertId ( $name );
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
		$this->_connection = null;
	}
	
	/**
	 * 释放PDOStatement
	 */
	public function free() {
		$this->_statement = null;
	}
}