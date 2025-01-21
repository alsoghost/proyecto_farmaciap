<?php
require_once "../conexion.php";
session_start();
if (empty($_SESSION['active'])) {
    header('Location: ../login.php'); // Redirige al inicio de sesión
    exit();
}


if (isset($_GET['q'])) {
    $datos = array();
    $nombre = $_GET['q'];
    $cliente = mysqli_query($conexion, "SELECT * FROM cliente WHERE nombre LIKE '%$nombre%'");
    while ($row = mysqli_fetch_assoc($cliente)) {
        $data['id'] = $row['idcliente'];
        $data['label'] = $row['nombre'];
        $data['direccion'] = $row['direccion'];
        $data['telefono'] = $row['telefono'];
        $data['cedula'] = $row['cedula'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
}else if (isset($_GET['pro'])) {
    $id_user = $_SESSION['idUser'];
    $datos = array();
    $nombre = $_GET['pro'];
    $hoy = date('Y-m-d');
    $producto = mysqli_query($conexion, "SELECT * FROM producto WHERE (codigo LIKE '%" . $nombre . "%' OR descripcion LIKE '%" . $nombre . "%') AND (vencimiento > '$hoy' OR vencimiento = '0000-00-00')");

    while ($row = mysqli_fetch_assoc($producto)) {
        $id = $row['codproducto'];
        $detalle = mysqli_query($conexion, "SELECT * FROM detalle_temp WHERE id_producto = $id AND id_usuario = $id_user");

        if (mysqli_num_rows($detalle) > 0) {
            // Si hay resultados en `detalle`, procesa cada fila
            while ($row2 = mysqli_fetch_assoc($detalle)) {
                $data['id'] = $row['codproducto'];
                $data['cantidad_elegida'] = $row2['cantidad'];
                $data['label'] = $row['codigo'] . ' - ' . $row['descripcion'];
                $data['value'] = $row['descripcion'];
                $data['precio'] = $row['precio'];
                $data['existencia'] = $row['existencia'];
                array_push($datos, $data);
            }
        } else {
            // Si no hay resultados en `detalle`, establece cantidad_elegida a 0
            $data['id'] = $row['codproducto'];
            $data['cantidad_elegida'] = 0;
            $data['label'] = $row['codigo'] . ' - ' . $row['descripcion'];
            $data['value'] = $row['descripcion'];
            $data['precio'] = $row['precio'];
            $data['existencia'] = $row['existencia'];
            array_push($datos, $data);
        }
    }

    echo json_encode($datos);
    die();
}else if (isset($_GET['detalle'])) {
    $id = $_SESSION['idUser'];
    $datos = array();
    $detalle = mysqli_query($conexion, "SELECT d.*, p.codproducto, p.descripcion FROM detalle_temp d INNER JOIN producto p ON d.id_producto = p.codproducto WHERE d.id_usuario = $id");
    while ($row = mysqli_fetch_assoc($detalle)) {
        $data['id'] = $row['id'];
        $data['descripcion'] = $row['descripcion'];
        $data['cantidad'] = $row['cantidad'];
        $data['precio_venta'] = $row['precio_venta'];
        $data['sub_total'] = $row['total'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
} else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];
    $query = mysqli_query($conexion, "DELETE FROM detalle_temp WHERE id = $id_detalle");
    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
} else if (isset($_GET['procesarVenta'])) {
    $id_cliente = $_GET['id'];
    $id_user = $_SESSION['idUser'];
    $metodo_pago = $_GET['metodo_pago'];
    $consulta = mysqli_query($conexion, "SELECT SUM(total) AS total_pagar FROM detalle_temp WHERE id_usuario = $id_user");
    $result = mysqli_fetch_assoc($consulta);
    $total = $result['total_pagar'];
    $insertar = mysqli_query($conexion, "INSERT INTO ventas(id_cliente, total, id_usuario, metodo_pago) VALUES ($id_cliente, '$total', $id_user, '$metodo_pago')");
    if ($insertar) {
        $id_maximo = mysqli_query($conexion, "SELECT MAX(id) AS total FROM ventas");
        $resultId = mysqli_fetch_assoc($id_maximo);
        $ultimoId = $resultId['total'];
        $consultaDetalle = mysqli_query($conexion, "SELECT * FROM detalle_temp WHERE id_usuario = $id_user");
        while ($row = mysqli_fetch_assoc($consultaDetalle)) {
            $id_producto = $row['id_producto'];
            $cantidad = $row['cantidad'];
            $precio = $row['precio_venta'];
            $total = $row['total'];
            $insertarDet = mysqli_query($conexion, "INSERT INTO detalle_venta (id_producto, id_venta, cantidad, precio, total, metodo_pago) VALUES ($id_producto, $ultimoId, $cantidad, '$precio', '$total', '$metodo_pago')");
            $stockActual = mysqli_query($conexion, "SELECT * FROM producto WHERE codproducto = $id_producto");
            $stockNuevo = mysqli_fetch_assoc($stockActual);
            $stockTotal = $stockNuevo['existencia'] - $cantidad;
            $stock = mysqli_query($conexion, "UPDATE producto SET existencia = $stockTotal WHERE codproducto = $id_producto");
        } 
        if ($insertarDet) {
            $eliminar = mysqli_query($conexion, "DELETE FROM detalle_temp WHERE id_usuario = $id_user");
        } 
        $msg = array('id_cliente' => $id_cliente, 'id_venta' => $ultimoId);
    }else{
        $msg = array('mensaje' => 'error');
    }
    echo json_encode($msg);
    die();

}else if(isset($_GET['editarCliente'])){
    $idcliente = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarUsuario'])) {
    $idusuario = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM usuario WHERE idusuario = $idusuario");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarProducto'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM producto WHERE codproducto = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarTipo'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM tipos WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarPresent'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM presentacion WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarLab'])) {
    $id = $_GET['id'];
    $sql = mysqli_query($conexion, "SELECT * FROM laboratorios WHERE id = $id");
    $data = mysqli_fetch_array($sql);
    echo json_encode($data);
    exit;
}
if (isset($_POST['regDetalle'])) {
    $id = $_POST['id'];
    $cant = $_POST['cant'];
    $precio = $_POST['precio'];
    $id_user = $_SESSION['idUser'];
    $total = $precio * $cant;
    $verificar = mysqli_query($conexion, "SELECT * FROM detalle_temp WHERE id_producto = $id AND id_usuario = $id_user");
    $result = mysqli_num_rows($verificar);
    $datos = mysqli_fetch_assoc($verificar);
    if ($result > 0) {
        $cantidad = $datos['cantidad'] + $cant;
        $total_precio = ($cantidad * $total);
        $query = mysqli_query($conexion, "UPDATE detalle_temp SET cantidad = $cantidad, total = '$total_precio' WHERE id_producto = $id AND id_usuario = $id_user");
        if ($query) {
            $msg = "actualizado";
        } else {
            $msg = "Error al ingresar";
        }
    }else{
        $query = mysqli_query($conexion, "INSERT INTO detalle_temp(id_usuario, id_producto, cantidad ,precio_venta, total) VALUES ($id_user, $id, $cant,'$precio', '$total')");
        if ($query) {
            $msg = "registrado";
        }else{
            $msg = "Error al ingresar";
        }
    }
    echo json_encode($msg);
    die();
}else if (isset($_POST['cambio'])) {
    if (empty($_POST['actual']) || empty($_POST['nueva'])) {
        $msg = 'Los campos estan vacios';
    } else {
        $id = $_SESSION['idUser'];
        $actual = md5($_POST['actual']);
        $nueva = md5($_POST['nueva']);
        $consulta = mysqli_query($conexion, "SELECT * FROM usuario WHERE clave = '$actual' AND idusuario = $id");
        $result = mysqli_num_rows($consulta);
        if ($result == 1) {
            $query = mysqli_query($conexion, "UPDATE usuario SET clave = '$nueva' WHERE idusuario = $id");
            if ($query) {
                $msg = 'ok';
            }else{
                $msg = 'error';
            }
        } else {
            $msg = 'dif';
        }
        
    }
    echo $msg;
    die();
    
}

