<?php

require_once(__DIR__ . '/../db/conn.php');
require_once(__DIR__ . '/../utils/hash.php');
require_once(__DIR__ . '/../env.php');

function applicantSignup()
{
  global $conn;
  header('Content-Type: application/json; charset=utf-8');

  $data = json_decode(file_get_contents('php://input'), true);

  $name = $data['name'];
  $email = $data['email'];
  $phone = $data['phone'];
  $password = $data['password'];

  if (empty($name) || empty($email) || empty($phone) || empty($password)) {
    http_response_code(400);
    echo json_encode([
      "message" => "please fill up all required fields"
    ], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $good_name = $conn->real_escape_string($name);
  $good_email = $conn->real_escape_string($email);
  $good_phone = $conn->real_escape_string($phone);
  $good_password =  $conn->real_escape_string($password);

  $uniqueId = generateRandomUniqueId('@applt_'); // random 36 byte string
  $hashed = hashPassword($good_password);

  $sql = "INSERT INTO `applicants` 
    (`id`, `name`, `email`, `phone`, `password`) 
    VALUES ('$uniqueId', '$good_name', '$good_email', '$good_phone', '$hashed')
  ;";
  $result = $conn->query($sql);

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

function applicantSignIn()
{
  global $conn, $CIPHER_ALGO, $ADMIN_ENCR, $ADMIN_COOKIE_KEY;
  header('Content-Type: application/json; charset=utf-8');

  // receive posted json body
  $data = json_decode(file_get_contents('php://input'), true);

  $username = $data['username'];
  $password = $data['password'];
  if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["error" => "username and password are required"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $good_username = $conn->real_escape_string($username);
  $good_password = $conn->real_escape_string($password);

  $sql = "SELECT * FROM `admins` WHERE username='$good_username';";
  $result = $conn->query($sql);

  if ($result->num_rows == 0) {
    http_response_code(404);
    echo json_encode(["message" => $good_username], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $row = $result->fetch_assoc();

  if (!password_verify($good_password, $row['password'])) {
    http_response_code(404);
    echo json_encode(["message" => "username and password did not match"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $admin = [
    "id" => $row['id'],
    "type" => $row['type'],
    "username" => $row['username']
  ];

  $authAdmin = json_encode($admin, JSON_UNESCAPED_SLASHES);
  $admin_token = openssl_encrypt($authAdmin, $CIPHER_ALGO, $ADMIN_ENCR);
  setcookie($ADMIN_COOKIE_KEY, $admin_token, time() + (86400 * 30), '/'); // 1 day

  http_response_code(200);
  echo $authAdmin;
  exit;
}
