<?php
require_once __DIR__ . '/INCLUDES/cors.php';
require __DIR__ . "/Routing.php";
$route = new Routing();
$route->run();