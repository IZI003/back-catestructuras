<?php
require_once __DIR__ . '/../INCLUDES/cors.php';
require_once __DIR__ . '/../MiLog.php';

class BaseController
{
    /**
     * __call magic method.
     */
    public function __call($name, $arguments)
    {
        $this->sendOutput('', array('HTTP/1.1 404 Not Found'));
    }

    /**
     * Get URI elements.
     * 
     * @return array
     */
    protected function getUriSegments()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        return $uri;
    }

    /**
     * Get querystring params.
     * 
     * @return array
     */
    protected function getQueryStringParams()
    {
        parse_str($_SERVER['QUERY_STRING'], $query);

        return $query;
    }

    protected function PostFromData()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            // Procesar JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
            }
            return $data;
        } elseif (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // Procesar datos URL-encoded
            parse_str(file_get_contents('php://input'), $data);
            return $data;
        }

        return []; // En caso de que no haya datos o no se reconozca el formato
    }   

    protected function PutFromData()
    {
        return $this->PostFromData(); // Misma lógica para PUT
    }

    /**
     * Send API output.
     * @param mixed  $data
     * @param string $httpHeader
     */
    protected function sendOutput($data, $httpHeaders = array())
    {
        header_remove('Set-Cookie');

        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }

        echo $data;
        exit;
    }
}