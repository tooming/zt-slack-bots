<?php
namespace ZtSlack;

date_default_timezone_set('UTC');

class TestBase extends \PHPUnit_Framework_TestCase {

	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);

	}
}
