<?php
require_once "Database.php";

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }


    public function login($mail, $contrasena)
    {

        $sql = "SELECT id_usuario, dni, mail, id_rol 
                FROM usuario 
                WHERE mail = :mail 
                  AND contrasena = :contrasena 
                  AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mail' => $mail,
            ':contrasena' => $contrasena
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function buscarPorId($id)
    {
        $query = "SELECT * FROM usuario WHERE id_usuario = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $sql = "SELECT u.id_usuario, u.dni, u.mail, u.contrasena, u.id_rol, u.deleted_at, 
                       r.nombre_rol AS rol_nombre 
                FROM usuario u 
                LEFT JOIN rol r ON u.id_rol = r.id_rol";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivos()
    {
        $sql = "SELECT * FROM usuario WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEliminados()
    {
        $sql = "SELECT * FROM usuario WHERE deleted_at IS NOT NULL";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($dni, $mail, $contrasena, $id_rol)
    {
        $sqlCheck = "SELECT id_usuario, deleted_at FROM usuario 
                     WHERE dni = :dni OR mail = :mail";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([':dni' => $dni, ':mail' => $mail]);
        $usuarioExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($usuarioExistente) {
            if ($usuarioExistente['deleted_at']) {
                $sqlUpdate = "UPDATE usuario 
                             SET dni = :dni, mail = :mail, contrasena = :contrasena, 
                                 id_rol = :id_rol, deleted_at = NULL 
                             WHERE id_usuario = :id_usuario";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':dni' => $dni,
                    ':mail' => $mail,
                    ':contrasena' => $contrasena,
                    ':id_rol' => $id_rol,
                    ':id_usuario' => $usuarioExistente['id_usuario']
                ]);
                throw new Exception("Usuario reactivado y actualizado.");
            } else {
                throw new Exception("Ya existe un usuario activo con ese DNI o mail.");
            }
        } else {
            $sql = "INSERT INTO usuario (dni, mail, contrasena, id_rol) 
                    VALUES (:dni, :mail, :contrasena, :id_rol)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':dni' => $dni,
                ':mail' => $mail,
                ':contrasena' => $contrasena,
                ':id_rol' => $id_rol
            ]);
        }
    }

    public function update($id_usuario, $dni, $mail, $contrasena, $id_rol)
    {
        $sql = "UPDATE usuario 
                SET dni = :dni, mail = :mail, contrasena = :contrasena, id_rol = :id_rol 
                WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':dni' => $dni,
            ':mail' => $mail,
            ':contrasena' => $contrasena,
            ':id_rol' => $id_rol,
            ':id_usuario' => $id_usuario
        ]);
    }

    public function delete($id_usuario)
    {
        $sql = "UPDATE usuario SET deleted_at = NOW() WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
    }

    public function reactivar($id_usuario)
    {
        $sql = "UPDATE usuario SET deleted_at = NULL WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
    }

    public function buscarPorDniOMail($dni, $mail)
    {
        $sql = "SELECT * FROM usuario WHERE dni = :dni OR mail = :mail";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dni' => $dni, ':mail' => $mail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeOtroUsuario($dni, $mail, $id_usuario)
    {
        $sql = "SELECT * FROM usuario 
                WHERE (dni = :dni OR mail = :mail) AND id_usuario != :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':dni' => $dni,
            ':mail' => $mail,
            ':id_usuario' => $id_usuario
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
