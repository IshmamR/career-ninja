<?php

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;

$app->add(function (Request $request, RequestHandler $handler) {
  $uri = $request->getUri();
  $path = $uri->getPath();
  $response = new SlimResponse();

  $isStatic = substr($path, 0, 8) == '/static/';
  $isPNG = strtolower(substr($path, -4)) == '.png';
  $isJPG = strtolower(substr($path, -4)) == '.jpg';
  $isJPEG = strtolower(substr($path, -5)) == '.jpeg';
  $isGIF = strtolower(substr($path, -4)) == '.gif';

  if ($isStatic && ($isPNG || $isJPG || $isJPEG || $isGIF)) {
    $filePath = htmlentities(__DIR__ . '../public' . $path);

    if (!file_exists($filePath)) {
      $response->getBody()->write('<h1>404 file not found</h1>');
      return $response->withStatus(404);
    }


    $imageFile = file_get_contents($filePath);
    $response->getBody()->write($imageFile);

    $imageType = strtolower(substr($path, $isJPEG ? -4 : -3));
    return $response->withHeader("Content-Type", "image/" . $imageType);
  }
});
