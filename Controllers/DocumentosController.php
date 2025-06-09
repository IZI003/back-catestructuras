<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../SERVICIOS/DocumentosService.php';
require_once __DIR__ . '/../MiLog.php';

class DocumentosController extends BaseController
{
    public function upload($method)
    {
        // Verificar que el método sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $salida = (new salidaError())->response(405, 'Método no permitido');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }
        
        // Verificar que se hayan enviado los datos mínimos
        if (!isset($_POST['id_vendedor']) || !isset($_POST['tipo'])) {
            $salida = (new salidaError())->response(422, 'Faltan parámetros obligatorios');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }
        
        $id_vendedor = $_POST['id_vendedor'];
        // id_producto es opcional, puede venir vacío o no enviarse
        $id_producto = isset($_POST['id_producto']) && !empty($_POST['id_producto']) ? $_POST['id_producto'] : null;
        $tipo = $_POST['tipo'];  // Ej: "producto", "logo", "stand"
        
        // Verificar que se haya subido un archivo sin errores
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== 0) {
            $salida = (new salidaError())->response(400, 'Error en la carga del archivo');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }
        
        // Configuración de la carpeta base de uploads (ajusta la ruta según tu estructura)
        $baseDir = __DIR__ . '/../../uploads'; 
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        
        // Crear carpeta para el id_vendedor
        $folderVendedor = $baseDir . '/' . $id_vendedor;
        if (!is_dir($folderVendedor)) {
            mkdir($folderVendedor, 0777, true);
        }
        
        // Determinar la carpeta destino:
        // Si es de tipo "producto" y se envía id_producto, se guarda en una subcarpeta específica del producto.
        // Para otros tipos, se guarda en una carpeta nombrada según el tipo.
        if ($tipo === 'producto' && !empty($id_producto)) {
            $folderProducto = $folderVendedor . '/' . $id_producto;
            if (!is_dir($folderProducto)) {
                mkdir($folderProducto, 0777, true);
            }
            $destinationFolder = $folderProducto;
        } else {
            $folderTipo = $folderVendedor . '/' . $tipo;
            if (!is_dir($folderTipo)) {
                mkdir($folderTipo, 0777, true);
            }
            $destinationFolder = $folderTipo;
        }
        
        // Procesar el archivo
        $originalFileName = basename($_FILES['archivo']['name']);
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        // Generar un nombre único para evitar colisiones
        $newFileName = uniqid("doc_", true) . "." . $extension;
        $destinationPath = $destinationFolder . '/' . $newFileName;
        
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destinationPath)) {
            // Preparar los datos para insertar en la tabla "documentos"
            // Se guarda la ruta relativa para facilitar su uso posterior
            $relativePath = str_replace(__DIR__ . '/../../', '', $destinationPath);
            $documentos = new ProductoService();
            $documentData = [
                'id_vendedor'   => $id_vendedor,
                'id_producto'   => $id_producto,
                'tipo_archivo'  => $tipo,
                'ruta'          => $relativePath,
                'nombre_archivo'=> $originalFileName
            ];
            // Insertar en base de datos (ajusta según tu clase y método de inserción)
            $result =$documentos->postDocumentos($datos);

            if ($result) {
                $salida = (new salidaError())->response(200, 'Archivo cargado exitosamente');
                $salida->datos = $documentData;
                $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            } else {
                $salida = (new salidaError())->response(500, 'Error al guardar en la base de datos');
                $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            }
        } else {
            $salida = (new salidaError())->response(500, 'Error al mover el archivo');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
        }
    }

    public function importar($method)
    {
       // writeLog("DocumentosController.importar. INICIO");

        error_log("POST: " . print_r($_POST, true));
        error_log("FILES: " . print_r($_FILES, true));
         // Verificar que el método sea POST
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $salida = (new salidaError())->response(405, 'Método no permitido');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }
        // Verificar que se hayan enviado los datos mínimos
        if (!isset($_FILES['archivo']) || !isset($_POST['tipo'])) {
            $salida = (new salidaError())->response(422, 'Faltan parámetros obligatorios');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }
        
        $tipo = $_POST['tipo'];
        $archivo = $_FILES['archivo'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $salida = (new salidaError())->response(422, 'Error al subir el archivo');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }

        $servicio = new DocumentosService();
        $datos = $servicio->procesarArchivo($archivo['tmp_name'], $tipo);

        $salida = new salida_error();
        $salida_error = new salidaError();


        $salida = $salida_error->response(200, 'OK');
        $salida->datos = $datos;
        $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
    }

    public function exportar_reloj($method)
    {
        $servicio = new DocumentosService();
        $salida_error = new salidaError();
        $salida = new salida_error();
        
        if ($method === 'get') {
            try {
                if (isset($_GET['preview']) && $_GET['preview'] === 'true') {
                // Solo validar si hay datos
                $hayDatos = null;
                    if(isset($_GET['fecha']))
                    {
                        $fecha = $_GET['fecha'] ?? date('Y-m-d');
                        $hayDatos = $servicio->tieneRegistros($fecha);
                    }
                    else
                    {
                        if(isset($_GET['mes']) && isset($_GET['anio']) && isset($_GET['legajo']))
                            {
                                $hayDatos = $servicio->tieneRegistros_legajo($_GET['anio'], $_GET['mes'], $_GET['legajo']);
                            }
                          if(isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta']))
                            {
                                $hayDatos = $servicio->tieneRegistros_Rango_fecha($_GET['fecha_desde'], $_GET['fecha_hasta']);
                            }  
                    }

                header('Content-Type: application/json');
                echo json_encode([
                    'estado' => $hayDatos ? 'ok' : 'vacio'
                ]);
                exit;
            }
            //para quien exportamos? 
            if(isset($_GET['mes']) && isset($_GET['anio']) && isset($_GET['legajo']))
            {
                return $servicio->exportarPor_Fecha_Legajo($_GET['legajo'], $_GET['anio'], $_GET['mes'], null, null);
            }
            else{
                if(isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta']))
                    {
                        return $servicio->exportarPor_Fecha_Legajo(null, null, null, $_GET['fecha_desde'], $_GET['fecha_hasta']);

                    }  else
                    {
                        $fecha = $_GET['fecha'] ?? date('Y-m-d');
                        return $servicio->exportarPorFecha($fecha);
                    }
            }
                
         } catch (Error $e) {
                $salida = $salida_error->response(500, $e->getMessage());
            }
        }
    }

    public function importar_personal($method)
    {
       // writeLog("DocumentosController.importar_personal. INICIO");

        error_log("POST: " . print_r($_POST, true));
        error_log("FILES: " . print_r($_FILES, true));
         // Verificar que el método sea POST
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $salida = (new salidaError())->response(405, 'Método no permitido');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }
        // Verificar que se hayan enviado los datos mínimos
        if (!isset($_FILES['archivo'])) {
            $salida = (new salidaError())->response(422, 'Faltan parámetros obligatorios');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }

        $archivo = $_FILES['archivo'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $salida = (new salidaError())->response(422, 'Error al subir el archivo');
            $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
            return;
        }

        $servicio = new DocumentosService();
        $datos = $servicio->importarPersonal($archivo['tmp_name']);

        $salida = new salida_error();
        $salida_error = new salidaError();


        $salida = $salida_error->response(200, 'OK');
        $salida->datos = $datos;
        $this->sendOutput(json_encode($salida), array($salida->error->Descripcion, $salida->error->CodigoHttp));
    }
}