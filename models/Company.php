<?php

require_once(__DIR__ . "/../utils/hash.php");

class Company
{
  // DB stuff
  private $conn;
  private $table = 'companies';

  // company properties
  public $id;
  public $title;
  public $logo;
  public $cover;
  public $email;
  public $description;
  public $contact;
  public $website;
  public $country;
  public $city;
  public $address;
  public $verified;

  public function __construct($db)
  {
    $this->conn = $db;
  }

  // create company table
  protected function createTable()
  {
    $sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
      `id` CHAR(36) PRIMARY KEY NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `logo` TEXT,
      `cover` TEXT,
      `email` VARCHAR(255) NOT NULL,
      `description` VARCHAR(255),
      `contact` VARCHAR(255) NOT NULL,
      `website` TEXT,
      `country` VARCHAR(6) NOT NULL,
      `city` VARCHAR(50) NOT NULL,
      `address` TEXT,
      `verified` BOOLEAN
    );";

    $result = $this->conn->query($sql);

    if ($result === TRUE) {
      return true;
    }
    return false;
  }

  // create company
  public function create()
  {
    $query = 'INSERT INTO ' .
      $this->table .
      ' SET id = ?, title = ?, description = ?, email = ?, contact = ?, website = ?, 
      country = ?, city = ?, address = ?, verified = FALSE';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->id = generateRandomUniqueId('@cmpny_'); // generates 36 random bytes
    $this->title = htmlspecialchars(strip_tags($this->title));
    $this->description = htmlspecialchars(strip_tags($this->description));
    $this->email = htmlspecialchars(strip_tags($this->email));
    $this->contact = htmlspecialchars(strip_tags($this->contact));
    $this->website = htmlspecialchars(strip_tags($this->website));
    $this->country = htmlspecialchars(strip_tags($this->country));
    $this->city = htmlspecialchars(strip_tags($this->city));
    $this->address = htmlspecialchars(strip_tags($this->address));

    $stmt->bind_param(
      'sssssssss',
      $this->id,
      $this->title,
      $this->description,
      $this->email,
      $this->contact,
      $this->website,
      $this->country,
      $this->city,
      $this->address
    );

    // Execute query
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  // read company profile
  public function getProfile()
  {
    $query = 'SELECT * FROM ' .
      $this->table .
      ' WHERE id = ?
      LIMIT 0,1';

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param('s', htmlspecialchars(strip_tags($this->id)));

    if (!$stmt->execute()) {
      return false;
    }

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
      return false;
    }

    $row = $result->fetch_assoc();

    // Set properties
    $this->id = $row['id'];
    $this->title = $row['title'];
    $this->description = $row['description'];
    $this->logo = $row['logo'];
    $this->cover = $row['cover'];
    $this->email = $row['email'];
    $this->contact = $row['contact'];
    $this->website = $row['website'];
    $this->country = $row['country'];
    $this->city = $row['city'];
    $this->address = $row['address'];

    return true;
  }

  // read company list
  public function readCompanies($pageNo = 1, $limit = 5)
  {
    $offset = ($pageNo - 1) * $limit;

    $query = "SELECT count(*) FROM " . $this->table . ";";

    $countResult = $this->conn->query($query);
    $row = $countResult->fetch_row();

    $total = $row[0];

    $finalQuery = "SELECT * FROM " . $this->table .
      " LIMIT ?,?;";

    $stmt = $this->conn->prepare($finalQuery);

    $stmt->bind_param(
      'ii',
      htmlspecialchars(strip_tags($offset)),
      htmlspecialchars(strip_tags($limit))
    );

    if (!$stmt->execute()) {
      return false;
    }

    $result = $stmt->get_result();

    $rowsArray = array();

    while ($r = $result->fetch_assoc()) {
      $rowsArray[] = $r;
    }

    return ["companies" => $rowsArray, "count" => $total];
  }

  public function verifyCompany()
  {
    $this->id = htmlspecialchars(strip_tags($this->id));

    $query = 'UPDATE ' .
      $this->table .
      ' SET verified = TRUE
      WHERE id = :id';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->id = htmlspecialchars(strip_tags($this->id));

    // Bind data
    $stmt->bind_param(':id', $this->id);

    // Execute query
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
}
