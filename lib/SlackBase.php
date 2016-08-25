<?php
namespace ZtSlack;

class SlackBase {

	var $db;

	public function setDbConnection($db) {
		$this->db = $db;
	}

	/**
     * @codeCoverageIgnore
     */
	protected function executeSql($sql, $params) {
		$sth = $this->db->prepare($sql);
		$sth->execute($params);
		return $sth;
	}

	/**
     * @codeCoverageIgnore
     */
	protected function fetchSqlResult($sql, $params) {
		return $this->executeSql($sql, $params)->fetchAll();
	}
}
