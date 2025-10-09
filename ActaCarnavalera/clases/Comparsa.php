<?php
require_once "Database.php";
class Comparsa
{
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->connect();
    }
    public function add($nombre, $id_director)
    {
        $sql = "INSERT INTO Comparsa (nombre, id_director) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $id_director]);
    }
    public function getAll()
    {
        $sql = "SELECT 
    c.id_comparsa, 
    c.nombre, 
    c.id_director, 
    c.deleted_at, 
    u.mail AS director_mail,
    u.dni AS director_dni
    FROM Comparsa c
    INNER JOIN Usuario u 
        ON c.id_director = u.id_usuario;";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getActivos()
    {
        $sql = "SELECT 
    c.id_comparsa, 
    c.nombre, 
    c.id_director, 
    c.deleted_at, 
    u.mail AS director_mail,
    u.dni AS director_dni
    FROM Comparsa c
    INNER JOIN Usuario u 
        ON c.id_director = u.id_usuario WHERE c.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getEliminados()
    {
        $sql = "SELECT 
    c.id_comparsa, 
    c.nombre, 
    c.id_director, 
    c.deleted_at, 
    u.mail AS director_mail,
    u.dni AS director_dni
    FROM Comparsa c
    INNER JOIN Usuario u 
        ON c.id_director = u.id_usuario WHERE c.deleted_at IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function update($id_comparsa, $nombre, $id_director)
    {
        $sql = "UPDATE Comparsa SET nombre=?, id_director=? WHERE id_comparsa=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $id_director, $id_comparsa]);
    }
    public function delete($id_comparsa)
    {
        $sql = "UPDATE Comparsa SET deleted_at=NOW() WHERE id_comparsa=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_comparsa]);
    }
    public function reactivar($id_comparsa)
    {
        $sql = "UPDATE Comparsa SET deleted_at=NULL WHERE id_comparsa=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_comparsa]);
    }
    public function buscarPorNombre($nombre)
    {
        $sql = "SELECT * FROM Comparsa WHERE nombre = :nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function existeOtraComparsa($nombre, $id_comparsa)
    {
        $sql = "SELECT * FROM comparsa WHERE nombre = :nombre AND id_comparsa != :id_comparsa";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':id_comparsa' => $id_comparsa
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function buscarPorId($id)
    {
        $query = "SELECT c.*, 
                     u.dni as director_dni, 
                     u.mail as director_mail
              FROM comparsa c
              INNER JOIN usuario u ON c.id_director = u.id_usuario
              WHERE c.id_comparsa = :id 
              ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
