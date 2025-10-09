<?php
require_once "Database.php";
class Categoria
{
    private $db;
    public function __construct()
    {
        $this->db = (new Database())->connect();
    }
    public function add($nombre, $descripcion)
    {
        $sql = "INSERT INTO Categoria (nombre, descripcion) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $descripcion]);
    }
    public function getAll()
    {
        $sql = "SELECT * FROM Categoria";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getActivos()
    {
        $sql = "SELECT * FROM Categoria WHERE deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getEliminados()
    {
        $sql = "SELECT * FROM Categoria WHERE deleted_at IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function update($id_categoria, $nombre, $descripcion)
    {
        $sql = "UPDATE Categoria SET nombre=?, descripcion=? WHERE id_categoria=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $id_categoria]);
    }
    public function delete($id_categoria)
    {
        $sql = "UPDATE Categoria SET deleted_at=NOW() WHERE id_categoria=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_categoria]);
    }
    public function reactivar($id_categoria)
    {
        $sql = "UPDATE Categoria SET deleted_at=NULL WHERE id_categoria=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_categoria]);
    }

    public function buscarPorNombre($nombre)
    {
        $sql = "SELECT * FROM categoria 
            WHERE nombre = :nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function buscarPorId($id) {
    $query = "SELECT * FROM categoria WHERE id_categoria = :id";
    $stmt = $this->db->prepare($query);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
