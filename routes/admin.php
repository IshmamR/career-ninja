<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

defined("ADMIN_ENCR") ? null : define("ADMIN_ENCR", "@admin_passphrase");
defined("CIPHER_ALGO") ? null : define("CIPHER_ALGO", "AES-128-CTR");
defined("ADMIN_COOKIE_KEY") ? null : define("ADMIN_COOKIE_KEY", "@admin_auth_cookie");

// all json response
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->get("/admin/test", function ($request, $response) {
  $db = new Database();
  $conn = $db->connect();

  $sql = "SELECT * FROM admins WHERE username = 'admin';";
  $stmt = $conn->query($sql);
  $result = $stmt->fetch(PDO::FETCH_OBJ);

  $db = null;

  $response->getBody()->write(json_encode($result));
  return $response->withStatus(200);
});

/**
 * LOGIN
 */
$app->post('/admin/login', function (Request $request, Response $response) {
  try {
    $json = $request->getBody();
    $data = json_decode($json, true);

    if (empty($data['username']) || empty($data['password'])) {
      $response->getBody()->write(json_encode(["message" => "username and password are required"]));
      return $response->withStatus(400);
    }

    $db = new Database();
    $conn = $db->connect();

    $query = "SELECT * FROM `admins` WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', htmlspecialchars(strip_tags($data['username'])));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    // close connection
    $db = null;

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "username or password did not match"]));
      return $response->withStatus(400);
    }

    $verified = password_verify($data["password"], $result->password);
    if (!$verified) {
      $response->getBody()->write(json_encode(["message" => "admin not verified"]));
      return $response->withStatus(400);
    }

    $admin = [
      "id" => $result->id,
      "type" => $result->type,
      "username" => $result->username,
    ];
    $authAdmin = json_encode($admin);
    $adminToken = openssl_encrypt($authAdmin, CIPHER_ALGO, ADMIN_ENCR);
    $response->getBody()->write(json_encode([
      "admin" => $admin,
      "authAdminToken" => $adminToken
    ]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
});


/**
 * logout
 */
$app->put("/admin/logout", function (Request $request, Response $response) {
  $response->getBody()->write(json_encode(["message" => "Successfully logged out"]));
  return $response->withStatus(200);
});
