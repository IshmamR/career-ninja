<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

defined("CMPNY_ENCR") ? null : define("CMPNY_ENCR", "@cmpny_passphrase");
defined("CIPHER_ALGO") ? null : define("CIPHER_ALGO", "AES-128-CTR");

// all json response
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->post("/company/upload/test", function (Request $req, Response $res) {
  $files = $req->getUploadedFiles();
  $imageUploaded = $files["image"];

  $mediaType = $imageUploaded->getClientMediaType();

  $extension = substr($mediaType, 6);

  $route = "/static/test." .  $extension;
  $path = __DIR__ . "/../public" . $route;
  $imageUploaded->moveTo($path);

  $body = $req->getParsedBody();

  $res->getBody()->write(json_encode(["route" => $route, "body" => $body]));
  return $res;
});

/**
 * Sign up company
 */
$app->post("/company/signup", function (Request $request, Response $response) {
  try {
    $body = $request->getParsedBody();

    if (empty($body['company']) || empty($body['companyAdmin'])) {
      $response->getBody()->write(json_encode(["message" => "please provide all required data"]));
      return $response->withStatus(400);
    }

    $companyData = json_decode($body["company"], true);
    $companyAdminData = json_decode($body["companyAdmin"], true);

    $db = new Database();
    $conn = $db->connect();

    // check if company admin with same username already exists
    $sql = "SELECT * FROM `company_admins` WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', htmlspecialchars(strip_tags($companyAdminData['username'])));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if ($result) {
      $response->getBody()->write(json_encode(["message" => "username already exists"]));
      return $response->withStatus(400);
    }

    $logo = '/static/companies/__demo__.png';
    $companyId = generateRandomUniqueId("@cmpany");

    // get file
    $files = $request->getUploadedFiles();
    $imageUploaded = $files["image"];
    if (isset($imageUploaded)) {
      $mediaType = $imageUploaded->getClientMediaType();
      $extension = substr($mediaType, 6);
      $logo = "/static/companies/" . substr($companyId, 1) . "." .  $extension;
      $path = __DIR__ . "/../public" . $logo;
      $imageUploaded->moveTo($path);
    }

    $sql = "INSERT INTO `companies` 
      (`id`, `title`, `description`, `logo`, `email`, `contact`, `website`, `country`, `city`, `verified`)
      VALUES (:id, :title, :description, :logo, :email, :contact, :website, :country, :city, FALSE);";

    $stmt = $conn->prepare($sql);

    $stmt->bindValue(':id', $companyId);
    $stmt->bindValue(':title', htmlspecialchars(strip_tags($companyData["title"])));
    $stmt->bindValue(':description', htmlspecialchars(strip_tags($companyData["description"])));
    $stmt->bindValue(':logo', htmlspecialchars(strip_tags($logo)));
    $stmt->bindValue(':email', htmlspecialchars(strip_tags($companyData["email"])));
    $stmt->bindValue(':contact', htmlspecialchars(strip_tags($companyData["contact"])));
    $stmt->bindValue(':website', htmlspecialchars(strip_tags($companyData["website"])));
    $stmt->bindValue(':country', htmlspecialchars(strip_tags($companyData["country"])));
    $stmt->bindValue(':city', htmlspecialchars(strip_tags($companyData["city"])));

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not create company"]));
      return $response->withStatus(500);
    }

    // create a company admin
    $sql = "INSERT INTO `company_admins` (`companyId`, `username`, `password`)
      VALUES (:companyId, :username, :password);";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':companyId', $companyId);
    $stmt->bindParam(':username', htmlspecialchars(strip_tags($companyAdminData["username"])));
    $stmt->bindParam(
      ':password',
      hashPassword(
        htmlspecialchars(
          strip_tags(
            $companyAdminData["password"]
          )
        )
      )
    );

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not create company admin"]));
      return $response->withStatus(500);
    }

    $response->getBody()->write(json_encode([
      "message" => "Company has been created and awaiting for verification"
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
 * company login
 */
$app->post("/company/login", function (Request $request, Response $response) {
  try {
    $json = $request->getBody();
    $data = json_decode($json, true);

    if (empty($data['username']) || empty($data['password'])) {
      $response->getBody()->write(json_encode(["message" => "username and password are required"]));
      return $response->withStatus(400);
    }

    $db = new Database();
    $conn = $db->connect();

    $query = "SELECT c.id, c.title, c.verified, ca.username, ca.password
      FROM companies c
      JOIN company_admins ca ON ca.companyId = c.id
      WHERE ca.username = :username
        AND c.verified = 1;";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':username', htmlspecialchars(strip_tags($data['username'])));
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_OBJ);

    // close connection
    $db = null;

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "company not verified"]));
      return $response->withStatus(400);
    }


    $verified = password_verify($data["password"], $result->password);
    if (!$verified) {
      $response->getBody()->write(json_encode(["message" => "username or password did not match"]));
      return $response->withStatus(400);
    }

    $companyAdmin = [
      "companyId" => $result->id,
      "username" => $result->username,
    ];
    $authAdmin = json_encode($companyAdmin);
    $companyAdminToken = openssl_encrypt($authAdmin, CIPHER_ALGO, CMPNY_ENCR);
    $response->getBody()->write(json_encode([
      "companyAdmin" => $companyAdmin,
      "authCompanyToken" => $companyAdminToken
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
 * get profile
 */
$app->get("/company/profile", function (Request $request, Response $response) {
  try {
    $authCompanyAdmin = $request->getAttribute('authCompanyAdmin');
    $companyId = $authCompanyAdmin->companyId;

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `companies` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', htmlspecialchars(strip_tags($companyId)));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "company does not exist"]));
      return $response->withStatus(404);
    }

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
  return $authMiddleware($req, $handler, "company");
});

/**
 * update company logo
 */
$app->put("/company/logo", function (Request $request, Response $response) {
  try {
    $authCompanyAdmin = $request->getAttribute('authCompanyAdmin');
    $companyId = $authCompanyAdmin->companyId;

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `companies` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $companyId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Company not found"]));
      return $response->withStatus(404);
    }

    // get file
    $files = $request->getUploadedFiles();
    $imageUploaded = $files["image"];

    if (!isset($imageUploaded)) {
      $response->getBody()->write(json_encode(["message" => "Image not found"]));
      return $response->withStatus(400);
    }

    $mediaType = $imageUploaded->getClientMediaType();
    $extension = substr($mediaType, 6);
    $logo = "/static/companies/" . substr($companyId, 1) . "." .  $extension;
    $path = __DIR__ . "/../public" . $logo;
    $imageUploaded->moveTo($path);

    $sql = "UPDATE `companies` SET logo = :logo WHERE `id` = :id;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $companyId);
    $stmt->bindParam(':logo', $logo);
    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not update logo"]));
      return $response->withStatus(500);
    }

    $response->getBody()->write(json_encode(["message" => "Logo uploaded"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "company");
});

/**
 * update company cover
 */
$app->put("/company/cover", function (Request $request, Response $response) {
  try {
    $authCompanyAdmin = $request->getAttribute('authCompanyAdmin');
    $companyId = $authCompanyAdmin->companyId;

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `companies` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $companyId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Company not found"]));
      return $response->withStatus(404);
    }

    // get file
    $files = $request->getUploadedFiles();
    $imageUploaded = $files["image"];

    if (!isset($imageUploaded)) {
      $response->getBody()->write(json_encode(["message" => "Image file not found"]));
      return $response->withStatus(400);
    }

    $mediaType = $imageUploaded->getClientMediaType();
    $extension = substr($mediaType, 6);
    $cover = "/static/companies/covers/" . substr($companyId, 1) . "." .  $extension;
    $path = __DIR__ . "/../public" . $cover;
    $imageUploaded->moveTo($path);

    $sql = "UPDATE `companies` SET cover = :cover WHERE `id` = :id;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $companyId);
    $stmt->bindParam(':cover', $cover);
    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not update cover"]));
      return $response->withStatus(500);
    }

    $response->getBody()->write(json_encode(["message" => "Company cover uploaded"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "company");
});

/**
 * Update company information
 */
$app->put("/company/update", function (Request $request, Response $response) {
  try {
    $authCompanyAdmin = $request->getAttribute('authCompanyAdmin');
    $companyId = $authCompanyAdmin->companyId;

    $json = $request->getBody();
    $data = json_decode($json, true);

    $db = new Database();
    $conn = $db->connect();

    // Build the SQL query and the list of parameters
    $params = [];
    $sql = "UPDATE companies SET ";
    foreach ($data as $key => $value) {
      $sql .= "`{$key}` = :{$key}, ";
      $params[":{$key}"] = $value;
    }
    $sql = rtrim($sql, ", ");
    $sql .= " WHERE id = :companyId";
    $params[":companyId"] = $companyId;

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() === 0) {
      $response->getBody()->write(json_encode(["message" => "No records were updated"]));
      return $response->withStatus(200);
    }

    $response->getBody()->write(json_encode(["message" => "Company profile updated successfully"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "company");
});


/**
 * logout
 */
$app->put("/company/logout", function (Request $request, Response $response) {
  $response->getBody()->write(json_encode(["message" => "Successfully logged out"]));
  return $response->withStatus(200);
});


#################################
# BELOW ARE USED BY ADMINS ONLY #
#################################


/**
 * get companies paginated
 */
$app->get("/company/all", function (Request $request, Response $response) {
  try {
    $db = new Database();
    $conn = $db->connect();

    $params = $request->getQueryParams();
    $limit = isset($params['limit']) ? intval($params['limit']) : 10;
    $page = isset($params['page']) ? intval($params['page']) : 1;
    $page = max($page, 1); // Ensure page number is not negative

    // Calculate the offset for the current page
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM `companies` LIMIT :offset, :limit;";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

    $total_items_sql = "SELECT COUNT(*) FROM `companies`;";
    $total_items = $conn->query($total_items_sql)->fetchColumn();

    $db = null;

    $response->getBody()->write(json_encode([
      "companies" => $rows,
      "count" => $total_items
    ]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "admin");
});

/**
 * verify a company
 */
$app->put("/company/verify/{id}", function (Request $req, Response $response, array $args) {
  try {
    $companyId = $args["id"];

    $db = new Database();
    $conn = $db->connect();

    $sql = "UPDATE `companies` SET verified = TRUE WHERE `id` = :id;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', htmlspecialchars(strip_tags($companyId)));
    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not verify company"]));
      return $response->withStatus(500);
    }

    $response->getBody()->write(json_encode(["message" => "Company has been verified"]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "admin");
});

/**
 * get company profile
 */
$app->get("/company/get/{id}", function (Request $request, Response $response, array $args) {
  try {
    $companyId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `companies` WHERE companyId = :companyId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':companyId', htmlspecialchars(strip_tags($companyId)));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    $db = null;

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "company does not exist"]));
      return $response->withStatus(404);
    }

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
  return $authMiddleware($req, $handler, "admin");
});

/**
 * delete a company
 */
$app->delete("/company/{id}", function (Request $request, Response $response, array $args) {
  try {
    $companyId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "DELETE FROM `companies` WHERE id = :id;";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', htmlspecialchars(strip_tags($companyId)));

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not delete company"]));
      return $response->withStatus(500);
    }

    $db = null;

    $response->getBody()->write(json_encode([
      "message" => "company deleted successfully",
    ]));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
})->add(function ($req, $handler) use ($authMiddleware) {
  return $authMiddleware($req, $handler, "admin");
});

#########################
# BELOW ARE PUBLIC APIs #
#########################

$app->get("/company/{id}", function (Request $request, Response $response, array $args) {
  try {
    $companyId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT c.id, c.title, c.description, c.logo, c.cover, c.email, c.contact,
      c.website, c.country, c.city, c.address
     FROM companies as c WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', htmlspecialchars(strip_tags($companyId)));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    $db = null;

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "company does not exist"]));
      return $response->withStatus(404);
    }

    $response->getBody()->write(json_encode($result));
    return $response->withStatus(200);
  } catch (PDOException $err) {
    $error = array(
      "message" => $err->getMessage()
    );
    $response->getBody()->write(json_encode($error));
    return $response->withStatus(500);
  }
});
