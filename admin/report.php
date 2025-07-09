<?php
require_once('../fpdf186/fpdf.php');
date_default_timezone_set('America/Lima');

class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../assets/img/logo.png', 10, 5, 50, 20);
        $this->SetY(20);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(200, 10, mb_convert_encoding('Reporte de Productos', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ' . $this->PageNo() . ' - ' . date('d/m/Y | H:i'), 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 5);

// Distribuir ancho de las columnas (máximo total: 190 mm)
$ancho_id = 8;
$ancho_nombre = 35;
$ancho_precio_anterior = 22;
$ancho_precio_actual = 25;
$ancho_cantidad = 13;
$ancho_categoria_final = 30;
$ancho_categoria_media = 35;
$ancho_categoria_principal = 27;

// Cabecera de la tabla
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($ancho_id, 8, mb_convert_encoding("ID", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_nombre, 8, mb_convert_encoding("Nombre", 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
$pdf->Cell($ancho_precio_anterior, 8, mb_convert_encoding("Precio Anterior", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_precio_actual, 8, mb_convert_encoding("Precio Actual", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_cantidad, 8, mb_convert_encoding("Cantidad", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_categoria_final, 8, mb_convert_encoding("Categoria Final", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_categoria_media, 8, mb_convert_encoding("Categoria Media", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_categoria_principal, 8, mb_convert_encoding("Categoria Principal", 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);

require_once('inc/config.php');

// Obtener parámetros de filtro (por ejemplo, desde un formulario o URL)
$categoria_final = isset($_GET['categoria_final']) ? $_GET['categoria_final'] : null;

// Modificar la consulta para incluir el filtro de categoría
$query = "
    SELECT 
        p.p_id AS Producto_ID,
        p.p_name AS Nombre_Producto,
        p.p_old_price AS Precio_Anterior,
        p.p_current_price AS Precio_Actual,
        p.p_qty AS Cantidad_Disponible,
        ec.ecat_name AS Categoria_Final,
        mc.mcat_name AS Categoria_Media,
        tc.tcat_name AS Categoria_Principal
    FROM tbl_product p
    JOIN tbl_end_category ec ON p.ecat_id = ec.ecat_id
    JOIN tbl_mid_category mc ON ec.mcat_id = mc.mcat_id
    JOIN tbl_top_category tc ON mc.tcat_id = tc.tcat_id
    WHERE (:categoria_final IS NULL OR ec.ecat_name = :categoria_final);
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':categoria_final', $categoria_final);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $precio_anterior = (is_numeric($row['Precio_Anterior']) ? number_format($row['Precio_Anterior'], 2) : '0.00');
    $precio_actual = (is_numeric($row['Precio_Actual']) ? number_format($row['Precio_Actual'], 2) : '0.00');
    $nombre_producto = $row['Nombre_Producto'];

    // Ajustar altura de la fila según el texto más largo
    $lineas_nombre = ceil($pdf->GetStringWidth($nombre_producto) / $ancho_nombre);
    $altura_fila = max(8, 8 * $lineas_nombre);

    $pdf->Cell($ancho_id, $altura_fila, mb_convert_encoding($row['Producto_ID'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell($ancho_nombre, 8, mb_convert_encoding($nombre_producto, 'ISO-8859-1', 'UTF-8'), 1, 'L');
    $pdf->SetXY($x + $ancho_nombre, $y);

    $pdf->Cell($ancho_precio_anterior, $altura_fila, mb_convert_encoding('$' . $precio_anterior, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_precio_actual, $altura_fila, mb_convert_encoding('$' . $precio_actual, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_cantidad, $altura_fila, mb_convert_encoding($row['Cantidad_Disponible'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_categoria_final, $altura_fila, mb_convert_encoding($row['Categoria_Final'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_categoria_media, $altura_fila, mb_convert_encoding($row['Categoria_Media'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_categoria_principal, $altura_fila, mb_convert_encoding($row['Categoria_Principal'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
}

$pdf->Output();
