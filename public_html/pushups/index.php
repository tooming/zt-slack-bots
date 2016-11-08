<?php

define('DBHOST', 'localhost');
define('DBUSER', 'user');
define('DBPASS', 'pass');
define('DBNAME', 'slack');

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
$channel_name = isset($request['channel_name']) ? $request['channel_name'] : '';
$user_id = isset($request['user_id']) ? $request['user_id'] : '';
$user_name = isset($request['user_name']) ? $request['user_name'] : '';
$text = isset($request['text']) ? $request['text'] : '';

$error_message = "Well, we didn't quite get that, @".$user_name.", try this: /pushups help";

$data['response_type'] = 'in_channel';
$pieces = explode(" ", $text);
$action = isset($pieces[0]) ? $pieces[0] : false;
$data['text'] = '';
if($action == 'help') {
	$data['response_type'] = 'ephemeral';
	$data['text'] = 'For setting the target, use "/pushups set 20"
For doing pushups, use for example: "/pushups 20"
For seeing status: "/pushups status"';
} elseif($action == 'set') {
	if(isset($pieces[1]) && $pieces[1] > 0) {
		$sql = "DELETE FROM pushups WHERE team_id = ?
			AND channel_name = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($team_id, $channel_name));
		$sql = "DELETE FROM pushup_targets WHERE team_id = ?
			AND channel_name = ?";
		$sth = $db->prepare($sql);
		$sth->execute(array($team_id, $channel_name));

		$target = isset($pieces[1]) ? $pieces[1] : '';
		$data['text'] = '@'.$user_name.' set the target to '.$target.' pushup'.(($target > 1)?'s':'').', good luck!';

		$sql = 'INSERT INTO pushup_targets (team_id, channel_name, target, added_time)
			VALUES (?, ?, ?, NOW())';
		$sth = $db->prepare($sql);
		$sth->execute(array($team_id, $channel_name, $target));
	} else {
		$data['text'] = $error_message;
	}
} elseif($action == 'status') {
	$sql = "SELECT target FROM pushup_targets
	WHERE team_id = ?
	AND channel_name = ?
	ORDER BY ID DESC";
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_name));
	$result = $sth->fetchAll(PDO::FETCH_OBJ);
	if($result) {
		$target = $result[0]->target;
	} else {
		$target = 'infinity';
	}

	$sql = "SELECT SUM(count) as done, user_name FROM pushups
	WHERE team_id = ?
	AND channel_name = ?
	GROUP BY user_name";
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_name));
	$result = $sth->fetchAll(PDO::FETCH_OBJ);
	if(count($result) > 0) {
		foreach($result as $user) {
			$data['text'] .= '*'.$user->user_name.'*: '.$user->done.'/'.$target.'
';
		}
	} else {
		$data['text'] = 'All are lazy as hell! :sadpanda:';
	}

} elseif($action > 0) {
	$sql = "SELECT target FROM pushup_targets
	WHERE team_id = ?
	AND channel_name = ?
	ORDER BY ID DESC";
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_name));
	$result = $sth->fetchAll(PDO::FETCH_OBJ);
	if($result) {
		$target = $result[0]->target;
	} else {
		$target = 'infinity';
	}
	$sql = "SELECT SUM(count) as previous FROM pushups
	WHERE team_id = ?
	AND channel_name = ?
	AND user_id = ?";
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_name, $user_id));
	$result = $sth->fetchAll(PDO::FETCH_OBJ);
	if($result) {
		$previous = $result[0]->previous;
	} else {
		$previous = '0';
	}
	$done = $text + $previous;

	$chuck_norris = ($text > 80) ? ', Chuck Norris' : '';

	$data['text'] = '@'.$user_name.' did '.$text.' pushup'.(($text > 1)?'s':'').' ('.$done.'/'.$target.'), good job'.$chuck_norris.'!';
	$sql = 'INSERT INTO pushups (team_id, channel_name, user_id, user_name, count, added_time)
		VALUES (?, ?, ?, ?, ?, NOW())';
	$sth = $db->prepare($sql);
	$sth->execute(array($team_id, $channel_name, $user_id, $user_name, $text));
} else {
	$data['response_type'] = 'ephemeral';
	$data['text'] = $error_message;
}

header('Content-Type: application/json');
echo json_encode($data);
