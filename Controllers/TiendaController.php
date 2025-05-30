<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/ProductoService.php';
require_once __DIR__ . '/../MiLog.php';

class TiendaController extends BaseController
{
    public function lista($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
       // writeLog("TiendaController.lista. Inicio ");
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['tienda']) && $var['tienda']) {
               // $tienda = filter_var($var['tienda'], FILTER_SANITIZE_STRING);

                try {
                    $productos = $producto->Listatienda($var['tienda']);
                    
                    if (!($productos)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $productos;
                    }
                    
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            else {
                $salida = $salida_error->response(500, $e->getMessage());
            }            
        }        
        else {
            $salida = $salida_error->response(422, '');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    
    public function Info($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();

        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['usuario']) && $var['usuario']) {
              //  $tienda = filter_var($var['tienda'], FILTER_SANITIZE_STRING);

                try {
                    $tienda = $producto->infoTienda($var['usuario']);
                    
                    if (!($tienda)) {
                        $salida = $salida_error->response(204, '');
                        $salida->datos = [];

                    } else {

                        $salida = $salida_error->response(200, '');
                        $salida->datos = $tienda;
                    }
                    
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            else {
                $salida = $salida_error->response(500, $e->getMessage());
            }            
        }        
        else {
            $salida = $salida_error->response(422, '');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function InfoxNombre($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();

        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['tienda']) && $var['tienda']) {
              //  $tienda = filter_var($var['tienda'], FILTER_SANITIZE_STRING);

                try {
                    $tienda = $producto->infoTiendaxNombre($var['tienda']);
                    
                    if (!($tienda)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $tienda;
                    }
                    
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            else {
                $salida = $salida_error->response(500, $e->getMessage());
            }            
        }        
        else {
            $salida = $salida_error->response(422, '');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    
    public function preventa($method)
    {
      //  writeLog("ProductoController.preventa. Inicio");

        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'post') {
            //$var = $this->getQueryStringParams();
            $var = $this->PostFromData();

            if (isset($var['id_usuario']) && is_numeric($var['id_usuario']) && isset($var['id_producto']) && is_numeric($var['id_producto']) && isset($var['cantidad']) && is_numeric($var['cantidad']) && $var['id_compra'] ==='servicio'  ) 
            {
                 try 
                 {
                    writeLog("ProductoController.preventa. Llamando a ProductoService");

                    $productos = $producto->preventa($var['id_producto'], $var['id_usuario'], $var['cantidad'] );
                    
                    if (!($productos)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $productos;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("ProductoController.preventa. ".$e->getMessage());
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }else
            {
                if (isset($var['id_usuario']) && is_numeric($var['id_usuario']) && isset($var['id_compra'])) 
                {
                     try 
                     {
                        writeLog("ProductoController.preventa. Llamando a ProductoService");
    
                        $productos = $producto->PreventaServicio($var['id_usuario'], $var['id_compra'] );
                        
                        if (!($productos)) {
                            $salida = $salida_error->response(204, '');
                        } 
                        else {
                            $salida = $salida_error->response(200, '');
                            $salida->datos = $productos;
                        }
                        
                    } catch (Error $e) 
                    {
                        writeLog("ProductoController.preventa. ".$e->getMessage());
                        $salida = $salida_error->response(500, $e->getMessage());
                    }
                }else{
                $salida = $salida_error->response(500, "Datos requeridos");
                writeLog("ProductoController.preventa. Datos requeridos");
                }
            }
        }
         $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        ); 
    }
    
    public function linkpago($method)
    {
        writeLog("ProductoController.linkpago. Inicio");
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'post') {
            //$var = $this->getQueryStringParams();
            $var = $this->PostFromData();

            if (isset($var['id_usuario']) && is_numeric($var['id_usuario']) && isset($var['id_producto']) && is_numeric($var['id_producto']) && isset($var['monto']) && is_numeric($var['monto'])  ) 
            {
                 try 
                 {
                    writeLog("ProductoController.linkpago. Llamando a ProductoService");

                    $productos = $producto->link_Pago($var['monto'], $var['id_producto'], $var['id_usuario']);
                    
                    if (!($productos)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $productos;
                    }
                    
                } catch (Error $e) 
                {
                    writeLog("ProductoController.linkpago. ".$e->getMessage());
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }else{
                $salida = $salida_error->response(500, "Datos requeridos");
                writeLog("ProductoController.linkpago. Datos requeridos");
                }
                
                $this->sendOutput(
                    json_encode($salida),
                    array($salida->error->Descripcion, $salida->error->CodigoHttp)
                ); 
                
        }
    }

    
    public function productoVenta($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
       // writeLog("TiendaController.lista. Inicio ");
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id_producto']) && $var['id_producto']) {
               // $tienda = filter_var($var['tienda'], FILTER_SANITIZE_STRING);

                try {
                    $productos = $producto->productoTienda($var['id_producto'], $var['ref']);
                    
                    if (!($productos)) {
                        $salida = $salida_error->response(204, '');
                    } else {
                        $salida = $salida_error->response(200, '');
                        $salida->datos = $productos;
                    }
                    
                } catch (Error $e) {
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            }
            else {
                $salida = $salida_error->response(500, "Error El producto no existe");
            }            
        }        
        else {
            $salida = $salida_error->response(422, '');
        }
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
}