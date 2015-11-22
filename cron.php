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
 * TODO: Consolidate every minute for ease of charting, and cleanup old data
 */

