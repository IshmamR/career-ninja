<?php

echo $_SERVER['DOCUMENT_ROOT'];
echo " \n ";
echo __DIR__;

$env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env') or $_ENV;

$MYSQL_HOST = $env['MYSQL_HOST'];
$MYSQL_PORT = $env['MYSQL_PORT'];
$MYSQL_USER = $env['MYSQL_USER'];
$MYSQL_PASS = $env['MYSQL_PASS'];
$MYSQL_DTBS = $env['MYSQL_DTBS'];

$ADMIN_ENCR = $env['ADMIN_ENCR'];
$APPLT_ENCR = $env['APPLT_ENCR'];
$COMPN_ENCR = $env['COMPN_ENCR'];

$CIPHER_ALGO = $env['CIPHER_ALGO'];

$ADMIN_COOKIE_KEY = $env['ADMIN_COOKIE_KEY'];
$APPLT_COOKIE_KEY = $env['APPLT_COOKIE_KEY'];
$COMPN_COOKIE_KEY = $env['COMPN_COOKIE_KEY'];