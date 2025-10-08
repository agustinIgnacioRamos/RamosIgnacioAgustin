<?php
session_start();
require_once "../../clases/Usuario.php";
$usuario = new Usuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    $accion = '';
    if (isset($_POST['action'])) {
        $accion = $_POST['action'];
    }

    if ($accion === 'cargar_edicion') {
        $id_editar = $_POST['id_usuario'];
        $usuario_a_editar = $usuario->buscarPorId($id_editar);
       
    }
    
    elseif ($accion === 'insert') {
        $dni = trim($_POST['dni']);
        $mail = trim($_POST['mail']);
        $contrasena = trim($_POST['contrasena']);
        $id_rol = $_POST['id_rol'];


        $existe = $usuario->buscarPorDniOMail($dni, $mail);

        if ($existe) {
            
            if ($existe['deleted_at'] !== null) {
                $usuario->reactivar($existe['id_usuario']);
                $_SESSION['mensaje'] = "Usuario reactivado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                
                $_SESSION['mensaje'] = "Ya existe un usuario con ese DNI o EMAIL.";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } else {
           
            try {
                $usuario->add($dni, $mail, $contrasena, $id_rol);
                $_SESSION['mensaje'] = "Usuario agregado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } catch (Exception $e) {
                $_SESSION['mensaje'] = $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }

        
        header("Location: admin-usuario.php");
        exit();
    }
    
    elseif ($accion === 'update') {
        $id_usuario = $_POST['id_usuario'];
        $dni = trim($_POST['dni']);
        $mail = trim($_POST['mail']);
        $contrasena = trim($_POST['contrasena']);
        $id_rol = $_POST['id_rol'];

        
        $otro_usuario = $usuario->existeOtroUsuario($dni, $mail, $id_usuario);

        if ($otro_usuario) {
            $_SESSION['mensaje'] = "Ese DNI o EMAIL ya está en uso por otro usuario.";
            $_SESSION['tipo_mensaje'] = "danger";
        } else {
            $usuario->update($id_usuario, $dni, $mail, $contrasena, $id_rol);
            $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        }

        
        header("Location: admin-usuario.php");
        exit();
    }
   
    elseif ($accion === 'delete') {
        $usuario->delete($_POST['id_usuario']);
        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
        $_SESSION['tipo_mensaje'] = "danger";

      
        header("Location: admin-usuario.php");
        exit();
    }
   
    elseif ($accion === 'reactivar') {
        $usuario->reactivar($_POST['id_usuario']);
        $_SESSION['mensaje'] = "Usuario reactivado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";

       
        header("Location: admin-usuario.php");
        exit();
    }
}


$usuarios_activos = $usuario->getActivos();
$usuarios_eliminados = $usuario->getEliminados();


function mostrarRol($id_rol)
{
    $roles = [
        1 => ['texto' => 'Administrador', 'clase' => 'bg-primary'],
        2 => ['texto' => 'Delegado', 'clase' => 'bg-info'],
        3 => ['texto' => 'Usuario', 'clase' => 'bg-secondary']
    ];
    return $roles[$id_rol];
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-dark">
    <div class="container-fluid px-3 px-md-4 py-4">

        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-secondary shadow-sm">
                    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 py-3">
                        <h1 class="h3 mb-0 text-light">
                            <i class="bi bi-people-fill text-primary me-2"></i>
                            Gestión de Usuarios
                        </h1>
                        <a href="./admin.html" class="btn btn-primary">
                            <i class="bi bi-house-door-fill me-1"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <?php
            $tipo_mensaje = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'success';
            ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show shadow-sm" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-<?php echo $tipo_mensaje === 'success' ? 'check-circle-fill' : ($tipo_mensaje === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill'); ?> fs-4 me-3 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <strong>Sistema Informa:</strong> <?php echo $_SESSION['mensaje']; ?>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
            <?php
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
            ?>
        <?php endif; ?>

        <div class="row g-4">

           
            <div class="col-12 col-lg-4">
                <div class="card border-secondary shadow-sm h-100">
                    <div class="card-header <?php echo isset($usuario_a_editar) ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-<?php echo isset($usuario_a_editar) ? 'pencil-square' : 'person-plus-fill'; ?> me-2"></i>
                            <?php echo isset($usuario_a_editar) ? 'Editar Usuario' : 'Agregar Nuevo Usuario'; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">

                            <input type="hidden" name="action" value="<?php echo isset($usuario_a_editar) ? 'update' : 'insert'; ?>">

                          
                            <?php if (isset($usuario_a_editar)): ?>
                                <input type="hidden" name="id_usuario" value="<?php echo $usuario_a_editar['id_usuario']; ?>">
                            <?php endif; ?>

                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-card-text text-primary me-1"></i> DNI
                                </label>
                                <input type="text" 
                                       name="dni" 
                                       class="form-control form-control-lg"
                                       placeholder="Ingrese DNI"
                                       value="<?php echo isset($usuario_a_editar) ? htmlspecialchars($usuario_a_editar['dni']) : ''; ?>" 
                                       required>
                            </div>

                       
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope-fill text-primary me-1"></i> Email
                                </label>
                                <input type="email" 
                                       name="mail" 
                                       class="form-control form-control-lg"
                                       placeholder="correo@ejemplo.com"
                                       value="<?php echo isset($usuario_a_editar) ? htmlspecialchars($usuario_a_editar['mail']) : ''; ?>" 
                                       required>
                            </div>

                           
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill text-primary me-1"></i> Contraseña
                                </label>
                                <input type="password" 
                                       name="contrasena" 
                                       class="form-control form-control-lg"
                                       placeholder="Ingrese contraseña"
                                       value="<?php echo isset($usuario_a_editar) ? htmlspecialchars($usuario_a_editar['contrasena']) : ''; ?>" 
                                       required>
                            </div>

                           
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-shield-fill-check text-primary me-1"></i> Rol
                                </label>
                                <select name="id_rol" class="form-select form-select-lg" required>
                                    <option value="">Seleccione un rol...</option>
                                    <option value="1"  <?php echo (isset($usuario_a_editar) && $usuario_a_editar['id_rol'] == 1) ? 'selected' : ''; ?>>
                                        👑 Administrador
                                    </option>
                                    <option value="2"  <?php echo (isset($usuario_a_editar) && $usuario_a_editar['id_rol'] == 2) ? 'selected' : ''; ?>>
                                        🎯 Delegado
                                    </option>
                                    <option value="3" <?php echo (isset($usuario_a_editar) && $usuario_a_editar['id_rol'] == 3) ? 'selected' : ''; ?>>
                                        👤 Usuario
                                    </option>
                                </select>
                            </div>

                    
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-<?php echo isset($usuario_a_editar) ? 'warning' : 'primary'; ?> btn-lg">
                                    <i class="bi bi-<?php echo isset($usuario_a_editar) ? 'check-circle-fill' : 'plus-circle-fill'; ?> me-1"></i>
                                    <?php echo isset($usuario_a_editar) ? 'Guardar Cambios' : 'Agregar Usuario'; ?>
                                </button>
                                <?php if (isset($usuario_a_editar)): ?>
                                    <a href="admin-usuario.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle-fill me-1"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-lg-8">

                
                <div class="card border-success shadow-sm mb-4">
                    <div class="card-header bg-success text-white py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Usuarios Activos
                            </h5>
                            <span class="badge bg-white text-success fs-6">
                                <?php echo count($usuarios_activos); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($usuarios_activos)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">No hay usuarios activos</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ID</th>
                                            <th>DNI</th>
                                            <th>Email</th>
                                            <th class="text-center" style="width: 150px;">Rol</th>
                                            <th class="text-center" style="width: 200px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios_activos as $u):
                                            $rol = mostrarRol($u['id_rol']);
                                        ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#<?php echo $u['id_usuario']; ?></span>
                                                </td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($u['dni']); ?></td>
                                                <td>
                                                    <i class="bi bi-envelope me-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($u['mail']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?php echo $rol['clase']; ?> px-3 py-2">
                                                        <?php echo $rol['texto']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                        
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="cargar_edicion">
                                                            <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                                            <button type="submit" class="btn btn-warning btn-sm">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                        </form>

                                                     
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <i class="bi bi-trash-fill me-2"></i>
                                Usuarios Eliminados
                            </h5>
                            <span class="badge bg-white text-danger fs-6">
                                <?php echo count($usuarios_eliminados); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($usuarios_eliminados)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">No hay usuarios eliminados</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ID</th>
                                            <th>DNI</th>
                                            <th>Email</th>
                                            <th class="text-center" style="width: 150px;">Rol</th>
                                            <th class="text-center" style="width: 200px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-secondary">
                                        <?php foreach ($usuarios_eliminados as $u):
                                            $rol = mostrarRol($u['id_rol']);
                                        ?>
                                            <tr class="opacity-75">
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#<?php echo $u['id_usuario']; ?></span>
                                                </td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($u['dni']); ?></td>
                                                <td>
                                                    <i class="bi bi-envelope me-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($u['mail']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?php echo $rol['clase']; ?> px-3 py-2">
                                                        <?php echo $rol['texto']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="reactivar">
                                                        <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reactivar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>