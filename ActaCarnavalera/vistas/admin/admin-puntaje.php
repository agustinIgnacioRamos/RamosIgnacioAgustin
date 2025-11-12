<?php
session_start();
require_once "../../clases/Puntaje.php";
$puntaje = new Puntaje();


$accion = $_POST['action'] ?? '';
$selected_noche = $_POST['id_noche'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if ($accion === 'cargar_noche') {
    } elseif ($accion === 'insert_update') {
        $id_noche     = $_POST['id_noche']     ?? '';
        $id_comparsa  = $_POST['id_comparsa']  ?? '';
        $id_categoria = $_POST['id_categoria'] ?? '';
        $valor        = $_POST['valor_puntaje'] ?? '';
        $registrado   = $_POST['registrado_por'] ?? '';

        if (empty($id_noche) || empty($id_comparsa) || empty($id_categoria) || $valor === '' || empty($registrado)) {
            $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
            $_SESSION['tipo_mensaje'] = "danger";
        } else {
            try {
                $puntaje->addOrUpdate($id_noche, $id_comparsa, $id_categoria, (float)$valor, $registrado);
                $_SESSION['mensaje'] = "Puntaje guardado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } catch (Throwable $e) {
                $_SESSION['mensaje'] = "Error: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
        $selected_noche = $id_noche;
    } elseif ($accion === 'delete') {
        try {
            $puntaje->delete($_POST['id_puntaje']);
            $_SESSION['mensaje'] = "Puntaje eliminado.";
            $_SESSION['tipo_mensaje'] = "danger";
        } catch (Throwable $e) {
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        $selected_noche = $_POST['id_noche'] ?? '';
    } elseif ($accion === 'reactivar') {
        try {
            $puntaje->reactivar($_POST['id_puntaje']);
            $_SESSION['mensaje'] = "Puntaje reactivado.";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Throwable $e) {
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
}

$noches   = $puntaje->getNochesActivas();
$usuarios = $puntaje->getUsuariosActivos();

$comparsas = [];
$categorias = [];
$puntajes_noche = [];

if (!empty($selected_noche)) {
    $comparsas = $puntaje->getComparsasByNoche($selected_noche);
    $categorias = $puntaje->getCategoriasByNoche($selected_noche);
    $puntajes_noche = $puntaje->getPuntajesByNoche($selected_noche);
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Puntajes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                            <i class="bi bi-star-fill text-primary me-2"></i>
                            Gestión de Puntajes
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

            <div class="col-12 col-lg-4">
                <div class="card border-secondary shadow-sm sticky-top" style="top: 1rem;">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            Cargar / Actualizar Puntaje
                        </h5>
                    </div>
                    <div class="card-body p-4">


                        <form method="POST" class="mb-4">
                            <input type="hidden" name="action" value="cargar_noche">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-moon-stars-fill text-primary me-1"></i> Seleccionar Noche
                                </label>
                                <div class="input-group input-group-lg">
                                    <select name="id_noche" class="form-select" required>
                                        <option value="">-- Seleccione una noche --</option>
                                        <?php foreach ($noches as $n): ?>
                                            <option value="<?php echo $n['id_noche']; ?>"
                                                <?php echo ($selected_noche == $n['id_noche']) ? 'selected' : ''; ?>>
                                                #<?php echo $n['id_noche']; ?> — <?php echo date('d/m/Y', strtotime($n['fecha'])); ?> (<?php echo htmlspecialchars($n['lugar']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-secondary" type="submit" title="Cargar comparsas y categorías">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </div>
                                <div class="form-text text-white-50">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Seleccione y actualice para cargar los datos
                                </div>
                            </div>
                        </form>

                        <hr class="border-secondary my-4">


                        <?php if (!empty($selected_noche)): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="insert_update">
                                <input type="hidden" name="id_noche" value="<?php echo $selected_noche; ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-music-note-list text-primary me-1"></i> Comparsa
                                    </label>
                                    <select name="id_comparsa" class="form-select form-select-lg" required>
                                        <option value="">-- Seleccione comparsa --</option>
                                        <?php foreach ($comparsas as $c): ?>
                                            <option value="<?php echo $c['id_comparsa']; ?>">
                                                <?php echo htmlspecialchars($c['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tags-fill text-primary me-1"></i> Categoría
                                    </label>
                                    <select name="id_categoria" class="form-select form-select-lg" required>
                                        <option value="">-- Seleccione categoría --</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat['id_categoria']; ?>">
                                                <?php echo htmlspecialchars($cat['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-check-fill text-primary me-1"></i> Registrado por
                                    </label>
                                    <select name="registrado_por" class="form-select form-select-lg" required>
                                        <option value="">-- Seleccione usuario --</option>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?php echo $u['id_usuario']; ?>">
                                                <?php echo htmlspecialchars($u['mail']); ?> (DNI: <?php echo htmlspecialchars($u['dni']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-123 text-primary me-1"></i> Valor del Puntaje
                                    </label>
                                    <input type="number"
                                        step="0.01"
                                        min="0"
                                        max="10"
                                        name="valor_puntaje"
                                        class="form-control form-control-lg"
                                        placeholder="0.00"
                                        required>
                                    <div class="form-text text-white-50">
                                        Ingrese un valor entre 0 y 10 (puede incluir decimales)
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-check-circle-fill me-1"></i> Guardar Puntaje
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning d-flex align-items-start mb-0" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-2 flex-shrink-0"></i>
                                <div>
                                    Seleccione una <strong>noche</strong> arriba y haga clic en el botón de actualizar para cargar el formulario.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <div class="col-12 col-lg-8">
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <i class="bi bi-clipboard-check-fill me-2"></i>
                                Puntajes de la Noche Seleccionada
                            </h5>
                            <span class="badge bg-white text-info fs-6">
                                <?php echo count($puntajes_noche); ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($selected_noche)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-arrow-up-circle display-1 text-info opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">Seleccione una noche arriba para ver sus puntajes</p>
                        </div>
                    <?php elseif (empty($puntajes_noche)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">Aún no hay puntajes cargados para esta noche</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 70px;">ID</th>
                                            <th>Comparsa</th>
                                            <th>Categoría</th>
                                            <th class="text-center" style="width: 100px;">Valor</th>
                                            <th>Registrado por</th>
                                            <th style="width: 150px;">Fecha</th>
                                            <th class="text-center" style="width: 100px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($puntajes_noche as $p): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#<?php echo $p['id_puntaje']; ?></span>
                                                </td>
                                                <td class="fw-semibold">
                                                    <i class="bi bi-music-note me-1 text-primary"></i>
                                                    <?php echo htmlspecialchars($p['nombre_comparsa']); ?>
                                                </td>
                                                <td>
                                                    <i class="bi bi-tag me-1 text-info"></i>
                                                    <?php echo htmlspecialchars($p['nombre_categoria']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success fs-6 px-3">
                                                        <?php echo number_format((float)$p['valor_puntaje'], 2); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-white">
                                                            <i class="bi bi-person-fill me-1 text-warning"></i>
                                                            <?php echo htmlspecialchars($p['registrado_mail']); ?>
                                                        </span>
                                                        <small class="text-white-50">
                                                            DNI: <?php echo htmlspecialchars($p['registrado_dni']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-white-50">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo date('d/m/Y H:i', strtotime($p['fecha_registro'])); ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="id_noche" value="<?php echo $selected_noche; ?>">
                                                        <input type="hidden" name="id_puntaje" value="<?php echo $p['id_puntaje']; ?>">
                                                        <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            name="action"
                                                            value="delete"
                                                            title="Eliminar puntaje"
                                                            onclick="return confirm('¿Está seguro de eliminar este puntaje?')">
                                                            <i class="bi bi-trash-fill"></i>
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