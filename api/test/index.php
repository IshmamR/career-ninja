<?php

require_once(__DIR__ . "/../../utils/hash.php");
require_once(__DIR__ . "/../../config.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = generateRandomUniqueId('@admin_');
  echo $id;
  echo "<br />";

  $pass = hashPassword("admin1234");
  echo $pass;
}
