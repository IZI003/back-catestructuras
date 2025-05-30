<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../MiLog.php';
require_once __DIR__ . '/../SERVICIOS/DocumentosService.php';
require_once __DIR__ . '/../SERVICIOS/CarritoService.php';

class ProductoService
{
    private $dbHandler;
    private $table = 'producto';
    
    public function __construct()
    {
        $this->dbHandler = new DatabaseHandler();
    }

    public function getListaXUsuario($id_vendedor)
    {    
        $query = "SELECT prod.id, prod.plan, prod.titulo, prec.moneda, prec.monto
                    FROM  usuarios usuar
                    INNER JOIN precios prec on usuar.Moneda = prec.Moneda and prec.activo = 1
                    INNER JOIN producto prod on  prec.id_producto = prod.id 
                    WHERE usuar.usuario_id = ".$id_vendedor;   
         
        try {
            // Preparar la consulta
            $result= $this->dbHandler->querySrting($query);
           
            return $result ?: null;
        } catch (PDOException $e) {
            writeLog("ProductoService.getListaXUsuario ".$e->getMessage(), '/logs/app.log');
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }  
      /*  $query = "SELECT prod.id_plan, prod.plan, prod.titulo, prec.moneda, prec.monto  FROM  usuarios usuar
                    INNER JOIN precios prec on usuar.Moneda = prec.Moneda
                    INNER JOIN producto prod on  prec.id_plan = prod.id_plan 
                    WHERE usuar.usuario_id = :id_usurio";
        
        $stmt = $this->dbHandler->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
      //  $result= $this->dbHandler->querySrting($query);
        error_log("Consulta generada: " . $query);
        
        return $result ?: null;*/
    }
    
    public function ListaXUsuario($id_vendedor)
    {    
        $query = "SELECT prod.ID, prod.id_vendedor, prod.nombre, prod.categoria, prod.stock , prec.Monto as precio, prod.tipo
                FROM producto_venta prod
                INNER JOIN precios prec on prod.ID = prec.id_producto and prec.activo = 1
                WHERE  prod.activo = 1 AND prod.id_vendedor=".$id_vendedor;  
                      
        try {
            // Preparar la consulta
            $result= $this->dbHandler->querySrting($query);
           
            return $result ?: null;
        } catch (PDOException $e) {
            writeLog("ProductoService.ListaXUsuario ".$e->getMessage());
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }
    }
    
    public function getLista()
    {        
        $query = "SELECT prod.id, prod.plan, prod.titulo, prec.Moneda, prec.Monto, prod.id_vendedor 
                    FROM precios prec 
                    INNER JOIN  producto prod on prec.id_producto = prod.id and prod.activo = 1                 
                    WHERE prec.Moneda = 'COP' and prec.activo = 1 ";
        
        $result= $this->dbHandler->querySrting($query);
        
        return $result ?: null;
    }
    
    public function productoUsuario($ID_PRODUCTO, $id_usurio)
    {
        $query = "SELECT usuar.email, 
                    usuar.nombre, 
                    usuar.apellido, 
                    usuar.telefono, 
                    usuar.documento, 
                    usuar.tipo_documento, 
                    
                    met_pago.public_key, 
                    met_pago.secret_integr, 
                    
                    prec.Moneda, prec.Monto
                    FROM  usuarios usuar
                     INNER JOIN producto prod on usuar.Moneda = 
                    INNER JOIN precios prec on usuar.Moneda = prec.id_producto
                    INNER JOIN metodo_pago met_pago on met_pago.nombre = 'WOMPI'
                    WHERE usuar.usuario_id = ".$id_usurio." and prod.id = ".$id_producto;
    
        $result= $this->dbHandler->querySrting($query);
        
        if(!isset($result[0]))
        {
            return null;
        }
        
        $ref = password_hash(date('Y-m-d H:i:s').$id_usurio, PASSWORD_DEFAULT);

        $data = [
            'ID_COMPRA' => $ref,
            'ID_PRODUCTO' => $ID_PRODUCTO,
            'ID_USUARIO' => $id_usurio,
            'MONTO' => $result[0]['Monto'],
            'MONEDA' => $result[0]['Moneda'],
            'MONEDA' => $result[0]['Moneda']
        ];
        $this->dbHandler->insert("compra",  $data);
        
        $FechaExpiracion="";
        $cadena_concatenada = $ref.$result[0]['Monto'].$result[0]['Moneda'].$FechaExpiracion.$result[0]['secret_integr'];
        $secret_integr = hash ("sha256", $cadena_concatenada);
        $salida = [
                    'ref' => $ref,
                    'email' => $result[0]['email'],
                    'nombre' => $result[0]['nombre']." ".$result[0]['apellido'],
                    'telefono' => $result[0]['telefono'],
                    'documento' => $result[0]['documento'],
                    'tipo_documento' => $result[0]['tipo_documento'],
                    'secret_integr' => $secret_integr,
                    'public_key' => $result[0]['public_key'],
                    'monto' => $data['MONTO'],
                    'moneda' => $data['MONEDA']
                 ]; 

        return $salida;
    }

