<?php

if(file_exists(__DIR__.'/../config_override.php')) {
	require_once(__DIR__.'/../config_override.php');
} else {
	require_once(__DIR__.'/../config.php');
}

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
$action = isset($pieces[0]) ? trim($pieces[0]) : false;
$data['text'] = '';
if($action == 'help') {
	$data['response_type'] = 'ephemeral';
	$data['text'] = 'For adding standup notes, use for example: "/standup notes"
For seeing status: "/standup status"
For adding team members: "/standup add name1 name2"
For removing team members: "/standup remove name1 name2"
For seeing member list: "/standup members"
For setting status to away with reason "on vacation": "/standup away on vacation"
For setting status to back online: "/standup online"';
} elseif($action == 'status') {
	$sql = "SELECT sm.user_name as sm_user_name, s.user_name as s_user_name, status, text, s.date as s_date
			FROM standup_members sm 
			LEFT JOIN standups s
			ON s.user_name = sm.user_name
			AND s.channel_id = sm.channel_id
			WHERE 
				(sm.channel_id = ?
				OR s.channel_id = ?)
		UNION
			SELECT sm.user_name as sm_user_name, s.user_name as s_user_name, status, text, s.date as s_date
			FROM standup_members sm 
			RIGHT JOIN standups s
			ON s.user_name = sm.user_name
			AND s.channel_id = sm.channel_id
			WHERE 
				(sm.channel_id = ?
				OR s.channel_id = ?)
				AND s.date = CURDATE()

";
	$sth = $db->prepare($sql);
	$sth->execute([$channel_id, $channel_id, $channel_id, $channel_id]);
	$result = $sth->fetchAll(PDO::FETCH_ASSOC);
	if(count($result) > 0) {
		foreach($result as $user) {
			$user_name = ($user['sm_user_name'])?$user['sm_user_name']:$user['s_user_name'];
			if($user['status']) {
				$data['text'] .= '*'.$user_name.'*: '.$user['status'].'
';
			} elseif($user['text'] && $user['s_date']==date('Y-m-d')) {
				$data['text'] .= '*'.$user_name.'*: '.$user['text'].'
';
			} else {
				$data['text'] .= '*'.$user_name.'*: :sadpanda:
';
			}
		}
	} else {
		$data['text'] = 'All are lazy as hell! :sadpanda:';
	}

} elseif($action == 'add') {
	$member_string = trim(str_replace("@", "", str_replace("add", "", $text)));
	$members = explode(" ", $member_string);
	foreach($members as $member) {
		$sql = 'INSERT INTO standup_members (channel_id, channel_name, user_name, added_time)
			VALUES (?, ?, ?, NOW())';
		$sth = $db->prepare($sql);
		$sth->execute(array($channel_id, $channel_name, $member));
	}
	$data['text'] = $user_name.' added to this team: '. $member_string;
} elseif($action == 'remove') {
	$member_string = trim(str_replace("@", "", str_replace("remove", "", $text)));
	$members = explode(" ", $member_string);
	foreach($members as $member) {
		$sql = 'DELETE FROM standup_members WHERE channel_id = ? AND user_name = ?';
		$sth = $db->prepare($sql);
		$sth->execute(array($channel_id, $member));
	}
	$data['text'] = $user_name.' removed from this team: '. $member_string;
} elseif($action == 'members') {
	$sql = "SELECT user_name, status FROM standup_members
	WHERE channel_id = ?
	GROUP BY user_name";
	$sth = $db->prepare($sql);
	$sth->execute([$channel_id]);
	$result = $sth->fetchAll(PDO::FETCH_OBJ);
	if(count($result) > 0) {
		foreach($result as $user) {
			$data['text'] .= '*'.$user->user_name.'*'.(($user->status)?': '.$user->status:'').'
';
		}
	} else {
		$data['text'] = 'No members in the team yet! :sadpanda:';
	}
} elseif($action == 'away') {
	$reason = trim(str_replace("away", "", $text));
	$sql = "INSERT INTO standup_members (status, channel_id, channel_name, user_name, added_time) VALUES (?, ?, ?, ?, NOW())
		ON DUPLICATE KEY UPDATE status=?, channel_id=?, user_name=?, added_time=NOW()";
	$sth = $db->prepare($sql);
	$sth->execute([$reason, $channel_id, $channel_name, $user_name, $reason, $channel_id, $user_name]);
	$data['text'] = $user_name .' is now away because: ' . $reason;
} elseif($action == 'online') {
	$sql = "INSERT INTO standup_members (status, channel_id, channel_name, user_name, added_time) VALUES (NULL, ?, ?, ?, NOW())
		ON DUPLICATE KEY UPDATE status=NULL, channel_id=?, user_name=?, added_time=NOW()";
	$sth = $db->prepare($sql);
	$sth->execute([$channel_id, $channel_name, $user_name, $channel_id, $user_name]);
	$data['text'] = $user_name .' is now online';
} elseif($action != '') {
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

?>
