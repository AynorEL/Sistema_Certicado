<?php
require_once('header.php');

// Redirigir si no está logueado
if (!isset($_SESSION['cliente'])) {
    header('location: login.php');
    exit;
}

// Obtener ID del cliente
$idCliente = $_SESSION['cliente']['idcliente'];

// Obtener los cursos del cliente
$statement = $pdo->prepare("
    SELECT c.nombre_curso, c.descripcion, c.idcurso
    FROM inscripcion i
    JOIN curso c ON i.idcurso = c.idcurso
    WHERE i.idcliente = ?
");
$statement->execute([$idCliente]);
$cursos = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-banner" style="background-color:#444;">
    <div class="inner">
        <h1>Mis Cursos</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <h3>Cursos en los que estás inscrito</h3>
                    <?php if (count($cursos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre del Curso</th>
                                        <th>Descripción</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cursos as $curso): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                            <td><?php echo htmlspecialchars($curso['descripcion']); ?></td>
                                            <td>
                                                <a href="curso.php?id=<?php echo $curso['idcurso']; ?>" class="btn btn-primary btn-sm">Ver Curso</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aún no te has inscrito a ningún curso.</p>
                        <a href="curso.php" class="btn btn-primary">Ver Cursos Disponibles</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?> 