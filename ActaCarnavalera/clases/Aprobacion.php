<?php
require_once "Database.php";

class Aprobacion
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getPuntajesByNoche($id_noche, $id_delegado)
    {
        $sql = "SELECT 
                    p.id_puntaje,
                    p.valor_puntaje,
                    p.fecha_registro,
                    c.nombre AS comparsa,
                    cat.nombre AS categoria,
                    u.mail AS registrado_por,
                    a.estado AS estado_aprobacion,
                    a.justificacion,
                    a.fecha_aprobacion
                FROM Puntaje p
                JOIN noche_comparsa nc ON nc.id_noche_comparsa = p.id_noche_comparsa
                JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
                JOIN noche_categoria ncat ON ncat.id_noche_categoria = p.id_noche_categoria
                JOIN Categoria cat ON cat.id_categoria = ncat.id_categoria
                JOIN Usuario u ON u.id_usuario = p.registrado_por
                LEFT JOIN Aprobacion a ON a.id_puntaje = p.id_puntaje AND a.id_usuario = ?
                WHERE nc.id_noche = ?
                  AND ncat.id_noche = ?
                  AND p.deleted_at IS NULL
                ORDER BY c.nombre ASC, cat.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_delegado, $id_noche, $id_noche]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAprobacion($id_puntaje, $id_usuario, $estado, $justificacion)
    {
       
        $check = "SELECT COUNT(*) FROM Aprobacion WHERE id_puntaje = ? AND id_usuario = ?";
        $st = $this->db->prepare($check);
        $st->execute([$id_puntaje, $id_usuario]);
        $existe = $st->fetchColumn();

        if ($existe > 0) {
            throw new Exception("Este puntaje ya fue aprobado o rechazado por este delegado.");
        }

        $sql = "INSERT INTO Aprobacion (id_puntaje, id_usuario, estado, justificacion)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_puntaje, $id_usuario, $estado, $justificacion]);
    }
}
