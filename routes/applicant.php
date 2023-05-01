<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

defined("APCNT_ENCR") ? null : define("APCNT_ENCR", "@apcnt_passphrase");
defined("CIPHER_ALGO") ? null : define("CIPHER_ALGO", "AES-128-CTR");
defined("APCNT_COOKIE_KEY") ? null : define("APCNT_COOKIE_KEY", "@apcnt_auth_cookie");


// TODO
// register
$app->post("/applicant/register", function (Request $request, Response $response) {
  try {
    $body = $request->getParsedBody();

    if (empty($body['name']) || empty($body['email']) || empty($body['password']) || empty($body['phone'])) {
      $response->getBody()->write(json_encode(["message" => "please provide all required data"]));
      return $response->withStatus(400);
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `applicants` WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', htmlspecialchars(strip_tags($body['email'])));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if ($result) {
      $response->getBody()->write(json_encode(["message" => "email already exists"]));
      return $response->withStatus(400);
    }

    $sql = "SELECT * FROM `applicants` WHERE phone = :phone";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':phone', htmlspecialchars(strip_tags($body['phone'])));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if ($result) {
      $response->getBody()->write(json_encode(["message" => "phone already exists"]));
      return $response->withStatus(400);
    }

    $image = null;
    $applicantId = generateRandomUniqueId("@aplcnt");

    $files = $request->getUploadedFiles();
    $imageUploaded = $files["image"];
    if (isset($imageUploaded)) {
      $mediaType = $imageUploaded->getClientMediaType();
      $extension = substr($mediaType, 6);
      $image = "/static/applicants/" . substr($applicantId, 1) . "." .  $extension;
      $path = __DIR__ . "/../public" . $image;
      $imageUploaded->moveTo($path);
    }

    $sql = "INSERT INTO `applicants` 
      (`id`, `name`, `email`, `phone`, `password`, `image`, `gender`)
      VALUES (:id, :name, :email, :phone, :password, :image, :gender);";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $applicantId);
    $stmt->bindValue(':name', htmlspecialchars(strip_tags($body["name"])));
    $stmt->bindValue(':email', htmlspecialchars(strip_tags($body["email"])));
    $stmt->bindValue(':phone', htmlspecialchars(strip_tags($body["phone"])));
    $stmt->bindValue(':image', $image);
    $stmt->bindValue(':gender', htmlspecialchars(strip_tags($body["gender"])));
    $stmt->bindValue(
      ':password',
      hashPassword(
        htmlspecialchars(
          strip_tags(
            $body["password"]
          )
        )
      )
    );

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "failed to register"]));
      return $response->withStatus(500);
    }

    $sql = "SELECT * FROM `applicants` WHERE `id` = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $applicantId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "failed to register"]));
      return $response->withStatus(400);
    }

    // close connection
    $db = null;

    $applicantAuthData = [
      "id" => $result->id,
      "name" => $result->name,
      "email" => $result->email,
      "avatar" => $result->image
    ];
    $authApplicant = json_encode($applicantAuthData);
    $authApplicantToken = openssl_encrypt($authApplicant, CIPHER_ALGO, APCNT_ENCR);

    $response->getBody()->write(json_encode([
      "authApplicant" => $applicantAuthData,
      "authApplicantToken" => $authApplicantToken
    ]));
    return $response->withStatus(201);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
});

