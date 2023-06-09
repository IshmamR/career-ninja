<?php

require_once(__DIR__ . "/../config.php");

$conn = mysqli_init();
if (!$conn) {
  die('mysqli_init failed');
}

if (!$conn->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
  die('Setting MYSQLI_INIT_COMMAND failed');
}

if (!$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
  die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
}

if ($MYSQL_PORT) {
  if (!$conn->real_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DTBS, $MYSQL_PORT)) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
      . mysqli_connect_error());
  }
} else {
  if (!$conn->real_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DTBS)) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
      . mysqli_connect_error());
  }
}
