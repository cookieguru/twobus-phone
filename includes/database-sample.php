<?php
class DB {
	function __construct() {
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
		return [];
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
		return new StdClass;
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
		return true;
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
		return true;
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
		return true;
	}

	/**
	 * Escapes special characters in a string for use in an SQL statement
	 *
	 * @param string $string The string to escape
	 *
	 * @return string The escaped string
	 */
	public function escape($string) {
		return $string;
	}

	function __destruct() {
	}

	private function prepare_cols($cols) {
		return $cols;
	}

	private function prepare_values($values) {
		return $values;
	}
}