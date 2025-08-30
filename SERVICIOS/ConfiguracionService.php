<?php
require_once __DIR__ . '/../INCLUDES/DatabaseHandler.php';
require_once __DIR__ . '/../MiLog.php';

class ConfiguracionService
{
    private $dbHandler;
    private $table = 'categorias';
    private $table2 = 'caracteristicas';

    public function __construct()
    {
        $this->dbHandler = new DatabaseHandler();
    }
    
    function crearcategorias($nombre, $id_categoria) 
        {
            //controlamos que no exista una categoria no el mismo nombre
            $query = "SELECT id, nombre, super_categoria FROM categorias WHERE  nombre ='".$nombre."'";
            $result= $this->dbHandler->querySrting($query);

            if(isset($result[0]))
            {
                 return $result[0];
            }
            
            $data = [
                'nombre' => $nombre,
                'super_categoria' => $id_categoria
                ];
    
            return $this->dbHandler->insert($this->table, $data);//devuelve el id 
      }
      
   function verCategoria()
        {
            $query = "SELECT 
                        c1.id,
                        c1.nombre,
                        c2.nombre AS nombre_subcategoria
                     FROM categorias c1
                     LEFT JOIN categorias c2 ON c2.id = c1.super_categoria
                     ORDER BY c1.nombre, c2.nombre;";   

            return $this->dbHandler->querySrting($query);
        }
        
    function eliminarCategoria($id)
        {
            $query = "DELETE FROM categorias 
                      WHERE id = ".$id;   
            $result = $this->dbHandler->querySrting($query);
            
            return "OK";
        }
        
    function putCategoria($data, $condicion)
        {
            $con = [
                'nombre' => $data['nombre'],
                'super_categoria'=> $data['id_categoria'],
            ];
            
            $result = $this->dbHandler->update($this->table,  $con, $condicion);
            return $result;
        }
        
    public function Lista_caracteristicas()
        {
            $query ="SELECT id, nombre FROM caracteristicas ORDER BY nombre";
            return $this->dbHandler->querySrting($query);
        }
    
    public function postCaracteristicas($datos)
        {
            $var= ['nombre' => $datos['nombre']];                                      
            return $this->dbHandler->insert($this->table2, $var);
        }
}