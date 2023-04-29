<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/test/all', function (Request $request, Response $response) {
  $sql = "SELECT * FROM tests";

  try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->query($sql);
    $tests = $stmt->fetchAll(PDO::FETCH_OBJ);

    $db = null;

    $response->getBody()->write(json_encode($tests));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(500);
  }
});

$app->get('/test/ping', function (Request $request, Response $response) {
  $response->getBody()->write("PONG");
  return $response->withHeader("Content-Type", "application/json");
});

$app->get('/test/admin/auth', function (Request $request, Response $response) {
  $response->getBody()->write("PONG");
  return $response->withHeader("Content-Type", "application/json");
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "admin");
});
