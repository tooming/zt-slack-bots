<?php
namespace ZtSlack;

class Standup extends SlackBase {

	public $data;

	public function buildResponse($tokens, $request) {

		if (!isset($request['token']) || !in_array($request['token'], $tokens)) die("nice try!");

		$team_id = isset($request['team_id']) ? $request['team_id'] : '';
		$channel_id = isset($request['channel_id']) ? $request['channel_id'] : '';
		$channel_name = isset($request['channel_name']) ? $request['channel_name'] : '';
		$user_id = isset($request['user_id']) ? $request['user_id'] : '';
		$user_name = isset($request['user_name']) ? $request['user_name'] : '';
		$text = isset($request['text']) ? $request['text'] : '';

		$error_message = "Well, we didn't quite get that, @".$user_name.", try this: /standup help";

		$this->data['response_type'] = 'in_channel';
		$pieces = explode(" ", $text);
		$action = isset($pieces[0]) ? $pieces[0] : false;
		$this->data['text'] = '';

		if($action == 'help') {
			$this->data['response_type'] = 'ephemeral';
			$this->data['text'] = 'For adding standup notes, use for example: "/standup notes"
For seeing status: "/standup status"';
		} elseif($action == 'status') {
			$result = $this->getStandupStatus($team_id, $channel_id);
			if(count($result) > 0) {
				foreach($result as $user) {
					$this->data['text'] .= '*'.$user['user_name'].'*: '.$user['text'].'
';
				}
			} else {
				$this->data['text'] = 'All are lazy as hell! :sadpanda:';
			}

		} elseif(trim($action) != '') {
			$this->deleteUserNotes($team_id, $channel_id, $user_id);
			$this->insertUserNotes($team_id, $channel_id, $channel_name, $user_id, $user_name, $text);
			$this->data['text'] = '@'.$user_name.' notes added!';
		} else {
			$this->data['response_type'] = 'ephemeral';
			$this->data['text'] = $error_message;
		}
	}

	public function response() {
		return json_encode($this->data);
	}
	public function getStandupStatusSql() {
		return "SELECT max(ID) as ID, `text`, user_name FROM standups
		WHERE team_id = ?
		AND channel_id = ?
		AND date = CURDATE()
		GROUP BY user_name
		ORDER BY ID DESC";
	}

	public function deleteUserNotesSql() {
		return "DELETE FROM standups WHERE team_id = ?
				AND channel_id = ?
				AND user_id = ?";
	}

	public function insertUserNotesSql() {
		return "INSERT INTO standups (team_id, channel_id, channel_name, user_id, user_name, `text`, `date`, added_time)
				VALUES (?, ?, ?, ?, ?, ?, CURDATE(), NOW())";
	}

	public function getStandupStatus($team_id, $channel_id) {
		return $this->fetchSqlResult($this->getStandupStatusSql(), [$team_id, $channel_id]);
	}

	public function deleteUserNotes($team_id, $channel_id, $user_id) {
		$this->executeSql($this->deleteUserNotesSql(), [$team_id, $channel_id, $user_id]);
	}

	public function insertUserNotes($team_id, $channel_id, $channel_name, $user_id, $user_name, $text) {
		$this->executeSql($this->insertUserNotesSql(), [$team_id, $channel_id, $channel_name, $user_id, $user_name, $text]);
	}
}
