<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once(__DIR__ . '/../../../db/Database.php');
require_once(__DIR__ . '/../../../models/Admin.php');

$database = new Database();
$conn = $database->connect();

$admin = new Admin($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $admin->logout();

  http_response_code(200);
  echo json_encode(["message" => "logged out"], JSON_UNESCAPED_SLASHES);
  exit;
}
