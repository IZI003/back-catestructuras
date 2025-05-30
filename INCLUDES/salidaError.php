<?php
class salidaError
{
    private $CodError;

    public function error()
    {
        header('HTTP/1.0 400 Bad Request; charset=utf-8');
    }

    public function sendOutput($data, $httpHeaders = array())
        {
            header_remove('Set-Cookie');
            if (is_array($httpHeaders) && count($httpHeaders)) {
                foreach ($httpHeaders as $httpHeader) {
                    header($httpHeader);
                }
            }

            echo $data; // Envía la respuesta al cliente
            exit; // Detiene la ejecución
        }

    public function response($numero_error, $mensaje_ex)
    {
        $mens_error = new salida_error();
        $mens_error->error = new Error_class();

        switch ($numero_error) {
            case 400:
                http_response_code(400);
                $mens_error->error->Descripcion = '¡Algo salió mal! ' . $mensaje_ex;
                $mens_error->error->CodigoHttp = 'Content-Type: application/json;HTTP/1.1 400 Error interno; charset=utf-8';
                $mens_error->estado = 'fail';
                break;
            case 401:
                    http_response_code(401);
                    $mens_error->error->Descripcion = $mensaje_ex;
                    $mens_error->error->CodigoHttp = 'Content-Type: application/json;HTTP/1.1 401 Unauthorized; charset=utf-8';
                    $mens_error->estado = 'fail';
                    break;
            case 422:
                http_response_code(422);
                $mens_error->error->Descripcion = 'Method not supported';
                $mens_error->error->CodigoHttp = 'Content-Type: application/json;HTTP/1.1 422 Unprocessable Entity; charset=utf-8';
                $mens_error->estado = 'fail';
                break;
            case 500:
                http_response_code(500);
                $mens_error->error->Descripcion = "¡Algo salió mal! Póngase en contacto con soporte. " . $mensaje_ex;
                $mens_error->error->CodigoHttp = "Content-Type: application/json;HTTP/1.1 500 Internal Server Error; charset=utf-8";
                $mens_error->estado = 'fail';
                break;
            case 204:
                http_response_code(204);
                $mens_error->error->Descripcion = 'No se hallaron datos.';
                $mens_error->error->CodigoHttp = 'Content-Type: application/json;HTTP/1.1 204  No content; charset=utf-8';
                $mens_error->estado = 'fail';
                break;
            case 200:
                http_response_code(200);
                $mens_error->error->Descripcion = 'Content-Type: application/json; charset=utf-8';
                $mens_error->error->CodigoHttp = 'HTTP/1.1 200 OK';
                $mens_error->estado = 'OK';
                
                break;
           

            default:
                http_response_code(500);
                $mens_error->error->Descripcion = "¡Algo salió mal! Póngase en contacto con soporte. " . $mensaje_ex;
                $mens_error->error->CodigoHttp = "Content-Type: application/json;HTTP/1.1 500 Internal Server Error; charset=utf-8";
                $mens_error->estado = 'fail';
                break;
        }

        return $mens_error;
    }
}

class salida_error
{
    public $estado;
    public $error;
    public $datos;
}

class Error_class
{
    public $CodigoHttp;
    public $CodigoInterno;
    public $Descripcion;
}
class salida_modelo
{
    public $Error;
    public $Datos;
}