<?php
session_start();

if (!isset($_SESSION['teachers'])) {
    $_SESSION['teachers'] = [];
}

require 'api.php';

$api = new AssignmentAPI();
$api->handleRequest();
