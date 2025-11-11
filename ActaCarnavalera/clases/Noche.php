<?php
require_once "Database.php";

class Noche
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function add($fecha, $lugar, $numero_noche, $detalles)
    {
        $sql = "INSERT INTO Noche (fecha, lugar, numero_noche, detalles) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fecha, $lugar, $numero_noche, $detalles]);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM Noche";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivos()
    {
        $sql = "SELECT * FROM Noche WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEliminados()
    {
        $sql = "SELECT * FROM Noche WHERE deleted_at IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id_noche, $fecha, $lugar, $numero_noche, $detalles)
    {
        try {
            $sql = "UPDATE Noche SET fecha=?, lugar=?, numero_noche=?, detalles=? WHERE id_noche=?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$fecha, $lugar, $numero_noche, $detalles, $id_noche]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                throw new Exception("Ya existe otra noche con esa misma fecha o número.");
            } else {
                throw $e; 
            }
        }
    }


    public function delete($id_noche)
    {
        $sql = "UPDATE Noche SET deleted_at=NOW() WHERE id_noche=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
    }

    public function reactivar($id_noche)
    {
        $sql = "UPDATE Noche SET deleted_at=NULL WHERE id_noche=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
    }

    public function buscarPorFechaYLugar($fecha, $lugar)
    {
        $sql = "SELECT * FROM Noche 
                WHERE fecha = :fecha AND lugar = :lugar";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':fecha' => $fecha,
            ':lugar' => $lugar
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $query = "SELECT * FROM Noche WHERE id_noche = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getComparsasActivas()
    {
        $sql = "SELECT id_comparsa, nombre 
                FROM Comparsa 
                WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComparsasByNoche($id_noche)
    {
        $sql = "SELECT id_comparsa 
                FROM noche_comparsa 
                WHERE id_noche = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateComparsasNoche($id_noche, $comparsasSeleccionadas = [])
    {
        try {
            $this->db->beginTransaction();

           
            $sql_soft_delete = "UPDATE noche_comparsa 
                            SET deleted_at = NOW() 
                            WHERE id_noche = ? AND deleted_at IS NULL";
            $st = $this->db->prepare($sql_soft_delete);
            $st->execute([$id_noche]);

            
            $sql_check = "SELECT id_noche_comparsa, deleted_at 
                      FROM noche_comparsa 
                      WHERE id_noche = ? AND id_comparsa = ?";

            $sql_reactivate = "UPDATE noche_comparsa 
                           SET deleted_at = NULL, updated_at = NOW() 
                           WHERE id_noche_comparsa = ?";

            $sql_insert = "INSERT INTO noche_comparsa (id_noche, id_comparsa) 
                       VALUES (?, ?)";

            $checkStmt = $this->db->prepare($sql_check);
            $reactStmt = $this->db->prepare($sql_reactivate);
            $insertStmt = $this->db->prepare($sql_insert);

            foreach ($comparsasSeleccionadas as $id_comparsa) {
                
                $checkStmt->execute([$id_noche, $id_comparsa]);
                $rel = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($rel) {
                    
                    if ($rel['deleted_at'] !== null) {
                        $reactStmt->execute([$rel['id_noche_comparsa']]);
                    }
                    
                } else {
                    
                    $insertStmt->execute([$id_noche, $id_comparsa]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


   
    public function getCategoriasActivas()
    {
        $sql = "SELECT id_categoria, nombre 
                FROM Categoria 
                WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoriasByNoche($id_noche)
    {
        $sql = "SELECT id_categoria 
                FROM noche_categoria 
                WHERE id_noche = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateCategoriasNoche($id_noche, $categoriasSeleccionadas = [])
    {
        try {
            $this->db->beginTransaction();

            $sql_soft_delete = "UPDATE noche_categoria 
                            SET deleted_at = NOW() 
                            WHERE id_noche = ? AND deleted_at IS NULL";
            $st = $this->db->prepare($sql_soft_delete);
            $st->execute([$id_noche]);

            $sql_check = "SELECT id_noche_categoria, deleted_at 
                      FROM noche_categoria 
                      WHERE id_noche = ? AND id_categoria = ?";

            $sql_reactivate = "UPDATE noche_categoria 
                           SET deleted_at = NULL, updated_at = NOW() 
                           WHERE id_noche_categoria = ?";

            $sql_insert = "INSERT INTO noche_categoria (id_noche, id_categoria) 
                       VALUES (?, ?)";

            $checkStmt = $this->db->prepare($sql_check);
            $reactStmt = $this->db->prepare($sql_reactivate);
            $insertStmt = $this->db->prepare($sql_insert);

            foreach ($categoriasSeleccionadas as $id_categoria) {
                $checkStmt->execute([$id_noche, $id_categoria]);
                $rel = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($rel) {
                    if ($rel['deleted_at'] !== null) {
                        $reactStmt->execute([$rel['id_noche_categoria']]);
                    }
                } else {
                    $insertStmt->execute([$id_noche, $id_categoria]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    public function getDelegadosActivos()
    {
        $sql = "SELECT id_usuario, mail 
                FROM Usuario 
                WHERE deleted_at IS NULL 
                AND id_rol = (SELECT id_rol FROM Rol WHERE nombre_rol = 'Delegado' LIMIT 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDelegadosByNoche($id_noche)
    {
        $sql = "SELECT id_usuario 
                FROM noche_delegado 
                WHERE id_noche = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateDelegadosNoche($id_noche, $delegadosSeleccionados = [])
    {
        try {
            $this->db->beginTransaction();

            
            $sql_soft_delete = "UPDATE noche_delegado 
                            SET deleted_at = NOW() 
                            WHERE id_noche = ? AND deleted_at IS NULL";
            $st = $this->db->prepare($sql_soft_delete);
            $st->execute([$id_noche]);

            
            $sql_check = "SELECT id_noche_delegado, deleted_at 
                      FROM noche_delegado 
                      WHERE id_noche = ? AND id_usuario = ?";

            $sql_reactivate = "UPDATE noche_delegado 
                           SET deleted_at = NULL, updated_at = NOW() 
                           WHERE id_noche_delegado = ?";

            $sql_insert = "INSERT INTO noche_delegado (id_noche, id_usuario) VALUES (?, ?)";

            $checkStmt = $this->db->prepare($sql_check);
            $reactStmt = $this->db->prepare($sql_reactivate);
            $insertStmt = $this->db->prepare($sql_insert);

            foreach ($delegadosSeleccionados as $id_usuario) {
                $checkStmt->execute([$id_noche, (int) $id_usuario]);
                $rel = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($rel) {
                    if ($rel['deleted_at'] !== null) {
                        $reactStmt->execute([$rel['id_noche_delegado']]);
                    }
                } else {
                    $insertStmt->execute([$id_noche, (int) $id_usuario]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    public function getComparsasNombresByNoche($id_noche)
    {
        $sql = "SELECT c.nombre
                FROM noche_comparsa nc
                JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
                WHERE nc.id_noche = ? AND nc.deleted_at IS NULL AND c.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
        $nombres = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return implode(', ', $nombres);
    }

    public function getCategoriasNombresByNoche($id_noche)
    {
        $sql = "SELECT cat.nombre
                FROM noche_categoria nc
                JOIN Categoria cat ON cat.id_categoria = nc.id_categoria
                WHERE nc.id_noche = ? AND nc.deleted_at IS NULL AND cat.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
        $nombres = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return implode(', ', $nombres);
    }

    public function getDelegadosMailsByNoche($id_noche)
    {
        $sql = "SELECT u.mail
                FROM noche_delegado nd
                JOIN Usuario u ON u.id_usuario = nd.id_usuario
                WHERE nd.id_noche = ? AND nd.deleted_at IS NULL AND u.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche]);
        $mails = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return implode(', ', $mails);
    }

    public function getRelacionesComparsaEliminada($id_noche)
    {
    
        $sql = "SELECT nc.id_noche_comparsa, c.id_comparsa, c.nombre, c.deleted_at AS comparsa_deleted_at
            FROM noche_comparsa nc
            LEFT JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
            WHERE nc.id_noche = ? 
              AND nc.deleted_at IS NULL
              AND c.deleted_at IS NOT NULL";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelacionesCategoriaEliminada($id_noche)
    {
        
        $sql = "SELECT ncat.id_noche_categoria, cat.id_categoria, cat.nombre, cat.deleted_at AS categoria_deleted_at
            FROM noche_categoria ncat
            LEFT JOIN Categoria cat ON cat.id_categoria = ncat.id_categoria
            WHERE ncat.id_noche = ? 
              AND ncat.deleted_at IS NULL
              AND cat.deleted_at IS NOT NULL";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeNocheComparsaById($id_noche_comparsa)
    {
        $sql = "UPDATE noche_comparsa SET deleted_at = NOW() WHERE id_noche_comparsa = ? AND deleted_at IS NULL";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche_comparsa]);
    }

    public function removeNocheCategoriaById($id_noche_categoria)
    {
        $sql = "UPDATE noche_categoria SET deleted_at = NOW() WHERE id_noche_categoria = ? AND deleted_at IS NULL";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche_categoria]);
    }


}
