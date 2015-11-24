<?php

$_config = [
    'pidFilePath' => __DIR__ . '/run'
];

class MyDb
{
    private $conn;
    private static $instance;

    public static function getInstance()
    {
        if (empty(MyDb::$instance)) {
            MyDb::$instance = new MyDb();
        }
        return MyDb::$instance;
    }

    protected function __construct()
    {
        $this->conn = new mysqli('localhost', 'wifi-ping', getenv('MYSQL_PASSWORD'), 'wifi-ping');
    }

    public function query($q)
    {
        if (!($res = $this->conn->query($q))) {
            throw new Exception('MySQL query failed: ' . $this->conn->error);
        }
        return $res;
    }

    public function fetchOne($q)
    {
        $res = $this->query($q);
        return $res->fetch_array();
    }
    
    public function fetchCol($q)
    {
        $res = $this->query($q);
        $return = [];
        while ($row = $res->fetch_array()) {
            $return[] = $row[0];
        }
        return $return;
    }
}
