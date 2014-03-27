<?php
class DB {
	private $mysqli;
	function __construct() {
		$this->mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
		if($this->mysqli->connect_error) {
			echo "<!-- {$this->mysqli->connect_error} -->";
		}
	}

	/**
	 * Fetch rows from the database
	 *
	 * @param string $table The name of the table to query
	 * @param array $cols An array of columns to return
	 * @param array $where An array of WHERE conditions to be added to the query
	 * @param array $order An array specifying the column and direction to sort
	 * @param integer $limit An integer indicating the maximum number of rows to return
	 *
	 * @return array An array of objects with the specified columns
	 */
	public function get($table, $cols, $where, $order = [], $limit = NULL) {
		$table = '`' . $this->escape($table) . '`';
		$cols = $this->prepare_cols($cols);
		$where_text = $order_text = '';
		$where_conditions = [];
		if(!empty($where)) {
			foreach($where as $col => $val) {
				$where_conditions[] = '`' . $this->escape($col) . '` = ' . (is_numeric($val) ? $val : "'" . $this->escape($val) . "'");
			}
			$where_text = 'WHERE (' . implode(') AND (', $where_conditions) . ')';
		}
		if(!empty($order)) {
			$order_text = 'ORDER BY `' . $this->escape(key($order)) . '` ' . ($order == 'ASC' ? 'ASC' : 'DESC');
		}
		$limit = is_numeric($limit) ? "LIMIT $limit" : NULL;
		
		$result = $this->mysqli->query('SELECT ' . implode(',', $cols) . " FROM $table $where_text $order_text $limit;");
		$return = [];
		while($obj = $result->fetch_object()) {
			$return[] = $obj;
		}
		return $return;
	}

	/**
	 * Fetch one row from the database
	 *
	 * @param string $table The name of the table to query
	 * @param array $cols An array of columns to return
	 * @param array $where An array of WHERE conditions to be added to the query
	 * @param array $order An array specifying the column and direction to sort
	 *
	 * @return object An object with the specified columns
	 */
	public function get_one($table, $cols, $where, $order = []) {
		$table = '`' . $this->escape($table) . '`';
		$cols = $this->prepare_cols($cols);
		$where_text = $order_text = '';
		$where_conditions = [];
		if(!empty($where)) {
			foreach($where as $col => $val) {
				$where_conditions[] = '`' . $this->escape($col) . '` = ' . (is_numeric($val) ? $val : "'" . $this->escape($val) . "'");
			}
			$where_text = 'WHERE (' . implode(') AND (', $where_conditions) . ')';
		}
		if(!empty($order)) {
			$order_text = 'ORDER BY `' . $this->escape(key($order)) . '` ' . ($order == 'ASC' ? 'ASC' : 'DESC');
		}
		$limit = is_numeric($limit) ? "LIMIT $limit" : NULL;
		
		$result = $this->mysqli->query('SELECT ' . implode(',', $cols) . " FROM $table $where_text $order_text LIMIT 1;");
		return $result->fetch_object();
	}

	/**
	 * Insert a row in to the database
	 *
	 * @param string $table The name of the table to query
	 * @param array $cols An array of columns representing the $values
	 * @param array $values An array data to be inserted represented by $cols
	 *
	 * @return boolean True on success; false on failure
	 */
	public function insert($table, $cols, $values) {
		$table = '`' . $this->escape($table) . '`';
		$cols = $this->prepare_cols($cols);
		$values = $this->prepare_values($values);

		return $this->mysqli->query("INSERT INTO $table (" . implode(',', $cols) . ') VALUES (' . implode(',', $values) . ');');
	}

	/**
	 * Insert a row in to the database without throwing an error on a duplicate key
	 *
	 * @param string $table The name of the table to query
	 * @param array $cols An array of columns representing the $values
	 * @param array $values An array data to be inserted represented by $cols
	 *
	 * @return boolean True on success; false on failure
	 */
	public function insert_ignore($table, $cols, $values) {
		$table = '`' . $this->escape($table) . '`';
		$cols = $this->prepare_cols($cols);
		$values = $this->prepare_values($values);

		return $this->mysqli->query("INSERT IGNORE INTO $table (" . implode(',', $cols) . ') VALUES (' . implode(',', $values) . ');');
	}

	/**
	 * Delete rows from the database
	 *
	 * @param string $table The name of the table to query
	 * @param array $where An array of WHERE conditions to be added to the query
	 * @param integer $limit An integer indicating the maximum number of rows to return
	 *
	 * @return boolean True on success; false on failure
	 */
	public function delete($table, $where, $limit = NULL) {
		$table = '`' . $this->escape($table) . '`';
		$where_text = '';
		$where_conditions = [];
		foreach($where as $col => $val) {
			$where_conditions[] = '`' . $this->escape($col) . '` = ' . (is_numeric($val) ? $val : "'" . $this->escape($val) . "'");
		}
		$where_text = 'WHERE ' . implode(' AND ', $where_conditions);

		$limit = is_numeric($limit) ? "LIMIT $limit" : NULL;
		
		return $this->mysqli->query("DELETE FROM $table $where_text $limit;");
	}

	/**
	 * Escapes special characters in a string for use in an SQL statement
	 *
	 * @param string $string The string to escape
	 *
	 * @return string The escaped string
	 */
	public function escape($string) {
		return $this->mysqli->real_escape_string($string);
	}

	function __destruct() {
		if(method_exists($this, 'close'))
			$this->close();
	}

	private function prepare_cols($cols) {
		return array_map(function($val) {
			return '`' . $this->escape($val) . '`';
		}, $cols);
	}

	private function prepare_values($values) {
		return array_map(function($val) {
			return is_numeric($val) ? $val : "'" . $this->escape($val) . "'";
		}, $values);
	}
}