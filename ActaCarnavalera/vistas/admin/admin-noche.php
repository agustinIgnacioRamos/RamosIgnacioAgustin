<?php

session_start();
require_once "../../clases/Noche.php";
$noche = new Noche();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['action'] ?? '';

    
    if ($accion === 'cargar_edicion') {
        $id_editar = $_POST['id_noche'];
        $noche_a_editar = $noche->buscarPorId($id_editar);
    }

    
    elseif ($accion === 'insert' || $accion === 'update') {
        $fecha = $_POST['fecha'];
        $lugar = trim($_POST['lugar']);
        $numero = $_POST['numero'];
        $detalles = $_POST['detalles'] ?? '';
        $comparsas = $_POST['comparsas'] ?? [];
        $categorias = $_POST['categorias'] ?? [];
        $delegados = $_POST['delegados'] ?? [];

        if (empty($comparsas) || empty($categorias) || empty($delegados)) {
    $_SESSION['mensaje'] = "No se puede guardar la noche. Debe asignar al menos una comparsa, una categoría y un delegado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: admin-noche.php");
    exit();
}

        if ($accion === 'insert') {
            $existe = $noche->buscarPorFechaYLugar($fecha, $lugar);

            if ($existe) {
                if ($existe['deleted_at'] !== null) {
                    $noche->reactivar($existe['id_noche']);
                    $_SESSION['mensaje'] = "Noche reactivada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Ya existe una noche activa con esa fecha y lugar.";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } else {
            
                $noche->add($fecha, $lugar, $numero, $detalles);
                $nueva = $noche->buscarPorFechaYLugar($fecha, $lugar);
                $id_noche = $nueva['id_noche'];

                if (!empty($comparsas)) $noche->updateComparsasNoche($id_noche, $comparsas);
                if (!empty($categorias)) $noche->updateCategoriasNoche($id_noche, $categorias);
                if (!empty($delegados)) $noche->updateDelegadosNoche($id_noche, $delegados);

                $_SESSION['mensaje'] = "Noche agregada correctamente con sus asignaciones.";
                $_SESSION['tipo_mensaje'] = "success";
            }
        } else {

            $id_noche = $_POST['id_noche'];

            try {
                $noche->update($id_noche, $fecha, $lugar, $numero, $detalles);
                $noche->updateComparsasNoche($id_noche, $comparsas);
                $noche->updateCategoriasNoche($id_noche, $categorias);
                $noche->updateDelegadosNoche($id_noche, $delegados);

                $_SESSION['mensaje'] = "Noche actualizada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } catch (Exception $e) {
                $_SESSION['mensaje'] = $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }

            header("Location: admin-noche.php");
            exit();
        }

        header("Location: admin-noche.php");
        exit();
    }

    
elseif ($accion === 'quitar_nc' && !empty($_POST['id_nc'])) {
    $noche->removeNocheComparsaById((int)$_POST['id_nc']);

    $_POST['action'] = 'cargar_edicion';
    $_POST['id_noche'] = $_POST['id_noche'] ?? $_POST['noche_id'] ?? '';
}


elseif ($accion === 'quitar_ncat' && !empty($_POST['id_ncat'])) {
    $noche->removeNocheCategoriaById((int)$_POST['id_ncat']);

    $_POST['action'] = 'cargar_edicion';
    $_POST['id_noche'] = $_POST['id_noche'] ?? $_POST['noche_id'] ?? '';
}


    elseif ($accion === 'delete') {
        $noche->delete($_POST['id_noche']);
        $_SESSION['mensaje'] = "Noche eliminada correctamente.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: admin-noche.php");
        exit();
    }


    elseif ($accion === 'reactivar') {
        $noche->reactivar($_POST['id_noche']);
        $_SESSION['mensaje'] = "Noche reactivada correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: admin-noche.php");
        exit();
    }
}


$noches_activas = $noche->getActivos();
$noches_eliminadas = $noche->getEliminados();


$comparsas_activas = $noche->getComparsasActivas();
$categorias_activas = $noche->getCategoriasActivas();
$delegados_activos = $noche->getDelegadosActivos();

