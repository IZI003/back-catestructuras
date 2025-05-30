<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/ConfiguracionService.php';
require_once __DIR__ . '/../MiLog.php';

class ConfiguracionController extends BaseController
{
    public function categoria($method)
    {
            $Configuracion = new ConfiguracionService();
            $salida_error = new salidaError();
            $salida = new salida_error();
            writeLog("ConfiguracionController.categoria. INICIO");
            
            switch($method)
            {
                case 'post':
                {
                    $var = $this->PostFromData();
                        
                     try {
                        if(is_string($var['nombre']) && ( !isset($var['id_categoria']) || is_numeric($var['id_categoria'])))
                            { 
                                // writeLog("CarritoController.carrito. llamando al servicio ".json_encode($con));
                                
                                $resultado = $Configuracion->crearcategorias($var['nombre'],isset($var['id_categoria']) ? $var['id_categoria'] : 0);
                                            
                                if (!($resultado)) {
                                        $salida = $salida_error->response(404, '');
                                    } 
                                else {
                                        $salida = $salida_error->response(200, '');
                                        $salida->datos = $resultado;
                                    }
                            }else
                            {
                                writeLog("ConfiguracionController.categoria. Datos Incorrectos");
                                $salida = $salida_error->response(500, "Datos Incorrectos");  
                            }
                                    
                        } catch (Error $e) 
                            {
                                writeLog("ConfiguracionController.categoria. ".$e->getMessage());
                                $salida = $salida_error->response(500, $e->getMessage());
                            }
                        break;

                }
                case 'get':
                    {
                       // writeLog("CarritoController.carrito. POST");
                         try {
                                    // writeLog("CarritoController.carrito. llamando al servicio ".json_encode($con));
                                    $resultado = $Configuracion->verCategoria();
                                                
                                    if (!($resultado)) {
                                            $salida = $salida_error->response(204, '');
                                        } 
                                    else {
                                            $salida = $salida_error->response(200, '');
                                            $salida->datos = $resultado;
                                        }
                                        
                            } catch (Error $e) 
                                {
                                    writeLog("CarritoController.carrito, ".$e->getMessage());
                                    $salida = $salida_error->response(500, $e->getMessage());
                                }
                            break;
                    }
                case 'delete':
                    {
                           // writeLog("CarritoController.carrito. POST");                
                             try {
                                $uriSegments = $this->getUriSegments();
                                    foreach ($uriSegments as $segment) {
                                        if (is_numeric($segment)) {
                                            $id=(int)$segment;
                                            break;
                                        }
                                    }
                
                                    if($id>0)
                                    {
                                    writeLog("ConfiguracionController.categoria. por eliminar ".$id);
        
                                        $resultado = $Configuracion->eliminarCategoria($id);
                                                    
                                        if (!($resultado)) {
                                                $salida = $salida_error->response(404, '');
                                            } 
                                        else {
                                                $salida = $salida_error->response(200, '');
                                                $salida->datos = $resultado;
                                            }
                                    }else
                                    {
                                        writeLog("ConfiguracionController.categoria. Datos Incorrectos");
                                        $salida = $salida_error->response(500, "Datos Incorrectos");  
                                    }
                                            
                                } catch (Error $e) 
                                    {
                                        writeLog("ConfiguracionController.categoria. ".$e->getMessage());
                                        $salida = $salida_error->response(500, $e->getMessage());
                                    }
                                break;
                    }
                case 'put':
                    {
                        $var = $this->PutFromData();
                        
                        $uriSegments = $this->getUriSegments();
                        foreach ($uriSegments as $segment) {
                            if (is_numeric($segment)) {
                                $condicion=['id' =>(int)$segment ];
                                break;
                            }
                        }

                        try {
                            $con = [
                                'nombre' => $var['nombre'],
                                'id_categoria'=> $var['id_categoria'],
                            ];
                            
                            
                            $result = $Configuracion->putCategoria($con, $condicion);
                            if (!($result)) {
                                $salida = $salida_error->response(204, '');
                            } else {
                                $salida = $salida_error->response(200, '');
                                $salida->datos = $result;
                            }
                        } catch (Error $e) {
                            $salida = $salida_error->response(500, $e->getMessage());
                        }
                    }
            }
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
    }

    public function caracteristicas($method)
    {
        $Configuracion = new ConfiguracionService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        switch($method)
        {
            case 'post':
                $var = $this->PostFromData(); 
                $nombre = trim($var['nombre'] ?? '');
                if ($nombre === '') {
                    $salida = $salida_error->response(400, 'Nombre requerido');                    
                }
                else
                {                     
                    try {
                        $con= [
                            'nombre' => $nombre                       
                        ];
                        
                        $productos = $Configuracion->postCaracteristicas($con);
                       
                        if (!($productos)) {
                            $salida = $salida_error->response(204, '');
                        } 
                        else {
                            $con= [
                                'nombre' => $nombre,
                                'id' => $productos
                                                       
                            ];
                            $salida = $salida_error->response(200, '');
                            $salida->datos = $con;
                        }
                        
                    } catch (Error $e) 
                    {
                        writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                        $salida = $salida_error->response(500, $e->getMessage());
                    }                    
                }
                break;
            case 'get':
                        try {
                            $caracteristicas = $Configuracion->Lista_caracteristicas();
                            
                            if (!($caracteristicas)) {
                                $salida = $salida_error->response(204, '');
                            } else {
                                $salida = $salida_error->response(200, '');
                                $salida->datos = $caracteristicas;
                            }
                            
                        } catch (Error $e) {
                            $salida = $salida_error->response(500, $e->getMessage());
                        }
                     
                break;
        default:
        $salida = $salida_error->response(422, '');
        break;
                 
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
}