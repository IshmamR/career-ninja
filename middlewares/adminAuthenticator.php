<?php

require_once(__DIR__ . '/../config.php');

$admin_cookie = $_COOKIE[$ADMIN_COOKIE_KEY];

if (empty($admin_cookie)) {
  http_response_code(403);
  echo json_encode(["message" => "Unauthorized request"],);
  exit;
}

$authAdminString = openssl_decrypt($admin_cookie, $CIPHER_ALGO, $ADMIN_ENCR);

if (!$authAdminString) {
  http_response_code(403);
  echo json_encode(["message" => "Unauthorized request"],);
  exit;
}

$authAdmin = json_decode($authAdminString);
$adminType = $authAdmin['type'];

if ($adminType !== "SUPER_ADMIN") {
  http_response_code(403);
  echo json_encode(["message" => "Unauthorized request"],);
  exit;
}
