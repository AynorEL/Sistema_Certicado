<?php
require_once 'admin/inc/config.php';
session_start();
require_once 'header.php';

if (!isset($_SESSION['customer']['idcliente'])) {
    echo '<div class="alert alert-danger">Debes iniciar sesión para ver tus certificados.</div>';
    require_once 'footer.php';
    exit;
}

$idcliente = $_SESSION['customer']['idcliente'];
$stmt = $pdo->prepare("SELECT cg.*, cu.nombre_curso 
    FROM certificado_generado cg
    JOIN curso cu ON cg.idcurso = cu.idcurso
    WHERE cg.idcliente = ? AND cg.estado = 'Activo'");
$stmt->execute([$idcliente]);
$certificados = $stmt->fetchAll();
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

<div class="container" style="max-width:1200px;margin:40px auto 30px auto;">
    <h2 class="mb-4 text-center" style="font-weight:700;color:#222;"><i class="bi bi-award-fill"></i> Mis Certificados</h2>
    <?php if (count($certificados) > 0): ?>
        <div class="certificados-grid">
        <?php foreach ($certificados as $cert): ?>
            <div class="certificado-card">
                <div class="top-section">
                    <div class="border"></div>
                    <div class="logo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path fill="#049fbb" d="M12 2c.28 0 .53.15.66.4l2.1 3.8 4.24.62c.27.04.5.23.58.49.08.26 0 .54-.2.73l-3.07 2.99.73 4.23c.05.27-.06.54-.28.7a.7.7 0 0 1-.74.05L12 14.77l-3.8 2a.7.7 0 0 1-.74-.05.7.7 0 0 1-.28-.7l.73-4.23-3.07-2.99a.7.7 0 0 1-.2-.73c.08-.26.31-.45.58-.49l4.24-.62 2.1-3.8A.7.7 0 0 1 12 2Z"/></svg>
                    </div>
                </div>
                <div class="main-title"><?php echo htmlspecialchars($cert['nombre_curso']); ?></div>
                <div class="fecha-emision">
                    <?php if (!empty($cert['fecha_generacion'])): ?>
                        Emitido: <?php echo date('d/m/Y', strtotime($cert['fecha_generacion'])); ?>
                    <?php endif; ?>
                </div>
                <div class="cert-btns">
                    <a href="verificar-certificado.php?codigo=<?php echo urlencode($cert['codigo_validacion']); ?>" target="_blank" class="btn btn-info"><i class="bi bi-eye"></i> Ver</a>
                                         <a href="generar-pdf-certificado.php?codigo=<?php echo urlencode($cert['codigo_validacion']); ?>" target="_blank" class="btn btn-success"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                </div>
                <div class="codigo">Código: <?php echo htmlspecialchars($cert['codigo_validacion']); ?></div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column align-items-center justify-content-center" style="min-height:300px;">
            <div style="font-size:3.5rem;color:#e0e0e0;"><i class="bi bi-emoji-neutral"></i></div>
            <div class="alert alert-info text-center mt-3" style="max-width:400px;">No tienes certificados disponibles aún.<br>Cuando apruebes un curso y se genere tu certificado, aparecerá aquí.</div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>