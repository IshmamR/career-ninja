<?php

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
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
  // receive posted json body
  $data = json_decode(file_get_contents('php://input'), true);

  if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "username and password are required"], JSON_UNESCAPED_SLASHES);
    exit;
  }


  $admin->username = $data['username'];

  if (!$admin->readSingle()) {
    http_response_code(404);
    echo json_encode(["message" => "username or password did not match"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  echo json_encode(["message" => "got here"], JSON_UNESCAPED_SLASHES);
  exit;

  if (!$admin->verifyPassword($data['password'])) {
    http_response_code(404);
    echo json_encode(["message" => "username and password did not match"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $authAdmin = $admin->setLoginCookies();

  http_response_code(200);
  echo $authAdmin;
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  adminTestFunc();
}
