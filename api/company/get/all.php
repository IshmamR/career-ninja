<?php

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once(__DIR__ . '/../../../db/Database.php');
require_once(__DIR__ . '/../../../models/Company.php');

$database = new Database();
$conn = $database->connect();

$company = new Company($conn);

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $pageNo = $_GET['pageNo'];
  $limit = $_GET['limit'];

  if (!isset($pageNo)) {
    $pageNo = 1;
  }
  if (!isset($limit)) {
    $limit = 5;
  }

  $data = $company->readCompanies($pageNo, $limit);

  if (!$data) {
    http_response_code(404);
    echo json_encode(["message" => "could not fetch companies"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  http_response_code(200);
  echo json_encode($data, JSON_UNESCAPED_SLASHES);
  exit;
}
