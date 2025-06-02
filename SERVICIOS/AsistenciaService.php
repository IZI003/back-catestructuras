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

            $sql="SELECT usua.legajo, usua.nombre,  reloj.fecha_hora as fechaHora, 
                        CASE 
                                WHEN reloj.fecha_hora IS NULL THEN 'No registra'
                                WHEN TIME(reloj.fecha_hora) BETWEEN '07:00:00' AND '08:10:00' THEN 'OK'
                                WHEN TIME(reloj.fecha_hora) > '08:10:00' THEN 'TARDE'
                                ELSE 'No registra'
                            END AS estado
                    FROM personal usua 
                    LEFT JOIN rrhh_reloj reloj on reloj.legajo = usua.legajo 
                    AND DATE(reloj.fecha_hora) BETWEEN '".$fecha_desde."' AND '".$fecha_desde."'
                ";
     //   writeLog("AsistenciaService.lista_asistencia. QUERY: ".$sql);

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