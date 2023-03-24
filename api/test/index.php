<?php

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $bytes = openssl_random_pseudo_bytes(10);
  echo $bytes;
  echo "<br />";
  $hex = bin2hex($bytes);
  echo $hex;
  echo "<br />";
  echo strlen($hex);
  echo "<br />";
  echo $bytes . $hex;
}
