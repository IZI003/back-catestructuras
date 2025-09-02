<?php
require __DIR__ . '/../vendor/autoload.php'; 
use Dotenv\Dotenv;
//require_once __DIR__ . '/../MiLog.php';

//writeLog("cors.php INICIO");

// Detecta APP_ENV (viene de .htaccess o sistema)
$appEnv = getenv('APP_ENV') ?: 'prod';

// Carga el .env correspondiente
$dotenvFile = ".env.$appEnv";
if (file_exists(__DIR__ . "/../$dotenvFile")) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../', $dotenvFile);
    $dotenv->load();
} else {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

$allowedOrigin = getenv('CORS_ALLOWED_ORIGIN') ?: '*';
//writeLog("cors. Allowed Origin: $allowedOrigin");

// Encabezados de CORS completos
header("Access-Control-Allow-Origin: $allowedOrigin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Manejar preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}