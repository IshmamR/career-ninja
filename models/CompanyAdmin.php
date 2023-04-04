<?php

require_once(__DIR__ . "/../utils/hash.php");

class CompanyAdmin
{
  // DB stuff
  private $conn;
  private $table = 'companyAdmins';

  private $COMPN_ENCR;
  private $CIPHER_ALGO;
  private $COMPN_COOKIE_KEY;

  public $companyId;
  public $username;
  public $password;

  public function __construct($db)
  {
    $this->conn = $db;

    $this->COMPN_ENCR = $_ENV['COMPN_ENCR'] ? $_ENV['COMPN_ENCR'] : '@cmpny_passphrase';
    $this->CIPHER_ALGO = $_ENV['CIPHER_ALGO'] ? $_ENV['CIPHER_ALGO'] : 'AES-128-CTR';
    $this->COMPN_COOKIE_KEY = $_ENV['COMPN_COOKIE_KEY'] ? $_ENV['COMPN_COOKIE_KEY'] : '@cmpny_auth_cookie';
  }

  // create company table
  protected function createTable()
  {
    $sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
      `companyId` CHAR(36) PRIMARY KEY NOT NULL,
      `username` VARCHAR(255) UNIQUE NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      PRIMARY KEY (companyId, username),
      UNIQUE (username),
      FOREIGN KEY (companyId) REFERENCES companies(id)
    );";

    $result = $this->conn->query($sql);

    if ($result === TRUE) {
      return true;
    }
    return false;
  }

  // create a secondary admin
  public function create()
  {
    // Create query
    $query = 'INSERT INTO ' .
      $this->table .
      ' SET companyId = ?, username = ?, password = ?';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->username = htmlspecialchars(strip_tags($this->username));
    $this->password = hashPassword(htmlspecialchars(strip_tags($this->password)));

    // Bind data
    $stmt->bind_param(
      'sss',
      $this->companyId,
      $this->username,
      $this->password
    );

    // Execute query
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }
}
