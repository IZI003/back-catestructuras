<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';

class UsuarioService 
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


    public function lista_asistencia()
    {
            $sql="SELECT usua.legajo, usua.nombre,  reloj.fecha_hora as fechaHora, 
                        CASE 
                                WHEN reloj.fecha_hora IS NULL THEN 'No registra'
                                WHEN TIME(reloj.fecha_hora) BETWEEN '07:00:00' AND '08:10:00' THEN 'OK'
                                WHEN TIME(reloj.fecha_hora) > '08:10:00' THEN 'TARDE'
                                ELSE 'No registra'
                            END AS estado
                    FROM usuarios usua 
                    LEFT JOIN rrhh_reloj reloj on reloj.legajo = usua.legajo 
                    AND DATE(reloj.fecha_hora) BETWEEN ? AND ?
                WHERE usua.activo=1";

            return $this->dbHandler->querySrting($sql); 
    }
   /* public function get($condiciones)
    {
        return  $this->dbHandler->get($this->table, $condiciones);
    }

    public function post($data)
    {
        $result = $this->dbHandler->insert($this->table,  $data);
        return $result;
    }
    
    public function put($data, $condicion)
    {
        $result = $this->dbHandler->update($this->table,  $data,$condicion);
        return $result;
    }

    public function delete( $condicion)
    {
        $result = $this->dbHandler->delete($this->table, $condicion);
        
        return $result;
    }

    public function getvendedor($condiciones)
    {        
        return  $this->dbHandler->get('metodo_pago', $condiciones);
    }

    public function getCompras($id_usuario)
    {       
        $sql ="SELECT compra.id_compra, carr_det.id_producto, carr_det.cantidad, 
            carr_det.subtotal as monto, tiend.nombre as tienda, product.nombre 
            FROM carrito carr 
            INNER JOIN compra compra on compra.id_carrito = carr.id
            INNER JOIN carrito_detalle carr_det on carr_det.id_carrito = compra.id_carrito
            INNER JOIN producto_venta product on product.ID = carr_det.id_producto
            INNER JOIN tienda tiend on tiend.id_vendedor=carr.ID_VENDEDOR
            WHERE carr.id_usuario = ".$id_usuario;
            
        return $this->dbHandler->querySrting($sql); 
    }

    public function getComprasAgrupadas($id_usuario)
    {
        $sql = "SELECT compra.id_compra,
                     CASE 
                         WHEN pag.id_pasarela THEN 'PAGADO'
                        ELSE 'PAGO NO INFORMADO'
                    END AS pago,  
                    pag.fecha,
                    carr.total , 
                    tiend.nombre as tienda,
                    carr_det.id_producto,
                     carr_det.cantidad, 
                    carr_det.subtotal as monto,
                    product.nombre as producto_nombre
                FROM carrito carr 
                INNER JOIN compra compra ON compra.id_carrito = carr.id
                INNER JOIN carrito_detalle carr_det ON carr_det.id_carrito = compra.id_carrito
                INNER JOIN producto_venta product ON product.ID = carr_det.id_producto
                INNER JOIN tienda tiend ON tiend.id_vendedor = carr.id_vendedor
                LEFT JOIN pagos pag ON pag.reference = compra.id_compra
                WHERE carr.id_usuario = " . intval($id_usuario) . "
                ORDER BY compra.id_compra DESC";

        $resultado = $this->dbHandler->querySrting($sql);

        $compras = [];

        foreach ($resultado as $fila) {
            $id = $fila['id_compra'];

            if (!isset($compras[$id])) {
                $compras[$id] = [
                    'id_compra' => $fila['id_compra'],
                    'pago' => $fila['pago'],
                    'fecha' => $fila['fecha'],
                    'total' => $fila['total'],
                    'tienda' => $fila['tienda'],
                    'productos' => []
                ];
            }

            $compras[$id]['productos'][] = [
                'nombre' => $fila['producto_nombre'],
                'cantidad' => $fila['cantidad'],
                'monto' => $fila['monto'],
            ];
        }

        return array_values($compras);
    }

    public function getVentas($id_usuario)
    {        
            $sql="SELECT 
                compra.id_compra, 
                carr_det.id_producto, 
                carr_det.cantidad, 
                carr_det.subtotal as monto,
                CONCAT(usua.nombre, ' ', usua.apellido) AS nombre_usuario, 
                product.nombre,
                CASE 
                    WHEN pagos.status = 'APPROVED' THEN 'Confirmado' 
                    ELSE 'Sin confirmar'
                END AS pago 
             FROM carrito carr 
            INNER JOIN carrito_detalle carr_det on carr_det.id_carrito = carr.id
            INNER JOIN compra compra on compra.id_carrito = carr.id
            INNER JOIN producto_venta product ON product.ID = carr_det.id_producto
            INNER JOIN usuarios usua ON usua.usuario_id = carr.id_usuario
            LEFT JOIN pagos pagos ON pagos.reference = compra.id_compra
             WHERE carr.id_vendedor = ".$id_usuario;

        return $this->dbHandler->querySrting($sql); 
    }

    public function getVentasAgrupadas($id_usuario)
    {
        $sql = "SELECT 
                    compra.id_compra, 
                    CONCAT(usua.nombre, ' ', usua.apellido) AS nombre_usuario, 
                    carr.total,
                    carr_det.id_producto, 
                    carr_det.cantidad, 
                    carr_det.subtotal as monto,
                    product.nombre AS producto_nombre,
                    CASE 
                        WHEN pagos.status = 'APPROVED' THEN 'Confirmado' 
                        ELSE 'Sin confirmar'
                    END AS pago,
                    pagos.fecha
                FROM carrito carr 
                INNER JOIN carrito_detalle carr_det ON carr_det.id_carrito = carr.id
                INNER JOIN compra compra ON compra.id_carrito = carr.id
                INNER JOIN producto_venta product ON product.ID = carr_det.id_producto
                INNER JOIN usuarios usua ON usua.usuario_id = carr.id_usuario
                LEFT JOIN pagos pagos ON pagos.reference = compra.id_compra
                WHERE carr.id_vendedor = " . intval($id_usuario) . "
                ORDER BY compra.id_compra DESC";

        $resultado = $this->dbHandler->querySrting($sql);

        $ventas = [];

        foreach ($resultado as $fila) {
            $id = $fila['id_compra'];

            if (!isset($ventas[$id])) {
                $ventas[$id] = [
                    'id_compra' => $fila['id_compra'],
                    'pago' => $fila['pago'],
                    'fecha' => $fila['fecha'],
                    'total' => $fila['total'],
                    'cliente' => $fila['nombre_usuario'],
                    'productos' => []
                ];
            }

            $ventas[$id]['productos'][] = [
                'nombre' => $fila['producto_nombre'],
                'cantidad' => $fila['cantidad'],
                'monto' => $fila['monto']
            ];
        }

        return array_values($ventas);
    }*/

}