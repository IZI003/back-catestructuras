<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../MiLog.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Importante
require_once __DIR__ . '/AsistenciaService.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

//composer require phpoffice/phpspreadsheet

class DocumentosService
{
    private $dbHandler;
    private $table = 'documentos';
    
    public function __construct()
    {
        $this->dbHandler = new DatabaseHandler();
    }

    public function postDocumentos($datos)
    {
        $documentData = [
            'id_vendedor'   => $datos['id_vendedor'],
            'id_producto'   => $datos['id_producto'],
            'tipo_archivo'  => $datos['tipo_archivo'],
            'ruta'          => $datos['ruta'],
            'nombre_archivo'=> $datos['nombre_archivo'],
            'f_creacion'    => date('Y-m-d H:i:s')
        ];
        
        // Insertar en base de datos (ajusta según tu clase y método de inserción)
        $result = $this->dbHandler->insert('documentos', $documentData);
        
        if(!isset($result))
        {
            return null;
        }
        
        $salida = [
        'status' =>"OK"
        ];

        return $salida;
    }


    public function procesarArchivo($ruta, $tipo)
    {
        writeLog("DocumentosService.procesarArchivo. INIO ");
        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            die("No se encuentra autoload.php en el path esperado");
        }
        $resultado = [];
        $limpieza=false; //pasar una sola vez la limpieza de ese dia.
        if ($tipo === 'hikvision') {
            $spreadsheet = IOFactory::load($ruta);
            $hoja = $spreadsheet->getActiveSheet();
            foreach ($hoja->getRowIterator(2) as $fila) {
                $celdas = $fila->getCellIterator();
                $celdas->setIterateOnlyExistingCells(false);

                $valores = [];
                foreach ($celdas as $celda) {
                    $valor = $celda->getValue();
                    $valores[] = trim($valor ?? '');
                }

                $resultado[] = [
                    'legajo' => ltrim($valores[0], "'"),
                    'nombre' => $valores[1],
                    'fechaHora' => $valores[3], // Suponiendo columna 3 = fecha y hora
                    'tipo_origen' => $tipo
                ];

                $this->guardarEnBD($resultado[count($resultado)-1]); // Guardar individualmente
            }

        }  elseif ($tipo === 'gadnic')
         {
            writeLog("DocumentosService.procesarArchivo. TIPO gadnic ");

                $reader = new Xls(); 
                $spreadsheet = $reader->load($ruta);
                $hoja = $spreadsheet->getSheetByName('Anormal');
            writeLog("DocumentosService.procesarArchivo. INGGRESO AL ARCHIVO");
                
                foreach ($hoja->getRowIterator(5) as $fila) {
                    $celdas = $fila->getCellIterator();
                    $celdas->setIterateOnlyExistingCells(false);
            writeLog("DocumentosService.procesarArchivo. CELDAS");
            
                    $valores = [];
                    foreach ($celdas as $celda) {
                        $valores[] = trim((string) $celda->getValue());
                    }
            writeLog("DocumentosService.procesarArchivo. datos");
            
                    $id      = $valores[0]; // Legajo
                    $nombre  = $valores[1]; // Nombre
                    $fecha   = $valores[3]; // Fecha en formato yyyy-mm-dd
                    $horas   = [$valores[4], $valores[5], $valores[6], $valores[7]]; // Horas AM/PM

            writeLog("DocumentosController.procesarArchivo. nombre ".$nombre);
                    //controlamos si ya se guardo para esa fecha la asistencia 
                    if(!$limpieza)
                    {
                        $this->eliminar_registros($fecha, $tipo);
                        $limpieza =true;
                    }
            writeLog("DocumentosService.procesarArchivo. POR ENTRAR AL FOREACH ");

                    foreach ($horas as $hora) {
                        $fechaHora = $this->formatearHora($hora, $fecha);
                        if ($fechaHora) {
                        writeLog("DocumentosService.procesarArchivo. POR fechaHora ".$fechaHora);

                            $registro = [
                                'legajo' => $id,
                                'nombre' => $nombre,
                                'fechaHora' => $fechaHora,
                                'tipo_origen' => $tipo
                            ];
                             $resultado[] = [
                                    'legajo' => $id,
                                    'nombre' => $nombre,
                                    'fechaHora' => $fechaHora,// Suponiendo columna 3 = fecha y hora
                                    'tipo_origen' => $tipo
                                ];
                            $this->guardarEnBD($registro);
                        }
                    }
            }
        }

