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
