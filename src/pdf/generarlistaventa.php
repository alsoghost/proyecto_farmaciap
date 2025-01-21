<?php
require_once "../../conexion.php"; // Asegúrate de que este archivo es accesible
require('fpdf/fpdf.php');

// Configurar zona horaria
date_default_timezone_set('America/Caracas'); // Ajusta según tu ubicación

class PDF extends FPDF
{
    // Cabecera
    function Header()
    {
        // Logo
        $this->Image("../../assets/img/logo.png", 10, 10, 30); // Logo (ajustar ruta si es necesario)
        
        // Título
        $this->SetFont('Arial', 'B', 14);
        $this->Ln(12);
        $this->Cell(0, 10, 'Reporte de Ventas', 0, 1, 'C');
        
        // Fecha y Hora
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Fecha: ' . date('d/m/Y h:i:s A'), 0, 1, 'C');
        $this->Ln(10);

        // Encabezado de la Tabla
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(200, 220, 255); // Color de fondo para el encabezado
        $this->Ln(2);
        $this->Cell(10, 10, '#', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Cliente', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Total (Bs)', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Metodo de Pago', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Fecha', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Hora', 1, 1, 'C', true);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' / {nb}', 0, 0, 'C');
    }
}

// Instancia de PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // Habilita el conteo de páginas
$pdf->AddPage();
$pdf->SetTitle("Reporte de Ventas");
$pdf->SetFont('Arial', '', 10);

// Consultar Ventas
$query = mysqli_query($conexion, "SELECT v.id, c.nombre, v.total, v.fecha, v.metodo_pago FROM ventas v INNER JOIN cliente c ON v.id_cliente = c.idcliente");

if ($query && mysqli_num_rows($query) > 0) {
    $contador = 1;
    while ($row = mysqli_fetch_assoc($query)) {
        // Alternar color de fondo para filas
        $fillColor = ($contador % 2 == 0) ? 240 : 255;
        $pdf->SetFillColor($fillColor, $fillColor, $fillColor);

        // Dividir fecha y hora
        $fechaCompleta = explode(' ', $row['fecha']);
        $fecha = $fechaCompleta[0] ?? '';
        $hora = $fechaCompleta[1] ?? '';

        // Imprimir datos
        $pdf->Cell(10, 10, $contador, 1, 0, 'C', true);
        $pdf->Cell(40, 10, utf8_decode($row['nombre']), 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Bs.' . number_format($row['total'], 2), 1, 0, 'C', true);
        $pdf->Cell(50, 10, utf8_decode($row['metodo_pago']), 1, 0, 'C', true);
        $pdf->Cell(30, 10, $fecha, 1, 0, 'C', true);
        $pdf->Cell(30, 10, $hora, 1, 1, 'C', true);

        $contador++;
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(190, 10, 'No hay ventas registradas.', 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('Reporte_Ventas.pdf', "I");
?>