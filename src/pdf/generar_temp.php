<?php
require_once '../../conexion.php';
require_once 'fpdf/fpdf.php';

// Otros datos
$id = $_GET['v'];
$idcliente = $_GET['cl'];

$config = mysqli_query($conexion, "SELECT * FROM configuracion");
$datos = mysqli_fetch_assoc($config);
$clientes = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente");
$datosC = mysqli_fetch_assoc($clientes);
$ventas = mysqli_query($conexion, "SELECT d.*, p.codproducto, p.descripcion, d.metodo_pago FROM detalle_venta d INNER JOIN producto p ON d.id_producto = p.codproducto WHERE d.id_venta = $id");


$pdf = new FPDF('P', 'mm', array(80, 200));
$pdf->AddPage();
$pdf->SetMargins(5, 0, 0);
$pdf->SetTitle("Factura de Venta");
$pdf->SetFont('Arial', 'B', 12);

// Encabezado
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(60, 5, utf8_decode($datos['nombre']), 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 5, utf8_decode($datos['direccion']), 0, 1, 'C');
$pdf->Cell(70, 5, "Tel: " . $datos['telefono'], 0, 1, 'C');
$pdf->Cell(70, 5, "Email: " . $datos['email'], 0, 1, 'C');
$pdf->Image("../../assets/img/logo.png", 62, 13, 13, 13, 'PNG');
$pdf->Ln(5);

// Datos del Cliente
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(70, 5, "Datos del Cliente", 0, 1, 'C', 1);

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 5, "Nombre:", 0, 0, 'L');
$pdf->Cell(50, 5, utf8_decode($datosC['nombre']), 0, 1, 'L');
$pdf->Cell(20, 5, utf8_decode("Teléfono:"), 0, 0, 'L');
$pdf->Cell(50, 5, $datosC['telefono'], 0, 1, 'L');
$pdf->Cell(20, 5, "Direccion:", 0, 0, 'L');
$pdf->Cell(50, 5, utf8_decode($datosC['direccion']), 0, 1, 'L');
$pdf->Cell(20, 5, "Cedula:", 0, 0, 'L');
$pdf->Cell(50, 5, utf8_decode($datosC['cedula']), 0, 1, 'L');
$pdf->Ln(3);

// Detalle de Productos
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(0, 102, 204);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(73, 5, "Detalle de Productos", 1, 1, 'C', 1);

$pdf->SetFont('Arial', 'B', 7);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(32, 5, utf8_decode('Descripción'), 1, 0, 'L');
$pdf->Cell(12, 5, 'Cant.', 1, 0, 'C');
$pdf->Cell(12, 5, 'P. Unit.', 1, 0, 'C');
$pdf->Cell(17, 5, 'Sub Total', 1, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$totalVentas = 0.00;


while ($row = mysqli_fetch_assoc($ventas)) {
    $sub_total = $row['total'];
    $totalVentas += $sub_total;

    $metodo_pago = $row['metodo_pago'];

    $pdf->Cell(32, 5, utf8_decode($row['descripcion']), 1, 0, 'L');
    $pdf->Cell(12, 5, $row['cantidad'], 1, 0, 'C');
    $pdf->Cell(12, 5, number_format($row['precio'], 2, '.', ','), 1, 0, 'C');
    $pdf->Cell(17, 5, number_format($sub_total, 2, '.', ','), 1, 1, 'C');
}
$pdf->Ln(3);

// Método de Pago
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(70, 5, "Metodo de Pago", 0, 1, 'C', 1);

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(70, 5, utf8_decode($metodo_pago), 0, 1, 'C');
$pdf->Ln(3);

// Resumen de Totales
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(70, 5, "Resumen de Totales", 0, 1, 'C', 1);


$pdf->Cell(55, 5, 'Monto Total - Bs.', 0, 0, 'R');
$pdf->Cell(15, 5, number_format($totalVentas, 2, '.', ','), 0, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 7);
$pdf->Cell(70, 5, utf8_decode("Gracias por su compra. ¡Vuelva pronto!"), 0, 1, 'C');

// Salida del PDF
$pdf->Output("ventas.pdf", "I");