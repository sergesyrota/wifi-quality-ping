<?php
require_once __DIR__ . '/include.php';
$db = MyDb::getInstance();

$hostId = (empty($_REQUEST['host']) ? 1 : (int)$_REQUEST['host']);
$timeStart = date('Y-m-d H:i:s', (empty($_REQUEST['time']) ? strtotime('-24 hours') : max(strtotime('-7 days'), strtotime($_REQUEST['time']))));

$q = "SELECT * from hosts WHERE id = $hostId";
$hostData = $db->fetchOne($q);
$data = [
    'host' => sprintf('%s (%s)', $hostData['description'], $hostData['hostname'])
];

$q = "SELECT time, avg, perc80, perc90, percent_timeout*10 as percent_timeout FROM `ping-summary` WHERE host_id = $hostId AND time >= '$timeStart'";
$res = $db->query($q);
while ($row = $res->fetch_assoc()) {
    foreach ($row as $k=>$v) {
        $data[$k][] = ($k == 'time' ? $v : (int)$v);
    }
}
echo json_encode($data);
