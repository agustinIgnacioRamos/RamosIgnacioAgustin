<?php
session_start();
require_once "./clases/Usuario.php";

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $mail = '';
    if (isset($_POST['mail'])) {
        $mail = trim($_POST['mail']);
    }

    $contrasena = '';
    if (isset($_POST['contrasena'])) {
        $contrasena = $_POST['contrasena'];
    }


    if (empty($mail) || empty($contrasena)) {
        $mensaje = 'Por favor, complete todos los campos.';
        $tipo_mensaje = 'warning';
    } else {
       
        $usuario = new Usuario();
        $resultado = $usuario->login($mail, $contrasena);

        if ($resultado) {
            
            $_SESSION['id_usuario'] = $resultado['id_usuario'];
            $_SESSION['mail'] = $resultado['mail'];
            $_SESSION['dni'] = $resultado['dni'];
            $_SESSION['rol'] = $resultado['id_rol'];

           
            if ($resultado['id_rol'] == 1) {
               
                header('Location: vistas/admin/admin.html');
                exit;
            } elseif ($resultado['id_rol'] == 2) {
               
                header('Location: vistas/delegado/delegado.php');
                exit;
            } else {
                
                header('Location: vistas/usuario/usuario.php');
                exit;
            }
        } else {
            
            $mensaje = 'Email o contraseña incorrectos.';
            $tipo_mensaje = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acta Carnavalera - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-dark d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-10 col-md-6 col-lg-5 col-xl-4">

               
                <div class="card border-primary shadow-lg">

                    
                    <div class="card-header bg-gradient bg-primary text-white text-center py-4">
                        <div class="mb-2">
                            <i class="bi bi-music-note-beamed display-4"></i>
                        </div>
                        <h1 class="h3 mb-0 fw-bold">Acta Carnavalera</h1>
                        <small class="opacity-75">Sistema de Gestión</small>
                    </div>

                    
                    <div class="card-body p-4 p-md-5">

                        
                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show d-flex align-items-start" role="alert">
                                <i class="bi bi-<?php echo $tipo_mensaje === 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill'; ?> fs-5 me-2 flex-shrink-0"></i>
                                <div class="flex-grow-1">
                                    <?php echo htmlspecialchars($mensaje); ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                      
                        <form method="POST" action="index.php" novalidate>

                            
                            <div class="mb-4">
                                <label for="mail" class="form-label fw-semibold">
                                    <i class="bi bi-envelope-fill text-primary me-1"></i>
                                    Correo Electrónico
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-dark border-secondary">
                                        <i class="bi bi-at"></i>
                                    </span>
                                    <input type="email"
                                        class="form-control form-control-lg bg-dark border-secondary text-light"
                                        name="mail"
                                        id="mail"
                                        placeholder="correo@ejemplo.com"
                                        autocomplete="email"
                                        value="<?php echo isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : ''; ?>"
                                        required>
                                </div>
                            </div>

                     
                            <div class="mb-4">
                                <label for="contrasena" class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill text-primary me-1"></i>
                                    Contraseña
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-dark border-secondary">
                                        <i class="bi bi-key-fill"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control form-control-lg bg-dark border-secondary text-light"
                                        name="contrasena"
                                        id="contrasena"
                                        placeholder="••••••••"
                                        autocomplete="current-password"
                                        required>
                                </div>
                            </div>

                       
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold py-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Iniciar Sesión
                                </button>
                            </div>
                        </form>
                    </div>


                </div>


            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>