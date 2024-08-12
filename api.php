<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'database.php';
include_once 'class.php';

$database = new Database();
$db = $database->getConnection();

$item = new Nodemcu_log($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $item->suhu = $data->suhu;
    $item->suhu2 = $data->suhu2;
    $item->tds = $data->tds;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $item->suhu = isset($_GET['suhu']) ? $_GET['suhu'] : die('wrong structure!');
    $item->suhu2 = isset($_GET['suhu2']) ? $_GET['suhu2'] : die('wrong structure!');
    $item->tds = isset($_GET['tds']) ? $_GET['tds'] : die('wrong structure!');
} else {
    die('wrong request method');
}

if ($item->createLogData()) {
    echo 'Data created successfully.';
} else {
    echo 'Data could not be created.';
}
