<?php
    require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
    require_once __DIR__ . '/../MiLog.php';

    class TiendaProductoService
    {
        private $dbHandler;
        private $table = 'carrito_compra';
        
        public function __construct()
        {
            $this->dbHandler = new DatabaseHandler();
        } 
        
        public function get_carrito($id_usuario, $id_vendedor)
        {
            $query = "SELECT cart.id, cart.cantidad, cart.id_producto,
                            prod.nombre,
                            pre.Monto as precio_unidad, pre.Monto * cart.cantidad as precio_total 
                            , (prod.stock >= cart.cantidad AND prod.activo = 1) as disponible
                        FROM `carrito_compra` cart
                        INNER JOIN producto_venta prod on (prod.ID = cart.id_producto)
                        INNER JOIN precios pre on (pre.id_producto = cart.id_producto AND pre.activo = 1)
                        WHERE  cart.id_comprador = ".$id_usuario." AND cart.id_vendedor = ".$id_vendedor;   
                                    
            try {
                // Preparar la consulta
                $result= $this->dbHandler->querySrting($query);
            
                return $result ?: null;
            } catch (PDOException $e) {
                writeLog("TiendaProductoService.get_carrito ".$e->getMessage());
                throw new Exception("Ocurrió un error al obtener los datos: " . $e->getMessage());
            } 
        }

        public function post_item_carrito($datos)
        {
            writeLog("TiendaProductoService.Cart. INICIO");

            $data = [
                'id_comprador' => $datos['id_comprador'],
                'id_vendedor' => $datos['id_vendedor'], 
                'cantidad' => $datos['cantidad'],
                'id_producto' => $datos['id_producto']
            ];
            
            $result= $this->dbHandler->insert($this->table, $data);
            writeLog("TiendaProductoService.Cart. llamando al servicio ".json_encode($result));
            
            if(!isset($result))
            {
                return null;
            }
            
            $salida = [
            'status' =>"OK"
            ];

            return $salida;
        }

        public function delete_item_carrito($datos)
        {
            $condicion = [
                'id' => $datos['id'],
            ];
            
            $result = $this->dbHandler->delete($this->table, $condicion);
            
            if(!isset($result))
            {
                return null;
            }
            
            $salida = [
            'status' =>"OK"
            ];

            return $salida;
        }
    }

    