<?php

require_once(__DIR__ . '/../../../controllers/admin_controller.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  logoutAdmin();
}
