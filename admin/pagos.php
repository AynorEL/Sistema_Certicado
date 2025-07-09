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

<?php require_once('header.php'); ?>

<?php
// Conexión a la base de datos
require_once('inc/config.php');

// Consulta para obtener todos los pagos con información relacionada
$sql = "SELECT p.*, c.nombre, c.apellido, c.dni, cur.nombre_curso, ua.nombre_completo as verificado_por
        FROM pago p
        INNER JOIN inscripcion i ON p.idinscripcion = i.idinscripcion
        INNER JOIN cliente c ON i.idcliente = c.idcliente
        INNER JOIN curso cur ON i.idcurso = cur.idcurso
        LEFT JOIN usuarios_admin ua ON p.verificado_por = ua.id_usuario
        ORDER BY p.fecha_pago DESC";

$result = $pdo->query($sql);
?>

<section class="content-header">
    <h1>
        Gestión de Pagos
        <small>Administrar pagos del sistema</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li class="active">Gestión de Pagos</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Lista de Pagos</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportarExcel()">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="exportarPDF()">
                            <i class="fa fa-file-pdf-o"></i> Exportar PDF
                        </button>
                    </div>
                </div>
                
                <div class="box-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Completado">Completado</option>
                                <option value="Reembolsado">Reembolsado</option>
                                <option value="Cancelado">Cancelado</option>
                                <option value="Pendiente_Verificacion">Pendiente Verificación</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="filtroMetodo">
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
                            <button class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fa fa-search"></i> Buscar
                            </button>
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
                        <table class="table table-bordered table-striped" id="tablaPagos">
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
                                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['idpago']; ?></td>
                                    <td><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></td>
                                    <td><?php echo $row['dni']; ?></td>
                                    <td><?php echo $row['nombre_curso']; ?></td>
                                    <td>S/ <?php echo number_format($row['monto'], 2); ?></td>
                                    <td>S/ <?php echo number_format($row['monto_igv'], 2); ?></td>
                                    <td>S/ <?php echo number_format($row['monto_total'], 2); ?></td>
                                    <td><?php echo $row['metodo_pago']; ?></td>
                                    <td>
                                        <?php 
                                        $estadoClass = '';
                                        switch($row['estado']) {
                                            case 'Completado':
                                                $estadoClass = 'label-success';
                                                break;
                                            case 'Pendiente':
                                                $estadoClass = 'label-warning';
                                                break;
                                            case 'Reembolsado':
                                                $estadoClass = 'label-info';
                                                break;
                                            case 'Cancelado':
                                                $estadoClass = 'label-danger';
                                                break;
                                            case 'Pendiente_Verificacion':
                                                $estadoClass = 'label-primary';
                                                break;
                                            default:
                                                $estadoClass = 'label-default';
                                        }
                                        ?>
                                        <span class="label <?php echo $estadoClass; ?>">
                                            <?php echo $row['estado']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_pago'])); ?></td>
                                    <td><?php echo $row['verificado_por'] ?? 'No verificado'; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info btn-xs" onclick="verDetalles(<?php echo $row['idpago']; ?>)" title="Ver detalles">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <?php if ($row['estado'] === 'Pendiente_Verificacion'): ?>
                                            <button type="button" class="btn btn-success btn-xs" onclick="verificarPago(<?php echo $row['idpago']; ?>)" title="Verificar pago">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($row['estado'] === 'Pendiente'): ?>
                                            <button type="button" class="btn btn-danger btn-xs" onclick="cancelarPago(<?php echo $row['idpago']; ?>)" title="Cancelar pago">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Detalles del Pago</h4>
            </div>
            <div class="modal-body" id="detallesContenido">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#tablaPagos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[0, "desc"]]
        });
    });

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
        $('#detallesModal').modal('show');
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

<?php require_once('footer.php'); ?> 