<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response as SlimResponse;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../utils/hash.php';

$app = AppFactory::create();

$app->setBasePath('/api');
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// require __DIR__ . '/../middlewares/static.middleware.php';

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->add(function (Request $request, RequestHandler $handler) {
  $uri = $request->getUri();
  $path = $uri->getPath();

  if ($path != '/' && substr($path, -1) == '/') {
    // recursively remove slashes when its more than 1 slash
    $path = rtrim($path, '/');

    // permanently redirect paths with a trailing slash
    // to their non-trailing counterpart
    $uri = $uri->withPath($path);

    if ($request->getMethod() == 'GET') {
      $response = new SlimResponse();
      return $response
        ->withHeader('Location', (string) $uri)
        ->withStatus(301);
    } else {
      $request = $request->withUri($uri);
    }
  }
  return $handler->handle($request);
});


$app->get('/', function (Request $request, Response $response) {
  $params = $request->getQueryParams();
  $response->getBody()->write($params["id"]);
  return $response->withHeader("Content-Type", "application/json")->withStatus(200);
});


// middlewares
require_once __DIR__ . "/../middlewares/authenticator.php";

//
require __DIR__ . '/../routes/test.php';
//
require __DIR__ . '/../routes/applicant.php';
require __DIR__ . '/../routes/admin.php';
require __DIR__ . '/../routes/company.php';
require __DIR__ . '/../routes/circular.php';
require __DIR__ . '/../routes/field.php';
require __DIR__ . '/../routes/skill.php';

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
  throw new HttpNotFoundException($request);
});

$app->run();
