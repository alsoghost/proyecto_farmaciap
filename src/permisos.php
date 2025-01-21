<?php
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../index.php'); // Redirige al inicio de sesión
    exit();
}
include_once "includes/header.php"; ?>
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header card-header-primary">
                ATENCION
            </div>
            <div class="card-body text-center">
                No tienes los privilegios suficientes para acceder a este modulo.
                <br>
                Contacta con el administrador para mas informacion.
                <br>
                <a class="btn btn-danger" href="./">Atras</a>
            </div>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>