<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");

require_once(__DIR__ . '/../../../controllers/admin_controller.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  loginAdmin();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  adminTestFunc();
}