    public function postProducto($datos)
    {
        $data = [
            'ID_pasarela' => $datos['id'],
            'event' => $datos['event'],
            'amount_in_cents' => $datos['amount_in_cents'],
            'reference' => $datos['reference'],
            'customer_email' => $datos['customer_email'],
            'currency' => $datos['currency'],
            'payment_method_type' => $datos['payment_method_type'],
            'status' => $datos['status'],
            'environment' => $datos['environment'],
            'checksum' => $datos['checksum']
        ];

        $result= $this->dbHandler->insert($this->table, $data);
        
        if(!isset($result))
        {
            return null;
        }
        
        $salida = [
        'id' =>$result,
        'status' =>"OK"
        ];

        return $salida;
    }

    public function postProductoUsuario($datos)
    {      
            $var= [
                'nombre' => $datos['nombre'],
                'id_vendedor' => $datos['id_vendedor'],
                'tipo' => $datos['tipo'],
                'descripcion' => $datos['descripcion'],
                'categoria' => $datos['categoria'],
                'stock' => $datos['stock'],
                'unidad' => $datos['unidad'],
                'disponibilidad' => $datos['disponibilidad'],
                'estado' => $datos['estado'],
                'f_creacion' => date('Y-m-d H:i:s')
                ];                                      
        
        $result= $this->dbHandler->insert('producto_venta', $var);
        
        if($result)
        {
          //  $datos['id_producto'] = $result;
            $precio =[
                        'id_producto' => $result,
                        'Moneda' => 'COP',
                        'Monto' => $datos['precio'],
                        'activo' => 1,
                    ];
            
            $resultado= $this->dbHandler->insert('precios', $precio);
            $this->agregarCaracteristicas($datos['caracteristicas'], $result);
            
        } else
        {
            return null;
        }
        
        // writeLog("ProductoService.postProductoUsuario. "."por llamar a cargarArchivo ");

         // Supongamos que recibes las imágenes en un arreglo llamado $imagenes
         $imagenes = $datos['imagenes'] ?? []; // O a partir de un JSON decodificado
        $this->cargarArchivo($imagenes, $datos['id_vendedor'], $result, 'producto');
      
        $salida = [
        'datos' => $datos,
        'status'=> "OK"
        ];

        return $salida;
    }
    
    public function eliminarproducto($datos)
    {      
        $var= [
            'activo' => 0,
            'f_actualizacion' => date('Y-m-d H:i:s'),
        ];
        $where= [
            'id' => $datos['id_producto']
        ];      
        
        $result= $this->dbHandler->update('producto_venta', $var , $where);
        $var= [
            'activo' => 0,
        ];
        
        $where= [
            'id_producto' => $datos['id_producto']
        ];        
        
        $result= $this->dbHandler->update('precios', $var , $where);
        
        if(!isset($result))
        {
            return null;
        }
        
        return  [
        'status' =>"OK"
        ];
    }

    private function cargarArchivo($imagenes, $id_vendedor, $id_producto, $tipo_archivo)
    {      
        // Configuración de la carpeta base de uploads (ajusta la ruta según tu estructura)
        $baseDir =   dirname(__DIR__). '/uploads'; 
        writeLog("ProductoService.cargarArchivo. basedir $baseDir");

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        // Crear carpeta para el id_vendedor
        $folderVendedor = $baseDir . '/' . $id_vendedor;
        if (!is_dir($folderVendedor)) {
            mkdir($folderVendedor, 0777, true);
        }

        // Determinar la carpeta destino: si hay id_producto, se guarda en una subcarpeta; de lo contrario, en la carpeta del vendedor
        if (!empty($id_producto)) {
            $folderProducto = $folderVendedor . '/' . $id_producto;
            if (!is_dir($folderProducto)) {
                mkdir($folderProducto, 0777, true);
            }
            $destinationFolder = $folderProducto;
        } else {
            $destinationFolder = $folderVendedor;
        }
        writeLog("ProductoService.cargarArchivo. destinationFolder $destinationFolder");

        foreach ($imagenes as $base64Image) {

            // Extraer la extensión y limpiar la cadena base64 si tiene cabecera
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $extension = strtolower($type[1]); // Ejemplo: "png", "jpg", etc.
                // Validar la extensión
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    continue; // O manejar el error según corresponda
                }
            } else {
                // Si no tiene cabecera, se asume png por defecto
                $extension = 'jpg';
            }

            // Decodificar la imagen
            $data = base64_decode($base64Image);
            if ($data === false) {
                // Error al decodificar, saltar este archivo
                continue;
            }

            // Generar un nombre único para el archivo
            $fileName = uniqid('img_', true) . '.' . $extension;
            $destination = $destinationFolder . '/' . $fileName;
            // Generar la ruta relativa (ajústala según tu estructura)
            
            $relativePath = str_replace(dirname(__DIR__)."/", "", $destination);//str_replace(__DIR__ . '/', '', $destination);
            //$rutaRelativa = str_replace(dirname(__DIR__)."/", "", $destination);
            $documentos = new DocumentosService();
            $documentData = [
                'id_vendedor'   => $id_vendedor,
                'id_producto'   => $id_producto,
                'tipo_archivo'  => $tipo_archivo, // o el valor que corresponda
                'ruta'          => $relativePath,
                'nombre_archivo'=> $fileName
            ];

            // Insertar en la base de datos (asegúrate que el método postDocumentos esté implementado correctamente)
            $result_ = $documentos->postDocumentos($documentData);

