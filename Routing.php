<?php
require_once __DIR__ . '/INCLUDES/cors.php';
require __DIR__ . "/INCLUDES/Paquete.php";
require __DIR__ . "/INCLUDES/salidaError.php";
require_once __DIR__ . '/MiLog.php';

class Routing
{
    private $routes = array();
    private $controller;
    private $action;
    private $method;
    
    public function __construct()
    {       
        $uri = substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), strlen(dirname($_SERVER['SCRIPT_NAME'])));

        $this->MachRoute($uri);
    }

    public function MachRoute($uri)
    {
        $var = explode('/', $uri);
        $this->controller = ucfirst(strtolower($var[1])) . 'Controller';
        $this->action = strtolower($var[2]);
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
        
        if($this->controller != 'LoginController')
        { 
            $salida = new salida_error();
            $Error= new salidaError();
            $salida_error= new salidaError();
            
            $headers = getallheaders();
            $jwt = isset($headers['Authorization']) ? $headers['Authorization'] : null;
            if (empty($jwt))
                {
                    $salida = $salida_error->response(401, "No autoriza : Formato de token incorrecto.");
                $this->sendOutput(
                    json_encode($salida),
                    array($salida->error->Descripcion, $salida->error->CodigoHttp)
                    );
             }
                
            if (!preg_match('/Bearer\s(\S+)/', $jwt, $matches)) 
                {
                    $salida = $salida_error->response(401, "No autorizado: Formato de token incorrecto.");
                    $this->sendOutput(
                        json_encode($salida),
                        array($salida->error->Descripcion, $salida->error->CodigoHttp)
                        );
                }
                
                $token = str_replace( 'Bearer ','',$jwt);
               
                require_once  __DIR__ . "/Modelos/JWTmodel.php";
                $JWTmodel = new JWTmodel();
               $token = trim($token, '"');
            //   writeLog("Routing.MachRoute. ".$token);

                $decodedToken = $JWTmodel->getdesencripte_manual($token);
              //  writeLog("Routing.MachRoute. ".json_encode($decodedToken));
                
                if (isset($decodedToken->userId)) 
                {
                    if (file_exists(__DIR__ . "/Controllers/{$this->controller}.php")) 
                    {
                        require_once __DIR__ . "/Controllers/{$this->controller}.php";
                    } else 
                    {
                        header("HTTP/1.1 404 Not Found");
                    }
                }else
                {
               // writeLog("Routing.MachRoute. entro en el error");

                    $salida = $salida_error->response(401, "No autorizado.");
                    $this->sendOutput(
                        json_encode($salida),
                        array($salida->error->Descripcion, $salida->error->CodigoHttp)
                        );
                  }
        } else
            {

                if (file_exists(__DIR__ . "/Controllers/{$this->controller}.php")) 
                {
                    require_once __DIR__ . "/Controllers/{$this->controller}.php";
                } else 
                {
                    header("HTTP/1.1 404 Not Found");
                }
                /*if($this->controller == 'LoginController')
                { 
                    require_once __DIR__ . "/Controllers/LoginController.php";
                }
                if($this->controller ==  'TiendaController')
                { 
                    require_once __DIR__ . "/Controllers/TiendaController.php";
                }
                if($this->controller ==  'ChatController')
                { 
                    require_once __DIR__ . "/Controllers/ChatController.php";
                }*/
                
            }
    }
        
    public function run()
    {
        if (class_exists($this->controller)) {

            $controller = new $this->controller();

            if (method_exists($controller, $this->action)) {
                $controller->{$this->action}($this->method);
            } else {
                header("HTTP/1.1 404 Not Found");
            }
        } else {
            header("HTTP/1.1 404 Not Found");
        }
    }
    private function sendOutput($data, $httpHeaders = array())
    {
        if (is_array($httpHeaders) && count($httpHeaders)) {
            foreach ($httpHeaders as $httpHeader) {
                header($httpHeader);
            }
        }

        echo $data;
        exit;
    }
}