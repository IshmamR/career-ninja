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

    $companyId = $params['companyId'];

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
      LIMIT :offset, :limit;";

    if (isset($companyId) && $companyId !== "") {
      $sql .= " WHERE companyId = $companyId";
    }
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bindValue(':title', "%$title%", PDO::PARAM_STR);
    $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
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
    $json = $request->getBody();
    $data = json_decode($json, true);

    if (empty($data['title'])) {
      $response->getBody()->write(json_encode(["message" => "field title is required"]));
      return $response->withStatus(400);
    }

    $fieldId = generateRandomUniqueId("@fields");
    $fieldTitle = htmlspecialchars(strip_tags($data["title"]));

    $db = new Database();
    $conn = $db->connect();

    $sql = "INSERT INTO `fields` (`id`, `title`) VALUES (:id, :title);";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', $fieldId);
    $stmt->bindParam(':title', $fieldTitle);

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not create field"]));
      return $response->withStatus(500);
    }

    $db = null;

    $response->getBody()->write(json_encode([
      "id" => $fieldId,
      "title" => $fieldTitle
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
});
