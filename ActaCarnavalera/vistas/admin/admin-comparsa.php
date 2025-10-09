<?php
session_start();
require_once "../../clases/Comparsa.php";
require_once "../../clases/Usuario.php";

$comparsa = new Comparsa();
$usuario = new Usuario();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    $accion = '';
    if (isset($_POST['action'])) {
        $accion = $_POST['action'];
    }

    try {
      
        if ($accion === 'cargar_edicion') {
            $id_editar = $_POST['id_comparsa'];
            $comparsa_a_editar = $comparsa->buscarPorId($id_editar);
         
        }
       
        elseif ($accion === 'insert') {
            $nombre = trim($_POST['nombre']);
            $director = $_POST['director'];

          
            $existe = $comparsa->buscarPorNombre($nombre);

            if ($existe) {
               
                if ($existe['deleted_at'] !== null) {
                    $comparsa->update($existe['id_comparsa'], $existe['nombre'], $director);
                    $comparsa->reactivar($existe['id_comparsa']);
                    $_SESSION['mensaje'] = "Comparsa reactivada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    
                    $_SESSION['mensaje'] = "Ya existe una comparsa activa con ese nombre.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } else {
               
                $comparsa->add($nombre, $director);
                $_SESSION['mensaje'] = "Comparsa agregada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            }

           
            header("Location: admin-comparsa.php");
            exit();
        }
      
        elseif ($accion === 'update') {
            $id_comparsa = $_POST['id_comparsa'];
            $nombre = trim($_POST['nombre']);
            $director = $_POST['director'];

        
            $otra_comparsa = $comparsa->existeOtraComparsa($nombre, $id_comparsa);

            if ($otra_comparsa) {
                $_SESSION['mensaje'] = "Ya existe otra comparsa activa con ese nombre.";
                $_SESSION['tipo_mensaje'] = "danger";
            } else {
                $comparsa->update($id_comparsa, $nombre, $director);
                $_SESSION['mensaje'] = "Comparsa actualizada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            }

           
            header("Location: admin-comparsa.php");
            exit();
        }
      
        elseif ($accion === 'delete') {
            $comparsa->delete($_POST['id_comparsa']);
            $_SESSION['mensaje'] = "Comparsa eliminada correctamente.";
            $_SESSION['tipo_mensaje'] = "danger";

            header("Location: admin-comparsa.php");
            exit();
        }
      
        elseif ($accion === 'reactivar') {
            $comparsa->reactivar($_POST['id_comparsa']);
            $_SESSION['mensaje'] = "Comparsa reactivada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";

            header("Location: admin-comparsa.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: admin-comparsa.php");
        exit();
    }
}


$comparsas_activas = $comparsa->getActivos();
$comparsas_eliminadas = $comparsa->getEliminados();
$usuarios_activos = $usuario->getActivos();
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Comparsas</title>
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
                            <i class="bi bi-music-note-list text-primary me-2"></i>
                            Gestión de Comparsas
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
                    <div class="card-header <?php echo isset($comparsa_a_editar) ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-<?php echo isset($comparsa_a_editar) ? 'pencil-square' : 'music-note-list'; ?> me-2"></i>
                            <?php echo isset($comparsa_a_editar) ? 'Editar Comparsa' : 'Agregar Nueva Comparsa'; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">

                            
                            <input type="hidden" name="action" value="<?php echo isset($comparsa_a_editar) ? 'update' : 'insert'; ?>">

                       
                            <?php if (isset($comparsa_a_editar)): ?>
                                <input type="hidden" name="id_comparsa" value="<?php echo $comparsa_a_editar['id_comparsa']; ?>">
                            <?php endif; ?>

                           
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-music-note-beamed text-primary me-1"></i> Nombre
                                </label>
                                <input type="text"
                                    name="nombre"
                                    class="form-control form-control-lg"
                                    placeholder="Nombre de la comparsa"
                                    value="<?php echo isset($comparsa_a_editar) ? htmlspecialchars($comparsa_a_editar['nombre']) : ''; ?>"
                                    required>
                            </div>

                       
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-badge-fill text-primary me-1"></i> Director
                                </label>
                                <select name="director" class="form-select form-select-lg" required>
                                    <option value="">Seleccione un director...</option>
                                    <?php foreach ($usuarios_activos as $u): ?>
                                        <option value="<?php echo $u['id_usuario']; ?>"
                                            <?php
                                            if (isset($comparsa_a_editar) && $comparsa_a_editar['id_director'] == $u['id_usuario']) {
                                                echo 'selected';
                                            }
                                            ?>>
                                            <?php echo htmlspecialchars($u['dni'] . " - " . $u['mail']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-white-50">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Seleccione el usuario responsable de la comparsa
                                </div>
                            </div>

                           
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-<?php echo isset($comparsa_a_editar) ? 'warning' : 'primary'; ?> btn-lg">
                                    <i class="bi bi-<?php echo isset($comparsa_a_editar) ? 'check-circle-fill' : 'plus-circle-fill'; ?> me-1"></i>
                                    <?php echo isset($comparsa_a_editar) ? 'Guardar Cambios' : 'Agregar Comparsa'; ?>
                                </button>
                                <?php if (isset($comparsa_a_editar)): ?>
                                    <a href="admin-comparsa.php" class="btn btn-outline-secondary btn-lg">
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
                                Comparsas Activas
                            </h5>
                            <span class="badge bg-white text-success fs-6">
                                <?php echo count($comparsas_activas); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($comparsas_activas)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">No hay comparsas activas</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ID</th>
                                            <th>Nombre</th>
                                            <th>Director</th>
                                            <th class="text-center" style="width: 200px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comparsas_activas as $c): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#<?php echo $c['id_comparsa']; ?></span>
                                                </td>
                                                <td class="fw-semibold">
                                                    <i class="bi bi-music-note me-1 text-primary"></i>
                                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-white">
                                                            <i class="bi bi-person-fill me-1 text-info"></i>
                                                            <?php echo htmlspecialchars($c['director_dni']); ?>
                                                        </span>
                                                        <small class="text-white-50">
                                                            <i class="bi bi-envelope me-1"></i>
                                                            <?php echo htmlspecialchars($c['director_mail']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                        
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="cargar_edicion">
                                                            <input type="hidden" name="id_comparsa" value="<?php echo $c['id_comparsa']; ?>">
                                                            <button type="submit" class="btn btn-warning btn-sm">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                        </form>

                                                       
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id_comparsa" value="<?php echo $c['id_comparsa']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta comparsa?')">
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
                                Comparsas Eliminadas
                            </h5>
                            <span class="badge bg-white text-danger fs-6">
                                <?php echo count($comparsas_eliminadas); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($comparsas_eliminadas)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">No hay comparsas eliminadas</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ID</th>
                                            <th>Nombre</th>
                                            <th>Director</th>
                                            <th class="text-center" style="width: 200px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-secondary">
                                        <?php foreach ($comparsas_eliminadas as $c): ?>
                                            <tr class="opacity-75">
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#<?php echo $c['id_comparsa']; ?></span>
                                                </td>
                                                <td class="fw-semibold">
                                                    <i class="bi bi-music-note me-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-white-50">
                                                            <i class="bi bi-person-fill me-1"></i>
                                                            <?php echo htmlspecialchars($c['director_dni']); ?>
                                                        </span>
                                                        <small class="text-white-50">
                                                            <i class="bi bi-envelope me-1"></i>
                                                            <?php echo htmlspecialchars($c['director_mail']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="reactivar">
                                                        <input type="hidden" name="id_comparsa" value="<?php echo $c['id_comparsa']; ?>">
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