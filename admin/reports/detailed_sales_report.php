<?php
require_once('../fpdf186/fpdf.php');
date_default_timezone_set('America/Lima');

class PDF extends FPDF
{
    function Header()
    {
        // Logo
        $this->Image('../../assets/img/logo.png', 10, 5, 50, 20);
        $this->SetY(20);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(200, 10, mb_convert_encoding('Reporte de Ventas por Categorías', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        // Pie de página
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
$ancho_categoria_principal = 30;
$ancho_categoria_media = 35;
$ancho_categoria_final = 30;
$ancho_cantidad = 20;
$ancho_total_ventas = 35;
$ancho_precio_promedio = 25;

// Cabecera de la tabla
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($ancho_id, 8, mb_convert_encoding("ID", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_categoria_principal, 8, mb_convert_encoding("Categoria Principal", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_categoria_media, 8, mb_convert_encoding("Subcategoria", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_categoria_final, 8, mb_convert_encoding("Categoria Final", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_cantidad, 8, mb_convert_encoding("Num Ventas", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_total_ventas, 8, mb_convert_encoding("Total Ventas", 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell($ancho_precio_promedio, 8, mb_convert_encoding("Precio Promedio", 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);

require_once('../inc/config.php');
$query = "
    SELECT 
        tc.tcat_name AS categoria_principal,
        mc.mcat_name AS subcategoria,
        ec.ecat_name AS categoria_final,
        COUNT(o.id) AS num_ventas,
        SUM(o.quantity * o.unit_price) AS total_ventas,
        AVG(o.unit_price) AS precio_promedio
    FROM tbl_order o
    JOIN tbl_product p ON o.product_id = p.p_id
    JOIN tbl_end_category ec ON p.ecat_id = ec.ecat_id
    JOIN tbl_mid_category mc ON ec.mcat_id = mc.mcat_id
    JOIN tbl_top_category tc ON mc.tcat_id = tc.tcat_id
    GROUP BY tc.tcat_name, mc.mcat_name, ec.ecat_name
    ORDER BY total_ventas DESC;
";

$stmt = $pdo->query($query);


$id_counter = 1;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $num_ventas = $row['num_ventas'];
    $total_ventas = (is_numeric($row['total_ventas']) ? number_format($row['total_ventas'], 2) : '0.00');
    $precio_promedio = (is_numeric($row['precio_promedio']) ? number_format($row['precio_promedio'], 2) : '0.00');

    // Ajustar altura de la fila según el texto más largo
    $lineas_categoria_principal = ceil($pdf->GetStringWidth($row['categoria_principal']) / $ancho_categoria_principal);
    $lineas_subcategoria = ceil($pdf->GetStringWidth($row['subcategoria']) / $ancho_categoria_media);
    $lineas_categoria_final = ceil($pdf->GetStringWidth($row['categoria_final']) / $ancho_categoria_final);
    $altura_fila = max(8, 8 * max($lineas_categoria_principal, $lineas_subcategoria, $lineas_categoria_final));

    // Mostrar los datos en las celdas correspondientes
    $pdf->Cell($ancho_id, $altura_fila, mb_convert_encoding($id_counter, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C'); // Mostrar el ID
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Mostrar las celdas con los valores de la consulta
    $pdf->MultiCell($ancho_categoria_principal, 8, mb_convert_encoding($row['categoria_principal'], 'ISO-8859-1', 'UTF-8'), 1, 'L');
    $pdf->SetXY($x + $ancho_categoria_principal, $y);

    $pdf->MultiCell($ancho_categoria_media, 8, mb_convert_encoding($row['subcategoria'], 'ISO-8859-1', 'UTF-8'), 1, 'L');
    $pdf->SetXY($x + $ancho_categoria_principal + $ancho_categoria_media, $y);

    $pdf->MultiCell($ancho_categoria_final, 8, mb_convert_encoding($row['categoria_final'], 'ISO-8859-1', 'UTF-8'), 1, 'L');
    $pdf->SetXY($x + $ancho_categoria_principal + $ancho_categoria_media + $ancho_categoria_final, $y);

    $pdf->Cell($ancho_cantidad, $altura_fila, mb_convert_encoding($num_ventas, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_total_ventas, $altura_fila, mb_convert_encoding('$' . $total_ventas, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell($ancho_precio_promedio, $altura_fila, mb_convert_encoding('$' . $precio_promedio, 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');

   
    $id_counter++;
}

$pdf->Output();
