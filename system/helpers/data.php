<?php

namespace Data;

use mysqli;

class DataHelper
{
	protected $_db;
	protected $_connected = false;
	protected $_error = "";

	public function __construct()
	{
		$this->_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

		if ($this->_db->connect_errno) {
			$this->_connected = false;
			$this->_error = $this->_db->connect_error;
		} else {
			$this->_connected = true;
		}

		$this->_db->set_charset(DB_CHARSET);
	}

	public function __destruct()
	{
		if ($this->_connected) {
			if ($this->_db != null) {
				$this->_db->close();
			}
		}
	}

	public function Bye()
	{
		if ($this->_connected) {
			if ($this->_db != null) {
				$this->_db->close();
			}
		}
	}

	public function isConnected()
	{
		return $this->_connected;
	}

	public function getErrorMessage()
	{
		return $this->_error;
	}

	public function SQLIn($value)
	{
		$value = strtolower($value);
		$array = array(" union ", " select ", " and ", " or ", " order ", "(", ")", " where ", " having ", "--");
		foreach ($array as $token) {
			if (stristr($value, $token) !== FALSE) {
				return true;
			}
		}
		return false;
	}

	public function executeResultQuery($query, $params)
	{
		$stmt = $this->_db->prepare($query);
		if ($params != null) {
			$tmp = array();
			foreach ($params as $key => $value)
				$tmp[$key] = &$params[$key];

			call_user_func_array(array($stmt, 'bind_param'), $tmp);
		}

		$this->_db->query("SET NAMES utf8mb4");
		$stmt->execute();

		if ($this->isConnected()) {
			return $stmt->get_result();
		} else {
			return null;
		}
	}

	public function executeNonQuery($query, $params)
	{
		$stmt = $this->_db->prepare($query);
		if ($params != null) {
			$tmp = array();
			foreach ($params as $key => $value)
				$tmp[$key] = &$params[$key];

			call_user_func_array(array($stmt, 'bind_param'), $tmp);
		}

		$this->_db->query("SET NAMES utf8mb4");
		$stmt->execute();

		if ($this->isConnected()) {
			return $stmt->affected_rows;
		} else {
			return -1;
		}
	}

	public function executeInsertQuery($query, $params)
	{
		ini_set('mysql.connect_timeout', 300);
		ini_set('default_socket_timeout', 300);
		ini_set('max_execution_time', 0);
		//set_time_limit(0);
		$stmt = $this->_db->prepare($query);

		if ($params != null) {
			$tmp = array();

			foreach ($params as $key => $value)
				$tmp[$key] = &$params[$key];

			call_user_func_array(array($stmt, 'bind_param'), $tmp);
		}

		$this->_db->query("SET NAMES utf8mb4");
		$stmt->execute();

		if ($this->isConnected()) {
			return $stmt->affected_rows;
		} else {
			return -1;
		}
	}
}
