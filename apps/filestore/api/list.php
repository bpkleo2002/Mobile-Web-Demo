<?php
require_once('filestore.php');
session_start();
if (!isset($_SESSION['username'])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 401 Wrong username or password', true, 401);
    die();
}

$store = new FileStore('storage', false);
$userstore = $store->open($_SESSION['username']);
$list = $userstore->contents();

outputJSON($list);
?>


