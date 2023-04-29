<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// all json response
$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->get("/skill/all", function (Request $request, Response $response) {
  try {
    $db = new Database();
    $conn = $db->connect();

    $params = $request->getQueryParams();
    $limit = isset($params['limit']) ? intval($params['limit']) : 100;
    $page = isset($params['page']) ? intval($params['page']) : 1;
    $page = max($page, 1); // Ensure page number is not negative

    // Calculate the offset for the current page
    $offset = ($page - 1) * $limit;

    $sql = "SELECT skills.*, fields.title AS field
      FROM skills
      JOIN fields ON skills.fieldId = fields.id 
      LIMIT :offset, :limit;";

    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);

    $total_items_sql = "SELECT COUNT(*) FROM `skills`;";
    $total_items = $conn->query($total_items_sql)->fetchColumn();

    $db = null;

    $response->getBody()->write(json_encode([
      "skills" => $rows,
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


$app->post("/skill/add", function (Request $request, Response $response) {
  try {
    $body = $request->getParsedBody();

    if (empty($body['title']) || empty($body['fieldId'])) {
      $response->getBody()->write(json_encode(["message" => "skill title is required"]));
      return $response->withStatus(400);
    }

    $skillId = generateRandomUniqueId("@skills");
    $skillTitle = htmlspecialchars(strip_tags($body["title"]));
    $fieldId = htmlspecialchars(strip_tags($body['fieldId']));
    $skillLogo = '/static/skills/__demo__.png';

    // get file
    $files = $request->getUploadedFiles();
    $imageUploaded = $files["image"];
    if (isset($imageUploaded)) {
      $mediaType = $imageUploaded->getClientMediaType();
      $extension = substr($mediaType, 6);
      $skillLogo = "/static/skills/" . $skillId . "." .  $extension;
      $path = __DIR__ . "/../public" . $skillLogo;
      $imageUploaded->moveTo($path);
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "INSERT INTO `skills` (`id`, `fieldId`, `title`, `logo`) 
      VALUES (:id, :fieldId, :title, :logo);";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', $skillId);
    $stmt->bindParam(':fieldId', $fieldId);
    $stmt->bindParam(':title', $skillTitle);
    $stmt->bindParam(':logo', $skillLogo);

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not create skill"]));
      return $response->withStatus(500);
    }

    $db = null;

    $response->getBody()->write(json_encode([
      "id" => $skillId,
      "fieldId" => $fieldId,
      "title" => $skillTitle,
      "logo" => $skillLogo
    ]));
    return $response->withStatus(201);
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


$app->delete("/skill/{id}", function (Request $request, Response $response, array $args) {
  try {
    $skillId = $args['id'];

    $db = new Database();
    $conn = $db->connect();

    $sql = "DELETE FROM `skills` WHERE id = :id;";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':id', htmlspecialchars(strip_tags($skillId)));

    $result = $stmt->execute();

    if (!$result) {
      $response->getBody()->write(json_encode(["message" => "Could not delete skill"]));
      return $response->withStatus(500);
    }

    $db = null;

    $response->getBody()->write(json_encode([
      "message" => "skill deleted successfully",
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
