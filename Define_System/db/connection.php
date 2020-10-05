<?php

namespace DefineSystem;

/**
 * Define System Class for Database Connection
 *
 * usage ::
 * make instance:
 * activate()
 *
 * make database connection :
 * connect()
 *
 * disconnect database connection :
 * disconnect()
 *
 * execute SQL statement :
 * exec()
 *
 * execute SQL statement with quotation params directory :
 * execute()
 *
 * take result SQL execution set by select(), insert(), update() and other SQL create method
 * result()
 *
 * prepare select statement :
 * select()
 *
 * prepare insert statement :
 * insert()
 *
 * prepare insert values :
 * values()
 *
 * prepare update statement :
 * update()
 *
 * prepare delete statement :
 * delete()
 *
 * prepare where conditions :
 * where()
 *
 * prepare group by conditions :
 * group()
 *
 * prepare order by conditions :
 * order()
 *
 * prepare limit conditions :
 * limit()
 */
class Db {

	private $connection = null;
	private $sql = null;

	private $msg = [];
	private $recordCacheDirectory = null;
	private $recordCacheFileName = null;
	private $sqlDumpCacheDirectory = null;
	private $sqlDumpCacheFileName = null;
	private $binder = ':';

	private $sql_logs = [];

	const DSN_BASE_STRING = '%s:host=%s';
	const DSN_DB_STRING = '%s;dbname=%s';
	const DSN_PORT_STRING = '%s;port=%s';

	public static function activate() {
		return new self();
	}

	public function connect($driver, $user, $pass, $host = 'localhost', $port = null, $db_name = null) {
		$dsn = sprintf(self::DSN_BASE_STRING, $driver, $host);
		if (!is_null($db_name)) {
		    $dsn = sprintf(self::DSN_DB_STRING, $dsn, $db_name);
		}
		if (!is_null($port)) {
			$dsn = sprintf(self::DSN_PORT_STRING, $dsn, $port);
		}

		try {
			// connect
			$connection = new \PDO($dsn, $user, $pass);
			$this->connection = $connection;
		} catch (\PDOException $err) {
			// connection failed
			$this->msg[] = 'failed to connect database : ' . $err->getMessage();
			return false;
		}

		$this->activateSql();
		$this->msg[] = 'database connected';
		return true;
	}

	public function disconnect() {
	    $this->connection = null;
		$this->msg[] = 'database closed';
		return true;
	}

	public function exec($sql) {
	    if (is_null($this->connection)) {
			$this->msg[] = 'cannot execute sql,database disconnected：'.$sql;
			return false;
		}
		if (! SQL_COMMAND_EXEC) {
			$this->msg[] = 'cannot use exec command.turn on the define system setting';
			return false;
		}

		try {
			// execute
		    $result = $this->connection->exec($sql);
			if ($result === false) {
				throw new \PDOException();
			}
			$this->msg[] = 'sql query done,'.$result.'rows affwcted：'.$sql;

		} catch (\PDOException $err) {
			// error
			$this->msg[] = 'failed to execute sql ：'.$sql;
			return false;
		}

		return true;
	}

