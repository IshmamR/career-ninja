<?php

require_once(__DIR__ . "/../db/conn.php");
require_once(__DIR__ . "/../utils/hash.php");

function createApplicantTable()
{
  global $conn;

  $sql = "CREATE TABLE IF NOT EXISTS applicants (
    id VARCHAR(36) PRIMARY KEY NOT NULL,
    name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(255),
    address VARCHAR(255) NOT NULL,
    created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    type VARCHAR(255) NOT NULL
  );";

  $result = $conn->query($sql);

  if (!$result) {
    die($conn->error);
  }
}

function deleteAdmin($id)
{
  global $conn;

  $sql = "DELETE FROM 'admins' WHERE id = '$id';";
  $conn->query($sql);
}
