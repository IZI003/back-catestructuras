<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../MiLog.php';

class AsistenciaService 
{
    private $dbHandler;
    private $table = 'usuarios';

    public function __construct()
    {
        $this->dbHandler = new DatabaseHandler();
    }

    public function getlista()
    {
        $sql="SELECT usuario_id, usua.nombre, usua.apellido, usua.email, usua.telefono, usua.creado, tipo.N_tipo_usuario as tipo, 
                        usua.pasarela, usua.facturacion, tiend.rut
                FROM usuarios usua 
                INNER JOIN tipo_usuario tipo on tipo.ID = usua.tipo_usuario
                LEFT JOIN metodo_pago metodo on metodo.id_vendedor = usua.usuario_id
                LEFT JOIN tienda tiend on tiend.id_vendedor = usua.usuario_id
              WHERE usua.activo=1";

        return $this->dbHandler->querySrting($sql); 
    }

    public function lista_asistencia($fecha_desde, $fecha_hasta)
    {
            $sql="SELECT usua.legajo, usua.nombre, reloj.fecha_hora as fechaHora, 
                        CASE 
                                WHEN reloj.fecha_hora IS NULL THEN 'No registra'
                                WHEN TIME(reloj.fecha_hora) BETWEEN '07:00:00' AND '08:11:00' THEN 'OK'
                                WHEN TIME(reloj.fecha_hora) > '08:11:00' THEN 'TARDE'
                                WHEN TIME(reloj.fecha_hora) > '13:00:00' THEN 'salida'
                                ELSE 'No registra'
                            END AS estado
                    FROM personal usua 
                    LEFT JOIN rrhh_reloj reloj on reloj.legajo = usua.legajo 
                     AND DATE(reloj.fecha_hora) BETWEEN '$fecha_desde' AND '$fecha_hasta'
                ";
     //   writeLog("AsistenciaService.lista_asistencia. QUERY: ".$sql);

            return $this->dbHandler->querySrting($sql); 
    }

    public function lista_asistencia_por_legajo($legajo, $anio, $mes)
        {
        $fecha_desde = "$anio-$mes-01";
        $fecha_hasta = date("Y-m-t", strtotime($fecha_desde)); // último día del mes

        $sql = "
                        WITH RECURSIVE fechas AS (
                                SELECT DATE('$fecha_desde') AS fecha
                                UNION ALL
                                SELECT DATE_ADD(fecha, INTERVAL 1 DAY)
                                FROM fechas
                                WHERE fecha < DATE('$fecha_hasta')
                        )
                        SELECT 
                                '$legajo' AS legajo,
                                usua.nombre,
                                CONCAT(fechas.fecha, ' ', TIME(IFNULL(reloj.fecha_hora, '00:00:00'))) AS fechaHora,
                                CASE 
                                WHEN reloj.fecha_hora IS NULL THEN 'No registra'
                                WHEN TIME(reloj.fecha_hora) BETWEEN '07:00:00' AND '08:11:00' THEN 'OK'
                                WHEN TIME(reloj.fecha_hora) BETWEEN '08:11:00' AND '12:00:00' THEN 'TARDE'
                                WHEN TIME(reloj.fecha_hora) > '13:00:00' THEN 'salida'
                                ELSE 'No registra'
                                END AS estado
                        FROM fechas
                        LEFT JOIN rrhh_reloj reloj 
                                ON DATE(reloj.fecha_hora) = fechas.fecha 
                                AND reloj.legajo = '$legajo'
                        LEFT JOIN personal usua ON usua.legajo = '$legajo'
                        ORDER BY fechas.fecha
                        ";

        return $this->dbHandler->querySrting($sql);
        }

    public function lista_Personal()
    {
            $sql="SELECT * FROM personal order by legajo";
            return $this->dbHandler->querySrting($sql); 
    }

    public function eliminar_Personal($id_personal)
    {
            $sql="Delete FROM personal where id = ".$id_personal;
            return $this->dbHandler->querySrting($sql); 
    }
}