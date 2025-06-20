<?php
session_start();
require_once '../config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 'Super Admin') {
    header('Location: login.php');
    exit();
}

// Obtener la lista de pagos con información detallada
$sql = "SELECT p.*, i.idinscripcion, c.nombre, c.apellido, c.dni, cur.nombre_curso,
        p.monto, p.monto_igv, p.monto_total, p.estado, p.fecha_pago, p.metodo_pago,
        p.comprobante, p.fecha_verificacion, ua.nombre_completo as verificado_por
        FROM pago p
        INNER JOIN inscripcion i ON p.idinscripcion = i.idinscripcion
        INNER JOIN cliente c ON i.idcliente = c.idcliente
        INNER JOIN curso cur ON i.idcurso = cur.idcurso
        LEFT JOIN usuarios_admin ua ON p.verificado_por = ua.id_usuario
        ORDER BY p.fecha_pago DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Pagos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarExcel()">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarPDF()">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="filtroEstado">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Completado">Completado</option>
                            <option value="Reembolsado">Reembolsado</option>
                            <option value="Cancelado">Cancelado</option>
                            <option value="Pendiente_Verificacion">Pendiente Verificación</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filtroMetodo">
                            <option value="">Todos los métodos</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                            <option value="Yape">Yape</option>
                            <option value="Plin">Plin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="buscarPago" placeholder="Buscar por nombre, DNI o curso...">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>

                <!-- Tabla de Pagos -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>DNI</th>
                                <th>Curso</th>
                                <th>Monto</th>
                                <th>IGV</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Verificado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['idpago']; ?></td>
                                <td><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></td>
                                <td><?php echo $row['dni']; ?></td>
                                <td><?php echo $row['nombre_curso']; ?></td>
                                <td><?php echo number_format($row['monto'], 2); ?></td>
                                <td><?php echo number_format($row['monto_igv'], 2); ?></td>
                                <td><?php echo number_format($row['monto_total'], 2); ?></td>
                                <td><?php echo $row['metodo_pago']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($row['estado']) {
                                            'Completado' => 'success',
                                            'Pendiente' => 'warning',
                                            'Reembolsado' => 'info',
                                            'Cancelado' => 'danger',
                                            'Pendiente_Verificacion' => 'primary',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo $row['estado']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_pago'])); ?></td>
                                <td><?php echo $row['verificado_por'] ?? 'No verificado'; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $row['idpago']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($row['estado'] === 'Pendiente_Verificacion'): ?>
                                        <button type="button" class="btn btn-sm btn-success" onclick="verificarPago(<?php echo $row['idpago']; ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($row['estado'] === 'Pendiente'): ?>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="cancelarPago(<?php echo $row['idpago']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de Detalles -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContenido">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function aplicarFiltros() {
            const estado = document.getElementById('filtroEstado').value;
            const metodo = document.getElementById('filtroMetodo').value;
            const busqueda = document.getElementById('buscarPago').value;
            
            // Aquí implementaremos la lógica de filtrado
            console.log('Aplicando filtros:', { estado, metodo, busqueda });
        }

        function verDetalles(idPago) {
            // Aquí implementaremos la carga de detalles
            console.log('Ver detalles del pago:', idPago);
        }

        function verificarPago(idPago) {
            if (confirm('¿Está seguro de verificar este pago?')) {
                // Aquí implementaremos la verificación
                console.log('Verificar pago:', idPago);
            }
        }

        function cancelarPago(idPago) {
            if (confirm('¿Está seguro de cancelar este pago?')) {
                // Aquí implementaremos la cancelación
                console.log('Cancelar pago:', idPago);
            }
        }

        function exportarExcel() {
            // Implementar exportación a Excel
            console.log('Exportando a Excel');
        }

        function exportarPDF() {
            // Implementar exportación a PDF
            console.log('Exportando a PDF');
        }
    </script>
</body>
</html> 