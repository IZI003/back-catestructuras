<?php
require_once __DIR__ . '/Database.class.php';

class DatabaseHandler
{
    private $connection;

    public function __construct()
    {
        $db = new Database();
        $this->connection = $db->getConnection();
    }

    // Método para obtener datos (GET)
    public function get($table, $conditions = [])
    {
        $query = "SELECT * FROM $table";
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", array_map(function ($key) {
                return "$key = :$key";
            }, array_keys($conditions)));
        }

        $stmt = $this->connection->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para insertar datos (POST)
    public function insert($table, $data)
    {
       try{

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_map(fn($key) => ":$key", array_keys($data)));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->connection->prepare($query);
         // Binding de valores
         foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        // Ejecución
         if ($stmt->execute()) {
            return $this->connection->lastInsertId(); // Devuelve el ID insertado
        } else {
            return false; // Si hay un error, devuelve false
        }
     } catch (PDOException $e) {
            error_log("Error de PDO: " . $e->getMessage());
            throw new Exception("Ocurrió un error al insertar los datos: " . $e->getMessage());
        }
    }
    // Método para actualizar datos (PUT)
    public function update($table, $data, $conditions)
    {
       try{ 

        $setClause = implode(", ", array_map(function ($key) {
            return "$key = :$key";
        }, array_keys($data)));
        $whereClause = implode(" AND ", array_map(function ($key) {
            return "$key = :where_$key";
        }, array_keys($conditions)));

        $query = "UPDATE $table SET $setClause WHERE $whereClause";
        $stmt = $this->connection->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":where_$key", $value);
        }

        return $stmt->execute();
   } catch (PDOException $e) {
        error_log("Error de PDO: " . $e->getMessage());
        throw new Exception("Ocurrió un error al actualizar los datos: " . $e->getMessage());
    }
}

    // Método para eliminar datos (DELETE)
    public function delete($table, $conditions)
    {
       try
            {
                $whereClause = implode(" AND ", array_map(function ($key) {
                    return "$key = :where_$key";
                }, array_keys($conditions)));

                $query = "UPDATE $table SET activo = 0 WHERE $whereClause";
                
                $stmt = $this->connection->prepare($query);

                foreach ($conditions as $key => $value) {
                    $stmt->bindValue(":where_$key", $value);
                }

                return $stmt->execute();
            } catch (PDOException $e) {
                error_log("Error de PDO: " . $e->getMessage());
                throw new Exception("Ocurrió un error al actualizar los datos: " . $e->getMessage());
            }
    }
    
    public function querySrting($query)
    {
        try{ 
            $stmt = $this->connection->prepare($query);
            
            $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            } catch (PDOException $e) 
            {
                error_log("Error de PDO: " . $e->getMessage());
                
                throw new Exception("Ocurrió un error al actualizar los datos: " . $e->getMessage());
            }
    }
}