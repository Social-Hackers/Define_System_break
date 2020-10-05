<?php

namespace DefineSystem;

class Sql {

	private $sql = null;

	private $select = null;
	private $from = null;
	private $insert = null;
	private $into = null;
	private $values = null;
	private $update = null;
	private $set = null;
	private $delete = null;

	private $where = [];
	private $group = [];
	private $order = [];
	private $limit = [];

	private $left_join = null;
	private $right_join = null;
	private $inner_join = null;
	private $full_outer_join = null;
	private $join_on = [];

	private $bind_values_array = [];
	private $bind_values_update = [];
	private $bind_values_insert = [];
	private $bind_values_where = [];
	private $bind_values_where_join_on = [];
	private $bind_values_limit = [];

	private $msg = [];

	private $bind_values = null;

	private $insert_duplicate = false;

	const SQL_SELECT = 'SELECT %s FROM %s';
	const SQL_INSERT = 'INSERT INTO %s ( %s ) VALUES %s';
	const SQL_INSERT_DUPLICATE = 'INSERT INTO %s ( %s ) VALUES %s ON DUPLICATE KEY UPDATE %s';
	const SQL_UPDATE = 'UPDATE %s SET %s';
	const SQL_DELETE = 'DELETE FROM %s';

	const SQL_WHERE = '%s WHERE %s';
	const SQL_GROUP = '%s GROUP BY %s';
	const SQL_ORDER = '%s ORDER BY %s';
	const SQL_LIMIT = '%s LIMIT %s';

	const SQL_DUPLICATE_UPDATE_VALUES = '%s = VALUES(%s)';

	const SQL_LEFT_JOIN = '%s LEFT JOIN %s ON %s';
	const SQL_RIGHT_JOIN = '%s RIGHT JOIN %s ON %s';
	const SQL_INNER_JOIN = '%s INNER JOIN %s ON %s';
	const SQL_FULL_OUTER_JOIN = '%s FULL OUTER JOIN %s ON %s';

	const SQL_STATE_SELECT = 1;
	const SQL_STATE_INSERT = 2;
	const SQL_STATE_UPDATE = 3;
	const SQL_STATE_DELETE = 4;

	public static function activate() {
		return new self();
	}

	public function sql() {

	    $sql = $this->sqlPreserve();
	    $this->bindValuesBunch();
	    $this->resetStatements();

	    return $sql;
	}

	public function select($columns, $table) {
	    $from = '';
	    if (is_single_word($table)) {
	        $from = '`'.$table.'`';
	    } elseif (is_array($table)) {
	        if (isset($table[0]) && isset($table[1])) {
	            $from = '`'.$table[0].'` AS `'.$table[1].'`';
	        } else {
	            $this->msg[] = 'Please enter correct table name : ' . print_r($table, true);
	            return;
	        }
	    } else {
	        $this->msg[] = 'Please enter correct table name : ' . print_r($table, true);
	        return;
	    }
	    if (! is_array($columns)) {
	        $this->msg[] = 'Please enter correct column names as array : ' . print_r($columns, true);;
	        return;
	    }
	    foreach ($columns as $each) {
	        if (! is_string($each)) {
	            $this->msg[] = 'Please enter correct column name : ' . print_r($each, true);
	            return;
	        }
	        $this->select[] = trim($each) === '*' ? $each : '`'.$each.'`';
	    }

	    $this->from = $from;
	}

