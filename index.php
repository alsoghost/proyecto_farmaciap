<?php
session_start();
$alert = ''; // Inicializa para evitar duplicados

if (!empty($_SESSION['active'])) {
    header('location: src/');
} else {
    if (!empty($_POST)) {
        if (empty($_POST['usuario']) || empty($_POST['clave'])) {
            $alert = '<div class="alert alert-warning" role="alert">
                        Ingrese usuario y contraseña
                      </div>';
        } elseif (strlen($_POST['clave']) < 8) { // Validar longitud mínima
            $alert = '<div class="alert alert-warning" role="alert">
                        La contraseña debe tener al menos 8 caracteres
                      </div>';
        } elseif (!preg_match('/^[A-Z]/', $_POST['clave'])) { // Validar que inicie con mayúscula
            $alert = '<div class="alert alert-warning" role="alert">
                        La contraseña debe empezar con una letra mayúscula
                      </div>';
        } else {
            require_once "conexion.php";
            $user = mysqli_real_escape_string($conexion, $_POST['usuario']);
            $clave = md5(mysqli_real_escape_string($conexion, $_POST['clave']));
            $query = mysqli_query($conexion, "SELECT * FROM usuario WHERE usuario = '$user' AND clave = '$clave'");
            mysqli_close($conexion);
            $resultado = mysqli_num_rows($query);
            if ($resultado > 0) {
                $dato = mysqli_fetch_array($query);
                $_SESSION['active'] = true;
                $_SESSION['idUser'] = $dato['idusuario'];
                $_SESSION['nombre'] = $dato['nombre'];
                $_SESSION['user'] = $dato['usuario'];
                header('Location: src/index.php');
            } else {
                $alert = '<div class="alert alert-danger" role="alert">
                            Usuario o contraseña incorrectos
                          </div>';
                session_destroy();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Iniciar Sesión</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/css/material-dashboard.css">
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/img/favicon.ico" />
</head>

<body class="bg">
    <div class="col-md-4 mx-auto mt-5">
        <!-- Mostrar aviso si está definido -->
        <?php if (!empty($alert)): ?>
            <?php echo $alert; ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header card-header-primary text-center">
                <h4 class="card-title">Iniciar Sesión</h4>
                <img class="img-thumbnail" src="assets/img/logo.png" width="150" alt="Logo" />
            </div>
            <div class="card-body">
                <form action="" method="post" class="p-3">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg text-center" id="usuario" placeholder="Usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control form-control-lg text-center" id="clave" placeholder="Clave" name="clave" required>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-block btn-dark btn-lg font-weight-medium auth-form-btn" type="submit">INGRESAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- plugins:js -->
    <script src="assets/js/material-dashboard.js"></script>
    <!-- endinject -->
</body>

</html>