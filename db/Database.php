<?php

class Database
{
  private $MYSQL_HOST = '';
  private $MYSQL_PORT = '';
  private $MYSQL_USER = '';
  private $MYSQL_DTBS = '';
  private $MYSQL_PASS = '';
  private $conn;

  public function __construct()
  {
    $this->MYSQL_HOST = $_ENV['MYSQL_HOST'] ? $_ENV['MYSQL_HOST'] : 'localhost';
    $this->MYSQL_PORT = $_ENV['MYSQL_PORT'] ? $_ENV['MYSQL_PORT'] : '';
    $this->MYSQL_USER = $_ENV['MYSQL_USER'] ? $_ENV['MYSQL_USER'] : 'root';
    $this->MYSQL_PASS = $_ENV['MYSQL_PASS'] ? $_ENV['MYSQL_PASS'] : '1234';
    $this->MYSQL_DTBS = $_ENV['MYSQL_DTBS'] ? $_ENV['MYSQL_DTBS'] : 'careeer';
    $this->conn = null;
  }


  public function connect()
  {
    $this->conn = mysqli_init();
    if (!$this->conn) {
      http_response_code(500);
      die('mysqli_init failed');
    }

    if (!$this->conn->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
      http_response_code(500);
      die('Setting MYSQLI_INIT_COMMAND failed');
    }

    if (!$this->conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
      http_response_code(500);
      die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
    }

    if ($this->MYSQL_PORT) {
      if (!$this->conn->real_connect(
        $this->MYSQL_HOST,
        $this->MYSQL_USER,
        $this->MYSQL_PASS,
        $this->MYSQL_DTBS,
        $this->MYSQL_PORT
      )) {
        http_response_code(500);
        die('Connect Error (' . mysqli_connect_errno() . ') '
          . mysqli_connect_error());
      }
    } else {
      if (!$this->conn->real_connect(
        $this->MYSQL_HOST,
        $this->MYSQL_USER,
        $this->MYSQL_PASS,
        $this->MYSQL_DTBS,
      )) {
        http_response_code(500);
        die('Connect Error (' . ':' . $this->MYSQL_DTBS . ':' . mysqli_connect_errno() . ') '
          . mysqli_connect_error());
      }
    }

    return $this->conn;
  }
}
