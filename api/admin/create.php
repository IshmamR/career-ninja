<?php

require_once(__DIR__ . '/../../controllers/admin_controller.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  adminTestFunc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  createAdmin();
}
