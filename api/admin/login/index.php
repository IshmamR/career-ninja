<?php

require_once(__DIR__ . '/../../../controllers/admin_controller.php');

// Allow from any origin
// if (isset($_SERVER['HTTP_ORIGIN'])) {
//   header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
//   header('Access-Control-Allow-Credentials: true');
//   header('Access-Control-Max-Age: 86400');    // cache for 1 day
// }

// // Access-Control headers are received during OPTIONS requests
// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

//   if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
//     header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

//   if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
//     header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

//   exit(0);
// }

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  adminTestFunc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(200);
  echo json_encode(["REQUEST_METHOD" => "POST"], JSON_UNESCAPED_SLASHES);
  exit;
  // loginAdmin();
}
