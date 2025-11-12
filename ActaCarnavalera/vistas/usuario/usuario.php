<?php
session_start();
require_once "../../clases/Puntaje.php";
$puntaje = new Puntaje();

$accion = $_POST['action'] ?? '';
$selected_noche = $_POST['id_noche'] ?? '';

$noches = $puntaje->getNochesActivas();
$resultados = [];
$disponible = false;

if (!empty($selected_noche)) {
    $disponible = $puntaje->resultadosDisponibles($selected_noche);
    if ($disponible) {
        $resultados = $puntaje->getResultadosByNoche($selected_noche);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resultados por Noche</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body class="bg-dark text-light">
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3"><i class="bi bi-trophy"></i> Resultados del Carnaval</h1>
            <a href="../index.html" class="btn btn-primary"><i class="bi bi-house"></i> Inicio</a>
        </div>


        <div class="card bg-dark text-light mb-4">
            <div class="card-header"><i class="bi bi-moon-stars"></i> Seleccionar Noche</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="cargar_noche">
                    <div class="d-flex gap-2">
                        <select name="id_noche" class="form-select" required>
                            <option value="">-- Seleccione una noche --</option>
                            <?php foreach ($noches as $n): ?>
                                <option value="<?php echo $n['id_noche']; ?>" <?php echo ($selected_noche == $n['id_noche']) ? 'selected' : ''; ?>>
                                    #<?php echo $n['id_noche']; ?> — <?php echo $n['fecha']; ?>
                                    (<?php echo htmlspecialchars($n['lugar']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-secondary" type="submit">
                            <i class="bi bi-arrow-repeat"></i> Ver Resultados
                        </button>
                    </div>
                </form>
            </div>
        </div>

    
        <?php if (!empty($resultados)): ?>
            <?php
            
            $agrupado = [];
            foreach ($resultados as $r) {
                $agrupado[$r['comparsa']][] = $r;
            }
            ?>

            <div class="table-container">
                <div class="table-header">
                    <i class="bi bi-list-stars text-success fs-3"></i>
                    <h3 class="text-light mb-0">Resultados Oficiales</h3>
                </div>

                <?php foreach ($agrupado as $comparsa => $items): ?>
                    <div class="card bg-dark text-light border-success mb-4 shadow-sm">
                        <div class="card-header bg-success bg-opacity-25">
                            <h5 class="mb-0"><i class="bi bi-people-fill"></i> <?php echo htmlspecialchars($comparsa); ?></h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-dark table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50%">Categoría</th>
                                        <th style="width: 50%">Puntaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($r['categoria']); ?></td>
                                            <td><strong><?php echo number_format((float) $r['valor_puntaje'], 2); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>