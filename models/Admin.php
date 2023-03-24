<?php

require_once(__DIR__ . "/../db/conn.php");
require_once(__DIR__ . "/../utils/hash.php");

function createAdminsTable()
{
  global $conn;

  $sql = "CREATE TABLE IF NOT EXISTS `admins` (
    `id` CHAR(36) PRIMARY KEY NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NOT NULL
  );";

  $result = $conn->query($sql);

  if (!$result) {
    die($conn->error);
  }
}

function deleteAdmin($id)
{
  global $conn;

  $sql = "DELETE FROM `admins` WHERE `id`='$id';";
  $conn->query($sql);
}