	public function insert($columns, $table, $duplicate = false) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Please enter correct table name : ' . print_r($table, true);
	        return;
	    }
	    if (! is_array($columns)) {
	        $this->msg[] = 'Please enter correct column names as array : ' . print_r($columns, true);;
	        return;
	    }
	    foreach ($columns as $each) {
	        if (! is_string($each)) {
	            $this->msg[] = 'Please enter correct column name : ' . print_r($each, true);
	            return;
	        }
	        $this->insert[] = '`'.$each.'`';
	    }

	    $this->insert_duplicate = $duplicate;
	    $this->into = $table;
	}

	public function update($set, $table) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Please enter correct table name : ' . print_r($table, true);
	        return;
	    }
	    if (! is_array($set)) {
	        $this->msg[] = 'Please enter correct column names as array : ' . print_r($set, true);;
	        return;
	    }
	    $set_conbined = [];
	    foreach ($set as $column => $value) {
	        if (! is_string($column)) {
	            $this->msg[] = 'Please enter correct column name : ' . print_r($column, true);
	            return;
	        }
	        if (! is_single_word($value)) {
	            $this->msg[] = 'Please enter correct column value : ' . print_r($value, true);
	            return;
	        }
	        if ($this->isBindValues()) {
	            $bind_needle = '?';
	            $this->bind_values_update[] = $value;
	        } else {
	            $bind_needle = "'{$value}'";
	        }

	        $set_conbined[] = "{$column} = {$bind_needle}";
	    }

	    $this->update = $table;
	    $this->set = implode(',', $set_conbined);
	}

	public function delete($table) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Please enter correct table name : ' . print_r($table, true);
	        return;
	    }
	    $this->delete = $table;
	}

	public function values($values) {
	    if (! is_array($values)) {
	        $this->msg[] = 'Please enter correct insert values as array : ' . print_r($values, true);;
	        return;
	    }
	    $this->values = $values;
	}

	public function where($condition) {
	    $this->whereCases($condition, false, false, false);
	}


	public function group($targets) {
	    if (! is_array($targets)) {
	        $this->msg[] = 'Please enter correct group targets as array : ' . print_r($targets, true);
	        return;
	    }
	}

	public function order($targets) {
	    if (! is_array($targets)) {
	        $this->msg[] = 'Please enter correct order targets as array : ' . print_r($targets, true);
	        return;
	    }
	    $this->order = $targets;
	}


	public function limit($limit, $offset) {
	    if (! is_single_word($limit)) {
	        $this->msg[] = 'Incorrect limit : ' . print_r($limit, true);
	        return;
	    }
	    if (! is_null($offset) && ! is_single_word($offset)) {
	        $this->msg[] = 'Incorrect offset : ' . print_r($offset, true);
	        return;
	    }
	    $this->limit = [$limit, $offset];
	}

	public function join($table, $condition, $type) {
	    switch ($type) {
	        case LEFT_JOIN:
	            $this->leftJoin($table, $condition);
	            break;
	        case RIGHT_JOIN:
	            $this->rightJoin($table, $condition);
	            break;
	        case INNER_JOIN:
	            $this->innerJoin($table, $condition);
	            break;
	        case FULL_OUTER_JOIN:
	            $this->fullOuterJoin($table, $condition);
	            break;
	        default:
	            $this->msg[] = 'Incorrect join type : ' . print_r($type, true);
	    }
	}

	public function leftJoin($table, $condition) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Incorrect table name : ' . print_r($table, true);
	    }
	    $this->left_join = "`{$table}`";
	    $this->whereCases($condition, false, false, true);
	}

	public function rightJoin($table, $condition) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Incorrect table name : ' . print_r($table, true);
	    }
	    $this->right_join = "`{$table}`";
	    $this->whereCases($condition, false, false, true);
	}

	public function innerJoin($table, $condition) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Incorrect table name : ' . print_r($table, true);
	    }
	    $this->inner_join = "`{$table}`";
	    $this->whereCases($condition, false, false, true);
	}

	public function fullOuterJoin($table, $condition) {
	    if (! is_single_word($table)) {
	        $this->msg[] = 'Incorrect table name : ' . print_r($table, true);
	    }
	    $this->full_outer_join = "`{$table}`";
	    $this->whereCases($condition, false, false, true);
	}

	public function msg() {
        return $this->msg;
	}

	public function bindValues() {
	    return $this->bind_values_array;
	}

	public function eraseBindValues() {
	    $this->bind_values_array = [];
	}

	public function setBindValues($bind = true) {
	    if (! is_bool($bind)) {
	        $this->msg[] = 'Please set correct true/false parameter to bind parameters';
	        return false;
	    }
	    $this->bind_values = $bind;
	    return true;
	}

	private function whereCases($condition, $gathered = false, $last = false, $join = false) {
	    if (! is_array($condition)) {
	        $this->msg[] = 'Incorrect where case : ' . print_r($condition, true);
	        return;
	    }
	    $keys = [
	        'value_needle' => 0,
	        'condition_mark' => 1,
	        'conbined_with' => 2,
	        'quoted' => 3
	    ];
	    if ($gathered && count($condition) > 0) {
	        $join ? $this->join_on[] = ['('] : $this->where[] = ['('];
	    }

	    $j = 0;
	    foreach ($condition as $each) {
	        $j ++;
	        if (! is_array($each)) {
	            $this->msg[] = 'one statement skiped,incorrect where statement : ' . print_r($each, true);
	            continue;
	        }
	        $condition_keys = array_keys($each);
	        $condition_values = array_values($each);
	        $column = null;
	        $needle = null;
	        $mark = '=';
	        $conbined_with = 'AND';
	        $quoted = true;

	        if (isset($condition_values[0]) && is_array($condition_values[0])) {
	            $last = $j == count($condition) ? true : false;
	            $this->whereCases($condition_values, true, $last, $join);
	            continue;
	        }

	        $i = 0;
	        for ($i; $i<count($condition_keys); $i++) {
	            if ($i == $keys['value_needle'] && isset($condition_values[$i])) {
	                if (! is_string_all([$condition_keys[$i], $condition_values[$i]])) {
	                    $this->msg[] = 'Incorrect column condition';
	                    continue;
	                }
	                $column = $condition_keys[$i];
	                $needle = $condition_values[$i];
	            }
	            if ($i == $keys['condition_mark'] && isset($condition_values[$i])) {
	                if (! is_single_word($condition_values[$i])) {
	                    $this->msg[] = 'Incorrect condition : ' .$mark;
	                    continue;
	                }
	                $mark = $condition_values[$i];
	            }
	            if ($i == $keys['conbined_with'] && isset($condition_values[$i])) {
	                if ($condition_values[$i] !== 'and' && $condition_values[$i] !== 'or'
	                    && $condition_values[$i] !== 'AND' && $condition_values[$i] !== 'OR') {
	                        $this->msg[] = 'Please enter \'and\'/\'or\' for condition conbination';
	                        continue;
	                    }
	                    $conbined_with = $condition_values[$i];
	            }
	            if ($i == $keys['quoted'] && isset($condition_values[$i])) {
	                if ($join && empty($condition_values[$i])) $quoted = false;
	            }
	        }
	        if (is_null($column) || is_null($needle) || is_null($mark)) {
	            $this->msg[] = 'one statement skiped,incorrect where statement : ' . print_r($column . '|' . $needle . '|' . $mark, true);
	            continue;
	        }
	        if (! $join && $this->isBindValues()) {
	            $bind_needle = '?';
	            $join ? $this->bind_values_where_join_on[] = $needle : $this->bind_values_where[] = $needle;
	        } else {
	            $bind_needle = $quoted ? "'{$needle}'" : "{$needle}";
	        }

	        if ($j == 1 && ($where_condition_count = $join ? count($this->join_on) : count($this->where)) > 0) {
	            $join ? $this->join_on[$where_condition_count] = [$this->where[$where_condition_count][0], 'AND'] : $this->where[$where_condition_count] = [$this->where[$where_condition_count][0], 'AND'];
	        }
	        if ($j == count($condition)) {
	            $join ? $this->join_on[] = ["`{$column}` {$mark} {$bind_needle}"] : $this->where[] = ["`{$column}` {$mark} {$bind_needle}"];
	        } else {
	            $join ? $this->join_on[] = ["`{$column}` {$mark} {$bind_needle}", $conbined_with] : $this->where[] = ["`{$column}` {$mark} {$bind_needle}", $conbined_with];
	        }
	    }
	}

	private function sqlPreserve() {

	    $sql = null;

	    if (! is_null($this->insert)) {
	        $sql = is_null($this->select) ? $this->sqlInsert() : $this->sqlInsertSelect();

	    } elseif (! is_null($this->update)) {
	        $this->updateWarn();
	        $sql = is_null($this->select) ? $this->sqlUpdate() : $this->sqlUpdateSelect();

	    } elseif (! is_null($this->select)) {
	        $sql = $this->sqlSelect();


	    } elseif (! is_null($this->delete)) {
            $this->deleteWarn();
            $sql = $this->sqlDelete();

	    } else {
	        $this->msg[] = 'No sql query was specified,sql statement may not made';
	    }

	    return $sql;
	}

	private function sqlInsert() {
	    if (is_null($this->values)) {
	        $this->msg[] = 'Insert values are not set.';
	        return;
	    }
	    $values_conbined = '';
	    $sparation = '';
	    foreach ($this->values as $value) {
	        $values_parts = [];
	        foreach ((array)$value as $each) {
	            if ($this->isBindValues()) {
	                $values_parts[] = '?';
	                $this->bind_values_insert[] = (string)$each;
	            } else {
	                $values_parts[] = "'".(string)$each."'";
	            }
	        }

	        $values_conbined .= "{$sparation}( " . implode(',', $values_parts). ')';
	        $sparation = ',';
	    }

	    if (! $this->insert_duplicate) {
	        $sql = sprintf(self::SQL_INSERT, $this->into, implode(',', $this->insert), $values_conbined);
	    } else {
	        $duplicate_update_values_array = [];
	        foreach ($this->insert as $insert) {
	            $duplicate_update_values_array[] = sprintf(self::SQL_DUPLICATE_UPDATE_VALUES, $insert, $insert);
	        }
	        $duplicate_update_values = implode(',', $duplicate_update_values_array);
	        $sql = sprintf(self::SQL_INSERT_DUPLICATE, $this->into, implode(',', $this->insert), $values_conbined, $duplicate_update_values);
	    }
	    return $sql;
	}

	private function sqlInsertSelect() {}

	private function sqlUpdate() {
	    $sql = sprintf(self::SQL_UPDATE, $this->update, $this->set);
	    if (! empty($this->where)) {
	        $sql = sprintf(self::SQL_WHERE, $sql, $this->sqlWhere());
	    }
	    if (! empty($this->limit)) {
	        $sql = sprintf(self::SQL_LIMIT, $sql, $this->sqlLimit());
	    }

	    return $sql;
	}

	private function sqlUpdateSelect() {}

	private function sqlSelect() {
	    $sql = sprintf(self::SQL_SELECT, implode(',', $this->select), $this->from);
	    if (! empty($this->left_join)) {
	        $sql = sprintf(self::SQL_LEFT_JOIN, $sql, $this->left_join, $this->sqlWhereJoin());
	    }
	    if (! empty($this->right_join)) {
	        $sql = sprintf(self::SQL_RIGHT_JOIN, $sql, $this->right_join, $this->sqlWhereJoin());
	    }
	    if (! empty($this->inner_join)) {
	        $sql = sprintf(self::SQL_INNER_JOIN, $sql, $this->inner_join, $this->sqlWhereJoin());
	    }
	    if (! empty($this->full_outer_join)) {
	        $sql = sprintf(self::SQL_FULL_OUTER_JOIN, $sql, $this->full_outer_join, $this->sqlWhereJoin());
	    }
	    if (! empty($this->where)) {
	        $sql = sprintf(self::SQL_WHERE, $sql, $this->sqlWhere());
	    }
	    if (! empty($this->order)) {
	        $sql = sprintf(self::SQL_ORDER, $sql, $this->sqlOrder());
	    }
	    if (! empty($this->group)) {
	        $sql = sprintf(self::SQL_GROUP, $sql, $this->sqlGroup());
	    }
	    if (! empty($this->limit)) {
	        $sql = sprintf(self::SQL_LIMIT, $sql, $this->sqlLimit());
	    }

	    return $sql;
	}

	private function updateWarn() {
	    if (is_null($this->where)) {
	        if (SQL_UPDATE_ALL_NOTIFY == SQL_UPDATE_ALL_DENYED) {
	            $this->msg[] = 'Modification of all data from assigned table is prohibted on Defined Settings';
	            return null;
	        }
	        if (SQL_UPDATE_ALL_NOTIFY == SQL_UPDATE_ALL_NOTIFY) {
	            $this->msg[] = 'Update statement was made without running condition,all data from your assigned table may be changed';
	        }
	    }
	}

	private function sqlDelete() {
	    $sql = sprintf(self::SQL_DELETE, $this->delete);
	    if (! is_null($this->where)) {
	        $sql = sprintf(self::SQL_WHERE, $sql, $this->sqlWhere());
	    }
	    if (! empty($this->limit)) {
	        $sql = sprintf(self::SQL_LIMIT, $sql, $this->sqlLimit());
	    }
	    return $sql;
	}

	private function deleteWarn() {
	    if (is_null($this->where)) {
	        if (SQL_DELETE_ALL_NOTIFY == SQL_DELETE_ALL_DENYED) {
	            $this->msg[] = 'Deletion of all data from assigned table is prohibted on Defined Settings';
	            return null;
	        }
	        if (SQL_DELETE_ALL_NOTIFY == SQL_DELETE_ALL_NOTIFY) {
	            $this->msg[] = 'Delete statement was made without running condition,all data from your assigned table may be deleted';
	        }
	    }
	}

	private function sqlWhere() {
	    $where_values = array_values($this->where);
	    $i = 0;
	    for ($i; $i<count($where_values); $i++) {
	        $condition_parts = $where_values[$i][0];
	        $where = $i == 0 ? $condition_parts : "{$where} {$condition_parts}";
	        if (isset($where_values[$i][1])) {
	            $conbination_parts = $where_values[$i][1];
	            $where = "{$where} {$conbination_parts}";
	        }
	    }
	    return $where;
	}

	private function sqlWhereJoin() {
	    $where_values = array_values($this->join_on);
	    $i = 0;
	    for ($i; $i<count($where_values); $i++) {
	        $condition_parts = $where_values[$i][0];
	        $where = $i == 0 ? $condition_parts : "{$where} {$condition_parts}";
	        if (isset($where_values[$i][1])) {
	            $conbination_parts = $where_values[$i][1];
	            $where = "{$where} {$conbination_parts}";
	        }
	    }
	    return $where;
	}

	private function sqlGroup() {
	    $group = [];
	    foreach ($this->group as $column) {
	        if (! is_single_word($column)) {
	            $this->msg[] = 'Incorrect group target column : ' . print_r($column);
	            continue;
	        }
	        $group[] = $column;
	    }
	    return implode(',', $group);
	}

	private function sqlOrder() {
	    $order = [];
	    foreach ($this->order as $column => $direction) {
	        if (! is_single_word($column)) {
	            $this->msg[] = 'Incorrect order column : ' . print_r($column);
	            continue;
	        }
	        if ($direction !== 'asc' && $direction !== 'desc' && $direction !== 'ASC' && $direction !== 'DESC') {
	            $this->msg[] = 'Incorrect sort direction : ' . print_r($direction);
	            continue;
	        }
	        $order[] = "{$column} {$direction}";
	    }
	    return implode(',', $order);
	}

	private function sqlLimit() {
	    $limit = '';
	    if (isset($this->limit[0])) {
	        $limit .= $this->limit[0];
	    }
	    if (! is_null($this->limit[1])) {
	        $limit .= ',' . $this->limit[1];
	    }
	    return $limit;
	}

	private function bindValuesBunch() {
	    if (! empty($this->bind_values_array)) {
	        $this->bind_values_array = [];
	    }
	    if (count($this->bind_values_insert) > 0) {
	        $this->bind_values_array = $this->bind_values_insert;
	        return;
	    }
	    if (count($this->bind_values_where) > 0) {
	        if ($this->bind_values_update) {
	            $this->bind_values_array = array_merge($this->bind_values_array, $this->bind_values_update);
	        }
	        if (count($this->bind_values_where) > 0) {
	            $this->bind_values_array = array_merge($this->bind_values_array, $this->bind_values_where);
	        }
	        if (count($this->bind_values_limit) > 0) {
	            $this->bind_values_array = array_merge($this->bind_values_array, $this->bind_values_limit);
	        }
	    }
	}

	private function resetStatements() {
	    $this->select = null;
	    $this->from = null;
	    $this->insert = null;
	    $this->into = null;
	    $this->values = null;
	    $this->update = null;
	    $this->set = null;
	    $this->delete = null;
	    $this->where = [];
	    $this->group = [];
	    $this->order = [];
	    $this->limit = [];
	    $this->bind_values = null;
	    $this->bind_values_insert = [];
	    $this->bind_values_update = [];
	    $this->bind_values_where = [];
	    $this->bind_values_limit = [];
	}

	public function isBindValues() {
	    if ($this->bind_values === true || $this->bind_values === false) {
	        return $this->bind_values;
	    }
	    return SQL_BIND_VALUES === true ? true : false;
	}
}
