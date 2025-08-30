<?php
// En ImagenController.php
/*class ImagenController {
    public function mostrarImagen() {
        // La lógica que mostramos en el script "ver-imagen.php"
        header("Access-Control-Allow-Origin: *");

        if (!isset($_GET['path']) || empty($_GET['path'])) {
            header("HTTP/1.1 400 Bad Request");
            echo "Error: No se proporcionó la ruta del archivo.";
            exit;
        }
        
        $basePath = '../../uploads/';
        $requestedFile = realpath($basePath . $_GET['path']);
        if (!$requestedFile || strpos($requestedFile, realpath($basePath)) !== 0) {
            header("HTTP/1.1 403 Forbidden");
            echo "Acceso no permitido.";
            exit;
        }
        
        if (!file_exists($requestedFile)) {
            header("HTTP/1.1 404 Not Found");
            echo "Error: Archivo no encontrado.";
            exit;
        }
        
        $mimeType = mime_content_type($requestedFile);
        if (!preg_match('/^image\//', $mimeType)) {
            header("HTTP/1.1 400 Bad Request");
            echo "Error: El archivo no es una imagen.";
            exit;
        }
        
        header("Content-Type: $mimeType");
        header("Content-Length: " . filesize($requestedFile));
        header("Cache-Control: public, max-age=86400");
        
        readfile($requestedFile);
        exit;
    }
}*/