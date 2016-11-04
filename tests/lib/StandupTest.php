<?php
use ZtSlack\Standup;

/**
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/
class StandupTest extends ZtSlack\TestBase {
	var $tokens = ['TEST_TOKEN'];

	public function testResponseToEmptyRequest() {
		$Standup = new Standup;
		$request = ['token' => 'TEST_TOKEN', 'user_name' => 'TEST_USER'];
		$Standup->buildResponse($this->tokens, $request);
		$expected = ['response_type' => 'ephemeral',
		'text' => "Well, we didn't quite get that, @TEST_USER, try this: /standup help"];
		$this->assertEquals($expected, $Standup->data);
	}

	public function testResponseToHelpRequest() {
		$Standup = new Standup;
		$request = ['token' => 'TEST_TOKEN', 'user_name' => 'TEST_USER', 'text' => 'help'];
		$Standup->buildResponse($this->tokens, $request);
		$expected = ['response_type' => 'ephemeral',
		'text' => 'For adding standup notes, use for example: "/standup notes"
For seeing status: "/standup status"'];
		$this->assertEquals($expected, $Standup->data);
	}

	public function testResponseToNotesRequest() {
		$StandupStub = $this->getMockBuilder('ZtSlack\Standup')
					 ->setMethods(array('executeSql'))
                     ->getMock();

        // Configure the exporterStub.
        $StandupStub->method('executeSql')->willReturn(null);

		$request = ['token' => 'TEST_TOKEN', 'user_name' => 'TEST_USER', 'text' => 'notes'];
		$StandupStub->buildResponse($this->tokens, $request);
		$expected = ['response_type' => 'in_channel',
		'text' => "@TEST_USER notes added!"];
		$this->assertEquals($expected, $StandupStub->data);
	}

	public function testResponseToEmptyStatusRequest() {
		$StandupStub = $this->getMockBuilder('ZtSlack\Standup')
					 ->setMethods(array(
					 	'fetchSqlResult'))
                     ->getMock();

        // Configure the exporterStub.
        $StandupStub->method('fetchSqlResult')->willReturn(null);

		$request = ['token' => 'TEST_TOKEN', 'user_name' => 'TEST_USER_NAME', 'user_id' => 'TEST_USER_ID', 'text' => 'status'];
		$StandupStub->buildResponse($this->tokens, $request);
		$expected = ['response_type' => 'in_channel',
		'text' => "All are lazy as hell! :sadpanda:"];
		$this->assertEquals($expected, $StandupStub->data);
	}

	public function testResponseToStatusRequest() {
		$StandupStub = $this->getMockBuilder('ZtSlack\Standup')
					 ->setMethods(array('executeSql', 'getStandupStatus'))
                     ->getMock();

        $statusConfigMap = array(
          array('TEST_TEAM_ID', 'TEST_CHANNEL_NAME',
          	[0 => ['user_name' => 'martin', 'text' => 'my notes'],
          	1 => ['user_name' => 'karolin', 'text' => 'my notes too']])
        );

        // Configure the exporterStub.
        $StandupStub->method('executeSql')->willReturn(null);
        $StandupStub->method('getStandupStatus')->will($this->returnValueMap($statusConfigMap));

		$request = ['token' => 'TEST_TOKEN', 'team_id' => 'TEST_TEAM_ID', 'channel_id' => 'TEST_CHANNEL_ID', 
		'channel_name' => 'TEST_CHANNEL_NAME', 'user_name' => 'TEST_USER_NAME', 'user_id' => 'TEST_USER_ID', 'text' => 'status'];
		$StandupStub->buildResponse($this->tokens, $request);
		$expected = ['response_type' => 'in_channel',
		'text' => "*martin*: my notes
*karolin*: my notes too
"];
		$this->assertEquals($expected, $StandupStub->data);
	}

	public function testResponse() {
		$Standup = new Standup;
		$Standup->data = ['response' => '1'];
		$expected = json_encode(['response' => '1']);
		$this->assertEquals($expected, $Standup->response());
	}
}