	public function execute($sql, $params = null) {
	    if (is_null($this->connection)) {
			$this->msg[] = 'cannot execute sql,database disconnected：'.$sql;
			return false;
		}
		if (! $this->sqlValidateResult($sql)) {
			$this->msg[] = 'this query is unexecuttable.please use exec command';
			return;
		}

		try {
			// execute
		    $stmt = $this->connection->prepare($sql);
			if ($stmt === false) {
				throw new \PDOEXception();
			}

			$params_throw = false;
			if (is_array($params)) {
				foreach ($params as $key => &$value) {
				    if (is_numeric($key)) {
				        $params_throw = true;
				        continue;
				    }
					$stmt->bindParam($this->binder.$key, $value);
				}
			}

			$result = $params_throw ? $stmt->execute($params) : $stmt->execute();
			if ($result === false) {
				throw new \PDOEXception();
			}
			$this->sql_logs[] = [$sql, $params];

		} catch (\PDOException $err) {
			// error
		    $param_message = print_r($params, true);
		    $param_message = ' | params : ' . $param_message;
		    $this->msg[] = 'failed to execute sql, info : ' . print_r($stmt->errorInfo(), true). ' | sql ：'.$sql.$param_message;

			return SQL_RETURN_EMPTY_ARRAY ? [] : false;
		}

		return $stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function result() {
	    $this->activateSql();
	    $this->msg = array_merge($this->msg, $this->sql->msg());
	    $sql = $this->sql->sql();
	    $params = $this->sql->bindValues();
	    return count($params) > 0 ? $this->execute($sql, $params) : $this->execute($sql);
	}

	public function select($columns, $from) {
	    $this->activateSql();
	    $this->sql->select($columns, $from);
	}

	public function insert($columns, $into, $duplicate = false) {
	    $this->activateSql();
	    $this->sql->insert($columns, $into, $duplicate);
	}

	public function values($values) {
	    $this->activateSql();
	    $this->sql->values($values);
	}

	public function update($set, $update) {
	    $this->activateSql();
	    $this->sql->update($set, $update);
	}

	public function delete($delete) {
	    $this->activateSql();
	    $this->sql->delete($delete);
	}

	public function where($condition) {
	    $this->activateSql();
	    $this->sql->where($condition);
	}

	public function group($targets) {
	    $this->activateSql();
	    $this->sql->group($targets);
	}

	public function order($targets) {
	    $this->activateSql();
	    $this->sql->order($targets);
	}

	public function limit($limit, $offset = null) {
	    $this->activateSql();
	    $this->sql->limit($limit, $offset);
	}

	public function join($table, $condition, $type) {
	    $this->activateSql();
	    $this->sql->join($table, $condition, $type);
	}

	public function leftJoin($table, $condition) {
	    $this->activateSql();
	    $this->sql->leftJoin($table, $condition);
	}

	public function rightJoin($table, $condition) {
	    $this->activateSql();
	    $this->sql->rightJoin($table, $condition);
	}

	public function innerJoin($table, $condition) {
	    $this->activateSql();
	    $this->sql->innerJoin($table, $condition);
	}

	public function fullOuterJoin($table, $condition) {
	    $this->activateSql();
	    $this->sql->fullOuterJoin($table, $condition);
	}

	public function msg() {
		return $this->msg;
	}

	public function sqlLogs() {
	    return $this->sql_logs;
	}

	public function eraseMsg() {
		$this->msg = array();
	}

	public function setBindValues($bind) {
	    $this->sql->setBindValues($bind);
	}

	private function sqlValidateStmt($sql) {
		$unexecutableQuery = [
			'/^(\s*)(CREATE)(\s+)(DATABASE)(S?)(\s.)/i',
			'/^(\s*)(DROP)(\s+)(DATABASE)(S?)(\s.)/i',
			'/^(\s*)(CREATE)(\s+)(TABLE)(S?)(\s.)/i',
			'/^(\s*)(TRUNCATE)(\s+)(TABLE)(S?)(\s.)/i',
			'/^(\s*)(DROP)(\s)(TABLE)(S?)(\s.)/i',
			'/^(\s*)(USE)(\s.)/i'
		];
		$matched = false;
		foreach ($unexecutableQuery as $query) {
			if (preg_match($query, $sql)) {
				$matched = true;
				break;
			}
		}
		return $matched ? false : true;
	}

	private function sqlValidateResult($sql) {
		$unexecutableQuery = [
			'/^(\s*)(CREATE)(\s+)(DATABASE)(S?)(\s.)/i',
			'/^(\s*)(DROP)(\s+)(DATABASE)(S?)(\s.)/i',
			'/^(\s*)(CREATE)(\s+)(TABLE)(S?)(\s.)/i',
			'/^(\s*)(TRUNCATE)(\s+)(TABLE)(S?)(\s.)/i',
			'/^(\s*)(DROP)(\s)(TABLE)(S?)(\s.)/i',
			'/^(\s*)(USE)(\s.)/i'
		];
		$matched = false;
		foreach ($unexecutableQuery as $query) {
			if (preg_match($query, $sql)) {
				$matched = true;
				break;
			}
		}
		return $matched ? false : true;
	}


	private function activateSql() {
	    if (is_null($this->sql)) {
	        $this->sql = Sql::activate();
	    }
	}

}
