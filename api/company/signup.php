<?php

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once(__DIR__ . '/../../db/Database.php');
require_once(__DIR__ . '/../../models/Company.php');
require_once(__DIR__ . '/../../models/CompanyAdmin.php');

$database = new Database();
$conn = $database->connect();

$company = new Company($conn);
$companyAdmin = new CompanyAdmin($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // receive posted json body
  $data = json_decode(file_get_contents('php://input'), true);

  $companyData = $data['company'];
  $companyAdminData = $data['companyAdmin'];

  $company->title = $companyData['title'];
  $company->description = $companyData['description'];
  $company->email = $companyData['email'];
  $company->contact = $companyData['contact'];
  $company->website = $companyData['website'];
  $company->country = $companyData['country'];
  $company->city = $companyData['city'];
  $company->address = $companyData['address'];

  if (!$company->create()) {
    http_response_code(500);
    echo json_encode(["message" => "could not create company profile"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  $companyAdmin->companyId = $company->id;
  $companyAdmin->username = $companyAdminData['username'];
  $companyAdmin->companyId = $companyAdminData['password'];

  if (!$companyAdmin->create()) {
    http_response_code(500);
    echo json_encode(["message" => "could not create company profile"], JSON_UNESCAPED_SLASHES);
    exit;
  }

  http_response_code(201);
  echo json_encode([
    "message" =>
    "Your company details have been submitted for review. You will get an email after it is verified."
  ], JSON_UNESCAPED_SLASHES);
  exit;
}