// login
$app->post("/applicant/login", function (Request $request, Response $response) {
  try {
    $json = $request->getBody();
    $body = json_decode($json, true);

    if (empty($body['email']) || empty($body['password'])) {
      $response->getBody()->write(json_encode(["message" => "email and password are required"]));
      return $response->withStatus(400);
    }

    $db = new Database();
    $conn = $db->connect();

    $query = "SELECT id, email, name, image, password FROM `applicants` WHERE email = :email;";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':email', htmlspecialchars(strip_tags($body['email'])));
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_OBJ);

    // close connection
    $db = null;

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "email or password did not match"]));
      return $response->withStatus(400);
    }

    $verified = password_verify($body["password"], $result->password);
    if (!$verified) {
      $response->getBody()->write(json_encode(["message" => "email or password did not match"]));
      return $response->withStatus(400);
    }

    $applicantAuthData = [
      "id" => $result->id,
      "name" => $result->name,
      "email" => $result->email,
      "avatar" => $result->image
    ];
    $authApplicant = json_encode($applicantAuthData);
    $authApplicantToken = openssl_encrypt($authApplicant, CIPHER_ALGO, APCNT_ENCR);

    $response->getBody()->write(json_encode([
      "authApplicant" => $applicantAuthData,
      "authApplicantToken" => $authApplicantToken
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

// get profile
$app->get("/applicant/profile", function (Request $request, Response $response) {
  try {
    $authApplicant = $request->getAttribute('authApplicant');
    $applicantId = $authApplicant->id;

    if (!$applicantId) {
      $response->getBody()->write(json_encode(["message" => "Unauthorized"]));
      return $response->withStatus(401);
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `applicants` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', htmlspecialchars(strip_tags($applicantId)));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "applicant does not exist"]));
      return $response->withStatus(404);
    }

    // close connection
    $db = null;

    $response->getBody()->write(json_encode($result));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "applicant");
});

// update
$app->put("/applicant/profile", function (Request $request, Response $response) {
  try {
    $authApplicant = $request->getAttribute('authApplicant');
    $applicantId = $authApplicant->id;

    if (!$applicantId) {
      $response->getBody()->write(json_encode(["message" => "Unauthorized"]));
      return $response->withStatus(401);
    }

    $json = $request->getBody();
    $body = json_decode($json, true);

    $db = new Database();
    $conn = $db->connect();

    // Build the SQL query and the list of parameters
    $params = [];
    $sql = "UPDATE applicants SET ";
    foreach ($body as $key => $value) {
      $sql .= "`{$key}` = :{$key}, ";
      $params[":{$key}"] = $value;
    }
    $sql = rtrim($sql, ", ");
    $sql .= " WHERE id = :id";
    $params[":id"] = $applicantId;

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
      $response->getBody()->write(json_encode(["message" => "No records were updated"]));
      return $response->withStatus(200);
    }

    $response->getBody()->write(json_encode(["message" => "profile updated successfully"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "applicant");
});

// update image
$app->post("/applicant/image", function (Request $request, Response $response) {
  try {
    $authApplicant = $request->getAttribute('authApplicant');
    $applicantId = $authApplicant->id;

    if (!$applicantId) {
      $response->getBody()->write(json_encode(["message" => "Unauthorized"]));
      return $response->withStatus(401);
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `applicants` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $applicantId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Applicant not found"]));
      return $response->withStatus(404);
    }

    // get file
    $files = $request->getUploadedFiles();
    $imageUploaded = $files["image"];

    if (!isset($imageUploaded)) {
      $response->getBody()->write(json_encode(["message" => "Image file not found", "img" => json_encode($imageUploaded)]));
      return $response->withStatus(400);
    }

    $mediaType = $imageUploaded->getClientMediaType();
    $extension = substr($mediaType, 6);
    $image = "/static/applicants/" . substr($applicantId, 1) . "." .  $extension;
    $path = __DIR__ . "/../public" . $image;
    $imageUploaded->moveTo($path);

    $sql = "UPDATE `applicants` SET `image` = :image WHERE `id` = :id;";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $applicantId);
    $stmt->bindValue(':image', $image);
    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not update image"]));
      return $response->withStatus(500);
    }

    $response->getBody()->write(json_encode(["message" => "image uploaded"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "applicant");
});


// logout
$app->post("/applicant/logout", function (Request $request, Response $response) {
  try {
    $response->getBody()->write(json_encode(["message" => "Successfully logged out"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
});

##################
# USED BY ADMINS #
##################

// get all
// get by id