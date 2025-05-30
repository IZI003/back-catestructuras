<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../MiLog.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Importante
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

        if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
            die("No se encuentra autoload.php en el path esperado");
        }
        $resultado = [];

        if ($tipo === 'hikvision') {
            $spreadsheet = IOFactory::load($ruta);
            $hoja = $spreadsheet->getActiveSheet();
            foreach ($hoja->getRowIterator(2) as $fila) {
                $celdas = $fila->getCellIterator();
                $celdas->setIterateOnlyExistingCells(false);

                $valores = [];
                foreach ($celdas as $celda) {
                    $valores[] = trim($celda->getValue());
                }

                $resultado[] = [
                    'legajo' => ltrim($valores[0], "'"),
                    'nombre' => $valores[1],
                    'fechaHora' => $valores[3], // Suponiendo columna 3 = fecha y hora
                    'tipo_origen' => $tipo
                ];

                $this->guardarEnBD($resultado[count($resultado)-1]); // Guardar individualmente
            }

        }  elseif ($tipo === 'gadnic') {
            $reader = new Xls(); 
            $spreadsheet = $reader->load($ruta);
            $hoja = $spreadsheet->getSheetByName('Anormal');
            
            foreach ($hoja->getRowIterator(5) as $fila) {
                $celdas = $fila->getCellIterator();
                $celdas->setIterateOnlyExistingCells(false);
        
                $valores = [];
                foreach ($celdas as $celda) {
                    $valores[] = trim((string) $celda->getValue());
                }
        
                $id      = $valores[0]; // Legajo
                $nombre  = $valores[1]; // Nombre
                $fecha   = $valores[3]; // Fecha en formato yyyy-mm-dd
                $horas   = [$valores[4], $valores[5], $valores[6], $valores[7]]; // Horas AM/PM
       // writeLog("DocumentosController.procesarArchivo. nombre ".$nombre);
        
                foreach ($horas as $hora) {
                    $fechaHora = $this->formatearHora($hora, $fecha);
                    if ($fechaHora) {
                        $registro = [
                            'legajo' => $id,
                            'nombre' => $nombre,
                            'fechaHora' => $fechaHora,
                            'tipo_origen' => $tipo
                        ];
                        $resultado[] = $registro;
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
            'f_creacion'    => date('Y-m-d H:i:s')
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

    public function exportarPorFecha($fecha)
    {
        // Consultar registros
        $sql = "SELECT legajo, nombre, fecha_hora FROM rrhh_reloj WHERE DATE(fecha_hora) = '".$fecha."'";
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
}