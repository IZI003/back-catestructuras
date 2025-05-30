<?php
// ver-imagen.php

// Permitir acceso desde cualquier origen (CORS)
header("Access-Control-Allow-Origin: *");

// La carpeta base donde se encuentran las imágenes (debe estar en la misma ruta que este script)
$baseDir = realpath(__DIR__ . '/uploads');
if ($baseDir === false) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error en la configuración del servidor (no se encontró la carpeta uploads).";
    exit;
}

// Verifica que se haya enviado el parámetro "path"
// Nota: El parámetro 'path' debe ser relativo a la carpeta uploads.
// Por ejemplo, si la imagen está en uploads/123/imagen.png, entonces path debe ser "123/imagen.png".
if (!isset($_GET['path']) || empty($_GET['path'])) {
    header("HTTP/1.1 400 Bad Request");
    echo "Error: No se proporcionó la ruta del archivo.";
    exit;
}

// Construir la ruta completa a partir del parámetro 'path'
$relativeRequestedPath = $_GET['path'];
$requestedFile = realpath($baseDir . '/' . $relativeRequestedPath);

// Verificar que el archivo exista y que realmente esté dentro de la carpeta uploads
if ($requestedFile === false || strpos($requestedFile, $baseDir) !== 0) {
    header("HTTP/1.1 403 Forbidden");
    echo "Acceso no permitido.";
    exit;
}

// Verificar que el archivo exista
if (!file_exists($requestedFile)) {
    header("HTTP/1.1 404 Not Found");
    echo "Error: Archivo no encontrado.";
    exit;
}

// Obtener el tipo MIME del archivo
$mimeType = mime_content_type($requestedFile);
if (!preg_match('/^image\//', $mimeType)) {
    header("HTTP/1.1 400 Bad Request");
    echo "Error: El archivo no es una imagen.";
    exit;
}

// Enviar los encabezados adecuados
header("Content-Type: $mimeType");
header("Content-Length: " . filesize($requestedFile));
header("Cache-Control: public, max-age=86400"); // Cachea por 1 día

// Leer y enviar el contenido del archivo
readfile($requestedFile);
exit;
?>