$comparsas_noche_actual = [];
$categorias_noche_actual = [];
$delegados_noche_actual = [];

if (isset($noche_a_editar)) {
    $comparsas_noche_actual = $noche->getComparsasByNoche($noche_a_editar['id_noche']);
    $categorias_noche_actual = $noche->getCategoriasByNoche($noche_a_editar['id_noche']);
    $delegados_noche_actual = $noche->getDelegadosByNoche($noche_a_editar['id_noche']);
    $rel_comp_eliminadas = $noche->getRelacionesComparsaEliminada($noche_a_editar['id_noche']);
    $rel_cat_eliminadas  = $noche->getRelacionesCategoriaEliminada($noche_a_editar['id_noche']);
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Noches</title>
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
                        <i class="bi bi-moon-stars-fill text-primary me-2"></i>
                        Gestión de Noches
                    </h1>
                    <a href="./admin.html" class="btn btn-primary">
                        <i class="bi bi-house-door-fill me-1"></i> Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <?php if (isset($_SESSION['mensaje'])): ?>
        <?php $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'info'; ?>
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
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    <?php endif; ?>

    <div class="row g-4">
        
        <div class="col-12 col-xl-4">
            <div class="card border-secondary shadow-sm sticky-top" style="top: 1rem;">
                <div class="card-header <?php echo isset($noche_a_editar) ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo isset($noche_a_editar) ? 'pencil-square' : 'plus-circle-fill'; ?> me-2"></i>
                        <?php echo isset($noche_a_editar) ? 'Editar Noche' : 'Agregar Nueva Noche'; ?>
                    </h5>
                </div>
                <div class="card-body p-4" style="max-height: calc(100vh - 8rem); overflow-y: auto;">
                    <form method="POST">

                        <input type="hidden" name="action" value="<?php echo isset($noche_a_editar) ? 'update' : 'insert'; ?>">
                        <?php if (isset($noche_a_editar)): ?>
                            <input type="hidden" name="id_noche" value="<?php echo $noche_a_editar['id_noche']; ?>">
                        <?php endif; ?>

                       
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-event-fill text-primary me-1"></i> Fecha
                            </label>
                            <input type="date" name="fecha" class="form-control form-control-lg"
                                   value="<?php echo $noche_a_editar['fecha'] ?? ''; ?>" required>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-alt-fill text-primary me-1"></i> Lugar
                            </label>
                            <input type="text" name="lugar" class="form-control form-control-lg"
                                   placeholder="Lugar del evento"
                                   value="<?php echo $noche_a_editar['lugar'] ?? ''; ?>" required>
                        </div>

                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-hash text-primary me-1"></i> Número de Noche
                            </label>
                            <input type="number" name="numero" class="form-control form-control-lg"
                                   placeholder="Ej: 1, 2, 3..."
                                   value="<?php echo $noche_a_editar['numero_noche'] ?? ''; ?>" required>
                        </div>

                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i> Detalles
                            </label>
                            <textarea name="detalles" class="form-control" rows="2"
                                      placeholder="Información adicional"><?php echo $noche_a_editar['detalles'] ?? ''; ?></textarea>
                        </div>

                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-music-note-list text-primary me-1"></i> Comparsas Participantes
                            </label>
                            <div class="card bg-dark border-secondary">
                                <div class="card-body p-3">
                                    <?php if (empty($comparsas_activas)): ?>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            No hay comparsas activas disponibles.
                                        </p>
                                    <?php else: ?>
                                        <?php foreach ($comparsas_activas as $c): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="comparsas[]"
                                                       id="comp_<?php echo $c['id_comparsa']; ?>"
                                                       value="<?php echo $c['id_comparsa']; ?>"
                                                       <?php echo (isset($comparsas_noche_actual) && in_array($c['id_comparsa'], $comparsas_noche_actual)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="comp_<?php echo $c['id_comparsa']; ?>">
                                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (!empty($rel_comp_eliminadas)): ?>
<div class="alert alert-warning mt-3">
    <div class="fw-bold mb-2">Relaciones con comparsas eliminadas</div>
    <p class="mb-2 small">
        Estas comparsas fueron eliminadas globalmente y <u>no deberían puntuar</u>. 
        Siguen asociadas a esta noche. Podés quitarlas ahora:
    </p>
    <ul class="mb-2">
        <?php foreach ($rel_comp_eliminadas as $row): ?>
            <li class="d-flex align-items-center gap-2">
                <span class="badge text-bg-secondary"><?php echo htmlspecialchars($row['nombre'] ?? ('ID '.$row['id_comparsa'])); ?></span>
                <form method="post" class="m-0">
                    <input type="hidden" name="action" value="quitar_nc">
                    <input type="hidden" name="noche_id" value="<?php echo (int)$noche_a_editar['id_noche']; ?>">
                    <input type="hidden" name="id_nc" value="<?php echo (int)$row['id_noche_comparsa']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Quitar ahora</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="mb-0 small">
        <em>Tip:</em> Si simplemente guardás la noche con las casillas activas marcadas, 
        también se quitarán las que no estén en la lista de activas.
    </p>
</div>
<?php endif; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                       
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-tags-fill text-primary me-1"></i> Categorías Evaluadas
                            </label>
                            <div class="card bg-dark border-secondary">
                                <div class="card-body p-3">
                                    <?php if (empty($categorias_activas)): ?>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            No hay categorías activas disponibles.
                                        </p>
                                    <?php else: ?>
                                        <?php foreach ($categorias_activas as $cat): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="categorias[]"
                                                       id="cat_<?php echo $cat['id_categoria']; ?>"
                                                       value="<?php echo $cat['id_categoria']; ?>"
                                                       <?php echo (isset($categorias_noche_actual) && in_array($cat['id_categoria'], $categorias_noche_actual)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="cat_<?php echo $cat['id_categoria']; ?>">
                                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (!empty($rel_cat_eliminadas)): ?>
<div class="alert alert-warning mt-3">
    <div class="fw-bold mb-2">Relaciones con categorías eliminadas</div>
    <p class="mb-2 small">
        Estas categorías fueron eliminadas globalmente y <u>no deberían puntuar</u>.
        Siguen asociadas a esta noche. Podés quitarlas ahora:
    </p>
    <ul class="mb-2">
        <?php foreach ($rel_cat_eliminadas as $row): ?>
            <li class="d-flex align-items-center gap-2">
                <span class="badge text-bg-secondary"><?php echo htmlspecialchars($row['nombre'] ?? ('ID '.$row['id_categoria'])); ?></span>
                <form method="post" class="m-0">
                    <input type="hidden" name="action" value="quitar_ncat">
                    <input type="hidden" name="noche_id" value="<?php echo (int)$noche_a_editar['id_noche']; ?>">
                    <input type="hidden" name="id_ncat" value="<?php echo (int)$row['id_noche_categoria']; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Quitar ahora</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="mb-0 small">
        <em>Tip:</em> Guardar la noche con solo las activas tildadas también las quita.
    </p>
</div>
<?php endif; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-badge-fill text-primary me-1"></i> Delegados Asignados
                            </label>
                            <div class="card bg-dark border-secondary">
                                <div class="card-body p-3">
                                    <?php if (empty($delegados_activos)): ?>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            No hay delegados activos disponibles.
                                        </p>
                                    <?php else: ?>
                                        <?php foreach ($delegados_activos as $d): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="delegados[]"
                                                       id="del_<?php echo $d['id_usuario']; ?>"
                                                       value="<?php echo $d['id_usuario']; ?>"
                                                       <?php echo (isset($delegados_noche_actual) && in_array($d['id_usuario'], $delegados_noche_actual)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="del_<?php echo $d['id_usuario']; ?>">
                                                    <?php echo htmlspecialchars($d['mail']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                       
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-<?php echo isset($noche_a_editar) ? 'warning' : 'primary'; ?> btn-lg">
                                <i class="bi bi-<?php echo isset($noche_a_editar) ? 'check-circle-fill' : 'plus-circle-fill'; ?> me-1"></i>
                                <?php echo isset($noche_a_editar) ? 'Guardar Cambios' : 'Agregar Noche'; ?>
                            </button>
                            <?php if (isset($noche_a_editar)): ?>
                                <a href="admin-noche.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-x-circle-fill me-1"></i> Cancelar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-12 col-xl-8">
            
            <div class="card border-success shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Noches Activas
                        </h5>
                        <span class="badge bg-white text-success fs-6">
                            <?php echo count($noches_activas); ?>
                        </span>
                    </div>
                </div>

                <?php if (empty($noches_activas)): ?>
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                        <p class="text-muted mt-3 mb-0">No hay noches activas</p>
                    </div>
                <?php else: ?>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover table-striped mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width: 70px;">ID</th>
                                        <th style="width: 120px;">Fecha</th>
                                        <th>Lugar</th>
                                        <th class="text-center" style="width: 80px;">Nº</th>
                                        <th>Comparsas</th>
                                        <th>Categorías</th>
                                        <th>Delegados</th>
                                        <th>Detalles</th>
                                        <th class="text-center" style="width: 140px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($noches_activas as $n): ?>
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">#<?php echo $n['id_noche']; ?></span>
                                            </td>
                                            <td>
                                                <i class="bi bi-calendar-event me-1 text-primary"></i>
                                                <small><?php echo date('d/m/Y', strtotime($n['fecha'])); ?></small>
                                            </td>
                                            <td class="fw-semibold">
                                                <i class="bi bi-geo-alt me-1 text-warning"></i>
                                                <?php echo htmlspecialchars($n['lugar']); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo $n['numero_noche']; ?></span>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo htmlspecialchars($noche->getComparsasNombresByNoche($n['id_noche'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo htmlspecialchars($noche->getCategoriasNombresByNoche($n['id_noche'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo htmlspecialchars($noche->getDelegadosMailsByNoche($n['id_noche'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50 fst-italic">
                                                    <?php echo htmlspecialchars($n['detalles']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="cargar_edicion">
                                                        <input type="hidden" name="id_noche" value="<?php echo $n['id_noche']; ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id_noche" value="<?php echo $n['id_noche']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta noche?')">
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
                            Noches Eliminadas
                        </h5>
                        <span class="badge bg-white text-danger fs-6">
                            <?php echo count($noches_eliminadas); ?>
                        </span>
                    </div>
                </div>

                <?php if (empty($noches_eliminadas)): ?>
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                        <p class="text-muted mt-3 mb-0">No hay noches eliminadas</p>
                    </div>
                <?php else: ?>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover table-striped mb-0 align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" style="width: 70px;">ID</th>
                                        <th style="width: 120px;">Fecha</th>
                                        <th>Lugar</th>
                                        <th class="text-center" style="width: 80px;">Nº</th>
                                        <th>Comparsas</th>
                                        <th>Categorías</th>
                                        <th>Delegados</th>
                                        <th>Detalles</th>
                                        <th class="text-center" style="width: 140px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($noches_eliminadas as $n): ?>
                                        <tr class="opacity-75">
                                            <td class="text-center">
                                                <span class="badge bg-secondary">#<?php echo $n['id_noche']; ?></span>
                                            </td>
                                            <td>
                                                <i class="bi bi-calendar-event me-1 text-muted"></i>
                                                <small><?php echo date('d/m/Y', strtotime($n['fecha'])); ?></small>
                                            </td>
                                            <td class="fw-semibold text-white-50">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?php echo htmlspecialchars($n['lugar']); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?php echo $n['numero_noche']; ?></span>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo htmlspecialchars($noche->getComparsasNombresByNoche($n['id_noche'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo htmlspecialchars($noche->getCategoriasNombresByNoche($n['id_noche'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo htmlspecialchars($noche->getDelegadosMailsByNoche($n['id_noche'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50 fst-italic">
                                                    <?php echo htmlspecialchars($n['detalles']); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="reactivar">
                                                    <input type="hidden" name="id_noche" value="<?php echo $n['id_noche']; ?>">
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