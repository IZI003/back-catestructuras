<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/AsistenciaService.php';
require_once __DIR__ . '/../MiLog.php';

class AsistenciaController extends BaseController
{
    public function VerAsistencia_rango_fecha($method)
    {
        $asistencia_serv = new AsistenciaService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {

            $var = $this->getQueryStringParams();
                 try 
                 {
                    writeLog("AsistenciaController.VerAsistencia. por llamar a  lista_asistencia");

                    $asistencias = $asistencia_serv->lista_asistencia($var['fecha_desde'],$var['fecha_hasta']);
                    
                    if (!($asistencias)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $asistencias;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("AsistenciaController. ".$e->getMessage());
                    $salida = $salida_error->response(500, $e->getMessage());
                }
        }

        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function VerAsistencia_persona_mes($method)
    {
        $asistencia_serv = new AsistenciaService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {

            $var = $this->getQueryStringParams();
                 try 
                 {
                    writeLog("AsistenciaController.VerAsistencia_persona_mes. por llamar a  lista_asistencia");

                    $asistencias = $asistencia_serv->lista_asistencia_por_legajo($var['legajo'], $var['anio'], $var['mes']);
                    
                    if (!($asistencias)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $asistencias;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("AsistenciaController. ".$e->getMessage());
                    $salida = $salida_error->response(500, $e->getMessage());
                }
        }

        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    public function Ver_lista_Personal($method)
    {
        $asistencia_serv = new AsistenciaService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
                 try 
                 {
                    $asistencias = $asistencia_serv->lista_Personal();
                    if (!($asistencias)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $asistencias;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("AsistenciaController. ".$e->getMessage());
                    $salida = $salida_error->response(500, $e->getMessage());
                }
        }

        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function personal($method)
    {
        $asistencia_serv = new AsistenciaService();
        $salida_error = new salidaError();
        $salida = new salida_error();
         $httpMethod = $_SERVER['REQUEST_METHOD']; 
        switch ($httpMethod)
        {
            case 'delete':
                    $uriSegments = $this->getUriSegments();
                            foreach ($uriSegments as $segment) {
                                if (is_numeric($segment)) {
                                    $id=(int)$segment;
                                    break;
                                }
                            }
                    try 
                    {
                        $asistencias = $asistencia_serv-> eliminar_Personal($id);
                        if (!($asistencias)) {
                            $salida = $salida_error->response(204, '');
                        } 
                        else {
                            $salida = $salida_error->response(200, '');
                            $salida->datos = $asistencias;
                        }
                        
                    } catch (Error $e) 
                    {
                        writeLog("AsistenciaController. ".$e->getMessage());
                        $salida = $salida_error->response(500, $e->getMessage());
                    }
                break;
            case 'GET':
                    $var = $this->getQueryStringParams();

                    try 
                    {
                        $asistencias = $asistencia_serv->ver_Personal($var['id_personal']);
                        if (!($asistencias)) {
                            $salida = $salida_error->response(204, '');
                        } 
                        else {
                            $salida = $salida_error->response(200, '');
                            $salida->datos = $asistencias;
                        }
                        
                    } catch (Error $e) 
                    {
                        writeLog("AsistenciaController. ".$e->getMessage());
                        $salida = $salida_error->response(500, $e->getMessage());
                    }
                break;
            case 'update':
                $var = $this->PutFromData();
                $uriSegments = $this->getUriSegments();
                        foreach ($uriSegments as $segment) {
                            if (is_numeric($segment)) {
                                $id_personal=(int)$segment;
                                break;
                            }
                        }
                    try 
                    {
                        $asistencias = $asistencia_serv->modificar_Personal($id_personal,$var['nombre'], $var['legajo'] );
                        if (!($asistencias)) {
                            $salida = $salida_error->response(204, '');
                        } 
                        else {
                            $salida = $salida_error->response(200, '');
                            $salida->datos = $asistencias;
                        }
                        
                    } catch (Error $e) 
                    {
                        writeLog("AsistenciaController. ".$e->getMessage());
                        $salida = $salida_error->response(500, $e->getMessage());
                    }
                break;
           /* case 'POST'
                $var = $this->PostFromData();
                try {
                    
                    $con = [
                        'nombre' => $var['nombre'],
                        'legajo' => $var['legajo'],
                    ];

                    $productos = $producto->postProductoUsuario($con);

                     if (!($productos)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $productos;
                    }
                     
                } catch (Error $e) 
                {
                    writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
                break;*/
        }

        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }   
}