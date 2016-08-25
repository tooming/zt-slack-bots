<?php
use ZtSlack\SlackBase;

/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/
class SlackBaseTest extends ZtSlack\TestBase {

	public function testSetDbConnection() {
		$SlackBase = new SlackBase;
		$db = true;
		$SlackBase->setDbConnection($db);
		$this->assertEquals(true, $SlackBase->db);
	}
}
