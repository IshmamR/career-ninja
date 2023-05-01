<?php

use Slim\Psr7\Response as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

defined("APCNT_ENCR") ? null : define("APCNT_ENCR", "@apcnt_passphrase");
defined("ADMIN_ENCR") ? null : define("ADMIN_ENCR", "@admin_passphrase");
defined("CMPNY_ENCR") ? null : define("CMPNY_ENCR", "@cmpny_passphrase");
defined("CIPHER_ALGO") ? null : define("CIPHER_ALGO", "AES-128-CTR");

function throwUnauthorized()
{
  $response = new Response();
  $response->getBody()->write(json_encode(["message" => "Unauthorized"]));
  return $response
    ->withHeader('Content-Type', 'application/json')
    ->withStatus(401);
}


// Define a middleware that checks if the user is authenticated
$adminAuthMiddleware = function (Request $request, RequestHandler $handler) {
  try {
    // Check if the user is authenticated
    $token = $request->getHeaderLine('Authorization');
    if (!$token) {
      return throwUnauthorized();
    }


    $tokenData = openssl_decrypt($token, CIPHER_ALGO, ADMIN_ENCR);
    if (!$tokenData) {
      return throwUnauthorized();
    }

    $authAdmin = json_decode($tokenData);
    if (!$authAdmin) {
      return throwUnauthorized();
    }

    $request = $request->withAttribute('authAdmin', $authAdmin);

    // Admin is authenticated, continue to the next middleware
    return $handler->handle($request);
  } catch (Exception $err) {
    return throwUnauthorized();
  }
};

$companyAuthMiddleware = function (Request &$request, RequestHandler $handler) {
  try {
    // Check if the user is authenticated
    $token = $request->getHeaderLine('Authorization');
    if (!$token) {
      return throwUnauthorized();
    }

    $tokenData = openssl_decrypt($token, CIPHER_ALGO, CMPNY_ENCR);
    if (!$tokenData) {
      return throwUnauthorized();
    }

    $authCompanyAdmin = json_decode($tokenData);
    if (!$authCompanyAdmin) {
      return throwUnauthorized();
    }

    $request = $request->withAttribute('authCompanyAdmin', $authCompanyAdmin);

    // Admin is authenticated, continue to the next middleware
    return $handler->handle($request);
  } catch (Exception $err) {
    return throwUnauthorized();
  }
};

$applicantAuthMiddleware = function (Request &$request, RequestHandler $handler) {
  try {
    // Check if the user is authenticated
    $token = $request->getHeaderLine('Authorization');
    if (!$token) {
      return throwUnauthorized();
    }

    $tokenData = openssl_decrypt($token, CIPHER_ALGO, APCNT_ENCR);
    if (!$tokenData) {
      return throwUnauthorized();
    }

    $authApplicant = json_decode($tokenData);
    if (!$authApplicant) {
      return throwUnauthorized();
    }

    $request = $request->withAttribute('authApplicant', $authApplicant);

    // Admin is authenticated, continue to the next middleware
    return $handler->handle($request);
  } catch (Exception $err) {
    return throwUnauthorized();
  }
};

$authMiddleware = function (Request &$request, RequestHandler $handler, string $authType) {
  global $adminAuthMiddleware;
  global $companyAuthMiddleware;
  global $applicantAuthMiddleware;

  if ($authType === "admin") {
    return $adminAuthMiddleware($request, $handler);
  } else if ($authType === "company") {
    return $companyAuthMiddleware($request, $handler);
  } else if ($authType === "applicant") {
    return $applicantAuthMiddleware($request, $handler);
  }
};
