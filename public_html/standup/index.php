<?php

define('DBHOST', 'localhost');
define('DBUSER', 'user');
define('DBPASS', 'pass');
define('DBNAME', 'slack');

// verification tokens from slack.com to make sure the request came from Slack
$tokens = [
	'token1',
	'token2'
];

error_reporting(0);
ini_set('display_errors', 0);

$db = new PDO(
    'mysql:host='.DBHOST.';dbname='.DBNAME.';charset=utf8mb4',
    DBUSER,
    DBPASS,
    array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => true
    )
);

$request = $_REQUEST;
if (!isset($request['token']) || !in_array($request['token'], $tokens)) die("nice try!");

$team_id = isset($request['team_id']) ? $request['team_id'] : '';
$channel_id = isset($request['channel_id']) ? $request['channel_id'] : '';
$channel_name = isset($request['channel_name']) ? $request['channel_name'] : '';
$user_id = isset($request['user_id']) ? $request['user_id'] : '';
$user_name = isset($request['user_name']) ? $request['user_name'] : '';
$text = isset($request['text']) ? $request['text'] : '';

$error_message = "Well, we didn't quite get that, @".$user_name.", try this: /standup help";

$data['response_type'] = 'in_channel';
$pieces = explode(" ", $text);
$action = isset($pieces[0]) ? $pieces[0] : false;
$data['text'] = '';
if($action == 'help') {
	$data['response_type'] = 'ephemeral';
	$data['text'] = 'For adding standup notes, use for example: "/standup notes"
For seeing status: "/standup status"';
} elseif($action == 'status') {
	$sql = "SELECT max(ID) as ID, `text`, user_name FROM standups
	WHERE team_id = ?
	AND channel_id = ?
	AND date = CURDATE()
	GROUP BY user_name
	ORDER BY ID DESC";
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_id));
	$result = $sth->fetchAll(PDO::FETCH_OBJ);
	if(count($result) > 0) {
		foreach($result as $user) {
			$data['text'] .= '*'.$user->user_name.'*: '.$user->text.'
';
		}
	} else {
		$data['text'] = 'All are lazy as hell! :sadpanda:';
	}

} elseif(trim($action) != '') {
	$sql = "DELETE FROM standups WHERE team_id = ?
			AND channel_id = ?
			AND user_id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_id, $user_id));

	$sql = 'INSERT INTO standups (team_id, channel_id, channel_name, user_id, user_name, `text`, `date`, added_time)
		VALUES (?, ?, ?, ?, ?, ?, CURDATE(), NOW())';
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_id, $channel_name, $user_id, $user_name, $text));
	$data['text'] = '@'.$user_name.' notes added!';
} else {
	$data['response_type'] = 'ephemeral';
	$data['text'] = $error_message;
}

header('Content-Type: application/json');
echo json_encode($data);
