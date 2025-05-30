<?php
require_once __DIR__ . '/BaseController.php';

require_once __DIR__ . '/../SERVICIOS/Client.class.php';
require_once __DIR__ . '/../MiLog.php';

class UsuarioController extends BaseController
{
    public function lista($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            try {
                $usuarios = $usuario->getlista();
                if (!($usuarios)) {
                    $salida = $salida_error->response(204, '');
                } else {
                    $salida = $salida_error->response(200, '');
                    $salida->datos = $usuarios;
                }
            } catch (Error $e) {
                $salida = $salida_error->response(500, $e->getMessage());
            }
        } else {
            $salida = $salida_error->response(422, '');
        }
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function lista_asistencia($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            try {
                $usuarios = $usuario->lista_asistencia();
                if (!($usuarios)) {
                    $salida = $salida_error->response(204, '');
                } else {
                    $salida = $salida_error->response(200, '');
                    $salida->datos = $usuarios;
                }
            } catch (Error $e) {
                $salida = $salida_error->response(500, $e->getMessage());
            }
        } else {
            $salida = $salida_error->response(422, '');
        }
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    public function usuario($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id']) && $var['id']) {
                try {

                    $con = ['usuario_id' => $var['id']];

                    $usuarioxid = $usuario->get($con);
                    if (!($usuarioxid)) {
                        $salida->error = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $usuarioxid;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
        } else if ($method === 'post') {
            $var = $this->PostFromData();
                try {
                    $fecha_actual = date('Y-m-d H:i:s');
                    $con = [
                        'nombre' => $var['nombre'],
                        'apellido' => $var['apellido'],
                        'email' => $var['email'],
                        'token' => "",
                        'fin_sesion' => $fecha_actual,
                        'creado' => $fecha_actual,
                        'telefono' => $var['telefono']
                    ];
                    
                    $result =  $usuario->post($con);
                    if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
        } else if ($method === 'put') {
            $var = $this->PutFromData();
            
            $uriSegments = $this->getUriSegments();
            foreach ($uriSegments as $segment) {
                if (is_numeric($segment)) {
                    $condicion=['usuario_id' =>(int)$segment ];
                    break;
                }
            }

                try {
                    $con = [
                        'nombre' => $var['nombre'],
                        'apellido' => $var['apellido'],
                        'email' => $var['email'],
                        'telefono' => $var['telefono']
                    ];
                    
                    $result =  $usuario->put($con, $condicion);
                    if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
        } else if ($method === 'delete') {
            $uriSegments = $this->getUriSegments();
            foreach ($uriSegments as $segment) {
                if (is_numeric($segment)) {
                    $condicion=['usuario_id' =>(int)$segment ];
                    break;
                }
            }

                try {
                    
                    $result = $usuario->delete($condicion);
                    if (!($result)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $result;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );
        } else {
            $salida = $salida_error->response(422, '');
        }
    }

    /*
    public function vendedor($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id']) && $var['id']) {
                try {
                    $con = ['id_vendedor' => $var['id']];

                    $usuarioxid = $usuario->getvendedor($con);
                    if (!($usuarioxid)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $usuarioxid;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
                
        } else if ($method === 'put') {
            $var = $this->PutFromData();
            
            $uriSegments = $this->getUriSegments();
           // writeLog("ProductoController.vendedor. usuario_id  ".json_encode($uriSegments));
            
            foreach ($uriSegments as $segment) {
                if (is_numeric($segment)) {
                    $condicion=['usuario_id' =>(int)$segment ];
                    break;
                }
            }

                try {
                    $con = [];
             //       writeLog("ProductoController.vendedor. usuario_id  ".json_encode($condicion));

                    // Solo agrega 'pasarela' si fue enviado
                    if (isset($var['pasarela'])) {

                        $con['pasarela'] = filter_var($var['pasarela'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    }
        
                    // Solo agrega 'facturacion' si fue enviado
                    if (isset($var['facturacion'])) {
                        $con['facturacion'] = filter_var($var['facturacion'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    }
                  //  writeLog("ProductoController.vendedor. datos a modificar  ".json_encode($con));
                    
                    $result =  $usuario->put($con, $condicion);
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
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );   
    }


    public function compras($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['usuario_id']) && $var['usuario_id'] && is_numeric($var['usuario_id'])) {
                try {
                    $usuarioxid = $usuario->getComprasAgrupadas($var['usuario_id']);
                    
                    if (!($usuarioxid)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $usuarioxid;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );       
        } 
    }

    public function ventas($method)
    {
        $usuario = new UsuarioService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['usuario_id']) && $var['usuario_id'] && is_numeric($var['usuario_id'])) {
                try {
                    $usuarioxid = $usuario->getVentasAgrupadas($var['usuario_id']);
                    
                    if (!($usuarioxid)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $usuarioxid;
                    }
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            $this->sendOutput(
                json_encode($salida),
                array($salida->error->Descripcion, $salida->error->CodigoHttp)
            );       
        } 
    }*/

    
}