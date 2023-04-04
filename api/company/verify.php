<?php

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once(__DIR__ . '/../../db/Database.php');
require_once(__DIR__ . '/../../models/Company.php');

$database = new Database();
$conn = $database->connect();

$company = new Company($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // receive posted json body
  $data = json_decode(file_get_contents('php://input'), true);

  if (empty($data['companyId'])) {
    http_response_code(400);
    echo json_encode(["message" => "Company id is required"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $company->id = $data['companyId'];

  if (!$company->verifyCompany()) {
    http_response_code(400);
    echo json_encode(["message" => "Could not verify company"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  http_response_code(200);
  echo json_encode(['message' => "Company verified"], JSON_UNESCAPED_SLASHES);
  exit;
}
