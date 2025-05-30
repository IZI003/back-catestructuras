<?php
$allowed_origins = ['http://localhost:4200', 'http://catestructuras'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}


// Encabezados para permitir CORS
//header("Access-Control-Allow-Origin: http://catestructuras"); // Permite solicitudes desde tu frontend
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Encabezados permitidos
//header("Access-Control-Allow-Credentials: true"); // Permite cookies/sesiones si es necesario

// Manejar solicitudes preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  //  header("Access-Control-Allow-Origin: http://catestructuras");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
  //  header("Access-Control-Allow-Credentials: true");
    http_response_code(200);
    exit();
}