<?php

function hashPassword($password)
{
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  return $hashed_password;
}

function verifyPassword($password, $hashed_password)
{
  return password_verify($password, $hashed_password);
}

/**
 * @param string $prefix
 * @description generates 29(13 + 16) random bytes without prefix. 
 */
function generateRandomUniqueId($prefix)
{
  $bytes = uniqid($prefix); // generate 13 bytes + prefix;
  $rnHex = bin2hex(openssl_random_pseudo_bytes(8)); // generate 16 bytes
  return $bytes . $rnHex;
}
