<?php
session_start();
require_once "../../clases/Correccion.php";
require_once "../../clases/Puntaje.php";

$correccion = new Correccion();
$puntaje = new Puntaje();



$accion = $_POST['action'] ?? '';
$selected_noche = $_POST['id_noche'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'cargar_noche') {
     
    } elseif ($accion === 'aceptar') {
        $id_puntaje = $_POST['id_puntaje'];
        $id_aprobacion = $_POST['id_aprobacion'];
        $nuevo_valor = $_POST['nuevo_valor'];
        $justificacion = trim($_POST['justificacion']);
        try {
            $correccion->aceptarCorreccion($id_puntaje, $id_aprobacion, $id_admin, $nuevo_valor, $justificacion);
            $_SESSION['mensaje'] = "Corrección aceptada y puntaje actualizado.";
            $_SESSION['tipo_mensaje'] = "success";
        } catch (Throwable $e) {
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        $selected_noche = $_POST['id_noche'] ?? '';
    } elseif ($accion === 'rechazar') {
        $id_puntaje = $_POST['id_puntaje'];
        $id_aprobacion = $_POST['id_aprobacion'];
        $justificacion = trim($_POST['justificacion']);
        try {
            $correccion->rechazarCorreccion($id_puntaje, $id_aprobacion, $id_admin, $justificacion);
            $_SESSION['mensaje'] = "Corrección rechazada. Puntaje aprobado nuevamente.";
            $_SESSION['tipo_mensaje'] = "warning";
        } catch (Throwable $e) {
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
        $selected_noche = $_POST['id_noche'] ?? '';
    }
}

$noches = $puntaje->getNochesActivas();
$rechazados = [];
if (!empty($selected_noche)) {
    $rechazados = $correccion->getRechazadosByNoche($selected_noche);
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Revisión de Puntajes (Administrador)</title>
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
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            Revisión de Puntajes Rechazados
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
                            <i class="bi bi-<?php echo $tipo_mensaje === 'success' ? 'check-circle-fill' : ($tipo_mensaje === 'danger' ? 'exclamation-triangle-fill' : ($tipo_mensaje === 'warning' ? 'exclamation-circle-fill' : 'info-circle-fill')); ?> fs-4 me-3 flex-shrink-0"></i>
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

     
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-secondary shadow-sm">
                    <div class="card-header bg-secondary text-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-moon-stars-fill me-2"></i>
                            Seleccionar Noche
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="cargar_noche">
                            <div class="row align-items-end">
                                <div class="col-12 col-md-10">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-calendar-event-fill text-primary me-1"></i>
                                        Seleccione la noche a revisar
                                    </label>
                                    <select name="id_noche" class="form-select form-select-lg" required>
                                        <option value="">-- Seleccione una noche --</option>
                                        <?php foreach ($noches as $n): ?>
                                            <option value="<?php echo $n['id_noche']; ?>" <?php echo ($selected_noche == $n['id_noche']) ? 'selected' : ''; ?>>
                                                #<?php echo $n['id_noche']; ?> — <?php echo date('d/m/Y', strtotime($n['fecha'])); ?>
                                                (<?php echo htmlspecialchars($n['lugar']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mt-3 mt-md-0">
                                    <button class="btn btn-secondary btn-lg w-100" type="submit">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Cargar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

      
        <?php if (!empty($selected_noche)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card border-danger shadow-sm">
                        <div class="card-header bg-danger text-white py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Puntajes Rechazados Pendientes de Revisión
                                </h5>
                                <span class="badge bg-white text-danger fs-6">
                                    <?php echo count($rechazados); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (empty($rechazados)): ?>
                            <div class="card-body text-center py-5">
                                <i class="bi bi-check-circle display-1 text-success opacity-25"></i>
                                <p class="text-muted mt-3 mb-0">No hay puntajes rechazados en esta noche</p>
                                <small class="text-white-50">Todos los puntajes han sido aprobados</small>
                            </div>
                        <?php else: ?>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover table-striped mb-0 align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 15%;">Comparsa</th>
                                                <th style="width: 12%;">Categoría</th>
                                                <th class="text-center" style="width: 10%;">Valor Actual</th>
                                                <th style="width: 13%;">Delegado</th>
                                                <th style="width: 20%;">Justificación del Delegado</th>
                                                <th style="width: 30%;">Acción del Administrador</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rechazados as $r): ?>
                                                <tr>
                                                    <td class="fw-semibold">
                                                        <i class="bi bi-music-note me-1 text-primary"></i>
                                                        <?php echo htmlspecialchars($r['comparsa']); ?>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-tag me-1 text-info"></i>
                                                        <?php echo htmlspecialchars($r['categoria']); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-warning text-dark fs-6 px-3">
                                                            <?php echo number_format((float) $r['valor_puntaje'], 2); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-person-fill me-1 text-warning"></i>
                                                        <small><?php echo htmlspecialchars($r['delegado_mail']); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="alert alert-warning mb-0 py-2 px-3" role="alert">
                                                            <small class="text-dark">
                                                                <i class="bi bi-chat-left-quote me-1"></i>
                                                                <?php echo htmlspecialchars($r['justificacion_delegado']); ?>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                       
                                                        <div class="card bg-dark border-success mb-2">
                                                            <div class="card-body p-3">
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="aceptar">
                                                                    <input type="hidden" name="id_noche" value="<?php echo $selected_noche; ?>">
                                                                    <input type="hidden" name="id_puntaje" value="<?php echo $r['id_puntaje']; ?>">
                                                                    <input type="hidden" name="id_aprobacion" value="<?php echo $r['id_aprobacion']; ?>">

                                                                    <div class="mb-2">
                                                                        <label class="form-label fw-semibold text-success small mb-1">
                                                                            <i class="bi bi-pencil-square me-1"></i>
                                                                            Nuevo Valor
                                                                        </label>
                                                                        <input type="number"
                                                                            step="0.01"
                                                                            min="0"
                                                                            max="10"
                                                                            name="nuevo_valor"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="0.00"
                                                                            required>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label class="form-label fw-semibold text-success small mb-1">
                                                                            <i class="bi bi-chat-left-text me-1"></i>
                                                                            Justificación
                                                                        </label>
                                                                        <textarea name="justificacion"
                                                                            rows="2"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Explique por qué acepta la corrección..."
                                                                            required></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                                                        <i class="bi bi-check-circle-fill me-1"></i> Aceptar Corrección
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        
                                                        <div class="card bg-dark border-danger">
                                                            <div class="card-body p-3">
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="rechazar">
                                                                    <input type="hidden" name="id_noche" value="<?php echo $selected_noche; ?>">
                                                                    <input type="hidden" name="id_puntaje" value="<?php echo $r['id_puntaje']; ?>">
                                                                    <input type="hidden" name="id_aprobacion" value="<?php echo $r['id_aprobacion']; ?>">

                                                                    <div class="mb-2">
                                                                        <label class="form-label fw-semibold text-danger small mb-1">
                                                                            <i class="bi bi-chat-left-text me-1"></i>
                                                                            Justificación
                                                                        </label>
                                                                        <textarea name="justificacion"
                                                                            rows="2"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="Explique por qué rechaza la corrección..."
                                                                            required></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                                                        <i class="bi bi-x-circle-fill me-1"></i> Rechazar Corrección
                                                                    </button>
                                                                </form>
                                                            </div>
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
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>