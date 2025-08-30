<?php 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/ProductoService.php';
require_once __DIR__ . '/../MiLog.php';

class ProductoController extends BaseController
{
    public function producto($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id_vendedor']) && $var['id_vendedor'] && isset($var['id_producto']) && $var['id_producto'] ) 
            {
                 try 
                 {
                    $productos = $producto->productoUsuario($var['id_producto'], $var['id_vendedor']);//???
                    
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
            }else
            {
                $salida = $salida_error->response(500, "Datos requeridos");
                writeLog("ProductoControllerproducto. Datos requeridos", '/logs/app.log');
            }
        }
        if ($method === 'post') {
            $var = $this->PostFromData();
        
               try {
                    
                    $con = [
                        'nombre' => $var['nombre'],
                        'id_vendedor' => $var['id_vendedor'],
                        'tipo' => $var['tipo'],
                        'descripcion' => $var['descripcion'],
                        'categoria' => $var['categoria'],
                        'precio' => $var['precio'],
                        'stock' => $var['stock'],
                        'unidad' => $var['unidad'],
                        'disponibilidad' => $var['disponibilidad'],
                        'estado' => $var['estado'],
                        'imagenes' => $var['imagenes'],
                        'caracteristicas' =>$var['caracteristicas']
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
            }
            
        if ($method === 'delete') {
          $var = $this->getQueryStringParams();
    
             if (isset($var['id_producto']) && $var['id_producto']) {
               try {
                    $productos = $producto->eliminarproducto($var);
                        
                    if (!($productos)) 
                    {
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
           /* else
            {
                $salida = $salida_error->response(500, "Datos requeridos");
                writeLog("ProductoControllerproducto. Datos requeridos", '/logs/app.log');
            }*/
           
        
            $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    
    public function lista($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id_usuario']) && $var['id_usuario']) {
                try {
                    $productos = $producto->listaxusuario($var['id_usuario']);
                    
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
                try {
                    $productos = $producto->getlista();
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
            
        } else {
            $salida = $salida_error->response(422, '');
        }
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function listaxusuario($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['id_usuario']) && $var['id_usuario']) {
                try {
                    $productos = $producto->ListaXUsuario($var['id_usuario']);
                    
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
        
        if ($method === 'delete') {
            $var = $this->getQueryStringParams();

            if (isset($var['id_producto']) && $var['id_producto']) {
                try {
                    $productos = $producto->eliminarproducto($var['id_producto']);
                    
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

    public function productoxid($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();
            try 
            {
            $productos = $producto->productoXid($var['id_producto']);
            
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
        }
        if($method === 'put')
        {
            $var = $this->PutFromData();
            
            $uriSegments = $this->getUriSegments();
            foreach ($uriSegments as $segment) {
                if (is_numeric($segment)) {
                    $condicion=['id_producto' =>(int)$segment ];
                    break;
                }
            }

                try {
                    $con = [
                        'nombre' => $var['nombre'],
                        'tipo'=> $var['tipo'],
                        'descripcion'=> $var['descripcion'],
                        'categoria'=> $var['categoria'],
                        'precio'=> $var['precio'],
                        'stock'=> $var['stock'],
                        'unidad'=> $var['unidad'],
                        'disponibilidad'=> $var['disponibilidad'],
                        'estado'=> $var['estado'],
                        'id_vendedor'=> $var['id_vendedor'],
                        'imagenes'=> $var['imagenes'],
                        'caracteristicas' =>$var['caracteristicas']
                    ];
                    $rutas = [];
                    foreach ($var['imagenesserver'] as $imagen) {
                        $rutas[] = $imagen['ruta'];
                    }
                    
                    $result = $producto->putProducto($con, $condicion, $rutas);
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
    public function tienda($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            $var = $this->getQueryStringParams();

            if (isset($var['tienda']) && $var['tienda']) {
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
        if($method === 'post')
        {
            $var = $this->PostFromData();
        
               try {
                    $con= [
                        'nombre' => $var['nombre'],
                        'id_vendedor' => $var['id_vendedor'],
                        'logo' => $var['imagenelogo'],
                        'banner' => $var['imagenebanner'],
                        'telefono' => $var['telefono'],
                        'direccion' => $var['direccion'],
                        'linkdireccion' => $var['linkdireccion'],
                        'rut' => $var['rut'],
                    ];

                    $productos = $producto->postTiendaInfo($con);
                    
                     if (!($productos)) {
                        $salida = $salida_error->response(204, '');
                    } 
                    else {

                        if($productos['status']=='fail')
                        {
                            $salida = $salida_error->response(404, $productos['datos']);
                        }
                        else
                        {
                            $salida = $salida_error->response(200, '');
                            $salida->datos = $productos['datos'];
                        }
                    }
                     
                } catch (Error $e) 
                {
                    writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                    $salida = $salida_error->response(500, $e->getMessage());
                }
            } 
            
         if($method === 'put')
         {
            $var = $this->PostFromData();
        
            try {
                 $con= [
                     'nombre' => $var['nombre'],
                     'id_vendedor' => $var['id_vendedor'],
                     'logo' => $var['imagenelogo'],
                     'banner' => $var['imagenebanner'],
                     'telefono' => $var['telefono'],
                     'direccion' => $var['direccion'],
                     'linkdireccion' => $var['linkdireccion'],
                     'rut' => $var['rut'],
                 ];

                 $productos = $producto->putTiendaInfo($con);
                 
                  if (!($productos)) {
                     $salida = $salida_error->response(204, '');
                 } 
                 else {
                    if($productos['status']=='fail')
                    {
                        $salida = $salida_error->response(404, $productos['datos']);
                    }
                    else
                    {
                     $salida = $salida_error->response(200, '');
                     $salida->datos = $productos['datos'];
                    }
                 }
                  
             } catch (Error $e) 
             {
                 writeLog("ProductoControllerproducto. ".$e->getMessage(), '/logs/app.log');
                 $salida = $salida_error->response(500, $e->getMessage());
             }
         }   
        
        
         $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }

    public function MetodoPago($method)
    {
        $producto = new ProductoService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if($method === 'post')
        {
            $var = $this->PostFromData();
        
               try {
                    $con= [
                        'nombre' => $var['nombre'],
                        'id_vendedor' => $var['id_vendedor'],
                        'public_key' => $var['public_key'],
                        'priv_key' => $var['priv_key'],
                        'secret_event' => $var['secret_event'],
                        'secret_integr' => $var['secret_integr'],
                    ];
                    
                    $productos = $producto->postFormaPago($con);
                    
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
            } 
            
         if($method === 'put')
         {
            $var = $this->PostFromData();
            $uriSegments = $this->getUriSegments();
            foreach ($uriSegments as $segment) {
                if (is_numeric($segment)) {
                    $id_vendedor=(int)$segment;
                    break;
                }
            }
            try {
                $con= [
                    'nombre' => $var['nombre'],
                    'id_vendedor' => $id_vendedor,
                    'public_key' => $var['public_key'],
                    'priv_key' => $var['priv_key'],
                    'secret_event' => $var['secret_event'],
                    'secret_integr' => $var['secret_integr'],
                ];

                 $productos = $producto->putFormaPago($con);
                 
                  if (!($productos)) {
                     $salida = $salida_error->response(204, '');
                 } 
                 else {
                     $salida = $salida_error->response(200, '');
                     $salida->datos = $productos;
                 }
                  
             } catch (Error $e) 
             {
                 writeLog("ProductoController.MetodoPago. ".$e->getMessage(), '/logs/app.log');
                 $salida = $salida_error->response(500, $e->getMessage());
             }
         }  
        
        
        $this->sendOutput(
            json_encode($salida),
            array($salida->error->Descripcion, $salida->error->CodigoHttp)
        );
    }
    
    
}