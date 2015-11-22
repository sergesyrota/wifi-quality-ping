<?php

if (empty($argv[1]) || (int)$argv[1] == 0) {
    die("USAGE: ping-host.php [host-id]\n");
}

require_once __DIR__ . '/include.php';
$db = MyDb::getInstance();

$hostData = $db->fetchOne('SELECT * FROM hosts WHERE id = ' . (int)$argv[1]);

if (empty($hostData)) {
    die("Host definition does not exist\n");
}

$pidFile = $_config['pidFilePath'] . '/' . $hostData['id'];
file_put_contents($pidFile, getmypid());
register_shutdown_function(function($file){
    unlink($file);
}, $pidFile);

$start = time();
// Endless loop of pings
while (true) {
    $res = `ping -c 1 -q -W 1 {$hostData['hostname']}`;
    preg_match('%\s([0-9]+) received%', $res, $matchPackets);
    preg_match('%rtt min/avg/max/mdev = ([0-9\.]+)%', $res, $matchTime);
    // Check if we received any packets back, to determine timeout
    if ($matchPackets[1] == 0 || (float)$matchTime[1] == 0) {
        $timeMs = null;
    } else {
        $timeMs = $matchTime[1];
    }
    $q = "INSERT INTO `ping-result` (host_id, time, ping_ms) VALUES ({$hostData['id']}, NOW(), ".($timeMs === null ? 'NULL' : $timeMs).")";
    $db->query($q);
    // Limit how long one will run, just in case we have runaway processes; Also shutdown at the end of the minute for cron to restart new process right away;
    if ((time() - $start) > 3600 && (int)date('s') > 45 && (int)date('s') < 55) {
        exit();
    }
    sleep(1);
}
