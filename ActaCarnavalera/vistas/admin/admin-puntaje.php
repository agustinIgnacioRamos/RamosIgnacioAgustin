<?php
require_once "Database.php";

class Puntaje
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getNochesActivas()
    {
        $sql = "SELECT id_noche, fecha, lugar, numero_noche
                FROM Noche
                WHERE deleted_at IS NULL
                ORDER BY fecha DESC, numero_noche DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsuariosActivos()
    {
        $sql = "SELECT id_usuario, dni, mail
                FROM Usuario
                WHERE deleted_at IS NULL
                ORDER BY mail ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getComparsasByNoche($id_noche)
    {
        $sql = "SELECT c.id_comparsa, c.nombre
                FROM noche_comparsa nc
                JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
                WHERE nc.id_noche = ? AND nc.deleted_at IS NULL AND c.deleted_at IS NULL
                ORDER BY c.nombre ASC";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoriasByNoche($id_noche)
    {
        $sql = "SELECT cat.id_categoria, cat.nombre
                FROM noche_categoria ncat
                JOIN Categoria cat ON cat.id_categoria = ncat.id_categoria
                WHERE ncat.id_noche = ? AND ncat.deleted_at IS NULL AND cat.deleted_at IS NULL
                ORDER BY cat.nombre ASC";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findIdNocheComparsa($id_noche, $id_comparsa)
    {
        $sql = "SELECT id_noche_comparsa
                FROM noche_comparsa
                WHERE id_noche = ? AND id_comparsa = ? AND deleted_at IS NULL";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche, $id_comparsa]);
        $val = $st->fetchColumn();
        return $val ? (int) $val : null;
    }

    public function findIdNocheCategoria($id_noche, $id_categoria)
    {
        $sql = "SELECT id_noche_categoria
                FROM noche_categoria
                WHERE id_noche = ? AND id_categoria = ? AND deleted_at IS NULL";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche, $id_categoria]);
        $val = $st->fetchColumn();
        return $val ? (int) $val : null;
    }

    public function getPuntajesByNoche($id_noche)
    {
        $sql = "SELECT 
                    p.id_puntaje,
                    p.valor_puntaje,
                    p.fecha_registro,
                    c.id_comparsa,
                    c.nombre AS nombre_comparsa,
                    cat.id_categoria,
                    cat.nombre AS nombre_categoria,
                    u.id_usuario,
                    u.mail AS registrado_mail,
                    u.dni AS registrado_dni
                FROM Puntaje p
                JOIN noche_comparsa nc ON nc.id_noche_comparsa = p.id_noche_comparsa
                JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
                JOIN noche_categoria ncat ON ncat.id_noche_categoria = p.id_noche_categoria
                JOIN Categoria cat ON cat.id_categoria = ncat.id_categoria
                JOIN Usuario u ON u.id_usuario = p.registrado_por
                WHERE nc.id_noche = ?
                  AND ncat.id_noche = ?
                  AND p.deleted_at IS NULL
                ORDER BY c.nombre ASC, cat.nombre ASC";
        $st = $this->db->prepare($sql);
        $st->execute([$id_noche, $id_noche]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addOrUpdate($id_noche, $id_comparsa, $id_categoria, $valor_puntaje, $registrado_por)
    {
        if (!is_numeric($valor_puntaje) || $valor_puntaje < 0 || $valor_puntaje > 10) {
            throw new InvalidArgumentException("El puntaje debe estar entre 0 y 10.");
        }

        $id_nc = $this->findIdNocheComparsa($id_noche, $id_comparsa);
        $id_ncat = $this->findIdNocheCategoria($id_noche, $id_categoria);

        if (!$id_nc) {
            throw new RuntimeException("La comparsa seleccionada no participa en esa noche.");
        }
        if (!$id_ncat) {
            throw new RuntimeException("La categoría seleccionada no está habilitada en esa noche.");
        }

        $sql_check = "SELECT id_puntaje, deleted_at
                      FROM Puntaje
                      WHERE id_noche_comparsa = ? AND id_noche_categoria = ?
                      LIMIT 1";
        $st = $this->db->prepare($sql_check);
        $st->execute([$id_nc, $id_ncat]);
        $existing = $st->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if (!is_null($existing['deleted_at'])) {
                $sql_update = "UPDATE Puntaje
                               SET valor_puntaje = ?, registrado_por = ?,
                                   deleted_at = NULL, updated_at = CURRENT_TIMESTAMP
                               WHERE id_puntaje = ?";
                $st2 = $this->db->prepare($sql_update);
                $st2->execute([$valor_puntaje, $registrado_por, $existing['id_puntaje']]);
                return;
            }

            $sql_update = "UPDATE Puntaje
                           SET valor_puntaje = ?, registrado_por = ?, updated_at = CURRENT_TIMESTAMP
                           WHERE id_puntaje = ?";
            $st2 = $this->db->prepare($sql_update);
            $st2->execute([$valor_puntaje, $registrado_por, $existing['id_puntaje']]);
            return;
        }

        $sql_insert = "INSERT INTO Puntaje
                       (id_noche_comparsa, id_noche_categoria, valor_puntaje, registrado_por)
                       VALUES (?, ?, ?, ?)";
        $st3 = $this->db->prepare($sql_insert);
        $st3->execute([$id_nc, $id_ncat, $valor_puntaje, $registrado_por]);
    }

    public function updateValor($id_puntaje, $valor_puntaje)
    {
        if (!is_numeric($valor_puntaje) || $valor_puntaje < 0 || $valor_puntaje > 10) {
            throw new InvalidArgumentException("El puntaje debe estar entre 0 y 10.");
        }
        $sql = "UPDATE Puntaje SET valor_puntaje = ?, updated_at = CURRENT_TIMESTAMP WHERE id_puntaje = ?";
        $st = $this->db->prepare($sql);
        $st->execute([$valor_puntaje, $id_puntaje]);
    }

    public function delete($id_puntaje)
    {
        $sql = "UPDATE Puntaje SET deleted_at = NOW() WHERE id_puntaje = ?";
        $st = $this->db->prepare($sql);
        $st->execute([$id_puntaje]);
    }

    public function reactivar($id_puntaje)
    {
        $sql = "UPDATE Puntaje SET deleted_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id_puntaje = ?";
        $st = $this->db->prepare($sql);
        $st->execute([$id_puntaje]);
    }

    public function resultadosDisponibles($id_noche)
    {
        return $this->nocheCompletamenteAprobada($id_noche);
    }

    public function getResultadosByNoche($id_noche)
    {
        $sql = "SELECT 
                c.nombre AS comparsa,
                cat.nombre AS categoria,
                p.valor_puntaje
            FROM Puntaje p
            JOIN noche_comparsa nc ON nc.id_noche_comparsa = p.id_noche_comparsa
            JOIN noche_categoria ncat ON ncat.id_noche_categoria = p.id_noche_categoria
            JOIN Comparsa c ON c.id_comparsa = nc.id_comparsa
            JOIN Categoria cat ON cat.id_categoria = ncat.id_categoria
            JOIN Aprobacion a ON a.id_puntaje = p.id_puntaje
            WHERE nc.id_noche = ?
              AND ncat.id_noche = ?
              AND p.deleted_at IS NULL
              AND a.estado = 'Aprobado'
            ORDER BY c.nombre ASC, cat.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_noche, $id_noche]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nocheCompletamenteAprobada($id_noche)
    {
        $sql_delegados = "SELECT COUNT(*) FROM noche_delegado 
                      WHERE id_noche = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql_delegados);
        $stmt->execute([$id_noche]);
        $total_delegados = $stmt->fetchColumn();

        if ($total_delegados == 0)
            return false;

        $sql_ids = "SELECT id_usuario FROM noche_delegado WHERE id_noche = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql_ids);
        $stmt->execute([$id_noche]);
        $delegados = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($delegados as $id_delegado) {
            $sql_check = "SELECT COUNT(*) 
                      FROM Puntaje p
                      JOIN noche_comparsa nc ON nc.id_noche_comparsa = p.id_noche_comparsa
                      JOIN noche_categoria ncat ON ncat.id_noche_categoria = p.id_noche_categoria
                      LEFT JOIN Aprobacion a ON a.id_puntaje = p.id_puntaje AND a.id_usuario = ?
                      WHERE nc.id_noche = ?
                        AND ncat.id_noche = ?
                        AND p.deleted_at IS NULL
                        AND (a.estado IS NULL OR a.estado != 'Aprobado')";
            $stmt = $this->db->prepare($sql_check);
            $stmt->execute([$id_delegado, $id_noche, $id_noche]);
            $faltantes = $stmt->fetchColumn();
            if ($faltantes > 0) {
                return false;
            }
        }

        return true;
    }
}
