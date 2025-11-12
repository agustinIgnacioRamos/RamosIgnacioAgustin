<?php
require_once "Database.php";

class Correccion
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    
    public function getRechazadosByNoche($id_noche)
    {
        $sql = "SELECT 
                    p.id_puntaje,
                    p.valor_puntaje,
                    c.nombre AS comparsa,
                    cat.nombre AS categoria,
                    u.mail AS registrado_por,
                    a.id_aprobacion,
                    a.estado,
                    a.justificacion AS justificacion_delegado,
                    a.id_usuario AS id_delegado,
                    du.mail AS delegado_mail,
                    a.fecha_aprobacion
                FROM Aprobacion a
                JOIN Puntaje p ON p.id_puntaje = a.id_puntaje
                JOIN noche_comparsa nc ON nc.id_noche_comparsa = p.id_noche_comparsa
                JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
                JOIN noche_categoria ncat ON ncat.id_noche_categoria = p.id_noche_categoria
                JOIN Categoria cat ON cat.id_categoria = ncat.id_categoria
                JOIN Usuario u ON u.id_usuario = p.registrado_por
                JOIN Usuario du ON du.id_usuario = a.id_usuario
                WHERE a.estado = 'Rechazado'
                  AND nc.id_noche = ?
                  AND ncat.id_noche = ?
                  AND p.deleted_at IS NULL
                ORDER BY c.nombre, cat.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche, $id_noche]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function addCorreccion($id_puntaje, $id_usuario, $detalle)
    {
        $sql = "INSERT INTO Correccion (id_puntaje, id_usuario, detalle_modificacion)
                VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_puntaje, $id_usuario, $detalle]);
    }

   
    public function aceptarCorreccion($id_puntaje, $id_aprobacion, $id_admin, $nuevo_valor, $justificacion)
    {
        try {
            $this->db->beginTransaction();

            
            $sql1 = "UPDATE Puntaje 
                     SET valor_puntaje = ?, updated_at = CURRENT_TIMESTAMP
                     WHERE id_puntaje = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$nuevo_valor, $id_puntaje]);

           
            $this->addCorreccion($id_puntaje, $id_admin, "Corrección aceptada: {$justificacion}");

            
            $sql2 = "UPDATE Aprobacion 
                     SET estado = 'Aprobado', justificacion = ?
                     WHERE id_aprobacion = ?";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([$justificacion, $id_aprobacion]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

   
    public function rechazarCorreccion($id_puntaje, $id_aprobacion, $id_admin, $justificacion)
    {
        try {
            $this->db->beginTransaction();

            
            $this->addCorreccion($id_puntaje, $id_admin, "Corrección rechazada: {$justificacion}");

           
            $sql2 = "UPDATE Aprobacion 
                     SET estado = 'Aprobado', justificacion = ?
                     WHERE id_aprobacion = ?";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([$justificacion, $id_aprobacion]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
