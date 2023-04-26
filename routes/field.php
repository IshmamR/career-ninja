<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// all json response
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->get("/field/all", function (Request $request, Response $response) {
  try {
    $db = new Database();
    $conn = $db->connect();

    $params = $request->getQueryParams();
    $limit = isset($params['limit']) ? intval($params['limit']) : 10;
    $page = isset($params['page']) ? intval($params['page']) : 1;
    $page = max($page, 1); // Ensure page number is not negative

    // Calculate the offset for the current page
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM `fields` LIMIT :offset, :limit;";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

    $total_items_sql = "SELECT COUNT(*) FROM `fields`;";
    $total_items = $conn->query($total_items_sql)->fetchColumn();

    $db = null;

    $response->getBody()->write(json_encode([
      "fields" => $rows,
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


$app->post("/field/add", function (Request $request, Response $response) {
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


$app->delete("/field/{id}", function (Request $request, Response $response, array $args) {
  try {
    $fieldId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "DELETE FROM `fields` WHERE id = :id;";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', htmlspecialchars(strip_tags($fieldId)));

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not delete field"]));
      return $response->withStatus(500);
    }

    $db = null;

    $response->getBody()->write(json_encode([
      "message" => "field deleted successfully",
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
