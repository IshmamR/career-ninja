<?php

require_once(__DIR__ . '/../db/conn.php');
require_once(__DIR__ . '/../utils/hash.php');

function adminTestFunc()
{
  global $conn;
  header('Content-Type: application/json; charset=utf-8');
  http_response_code(200);
  echo json_encode(["message" => $conn->host_info], JSON_UNESCAPED_SLASHES);
  exit;
}

function createAdmin()
{
  global $conn;
  header('Content-Type: application/json; charset=utf-8');

  $data = json_decode(file_get_contents('php://input'), true);

  $username = $data['username'];
  $password = $data['password'];
  $confirmPassword = $data['confirmPassword'];

  if (empty($username) || empty($password) || empty($confirmPassword)) {
    http_response_code(400);
    echo json_encode(["message" => "please fill up all required fields"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  if ($password !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(["message" => "passwords should match"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $good_username = mysqli_real_escape_string($conn, $username);
  $good_password = mysqli_real_escape_string($conn, $password);

  $uniqueId = generateRandomUniqueId('@admin_'); // random 36 byte string
  $hashed = hashPassword($good_password);

  $sql = "INSERT INTO `admins` 
    (`id`, `username`, `password`, `type`) 
    VALUES ('$uniqueId', '$good_username', '$hashed', 'SECONDARY_ADMIN')
  ;";
  $result = mysqli_query($conn, $sql);

  if ($result === TRUE) {
    http_response_code(201);
    echo json_encode(["message" => "admin has been created successfully"], JSON_UNESCAPED_SLASHES);
    exit;
  } else {
    http_response_code(500);
    echo json_encode(["message" => "something went wrong"], JSON_UNESCAPED_SLASHES);
    exit;
  }
}


function testLoggedAdmin()
{
  http_response_code(200);
  echo json_encode(["message" => "Authenticated"], JSON_UNESCAPED_SLASHES);
  exit;
}
