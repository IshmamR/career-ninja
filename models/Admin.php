<?php

require_once(__DIR__ . "/../utils/hash.php");

class Admin
{
  // DB stuff
  private $conn;
  private $table = 'admins';

  // private stuff
  private $ADMIN_ENCR;
  private $CIPHER_ALGO;
  private $ADMIN_COOKIE_KEY;

  // Admin properties
  public $id;
  public $username;
  public $password;
  public $type;

  // Constructor with DB
  public function __construct($db)
  {
    $this->conn = $db;

    $this->ADMIN_ENCR = $_ENV['ADMIN_ENCR'] ? $_ENV['ADMIN_ENCR'] : '@admin_passphrase';
    $this->CIPHER_ALGO = $_ENV['CIPHER_ALGO'] ? $_ENV['CIPHER_ALGO'] : 'AES-128-CTR';
    $this->ADMIN_COOKIE_KEY = $_ENV['ADMIN_COOKIE_KEY'] ? $_ENV['ADMIN_COOKIE_KEY'] : '@admin_auth_cookie';
  }

  // create admin table
  protected function createTable()
  {
    $sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
      `id` CHAR(36) PRIMARY KEY NOT NULL,
      `username` VARCHAR(255) NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      `type` VARCHAR(255) NOT NULL
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
      ' SET id = :id, username = :username, password = :password, type = :type';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->id = generateRandomUniqueId('@admin_'); // generates 36 random bytes
    $this->username = htmlspecialchars(strip_tags($this->username));
    $this->password = hashPassword(htmlspecialchars(strip_tags($this->password)));
    $this->type = "SECONDARY_ADMIN";

    // Bind data
    $stmt->bind_param(':id', $this->id);
    $stmt->bind_param(':username', $this->username);
    $stmt->bind_param(':password', $this->password);
    $stmt->bind_param(':type', $this->type);

    // Execute query
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  // read a single admin
  public function readSingle()
  {
    $query = 'SELECT * FROM ' .
      $this->table .
      ' WHERE username = ?
      LIMIT 0,1';

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param('s', htmlspecialchars(strip_tags($this->username)));

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
    $this->username = $row['username'];
    $this->type = $row['type'];
    $this->password = $row['password'];

    return true;
  }

  // verify
  public function verifyPassword($password)
  {
    $verified = password_verify($password, $this->password);
    return $verified;
  }

  // set login cookies
  public function setLoginCookies()
  {
    $admin = [
      "id" => $this->id,
      "type" => $this->type,
      "username" => $this->username,
    ];

    $arr_cookie_options = array(
      'expires' => time() + (86400 * 30), // 1 day
      'path' => '/',
      'domain' => null, // leading dot for compatibility or use subdomain
      'secure' => true,     // or false
      'httponly' => true,    // or false
      'samesite' => 'None' // None || Lax  || Strict
    );

    $authAdmin = json_encode($admin, JSON_UNESCAPED_SLASHES);
    $admin_token = openssl_encrypt($authAdmin, $this->CIPHER_ALGO, $this->ADMIN_ENCR);
    setcookie($this->ADMIN_COOKIE_KEY, $admin_token, $arr_cookie_options); // 1 day

    return $authAdmin;
  }

  // logout admin
  public function logout()
  {
    unset($_COOKIE[$this->ADMIN_COOKIE_KEY]);
  }

  // promote an admin
  public function promote()
  {
    $query = 'UPDATE ' .
      $this->table .
      ' SET type = :type
      WHERE id = :id';

    // Prepare statement
    $stmt = $this->conn->prepare($query);

    // Clean data
    $this->id = htmlspecialchars(strip_tags($this->id));
    $this->type = "SUPER_ADMIN";

    // Bind data
    $stmt->bind_param(':id', $this->id);
    $stmt->bind_param(':type', $this->type);

    // Execute query
    if ($stmt->execute()) {
      return true;
    }
    return false;
  }

  // delete admin
  public function delete()
  {
    $query = 'DELETE FROM ' .
      $this->table .
      ' WHERE id = :id';

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
