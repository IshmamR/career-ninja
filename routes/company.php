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

/**
 * Sign up company
 */
$app->post("/company/signup", function (Request $request, Response $response) {
  try {
    $json = $request->getBody();
    $data = json_decode($json, true);

    if (empty($data['company']) || empty($data['companyAdmin'])) {
      $response->getBody()->write(json_encode(["message" => "please provide all required data"]));
      return $response->withStatus(400);
    }

    $companyData = $data["company"];
    $companyAdminData = $data["companyAdmin"];

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

    // required company variables
    $logo = $companyData['logo'] ?
      $companyData['logo'] :
      "https://ui-avatars.com/api/?name=" . $companyData['title'];
    $verified = FALSE;
    $companyId = generateRandomUniqueId("@cmpany");

    $sql = "INSERT INTO `companies` 
      (`id`, `title`, `description`, `logo`, `email`, `contact`, `website`, `country`, `city`, `verified`)
      VALUES (:id, :title, :description, :logo, :email, :contact, :website, :country, :city, :verified);";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', $companyId);
    $stmt->bindParam(':title', htmlspecialchars(strip_tags($companyData["title"])));
    $stmt->bindParam(':description', htmlspecialchars(strip_tags($companyData["description"])));
    $stmt->bindParam(':logo', htmlspecialchars(strip_tags($logo)));
    $stmt->bindParam(':email', htmlspecialchars(strip_tags($companyData["email"])));
    $stmt->bindParam(':contact', htmlspecialchars(strip_tags($companyData["contact"])));
    $stmt->bindParam(':website', htmlspecialchars(strip_tags($companyData["website"])));
    $stmt->bindParam(':country', htmlspecialchars(strip_tags($companyData["country"])));
    $stmt->bindParam(':city', htmlspecialchars(strip_tags($companyData["city"])));
    $stmt->bindParam(':verified', $verified);

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not create company"]));
      return $response->withStatus(500);
    }

    // create a company admin
    $sql = "INSERT INTO `companies` (`companyId`, `username`, `password`)
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

    $query = "SELECT * FROM `company_admins` WHERE username = :username AND verified = TRUE;";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', htmlspecialchars(strip_tags($data['username'])));
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    // clean var
    $db = null;

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "username or password did not match"]));
      return $response->withStatus(400);
    }

    $verified = password_verify($data["password"], $result->password);
    if (!$verified) {
      $response->getBody()->write(json_encode(["message" => "username or password did not match"]));
      return $response->withStatus(400);
    }

    $companyAdmin = [
      "companyId" => $result->companyId,
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
$app->get("/company/profile/{id}", function (Request $request, Response $response, array $args) {
  try {
    $companyId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM `companies` WHERE companyId = :companyId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':companyId', htmlspecialchars(strip_tags($companyId)));
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
});

/**
 * logout
 */
$app->put("/company/logout", function (Request $request, Response $response) {
  $response->getBody()->write(json_encode(["message" => "Successfully logged out"]));
  return $response->withStatus(200);
});


// below are used by admin
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
});

/**
 * verify a company
 */
$app->put("/company/verify", function (Request $req, Response $response) {
  try {
    $db = new Database();
    $conn = $db->connect();

    $sql = "UPDATE `companies` SET verified = TRUE;";
    $stmt = $conn->prepare($sql);
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
});
