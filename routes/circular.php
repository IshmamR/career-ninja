<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// all json response
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->get("/circular/all", function (Request $request, Response $response) {
  try {
    $params = $request->getQueryParams();

    $title = isset($params['search']) ? $params['search'] : '';
    $limit = isset($params['limit']) ? intval($params['limit']) : 10;
    $page = isset($params['page']) ? intval($params['page']) : 1;
    $page = max($page, 1); // Ensure page number is not negative

    // Calculate the offset for the current page
    $offset = ($page - 1) * $limit;

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT 
        circulars.id, circulars.title, circulars.description, circulars.jobType, 
        circulars.location, circulars.vacancy, circulars.salaryRangeStart, circulars.salaryRangeEnd,
        circulars.isSalaryNegotiable, circulars.isActive, circulars.externalLink, circulars.createdAt, 
        companies.id AS companyId, companies.title AS companyTitle, 
        fields.id AS fieldId, fields.title AS fieldTitle
      FROM circulars 
      INNER JOIN fields ON fields.id = circulars.fieldId 
      INNER JOIN companies ON companies.id = circulars.companyId 
      WHERE 
        (circulars.title LIKE :title OR circulars.jobType LIKE :title)
        AND (NOT :companyId OR circulars.companyId = :companyId)
        AND (NOT :fieldId OR circulars.fieldId = :fieldId)
      LIMIT :offset, :limit;";

    $fieldId = isset($params['fieldId']) ? $params['fieldId'] : '';
    $companyId = isset($params['companyId']) ? $params['companyId'] : '';

    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bindValue(':title', "%$title%", PDO::PARAM_STR);
    $stmt->bindValue(':companyId', $companyId, PDO::PARAM_STR);
    $stmt->bindValue(':fieldId', $fieldId, PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

    $total_items_sql = "SELECT COUNT(*) FROM `circulars`;";
    $total_items = $conn->query($total_items_sql)->fetchColumn();

    $db = null;

    $response->getBody()->write(json_encode([
      "circulars" => $rows,
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


$app->post("/circular/add", function (Request $request, Response $response) {
  try {
    $authCompanyAdmin = $request->getAttribute('authCompanyAdmin');
    $companyId = $authCompanyAdmin->companyId;

    $json = $request->getBody();
    $body = json_decode($json, true);

    if (empty($body['fieldId'])) {
      $response->getBody()->write(json_encode(["message" => "Please fill up all required fields"]));
      return $response->withStatus(400);
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "INSERT INTO `circulars` (
      `id`, `companyId`, `fieldId`, `title`, `description`, `vacancy`, `location`, `jobType`,
      `salaryRangeStart`, `salaryRangeEnd`, `isSalaryNegotiable`, `isActive`, `externalLink`
    ) VALUES (
      :id, :companyId, :fieldId, :title, :description, :vacancy, :location, :jobType,
      :salaryRangeStart, :salaryRangeEnd, :isSalaryNegotiable, :isActive, :externalLink
    )";

    $stmt = $conn->prepare($sql);

    $circularId = generateRandomUniqueId("@crclar");
    $isActive = TRUE;

    $stmt->bindParam(':id', $circularId);
    $stmt->bindParam(':companyId', $companyId);
    $stmt->bindParam(':fieldId', htmlspecialchars(strip_tags($body['fieldId'])));
    $stmt->bindParam(':title', htmlspecialchars(strip_tags($body['title'])));
    $stmt->bindParam(':description', htmlspecialchars(strip_tags($body['description'])));
    $stmt->bindParam(':vacancy', htmlspecialchars(strip_tags($body['vacancy'])));
    $stmt->bindParam(':location', htmlspecialchars(strip_tags($body['location'])));
    $stmt->bindParam(':jobType', htmlspecialchars(strip_tags($body['jobType'])));
    $stmt->bindParam(':salaryRangeStart', htmlspecialchars(strip_tags($body['salaryRangeStart'])));
    $stmt->bindParam(':salaryRangeEnd', htmlspecialchars(strip_tags($body['salaryRangeEnd'])));
    $stmt->bindParam(':isSalaryNegotiable', htmlspecialchars(strip_tags($body['isSalaryNegotiable'])));
    $stmt->bindParam(':isActive', $isActive);
    $stmt->bindParam(':externalLink', htmlspecialchars(strip_tags($body['externalLink'])));

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "failed to create circular"]));
      return $response->withStatus(500);
    }

    // fetch the inserted row's data
    $sql = "SELECT * FROM `circulars` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $circularId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $db = null;

    $response->getBody()->write(json_encode($row));
    return $response->withStatus(201);
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

$app->put("/circular/update/{id}", function (Request $request, Response $response, array $args) {
  try {
    $circularId = $args['id'];

    $json = $request->getBody();
    $body = json_decode($json, true);

    $db = new Database();
    $conn = $db->connect();

    // Define the base SQL query
    $sql = "UPDATE `circulars` SET ";

    // Initialize an array to store the bind parameters
    $params = array();

    // Loop through the fields that may be updated
    $updateFields = array(
      'companyId', 'fieldId', 'title', 'description', 'vacancy', 'location', 'jobType',
      'salaryRangeStart', 'salaryRangeEnd', 'isSalaryNegotiable', 'externalLink'
    );

    foreach ($updateFields as $key) {
      // Check if the field is present in the request body
      if (isset($body[$key])) {
        // Add the field to the SQL query and bind its value as a parameter
        $sql .= "`$key` = :$key, ";
        $params[":$key"] = htmlspecialchars(strip_tags($body[$key]));
      }
    }

    // Check if any fields were updated
    if (count($params) > 0) {
      // Remove the trailing comma from the SQL query
      $sql = rtrim($sql, ', ');

      // Add the WHERE clause to specify the row to update
      $sql .= " WHERE `id` = :id";
      $params[':id'] = $circularId;

      // Prepare and execute the SQL query
      $stmt = $conn->prepare($sql);
      $stmt->execute($params);
    }

    // fetch the inserted row's data
    $sql = "SELECT * FROM `circulars` WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $circularId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $db = null;

    $response->getBody()->write(json_encode($row));
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

$app->put("/circular/toggle-active/{id}", function (Request $request, Response $response, array $args) {
  try {
    $circularId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT `isActive` FROM `circulars` WHERE `id` = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $circularId);
    $stmt->execute();
    $isActive = $stmt->fetchColumn();

    // Then, toggle the value and update the row
    $newIsActive = !$isActive;
    $sql = "UPDATE `circulars` SET `isActive` = :isActive WHERE `id` = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':isActive', $newIsActive);
    $stmt->bindParam(':id', $circularId);
    $stmt->execute();

    // Fetch the updated row's data
    $sql = "SELECT * FROM `circulars` WHERE `id` = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $circularId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $db = null;

    $response->getBody()->write(json_encode($row));
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

$app->delete("/circular/{id}", function (Request $request, Response $response, array $args) {
  try {
    $circularId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "DELETE FROM `circulars` WHERE id = :id;";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', htmlspecialchars(strip_tags($circularId)));

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not delete circular"]));
      return $response->withStatus(500);
    }

    $db = null;

    $response->getBody()->write(json_encode([
      "message" => "circular deleted successfully",
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
