<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');


require_once(__DIR__ . '/../db/conn.php');
require_once(__DIR__ . '/../utils/hash.php');

function loginAdmin()
{
  global $conn;
  header('Content-Type: application/json; charset=utf-8');

  $username = $_POST['username'];
  $password = $_POST['password'];
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

  setcookie("@admin_auth_cookie", $authAdmin, time() + (86400 * 30), '/'); // 1 day

  echo $authAdmin;
  exit;
}

function logout()
{
  header('Content-Type: application/json; charset=utf-8');
  unset($_COOKIE['@admin_auth_cookie']);

  http_response_code(200);
  echo json_encode(["message" => "logged out"]);
  exit;
}
