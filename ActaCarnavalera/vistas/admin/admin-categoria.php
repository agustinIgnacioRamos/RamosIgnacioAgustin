<?php
session_start();
require_once "../../clases/Categoria.php";
$categoria = new Categoria();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = '';
    if (isset($_POST['action'])) {
        $accion = $_POST['action'];
    }

    if ($accion === 'cargar_edicion') {
        $id_editar = $_POST['id_categoria'];
        $categoria_a_editar = $categoria->buscarPorId($id_editar);
    } elseif ($accion === 'insert') {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $existe = $categoria->buscarPorNombre($nombre);

        if ($existe) {
            if ($existe['deleted_at'] !== null) {
                $categoria->reactivar($existe['id_categoria']);
                $_SESSION['mensaje'] = "Categoría reactivada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Ya existe una categoría activa con ese nombre.";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } else {
            try {
                $categoria->add($nombre, $descripcion);
                $_SESSION['mensaje'] = "Categoría agregada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            } catch (Exception $e) {
                $_SESSION['mensaje'] = $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }

        header("Location: admin-categoria.php");
        exit();
    } elseif ($accion === 'update') {
        $id_categoria = $_POST['id_categoria'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $existe = $categoria->buscarPorNombre($nombre);

        if ($existe) {
            if ($existe['id_categoria'] != $id_categoria) {
                $_SESSION['mensaje'] = "Ya existe otra categoría con ese nombre.";
                $_SESSION['tipo_mensaje'] = "danger";
            } else {
                $categoria->update($id_categoria, $nombre, $descripcion);
                $_SESSION['mensaje'] = "Categoría actualizada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
            }
        } else {
            $categoria->update($id_categoria, $nombre, $descripcion);
            $_SESSION['mensaje'] = "Categoría actualizada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        }

        header("Location: admin-categoria.php");
        exit();
    } elseif ($accion === 'delete') {
        $categoria->delete($_POST['id_categoria']);
        $_SESSION['mensaje'] = "Categoría eliminada correctamente.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: admin-categoria.php");
        exit();
    } elseif ($accion === 'reactivar') {
        $categoria->reactivar($_POST['id_categoria']);
        $_SESSION['mensaje'] = "Categoría reactivada correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: admin-categoria.php");
        exit();
    }
}

$categorias_activas = $categoria->getActivos();
$categorias_eliminadas = $categoria->getEliminados();
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Categorías</title>
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
                            <i class="bi bi-tags-fill text-primary me-2"></i>
                            Gestión de Categorías
                        </h1>
                        <a href="./admin.html" class="btn btn-primary">
                            <i class="bi bi-house-door-fill me-1"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <?php $tipo_mensaje = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'success'; ?>
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
            <?php unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="card border-secondary shadow-sm h-100">
                    <div class="card-header <?php echo isset($categoria_a_editar) ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-<?php echo isset($categoria_a_editar) ? 'pencil-square' : 'plus-circle-fill'; ?> me-2"></i>
                            <?php echo isset($categoria_a_editar) ? 'Editar Categoría' : 'Agregar Nueva Categoría'; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo isset($categoria_a_editar) ? 'update' : 'insert'; ?>">
                            <?php if (isset($categoria_a_editar)): ?>
                                <input type="hidden" name="id_categoria" value="<?php echo $categoria_a_editar['id_categoria']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-tag-fill text-primary me-1"></i> Nombre
                                </label>
                                <input type="text" name="nombre" class="form-control form-control-lg"
                                    placeholder="Nombre de la categoría"
                                    value="<?php echo isset($categoria_a_editar) ? htmlspecialchars($categoria_a_editar['nombre']) : ''; ?>"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-text-paragraph text-primary me-1"></i> Descripción
                                </label>
                                <textarea name="descripcion" class="form-control form-control-lg" rows="3"
                                    placeholder="Descripción de la categoría"><?php echo isset($categoria_a_editar) ? htmlspecialchars($categoria_a_editar['descripcion']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-<?php echo isset($categoria_a_editar) ? 'warning' : 'primary'; ?> btn-lg">
                                    <i class="bi bi-<?php echo isset($categoria_a_editar) ? 'check-circle-fill' : 'plus-circle-fill'; ?> me-1"></i>
                                    <?php echo isset($categoria_a_editar) ? 'Guardar Cambios' : 'Agregar Categoría'; ?>
                                </button>
                                <?php if (isset($categoria_a_editar)): ?>
                                    <a href="admin-categoria.php" class="btn btn-outline-secondary btn-lg">
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
                            <h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Categorías Activas</h5>
                            <span class="badge bg-white text-success fs-6"><?php echo count($categorias_activas); ?></span>
                        </div>
                    </div>

                    <?php if (empty($categorias_activas)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">No hay categorías activas</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th class="text-center" style="width: 200px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categorias_activas as $c): ?>
                                            <tr>
                                                <td class="text-center"><span class="badge bg-secondary">#<?php echo $c['id_categoria']; ?></span></td>
                                                <td class="fw-semibold"><i class="bi bi-tag me-1 text-primary"></i><?php echo htmlspecialchars($c['nombre']); ?></td>
                                                <td class="text-white-50"><?php echo htmlspecialchars($c['descripcion']); ?></td>
                                                <td>
                                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="cargar_edicion">
                                                            <input type="hidden" name="id_categoria" value="<?php echo $c['id_categoria']; ?>">
                                                            <button type="submit" class="btn btn-warning btn-sm">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id_categoria" value="<?php echo $c['id_categoria']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar esta categoría?')">
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
                            <h5 class="mb-0"><i class="bi bi-trash-fill me-2"></i>Categorías Eliminadas</h5>
                            <span class="badge bg-white text-danger fs-6"><?php echo count($categorias_eliminadas); ?></span>
                        </div>
                    </div>

                    <?php if (empty($categorias_eliminadas)): ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3 mb-0">No hay categorías eliminadas</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 80px;">ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th class="text-center" style="width: 200px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-secondary">
                                        <?php foreach ($categorias_eliminadas as $c): ?>
                                            <tr class="opacity-75">
                                                <td class="text-center"><span class="badge bg-secondary">#<?php echo $c['id_categoria']; ?></span></td>
                                                <td class="fw-semibold"><i class="bi bi-tag me-1 text-muted"></i><?php echo htmlspecialchars($c['nombre']); ?></td>
                                                <td class="text-white-50"><?php echo htmlspecialchars($c['descripcion']); ?></td>
                                                <td class="text-center">
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="reactivar">
                                                        <input type="hidden" name="id_categoria" value="<?php echo $c['id_categoria']; ?>">
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