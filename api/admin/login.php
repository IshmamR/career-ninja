<?php

require_once(__DIR__ . '/../../controllers/admin_controller.php');

require_once(__DIR__ . '/../../models/Admin.php');
createAdminsTable();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  loginAdmin();
}
