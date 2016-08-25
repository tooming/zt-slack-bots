<?php
use \ZtSlack\Standup;
require_once __DIR__.'/../bootstrap.php';

header('Content-Type: application/json');

$Standup = new Standup;

parse_str(file_get_contents('php://input'), $request);

$Standup->setDbConnection($db);
$Standup->buildResponse($conf['tokens']['standup'], $request);

echo $Standup->response();

?>
