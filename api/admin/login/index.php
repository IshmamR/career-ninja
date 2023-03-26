<?php

require_once(__DIR__ . '/../../../controllers/admin_controller.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  adminTestFunc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  loginAdmin();
}
