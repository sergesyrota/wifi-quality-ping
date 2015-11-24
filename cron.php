<?php

require_once __DIR__ . '/include.php';
$db = MyDb::getInstance();

/**
 * Make sure all individual pings are running for all hosts
 */

$res = $db->query('SELECT * FROM hosts');

while ($hostData = $res->fetch_array()) {
    $pidFile = $_config['pidFilePath'] . '/' . $hostData['id'];;

    // Check if we have fresh data for host
    $dataCheck = $db->fetchOne("SELECT time FROM `ping-result` WHERE host_id = {$hostData['id']} ORDER BY id DESC LIMIT 1");
    if ((time() - strtotime($dataCheck['time'])) < 5) {
        // Fresh data, no need to do anything. Next host please!
        continue;
    }

    // Need to kill existing process, as it might have hung, or just cleanup PID file
    if (file_exists($pidFile) && file_exists( "/proc/" . file_get_contents($pidFile))) {
        $pid = file_get_contents($pidFile);
        `kill -9 {$pid}`;
    }
    if (file_exists($pidFile)) {
        unlink($pidFile);
    }

    // Starting new thread for ping for this host
    $runFile = "nohup php " . __DIR__ . "/ping-host.php {$hostData['id']} > /dev/null 2> /dev/null </dev/null & ";
    `{$runFile}`;
}

/**
 * Consolidate every minute for ease of charting, and cleanup old data
 */

$res->data_seek(0);
// End time - previous minute; Same for all hosts
$consolidatedEndTime = date('Y-m-d H:i:s', time()-59);
while ($hostData = $res->fetch_array()) {
    // Need to find last minute entered, and that's time after which we want to start working on consolidation
    $lastEntry = $db->fetchOne("SELECT DATE_FORMAT(time, '%Y-%m-%d %H:%i') as `last_time` FROM `ping-summary` WHERE host_id = {$hostData['id']} ORDER BY id DESC LIMIT 1");
    $consolidateStartTime = '2000-01-01 00:00';
    if (!empty($lastEntry)) {
        $consolidateStartTime = $lastEntry['last_time'];
    }
    $q = "SELECT DATE_FORMAT(time, '%Y-%m-%d %H:%i') `minute` FROM `ping-result` WHERE host_id = {$hostData['id']} AND time > '{$consolidateStartTime}:59' AND time < '{$consolidatedEndTime}' GROUP BY DATE_FORMAT(time, '%Y-%m-%d %H:%i') ORDER BY id ASC";
    $minutes = $db->query($q);
    while ($minute = $minutes->fetch_array()) {
//        echo "\rWorking on {$hostData['description']} ({$hostData['id']}) at {$minute[0]}...";
        $q = "SELECT IFNULL(ping_ms, 1000) FROM `ping-result` WHERE host_id = {$hostData['id']} AND time BETWEEN '{$minute[0]}:00' AND '{$minute[0]}:59'";
        $ping = $db->fetchCol($q);
        sort($ping);
        $q = "INSERT INTO `ping-summary` (time, host_id, min, max, avg, perc50, perc80, perc90, perc95, perc98, percent_timeout) VALUES
        ('{$minute[0]}:00', {$hostData['id']}, ".min($ping).", ".max($ping).", ".round(array_sum($ping)/count($ping), 1).", ".perc($ping, 50).", ".perc($ping, 80).", ".perc($ping, 90).", ".perc($ping, 95).", ".perc($ping, 98).", ".getTimeoutPercentage($ping).")";
        $db->query($q);
        $q = "DELETE FROM `ping-result` WHERE host_id = {$hostData['id']} AND time BETWEEN '{$minute[0]}:00' AND '{$minute[0]}:59'";
        $db->query($q);
    }
//    echo "\n";
}

function perc($sortedArray, $percentile)
{
    $idx = ceil($percentile*count($sortedArray)/100) - 1;
    return $sortedArray[$idx];
}

function getTimeoutPercentage($ping)
{
    $values = array_count_values($ping);
    if (empty($values['1000.000'])) {
        return 0;
    }
    return round(100*$values['1000.000']/count($ping));
}