        return $resultado;
    }

    private function formatearHora($hora, $fecha = null)
    {
        if (!$hora || trim($hora) === '') {
            return null;
        }

        $hora = trim($hora);
        $fechaBase = $fecha ?? date('Y-m-d');

        // Detectar si ya contiene fecha y hora
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $hora)) {
            return $hora;
        }

        $formatos = ['h:i A', 'H:i', 'h:i'];
        foreach ($formatos as $formato) {
            $dt = DateTime::createFromFormat($formato, $hora);
            if ($dt) {
                return $fechaBase . ' ' . $dt->format('H:i:s');
            }
        }

        return null;
    }

    private function guardarEnBD($registro)
    {
        $documentData = [
            'legajo'   => $registro['legajo'],
            'nombre'   => $registro['nombre'],
            'fecha_hora'  => $registro['fechaHora'],
            'f_creacion'    => date('Y-m-d H:i:s'),
            'tipo_origen'  => $registro['tipo_origen'],
        ];
        
        // Insertar en base de datos (ajusta según tu clase y método de inserción)
        $result = $this->dbHandler->insert('rrhh_reloj', $documentData);
        
        if(!isset($result))
        {
            return null;
        }
        
        $salida = [
        'status' =>"OK"
        ];

        return $salida;
    }

    private function eliminar_registros($fecha, $tipo)
    {
         try
          {
            $sql = "DELETE FROM rrhh_reloj 
                    WHERE DATE(fecha_hora) = DATE('".$fecha."') AND tipo_origen = '".$tipo."'";

            $this->dbHandler->querySrting($sql); 
        } catch (Exception $e) {
                   writeLog("DocumentosService.eliminar_registros. Error al eliminar historial,  ".$tipo." error ".$e);

        }
    }

    public function exportarPorFecha($fecha)
    {
        // Consultar registros
        $sql = "SELECT legajo, nombre, fecha_hora FROM rrhh_reloj 
        WHERE DATE(fecha_hora) = '".$fecha."' AND legajo ";
        //writeLog("DocumentosController.procesarArchivo. sql ".$sql);
        $registros = $this->dbHandler->querySrting($sql); 

        if (!$registros || count($registros) === 0) {
            http_response_code(204); // No Content
            exit;
        }

        // Crear Excel en memoria
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Registros del $fecha");

        // Encabezados
        $sheet->fromArray(['legajo', 'nombre', 'fecha_hora', 'estado'], NULL, 'A1');

        // Cargar datos con estado
        $fila = 2;
        foreach ($registros as $r) {
            $estado = $this->determinarEstadoDesdeHora($r['fecha_hora']);
            $sheet->setCellValue("A$fila", $r['legajo']);
            $sheet->setCellValue("B$fila", $r['nombre']);
            $sheet->setCellValue("C$fila", $r['fecha_hora']);
            $sheet->setCellValue("D$fila", $estado);
            $fila++;
        } 

        // Salida directa como archivo de descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="registros_' . $fecha . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit; // Terminar script
    }

    private function determinarEstadoDesdeHora($fechaHora)
    {
            $hora = (int) date('H', strtotime($fechaHora));

            if ($hora >= 7 && $hora < 9) {
                return 'entrada';
            } elseif ($hora >= 13) {
                return 'salida';
            }

            return '';
    }

    public function tieneRegistros($fecha)
    {
        $sql = "SELECT 1 FROM rrhh_reloj WHERE DATE(fecha_hora) = '".$fecha."' LIMIT 1";
        $registros = $this->dbHandler->querySrting($sql); 

        return !empty($registros);
    }

    public function tieneRegistros_legajo($anio, $mes, $legajo)
    {
          $fecha_desde = "$anio-$mes-01";
          $fecha_hasta = date("Y-m-t", strtotime($fecha_desde)); // último día del mes

        $sql = "SELECT 1 FROM rrhh_reloj
                 WHERE DATE(fecha_hora)  > '".$fecha_desde."' 
                 AND DATE(fecha_hora)  < '".$fecha_hasta."' 
                 AND legajo= ".$legajo."
                  LIMIT 1";
        $registros = $this->dbHandler->querySrting($sql); 

        return !empty($registros);
    }

    public function tieneRegistros_Rango_fecha($fecha_desde, $fecha_hasta)
    {
        $sql = "SELECT 1 FROM rrhh_reloj
                 WHERE DATE(fecha_hora)  >= '".$fecha_desde."' 
                 AND DATE(fecha_hora)  <= '".$fecha_hasta."' 
                  LIMIT 1";
        $registros = $this->dbHandler->querySrting($sql); 

        return !empty($registros);
    }
    public function importarPersonal($rutaExcel) {

     //  writeLog("DocumentosController.importarPersonal. INIO ");

        try {
            require_once 'vendor/autoload.php'; // asegúrate de que esto esté ya incluido
            $reader = new Xls(); 
            $spreadsheet = $reader->load($rutaExcel);
            $hoja = $spreadsheet->getSheetByName('Sheet1');
       //writeLog("DocumentosController.importarPersonal. filas ".json_encode($hoja));

            foreach ($hoja->getRowIterator(2) as $fila) {
                $celdas = $fila->getCellIterator();
                $celdas->setIterateOnlyExistingCells(false);

                $valores = [];
                foreach ($celdas as $celda) {
                    $valor = $celda->getValue();
                    $valores[] = is_null($valor) ? '' : trim((string)$valor);
                }

                $registro = [
                    'legajo' => $valores[2],
                    'nombre' => $valores[1],
                ];
                $this->dbHandler->insert('personal', $registro);

                        }
            
            $salida = [
            'status' =>"OK"
            ];

            return $salida;
            
        } catch (\Exception $e) {
            return ['success' => false, 'mensaje' => 'Error al importar: ' . $e->getMessage()];
        }
    }

    public function exportarPor_Fecha_Legajo($legajo, $anio, $mes, $fechaDesde, $fechaHasta )
    {
        $asistencia_serv = new AsistenciaService();
        $titulo="";
        $registros ="";
        if(isset($legajo)&& isset($anio) && isset($mes))
        {
            $titulo =" del $legajo fecha $mes-$anio";
            $registros = $asistencia_serv->lista_asistencia_por_legajo($legajo, $anio, $mes);
        }else
        {
            $titulo ="desde ".str_replace('/', '-', $fechaDesde);
            $registros = $asistencia_serv->lista_asistencia($fechaDesde, $fechaHasta);
        }
    
        if (!$registros || count($registros) === 0) {
            http_response_code(204); // No Content
            exit;
        }

        // Crear Excel en memoria
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Registros $titulo");

        // Encabezados
        $sheet->fromArray(['legajo', 'nombre', 'fecha_hora', 'estado'], NULL, 'A1');

        // Cargar datos con estado
        $fila = 2;
        foreach ($registros as $r) {

            $fechaOriginal = $r['fechaHora'];
            $fechaFormateada = '';
            if (!empty($fechaOriginal)) {
               // $dt = DateTime::createFromFormat('Y-m-d H:i', $fechaOriginal);
                $dt = DateTime::createFromFormat('dd/mm/yyyy hh:mm', $fechaOriginal);

                if (!$dt) {
                  //  $dt = DateTime::createFromFormat('Y-m-d H:i', $fechaOriginal);
                    $dt = DateTime::createFromFormat('dd/mm/yyyy hh:mm', $fechaOriginal);

                }
                $fechaFormateada = $dt ? $dt->format('dd/mm/yyyy hh:mm') : '';
            }
            $sheet->setCellValue("A$fila", $r['legajo']);
            $sheet->setCellValue("B$fila", $r['nombre']);
            $sheet->setCellValue("C$fila", $fechaFormateada);
            $sheet->setCellValue("D$fila", $r['estado']);
            $fila++;
        } 

        // Salida directa como archivo de descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="registros_' .$titulo. '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        ob_clean();
        flush();
        $writer->save('php://output');
        exit; // Terminar script
    }
}