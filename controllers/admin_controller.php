<?php

require_once(__DIR__ . '/../db/conn.php');
require_once(__DIR__ . '/../utils/hash.php');
require_once(__DIR__ . '/../env.php');

function loginAdmin()
{
  global $conn, $CIPHER_ALGO, $ADMIN_ENCR, $ADMIN_COOKIE_KEY;
  header('Content-Type: application/json; charset=utf-8');

  // receive posted json body
  $data = json_decode(file_get_contents('php://input'), true);
  $username = $data['username'];
  $password = $data['password'];
  if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["error" => "username and password are required"]);
    exit;
  }

  $hashed = hashPassword($password);

  $sql = "SELECT * FROM 'admins' WHERE username = '$username' AND password = '$hashed';";

  $result = $conn->query($sql);


  if ($result->num_rows == 0) {
    http_response_code(404);
    echo json_encode(["error" => "admin not found"]);
    exit;
  }

  http_response_code(200);

  $row = $result->fetch_assoc();

  $admin = [
    "id" => $row['id'],
    "type" => $row['type'],
    "username" => $row['username']
  ];

  $authAdmin = json_encode($admin);
  $admin_token = openssl_encrypt($authAdmin, $CIPHER_ALGO, $ADMIN_ENCR);
  setcookie($ADMIN_COOKIE_KEY, $admin_token, time() + (86400 * 30), '/'); // 1 day

  echo $authAdmin;
  exit;
}

function logout()
{
  global $ADMIN_COOKIE_KEY;
  header('Content-Type: application/json; charset=utf-8');
  unset($_COOKIE[$ADMIN_COOKIE_KEY]);

  http_response_code(200);
  echo json_encode(["message" => "logged out"]);
  exit;
}