            // Guardar el archivo en el servidor
            if (file_put_contents($destination, $data)) {
                writeLog("ProductoService.cargarArchivo. Imagen guardada en: $destination");
            } else {
                $error = error_get_last();
                writeLog("ProductoService.cargarArchivo. Error al guardar la imagen en: $destination. Detalles: " . json_encode($error));
            }
        }
    }
    
    public function productoXid($id_producto)
    {
        try {
            $query = "SELECT prod.nombre, prod.id_vendedor, prod.tipo, prod.descripcion, prod.categoria, prod.stock,
                    prod.unidad, prod.disponibilidad, prod.estado,
                     prec.moneda, prec.monto as precio
                    FROM  producto_venta prod                    
                    INNER JOIN precios prec on prod.id = prec.id_producto and prec.activo = 1
                    WHERE prod.id = ".$id_producto; 

            $result= $this->dbHandler->querySrting($query);
           
            $queryimagenes = "SELECT doc.id_documento, doc.ruta, doc.nombre_archivo as name 
                              FROM documentos doc 
                              WHERE doc.id_producto = ".$id_producto; 
            
            $imagenes= $this->dbHandler->querySrting($queryimagenes);
            $imagenesArray = []; // Array donde se almacenarán las imágenes

            foreach ($imagenes as $imagen) {
                    $imagenesArray[] = [
                        'ruta'      => $imagen['ruta'],
                        'id_imagen' => $imagen['id_documento'],
                        'name' => $imagen['name']
                    ];
            }
            $result[0]['imagenes'] = $imagenesArray;

            // agregamos lista de caracteristicas
            $queryCaracterisiticas = "SELECT prod_car.caracteristica_id, prod_car.valor, caract.nombre
                                        FROM producto_caracteristica prod_car
                                        INNER JOIN caracteristicas caract  on caract.id= prod_car.caracteristica_id
                                        WHERE prod_car.producto_id = ".$id_producto; 

            $caracteristicas= $this->dbHandler->querySrting($queryCaracterisiticas);
            $caracteristicas_array = []; // Array donde se almacenarán las imágenes

            foreach ($caracteristicas as $caracter) {
                $caracteristicas_array[] = [
                    'nombre'      => $caracter['nombre'],
                    'id' => $caracter['caracteristica_id'],
                    'valor' => $caracter['valor']
                ];
        }

            
            $result[0]['caracteristicas'] = $caracteristicas_array;
            return $result ?: null;
        } catch (PDOException $e) {
            writeLog("ProductoService.getListaXUsuario ".$e->getMessage(), '/logs/app.log');
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }  
    }
     
    function Listatienda($tienda)
    {       
        $query = "SELECT prod.ID as id, prod.id_vendedor, prod.nombre as name, 
        prod.categoria, prod.tipo, prod.stock, prod.descripcion,
                        prec.Monto as price, 
                        0 as discount,
                        prec.Monto as previousPrice,
                         'Aun no cuenta con cuotas' as installments, 
                         'No cuenta con envios' as shipping, 
                        0 as imagenActualIndex,
                        'TVTCL32' as code, 
                        tiend.nombre as seller,
                        tiend.direccion as direccion,
                        tiend.Linkdireccion as Linkdireccion,
                        doc_banner.ruta as banner, doc_logo.ruta as logo,
                        prod.id_vendedor
                    FROM tienda tiend                    
                    INNER JOIN producto_venta prod on tiend.id_vendedor = prod.id_vendedor and prod.activo = 1
                    INNER JOIN precios prec on prod.ID = prec.id_producto  and prec.activo = 1 
                    inner join documentos doc_logo on doc_logo.id_vendedor = tiend.id_vendedor AND  doc_logo.tipo_archivo = 'logo'                 
                    inner join documentos doc_banner on doc_banner.id_vendedor = tiend.id_vendedor AND  doc_banner.tipo_archivo = 'banner'
                    WHERE tiend.nombrefix = '".$tienda."'";
                    
       // writeLog("ProductoService.Listatienda " . $query); //, '/logs/app.log'
        
        try {
            // Ejecuta la consulta de productos
            $result = $this->dbHandler->querySrting($query);
            if (!$result) {
                return null;
            }                  
            
            // Obtener los IDs de los productos
            $productIds = array_map(function($producto) {
                return $producto['id'];
            }, $result);
            
            // Consultar todas las imágenes asociadas a esos productos de una sola vez
            $imagenesPorProducto = [];
            if (!empty($productIds)) {        
                $idsString = implode(',', $productIds);
                $queryimagenes = "SELECT doc.id_producto, doc.id_documento, doc.ruta, doc.nombre_archivo as name 
                                FROM documentos doc 
                                WHERE doc.id_producto IN ($idsString)";
                $imagenesResult = $this->dbHandler->querySrting($queryimagenes);
                
                // Agrupar las imágenes por id_producto
                foreach ($imagenesResult as $imagen) {
                    $pid = $imagen['id_producto'];
                    if (!isset($imagenesPorProducto[$pid])) {
                        $imagenesPorProducto[$pid] = [];
                    }
                    $imagenesPorProducto[$pid][] = [
                        'ruta' => $imagen['ruta'],
                        'name' => $imagen['name'],
                        'id_imagen' => $imagen['id_documento'],
                    ];
                }
            }
            
            // Asignar a cada producto su listado de imágenes
            foreach ($result as &$producto) {
                $pid = $producto['id'];
                $producto['imageUrl'] = isset($imagenesPorProducto[$pid]) ? $imagenesPorProducto[$pid] : [];
            }    
                
            return $result;
            
        } catch (PDOException $e) {
            writeLog("ProductoService.ListaXUsuario " . $e->getMessage());
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }
    }
    
    function infoTienda($id_usuario)
    {        
        $query = "SELECT tiend.nombre, doc_banner.ruta as banner, doc_logo.ruta as logo, tiend.nombrefix,
                    tiend.telefono, tiend.direccion, tiend.Linkdireccion as linkdireccion, tiend.rut,
                    CASE 
                         WHEN us.tipo_usuario = 1 THEN 'admin'
                        WHEN us.tipo_usuario = 2 THEN 'vendedor'
                        ELSE 'comun'
                    END AS rol,
                       us.pasarela, us.facturacion
                    FROM usuarios us
                    LEFT join tienda tiend on us.usuario_id = tiend.id_vendedor 
                    LEFT join documentos doc_logo on doc_logo.id_vendedor = tiend.id_vendedor AND  doc_logo.tipo_archivo = 'logo'                 
                    LEFT join documentos doc_banner on doc_banner.id_vendedor = tiend.id_vendedor AND  doc_banner.tipo_archivo = 'banner'
                    WHERE us.usuario_id = ".$id_usuario; 
       // writeLog("ProductoService.infoTienda " . $query); //, '/logs/app.log'
        
        try {
            // Ejecuta la consulta de productos
            $result = $this->dbHandler->querySrting($query);
            if (!$result) {
                return null;
            }     
            
            return $result;
            
        } catch (PDOException $e) {
            writeLog("ProductoService.ListaXUsuario " . $e->getMessage(), '/logs/app.log');
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }
    }
    
    public function postTiendaInfo($datos)
        {      
            if($this->controlarTienda($datos['nombre']))
            {
                $this->cargarArchivo([$datos['logo']], $datos['id_vendedor'], null, 'logo');
                $this->cargarArchivo([$datos['banner']], $datos['id_vendedor'], null, 'banner');

                $var= [
                    'nombre' => $datos['nombre'],
                    'id_vendedor' => $datos['id_vendedor'],
                    'nombrefix' => str_replace(' ', '', $datos['nombre']),
                    'telefono' => $datos['telefono'],
                    'direccion' => $datos['direccion'],
                    'linkdireccion' => $datos['linkdireccion'],
                    'rut' => $datos['rut']
                    ];                                      
            
            $result= $this->dbHandler->insert('tienda', $var);

            $info= [
                'tipo_usuario' => 2,
                ]; 
                 
            $condicion =['usuario_id' => $datos['id_vendedor']];
            //actualizamos datos del usuario pasa a ser vendedor
            $this->dbHandler->update('usuarios',  $info, $condicion);
            $salida = [
                'datos' => $datos,
                'status'=> "OK"
                ];
            return $salida;
            }else
            {
                writeLog("ProductoService.putTiendaInfo Ya existe ese negocio" );
                $salida = [
                    'datos' => "Ya existe ese negocio",
                    'status'=> "fail"
                    ];
        
                    return $salida;
            }
            
    }
      
    public function putTiendaInfo($datos)
    {      
        if($this->controlarTienda($datos['nombre']))
            {
            $query = "SELECT tiend.id as tienda, 
            doc_banner.id_documento as banner, doc_logo.id_documento as logo
            FROM tienda tiend 
            inner join documentos doc_logo on doc_logo.id_vendedor = tiend.id_vendedor AND  doc_logo.tipo_archivo = 'logo'                 
            inner join documentos doc_banner on doc_banner.id_vendedor = tiend.id_vendedor AND  doc_banner.tipo_archivo = 'banner'
            WHERE tiend.id_vendedor = ".$datos['id_vendedor']; 
        
        try {
            // Ejecuta la consulta de productos
            $result = $this->dbHandler->querySrting($query);
              
            $var= [
                'nombre' => $datos['nombre'],
                'id_vendedor' => $datos['id_vendedor'],
                'nombrefix' => str_replace(' ', '', $datos['nombre']),
                'telefono' => $datos['telefono'],
                'direccion' => $datos['direccion'],
                'linkdireccion' => $datos['linkdireccion'],
                'rut' => $datos['rut'],
                ]; 
                 
            $condicion =['id' => $result[0]['tienda']];
            //actualizamos datos de la tienda
            $this->dbHandler->update('tienda',  $var, $condicion);
                if($datos['logo'] !== "")
                {
                    $query = "DELETE FROM documentos 
                    WHERE id_documento in (".$result[0]['logo'].")";
                    $this->dbHandler->querySrting($query);
        
                    $this->cargarArchivo([$datos['logo']], $datos['id_vendedor'], null, 'logo');  
                }
            
                if($datos['banner'] !== "")
                {
                    $query = "DELETE FROM documentos 
                    WHERE id_documento in (".$result[0]['banner'].")";
                    $this->dbHandler->querySrting($query);
        
                    $this->cargarArchivo([$datos['banner']], $datos['id_vendedor'], null, 'banner');
 
                }
            } catch (PDOException $e) {
                writeLog("ProductoService.putTiendaInfo " . $e->getMessage());
                $salida = [
                    'datos' => "Error" . $e->getMessage(),
                    'status'=> "fail"
                    ];
        
                    return $salida;
              //  throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
            }
            
            $salida = [
            'datos' => $datos,
            'status'=> "OK"
            ];

            return $salida;
            }else
            {
                writeLog("ProductoService.putTiendaInfo Ya existe ese negocio" );

                $salida = [
                    'datos' => "Ya existe ese negocio",
                    'status'=> "fail"
                    ];
        
                    return $salida;
            }
    }
      
    
    public function putProducto($datos,$cond, $imagenesserver)
    {
         $var= [
                'nombre' => $datos['nombre'],
                'id_vendedor' => $datos['id_vendedor'],
                'tipo' => $datos['tipo'],
                'descripcion' => $datos['descripcion'],
                'categoria' => $datos['categoria'],
                'stock' => $datos['stock'],
                'unidad' => $datos['unidad'],
                'disponibilidad' => $datos['disponibilidad'],
                'estado' => $datos['estado'],
                'f_actualizacion' => date('Y-m-d H:i:s')
                ];                                      

        $condicion =['ID' => $cond['id_producto']];
        try
        {
            
            $result= $this->dbHandler->update('producto_venta',  $var, $condicion);
            
            if($result)
            {
                $var= [
                    'activo' => 0,
                    ]; 
                    
                $condicion =['id_producto' => $cond['id_producto']];
                //actualizamos datos del precio
                $this->dbHandler->update('precios',  $var, $condicion);
                
                $precio =[
                            'id_producto' => $cond['id_producto'],
                            'Moneda' => 'COP',
                            'Monto' => $datos['precio'],
                        ];            
            
                $this->dbHandler->insert('precios', $precio);            
            } else
            {
                return null;
            }
            //en caso de que tubiera imagenes anteriores las borramos
            $querydelete = "DELETE FROM documentos 
                WHERE id_producto = ".$cond['id_producto'];
            $this->dbHandler->querySrting($querydelete);
            
            if (isset($imagenesserver) && count($imagenesserver) > 0)
            {
                for ($i=0; $i < count($imagenesserver); $i++) { 
                    $documentData = [
                        'id_vendedor'   => $datos['id_vendedor'],
                        'id_producto'   => $cond['id_producto'],
                        'tipo_archivo'  => 'producto', // o el valor que corresponda
                        'ruta'          => $imagenesserver[$i],
                        'nombre_archivo'=> basename($imagenesserver[$i])
                    ];
                    
                    $documentos = new DocumentosService();
                    $result_ = $documentos->postDocumentos($documentData);
                }  
            }                   
            // Supongamos que recibes las imágenes en un arreglo llamado $imagenes
            $imagenes = $datos['imagenes'] ?? []; // si contiene nuevas imagens         
            $this->cargarArchivo($imagenes, $datos['id_vendedor'], $cond['id_producto'], 'producto');
        
            $this->agregarCaracteristicas($datos['caracteristicas'], $cond['id_producto']);
            
            $salida = [
            'status'=> "OK"
            ];
            return $salida;
        } catch (PDOException $e) {
            writeLog("ProductoService.ListaXUsuario ".$e->getMessage(), '/logs/app.log');
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }
    }

    function infoTiendaxNombre($nombre)
    {        
        $query = "SELECT tiend.nombre, doc_banner.ruta as banner, doc_logo.ruta as logo, tiend.nombrefix,
                    tiend.telefono, tiend.direccion
                    FROM tienda tiend 
                    inner join documentos doc_logo on doc_logo.id_vendedor = tiend.id_vendedor AND  doc_logo.tipo_archivo = 'logo'                 
                    inner join documentos doc_banner on doc_banner.id_vendedor = tiend.id_vendedor AND  doc_banner.tipo_archivo = 'banner'
                    WHERE tiend.nombrefix = '".$nombre."'"; 
       // writeLog("ProductoService.infoTiendaxNombre " . $query); //, '/logs/app.log'
        
        try {
            // Ejecuta la consulta de productos
            $result = $this->dbHandler->querySrting($query);
            if (!$result) {
                return null;
            }     
            
            return $result;
            
        } catch (PDOException $e) {
            writeLog("ProductoService.infoTiendaxNombre " . $e->getMessage());
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }
    }

    public function productoXidTienda($id_producto)
    {
        try {
            $query = "SELECT prod.ID as id, prod.nombre, prod.id_vendedor, prod.tipo,
                     prod.descripcion, prod.categoria, prod.stock,
                     prod.unidad, prod.disponibilidad, prod.estado,
                     prec.moneda, prec.monto as precio
                      'no cuenta con cuotas' as installments, 
                     'no cuenta con envios a domicilio' as shipping,
                      'TVTCL32' as code,
                     prec.Monto as precioprevio,
                    FROM  producto_venta prod                    
                    INNER JOIN precios prec on prod.id = prec.id_producto and prec.activo = 1
                    WHERE prod.id = ".$id_producto; 

            $result= $this->dbHandler->querySrting($query);
           
            $queryimagenes = "SELECT doc.id_documento, doc.ruta, doc.nombre_archivo as name 
                              FROM documentos doc 
                              WHERE doc.id_producto = ".$id_producto; 
            
            $imagenes= $this->dbHandler->querySrting($queryimagenes);
            $imagenesArray = []; // Array donde se almacenarán las imágenes

            foreach ($imagenes as $imagen) {
                    $imagenesArray[] = [
                        'ruta'      => $imagen['ruta'],
                        'id_imagen' => $imagen['id_documento'],
                        'name' => $imagen['name']
                    ];
            }
            $result[0]['imagenes'] = $imagenesArray;

            return $result ?: null;
        } catch (PDOException $e) {
            writeLog("ProductoService.getListaXUsuario ".$e->getMessage(), '/logs/app.log');
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }  
    }
    
    public function Preventa($id_producto, $id_usurio, $cantidad)
    {
       // writeLog("ProductoService.preventa. INICIO");
        $query = "SELECT usuar.email, 
                    CONCAT (usuar.nombre,' ', usuar.apellido) AS nombre,  
                    usuar.telefono, 
                    usuar.documento, 
                    usuar.tipo_documento,                     
                    met_pago.public_key, 
                    met_pago.secret_integr, 
                    prec.Moneda, (prec.Monto * ".$cantidad.") as Monto,
                    prod.id_vendedor, 
                    prod.stock,
                     CONCAT ('https://tecnocomerciodigital.com/tienda/', tiend.nombrefix ,'/pago') AS as redirectUrl
                    FROM  usuarios usuar
                    INNER JOIN producto_venta prod on prod.id = ".$id_producto."
                    INNER JOIN precios prec on prod.id = prec.id_producto AND prec.activo = 1 
                    INNER JOIN metodo_pago met_pago on met_pago.id_vendedor = prod.id_vendedor
                    INNER JOIN tienda tiend on tiend.id_vendedor = prod.id_vendedor
                    WHERE usuar.usuario_id = ".$id_usurio;
                    
        //writeLog("ProductoService.preventa. QUERY ". $query);

        $result= $this->dbHandler->querySrting($query);
        
        if(!isset($result[0]))
        {
            return null;
        }
        
        $ref = password_hash(date('Y-m-d H:i:s').$id_usurio, PASSWORD_DEFAULT);

        $carrito = new CarritoService($this->dbHandler);

        $id_carrito = $carrito->crearCarrito($id_usurio,$result[0]['id_vendedor']);
        $carrito->agregarProductoAlCarrito($id_carrito, $id_producto, $cantidad, null);
                                  
        
        $this->insertar_Compra($ref, $id_producto, $id_usurio, $result[0]['Monto'], $result[0]['Moneda'], $result[0]['id_vendedor'], $id_carrito);
        $this->descontar_Producto($id_producto, $cantidad, $result[0]['stock']);
        
        $FechaExpiracion="";
        $cadena_concatenada = $ref. (int) round(floatval($result[0]['Monto']) * 100) .$result[0]['Moneda'].$FechaExpiracion.$result[0]['secret_integr'];
        
        
        $secret_integr = hash ("sha256", $cadena_concatenada);

        $cadena_concatenada = $ref.$result[0]['Monto'].$result[0]['Moneda'].$FechaExpiracion.$result[0]['secret_integr'];
        $salida = [
                    'ref' => $ref,
                    'email' => $result[0]['email'],
                    'nombre' => $result[0]['nombre'],
                    'telefono' => $result[0]['telefono'],
                    'documento' => $result[0]['documento'],
                    'tipo_documento' => $result[0]['tipo_documento'],
                    
                    'secret_integr' => $secret_integr,
                    'public_key' => $result[0]['public_key'],
                    'redirectUrl' => $result[0]['redirectUrl'],
                    'monto' =>   (int) round(floatval($result[0]['Monto']) * 100),
                    'moneda' => $result[0]['Moneda']
                 ]; 

        return $salida;
    }

    private function descontar_Producto($id_producto, $cantidad, $existencia)
    {
        $var= [
            'stock' => $existencia-$cantidad
                ];
        $where= [
            'id' => $id_producto
        ];      
        
        $result= $this->dbHandler->update('producto_venta', $var , $where);
    }
    
    private function insertar_Compra($ref, $id_producto, $id_usurio, $monto, $moneda, $id_vendedor,$id_carrito)
    {
        
        $data = [
            'ID_COMPRA' => $ref,
            'ID_PRODUCTO' => $id_producto,
            'ID_USUARIO' => $id_usurio,
            'MONTO' => $monto,
            'MONEDA' =>$moneda,
            'id_vendedor' => $id_vendedor,
            'id_carrito' => $id_carrito
        ];
        
        return $this->dbHandler->insert("compra",  $data);
    }

    public function PreventaServicio($id_usurio, $linkPago)
    {
        writeLog("ProductoService.PreventaServicio. INICIO");
        $query = "SELECT usuar.email, 
                    CONCAT (usuar.nombre,' ', usuar.apellido) AS nombre,  
                    usuar.telefono, 
                    usuar.documento, 
                    usuar.tipo_documento,                     
                    met_pago.public_key, 
                    met_pago.secret_integr, 
                    com.ID_COMPRA as referencia, 
                    com.Moneda, 
                    carr_det.subtotal Monto,
                    prod.id_vendedor, 
                    prod.stock
                    FROM  usuarios usuar
                    INNER JOIN compra com on com.linkPago = ".$linkPago."
                    INNER JOIN carrito carr on carr.id = com.id_carrito
                    INNER JOIN carrito_detalle carr_det on carr_det.id_carrito = carr.id
                    INNER JOIN producto_venta prod on prod.id = carr_det.ID_PRODUCTO
                    INNER JOIN metodo_pago met_pago on met_pago.id_vendedor = prod.id_vendedor
                    WHERE usuar.usuario_id = ".$id_usurio;
                    
        writeLog("ProductoService.PreventaServicio. QUERY ". $query);

        $result= $this->dbHandler->querySrting($query);
        if(!isset($result[0]))
        {
            return null;
        }

        //actualizamos la compra el usuario comprador
        $var= [
            'ID_USUARIO' => $id_usurio,
        ];
        $where= [
            'linkPago' => $linkPago
        ];      
        
        $this->dbHandler->update('compra', $var , $where);
        
        $ref = $result[0]['referencia'];
        
        $FechaExpiracion="";
        $cadena_concatenada = $ref. (int) round(floatval($result[0]['Monto']) * 100) .$result[0]['Moneda'].$FechaExpiracion.$result[0]['secret_integr'];
        
        $secret_integr = hash ("sha256", $cadena_concatenada);
        $cadena_concatenada = $ref.$result[0]['Monto'].$result[0]['Moneda'].$FechaExpiracion.$result[0]['secret_integr'];
        
        $salida = [
                    'ref' => $ref,
                    'email' => $result[0]['email'],
                    'nombre' => $result[0]['nombre'],
                    'telefono' => $result[0]['telefono'],
                    'documento' => $result[0]['documento'],
                    'tipo_documento' => $result[0]['tipo_documento'],
                    
                    'secret_integr' => $secret_integr,
                    'public_key' => $result[0]['public_key'],
                    
                    'monto' =>   (int) round(floatval($result[0]['Monto']) * 100),
                    'moneda' => $result[0]['Moneda']
                 ]; 

        return $salida;
    }

    public function link_Pago($monto, $id_producto, $id_usurio)
    {
        // writeLog("ProductoService.link_Pago. INICIO");
        $query = "SELECT 
        prec.Moneda, ".$monto." as Monto,
        prod.id_vendedor,
        tiend.nombrefix as nombre
        FROM  producto_venta prod 
        INNER JOIN precios prec on prod.id = prec.id_producto AND prec.activo = 1 
        INNER JOIN tienda tiend on tiend.id_vendedor = prod.id_vendedor 
        WHERE prod.id = ".$id_producto;
        writeLog("ProductoService.link_Pago. QUERY ". $query);

        $result= $this->dbHandler->querySrting($query);

        if(!isset($result[0]))
        {
            return null;
        }

        $ref = password_hash(date('Y-m-d H:i:s').$id_usurio, PASSWORD_DEFAULT);

        $carrito = new CarritoService($this->dbHandler);

        $id_carrito = $carrito->crearCarrito($id_usurio,$result[0]['id_vendedor']);
        $carrito->agregarProductoAlCarrito($id_carrito, $id_producto, 1, $monto);
        
      $link_pago = $this->insertar_Compra($ref, $id_producto, $id_usurio, $result[0]['Monto'], $result[0]['Moneda'], $result[0]['id_vendedor'], $id_carrito);
      
      writeLog("ProductoService.link_Pago. QUERY ". $link_pago);
        
       return ['ref' => $link_pago, 'nombre' =>$result[0]['nombre']];
    }

    function productoTienda($id_producto, $ref)
    {       
        if($ref == 'servicio')
        {
            $ref=null;
        }
        
        $query = "SELECT 
            prod.ID as id, 
            prod.id_vendedor, 
            prod.nombre as name, 
            prod.categoria, 
            prod.tipo, 
            prod.stock, 
            prod.descripcion,
            COALESCE(carr_det.subtotal, prec.Monto) as price, -- Usa Monto de compra si existe, sino el de precio
            0 as discount,
            COALESCE(carr_det.subtotal, prec.Monto) as previousPrice,
            'Aun no cuenta con cuotas' as installments, 
            'No cuenta con envios' as shipping, 
            0 as imagenActualIndex,
            'TVTCL32' as code, 
            tiend.nombre as seller,
            tiend.direccion as direccion,
            tiend.Linkdireccion as linkdireccion,
            doc_banner.ruta as banner, 
            doc_logo.ruta as logo,
            prod.id_vendedor
        FROM producto_venta prod                    
        INNER JOIN tienda tiend 
            ON tiend.id_vendedor = prod.id_vendedor 
            AND prod.activo = 1
        INNER JOIN precios prec 
            ON prod.ID = prec.id_producto  
            AND prec.activo = 1 
        INNER JOIN documentos doc_logo 
            ON doc_logo.id_vendedor = tiend.id_vendedor 
            AND doc_logo.tipo_archivo = 'logo'                 
        INNER JOIN documentos doc_banner 
            ON doc_banner.id_vendedor = tiend.id_vendedor 
            AND doc_banner.tipo_archivo = 'banner'
        LEFT JOIN compra comp ON comp.linkPago = " . ($ref ? "'$ref'" : "NULL") . " -- Evita error si $ref es vacío o NULL
        LEFT JOIN carrito carr  ON carr.id = comp.id_carrito 
        LEFT JOIN carrito_detalle carr_det on carr_det.id_carrito = carr.id
        WHERE prod.ID = ".$id_producto;
     //   writeLog("ProductoService.Listatienda " . $query); //, '/logs/app.log'
        try {
            // Ejecuta la consulta de productos
            $result = $this->dbHandler->querySrting($query);
            if (!$result) {
                return null;
            }                  
            
            // Obtener los IDs de los productos
            $productIds = array_map(function($producto) {
                return $producto['id'];
            }, $result);
            // Consultar todas las imágenes asociadas a esos productos de una sola vez
            $imagenesPorProducto = [];
            if (!empty($productIds)) {        
                $idsString = implode(',', $productIds);
                $queryimagenes = "SELECT doc.id_producto, doc.id_documento, doc.ruta, doc.nombre_archivo as name 
                                FROM documentos doc 
                                WHERE doc.id_producto IN ($idsString)";
                $imagenesResult = $this->dbHandler->querySrting($queryimagenes);
                
                // Agrupar las imágenes por id_producto
                foreach ($imagenesResult as $imagen) {
                    $pid = $imagen['id_producto'];
                    if (!isset($imagenesPorProducto[$pid])) {
                        $imagenesPorProducto[$pid] = [];
                    }
                    $imagenesPorProducto[$pid][] = [
                        'ruta' => $imagen['ruta'],
                        'name' => $imagen['name'],
                        'id_imagen' => $imagen['id_documento'],
                    ];
                }
            }
            
            // Asignar a cada producto su listado de imágenes
            foreach ($result as &$producto) {
                $pid = $producto['id'];
                $producto['imageUrl'] = isset($imagenesPorProducto[$pid]) ? $imagenesPorProducto[$pid] : [];
            }    
                    // agregamos lista de caracteristicas
                    $queryCaracterisiticas = "SELECT prod_car.caracteristica_id, prod_car.valor, caract.nombre
                    FROM producto_caracteristica prod_car
                    INNER JOIN caracteristicas caract  on caract.id= prod_car.caracteristica_id
                    WHERE prod_car.producto_id = ".$id_producto; 

                    $caracteristicas= $this->dbHandler->querySrting($queryCaracterisiticas);
                    $caracteristicas_array = []; // Array donde se almacenarán las imágenes

                    foreach ($caracteristicas as $caracter) {
                    $caracteristicas_array[] = [
                    'nombre'      => $caracter['nombre'],
                    'id' => $caracter['caracteristica_id'],
                    'valor' => $caracter['valor']
                    ];
                    }
                    $result[0]['caracteristicas'] = $caracteristicas_array;

            return $result;
            
        } catch (PDOException $e) {
            throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
        }
    }

    public function postFormaPago($datos)
        {      
                $var= [
                    'nombre' => $datos['nombre'],
                    'id_vendedor' => $datos['id_vendedor'],
                    'public_key' => $datos['public_key'],
                    'priv_key' => $datos['priv_key'],
                    'secret_event' => $datos['secret_event'],
                    'secret_integr' => $datos['secret_integr'],
                    ];                                      
            
            $result= $this->dbHandler->insert('metodo_pago', $var);
            return $datos;
    }
      
    public function putFormaPago($datos)
    {      
        $query = "SELECT id , nombre
                    FROM metodo_pago
                WHERE id_vendedor = ".$datos['id_vendedor']; 
        
        try {
            // Ejecuta la consulta de productos
            $result = $this->dbHandler->querySrting($query);
            
            $var= [
                'nombre' => $datos['nombre'],
                'id_vendedor' => $datos['id_vendedor'],
                'public_key' => $datos['public_key'],
                'priv_key' => $datos['priv_key'],
                'secret_event' => $datos['secret_event'],
                'secret_integr' => $datos['secret_integr'],
                ];   
               
            $id = $this->buscarIdPorNombre($result, $datos['nombre']);
                 
            $condicion =['id' => $id == null ? $result[0]['id'] : $id];
            //actualizamos datos de la tienda
            $this->dbHandler->update('metodo_pago',  $var, $condicion);
            } catch (PDOException $e) {
                writeLog("ProductoService.ListaXUsuario " . $e->getMessage(), '/logs/app.log');
                throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
            }
            
            return $datos;
    }

    private function buscarIdPorNombre($array, $nombreBuscado) {
        foreach ($array as $elemento) {
            if (isset($elemento['nombre']) && trim($elemento['nombre']) === $nombreBuscado) {
                return $elemento['id'];
            }
        }
        return null; // o false, si no se encuentra
    }

    private function controlarTienda($nombretienda)
    {
        $query = "SELECT         
        tiend.nombrefix as nombre
        FROM  tienda tiend   
        WHERE tiend.nombrefix ='".str_replace(' ', '', $nombretienda)."'";
        
        writeLog("ProductoService.controlarTienda. QUERY ". $query);

        $result= $this->dbHandler->querySrting($query);

        if(!isset($result[0]))
        {
            return true;
        } 
        return false;
    }
    
    public function agregarCaracteristicas(array  $datos,int $id_producto)
    {
        $query = "DELETE FROM producto_caracteristica  
                    WHERE producto_id = ".$id_producto; 
    try {
        // eliminamos todas caracteristicas de este producto
        $this->dbHandler->querySrting($query);

        foreach ($datos as $caracteristica) {
                $var= [
                    'producto_id' =>$id_producto,
                    'caracteristica_id' => $caracteristica['caracteristica_id'],
                    'valor' => $caracteristica['valor'],
                    ];                                      
                $result= $this->dbHandler->insert('producto_caracteristica', $var);
                // writeLog("ProductoService.agregarCaracteristicas.  caracteristica ID ". $result);
            }
            
         return $datos;
    } catch (PDOException $e) {
        
    }
    }
}