<?php

require_once(__DIR__ . '/../../../controllers/admin_controller.php');

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  include_once(__DIR__ . "/../../../middleware/adminAuthenticator.php");
  testLoggedAdmin();
}
