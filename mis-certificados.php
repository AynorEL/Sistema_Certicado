<?php
session_start();

if (!isset($_SESSION['customer']['idcliente'])) {
    echo '<div class="alert alert-danger">Debes iniciar sesión para ver tus certificados.</div>';
    require_once 'footer.php';
    exit;
}

require_once('header.php');

// Obtener ID del cliente
$idCliente = $_SESSION['cliente']['idcliente'];

// Obtener los certificados generados
$statement = $pdo->prepare("
    SELECT c.nombre_curso, c.duracion, i.fecha_finalizacion, cg.codigo_validacion, c.idcurso
    FROM certificado_generado cg
    JOIN inscripcion i ON cg.idcliente = i.idcliente AND cg.idcurso = i.idcurso
    JOIN curso c ON i.idcurso = c.idcurso
    WHERE cg.idcliente = ? AND cg.estado = 'Activo'
");
$statement->execute([$idCliente]);
$certificados = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.certificados-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 32px;
  justify-content: center;
}
.certificado-card {
  width: 270px;
  border-radius: 20px;
  background: #1b233d;
  padding: 0;
  overflow: hidden;
  box-shadow: rgba(100, 100, 111, 0.18) 0px 7px 20px 0px;
  transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
}
.certificado-card:hover {
  transform: scale(1.04);
  box-shadow: 0 8px 32px #0d6efd44, 0 3px 8px #0d6efd22;
}
.certificado-card .top-section {
  height: 120px;
  border-radius: 15px 15px 0 0;
  background: linear-gradient(45deg, #049fbb 0%, #50f6ff 100%);
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}
.certificado-card .top-section .border {
  border-bottom-right-radius: 10px;
  height: 28px;
  width: 120px;
  background: #1b233d;
  position: relative;
  transform: skew(-40deg);
  box-shadow: -10px -10px 0 0 #1b233d;
}
.certificado-card .top-section .border::before {
  content: "";
  position: absolute;
  width: 15px;
  height: 15px;
  top: 0;
  right: -15px;
  background: rgba(255,255,255,0);
  border-top-left-radius: 10px;
  box-shadow: -5px -5px 0 2px #1b233d;
}
.certificado-card .top-section .logo {
  position: absolute;
  top: 10px;
  left: 12px;
  width: 38px;
  height: 38px;
  background: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px #0002;
}
.certificado-card .top-section .logo svg {
  width: 26px;
  height: 26px;
  color: #049fbb;
}
.certificado-card .main-title {
  color: #fff;
  font-size: 1.13rem;
  font-family: 'Georgia', serif;
  font-weight: bold;
  text-align: center;
  margin: 18px 10px 0 10px;
  letter-spacing: 1px;
  min-height: 48px;
}
.certificado-card .fecha-emision {
  color: #b0eaff;
  font-size: 0.97em;
  text-align: center;
  margin-top: 2px;
}
.certificado-card .codigo {
  background: #222b44;
  color: #b0eaff;
  font-size: 0.98em;
  text-align: center;
  padding: 7px 0 6px 0;
  letter-spacing: 1px;
  border-radius: 0 0 12px 12px;
  margin: 0 10px 0 10px;
}
.certificado-card .cert-btns {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin: 18px 0 10px 0;
}
.certificado-card .cert-btns .btn {
  min-width: 90px;
  font-size: 0.98rem;
  border-radius: 7px;
  border: none;
  padding: 7px 0;
  font-weight: 500;
  transition: background 0.18s, color 0.18s;
}
.certificado-card .cert-btns .btn-info {
  background: #0d6efd;
  color: #fff;
}
.certificado-card .cert-btns .btn-info:hover {
  background: #0b5ed7;
  color: #fff;
}
.certificado-card .cert-btns .btn-success {
  background: #22c55e;
  color: #fff;
}
.certificado-card .cert-btns .btn-success:hover {
  background: #16a34a;
  color: #fff;
}
@media (max-width: 600px) {
  .certificados-grid { gap: 16px; }
  .certificado-card { width: 98vw; min-width: 0; max-width: 340px; }
}
</style>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <h3>Certificados Obtenidos</h3>
                    <?php if (count($certificados) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre del Curso</th>
                                        <th>Duración (horas)</th>
                                        <th>Fecha de Finalización</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificados as $certificado): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($certificado['nombre_curso']); ?></td>
                                            <td><?php echo htmlspecialchars($certificado['duracion']); ?></td>
                                            <td><?php echo date("d/m/Y", strtotime($certificado['fecha_finalizacion'])); ?></td>
                                            <td>
                                                <a href="generar-certificado.php?codigo=<?php echo urlencode($certificado['codigo_validacion']); ?>" class="btn btn-success btn-sm" target="_blank">Ver Certificado</a>
                                                <iframe src="admin/previsualizar_certificado_final.php?idcurso=<?php echo $certificado['idcurso']; ?>&idalumno=<?php echo $idCliente; ?>&modo=final" width="350" height="250" style="border:1px solid #ccc; border-radius:8px; margin-top:8px;"></iframe>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aún no has obtenido ningún certificado.</p>
                        <p>Sigue estudiando para conseguir tus certificados. ¡Mucho éxito!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>