<?php
require_once 'admin/inc/config.php';
session_start();

if (!isset($_REQUEST['id'])) {
    header('location: index.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM curso WHERE idcurso=?");
$statement->execute([$_REQUEST['id']]);
$result = $statement->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    header('location: index.php');
    exit;
}

// Datos del curso
$nombre_curso = $result['nombre_curso'];
$descripcion = $result['descripcion'];
$duracion = $result['duracion'];
$idcategoria = $result['idcategoria'];
$idinstructor = $result['idinstructor'];
$estado = $result['estado'];
$dias_semana = $result['dias_semana'];
$hora_inicio = $result['hora_inicio'];
$hora_fin = $result['hora_fin'];
$precio = $result['precio'] ?? 0.00;
$cupos_disponibles = $result['cupos_disponibles'] ?? 0;

// Categoría
$statement = $pdo->prepare("SELECT * FROM categoria WHERE idcategoria=?");
$statement->execute([$idcategoria]);
$categoria = $statement->fetch(PDO::FETCH_ASSOC);

// Instructor
$statement = $pdo->prepare("SELECT * FROM instructor WHERE idinstructor=?");
$statement->execute([$idinstructor]);
$instructor = $statement->fetch(PDO::FETCH_ASSOC);

$nombre_instructor = $instructor['nombre'] ?? 'Sin asignar';
$apellido_instructor = $instructor['apellido'] ?? '';
$especialidad = $instructor['especialidad'] ?? '';
$experiencia = $instructor['experiencia'] ?? '';
$email_instructor = $instructor['email'] ?? '';

// Módulos
$statement = $pdo->prepare("SELECT * FROM modulo WHERE idcurso=? ORDER BY idmodulo ASC");
$statement->execute([$_REQUEST['id']]);
$modulos = $statement->fetchAll(PDO::FETCH_ASSOC);

// Horas lectivas
$statement = $pdo->prepare("SELECT * FROM hora_lectiva WHERE idcurso=? ORDER BY fecha ASC");
$statement->execute([$_REQUEST['id']]);
$horas_lectivas = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once('header.php'); ?>

<div class="course-details">
  <div class="container">
    <div class="row">
      <div class="col-md-8">
        <div class="course-info">
          <h1 id="curso-<?php echo htmlspecialchars($_REQUEST['id']); ?>">
            <?php echo htmlspecialchars($nombre_curso); ?>
          </h1>
          <div class="course-meta mb-3">
            <span><i class="fa fa-clock-o"></i> Duración: <?php echo htmlspecialchars($duracion); ?> horas</span> |
            <span><i class="fa fa-calendar"></i> Estado: <?php echo htmlspecialchars($estado); ?></span> |
            <span><i class="fa fa-user"></i> Instructor: <?php echo htmlspecialchars($nombre_instructor . ' ' . $apellido_instructor); ?></span>
          </div>

          <div class="course-description mb-4">
            <h3>Descripción del Curso</h3>
            <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
          </div>

          <?php if(!empty($dias_semana) && !empty($hora_inicio) && !empty($hora_fin)): ?>
            <div class="course-schedule mb-4">
              <h3>Horario</h3>
              <p>Días: <?php echo htmlspecialchars($dias_semana); ?></p>
              <p>Horario: <?php echo date('h:i A', strtotime($hora_inicio)) . ' - ' . date('h:i A', strtotime($hora_fin)); ?></p>
            </div>
          <?php endif; ?>

          <?php if($modulos): ?>
            <div class="course-modules mb-4">
              <h3>Módulos del Curso</h3>
              <div class="accordion" id="courseModules">
                <?php foreach($modulos as $modulo): ?>
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $modulo['idmodulo']; ?>">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $modulo['idmodulo']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $modulo['idmodulo']; ?>">
                        <?php echo htmlspecialchars($modulo['nombre_modulo']); ?>
                      </button>
                    </h2>
                    <div id="collapse<?php echo $modulo['idmodulo']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $modulo['idmodulo']; ?>" data-bs-parent="#courseModules">
                      <div class="accordion-body">
                        <?php echo nl2br(htmlspecialchars($modulo['descripcion'])); ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if($horas_lectivas): ?>
            <div class="course-schedule mb-4">
              <h3>Calendario de Clases</h3>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Hora Inicio</th>
                      <th>Hora Fin</th>
                      <th>Tema</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($horas_lectivas as $hora): ?>
                      <tr>
                        <td><?php echo date('d/m/Y', strtotime($hora['fecha'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($hora['hora_inicio'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($hora['hora_fin'])); ?></td>
                        <td><?php echo htmlspecialchars($hora['tema']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-4">
        <div class="course-sidebar">
          <div class="sidebar-widget mb-4">
            <h3>Información del Curso</h3>
            <ul class="list-unstyled">
              <li><strong>Categoría:</strong> <?php echo htmlspecialchars($categoria['nombre_categoria'] ?? 'Sin categoría'); ?></li>
              <li><strong>Duración:</strong> <?php echo htmlspecialchars($duracion); ?> horas</li>
              <li><strong>Estado:</strong> <?php echo htmlspecialchars($estado); ?></li>
              <li><strong>Precio:</strong> <span class="text-primary h4">S/ <?php echo number_format($precio, 2); ?></span></li>
              <li><strong>Cupos Disponibles:</strong> <?php echo htmlspecialchars($cupos_disponibles); ?></li>
            </ul>
          </div>

          <div class="sidebar-widget mb-4">
            <h3>Instructor</h3>
            <div class="instructor-info">
              <h4><?php echo htmlspecialchars($nombre_instructor . ' ' . $apellido_instructor); ?></h4>
              <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($especialidad); ?></p>
              <p><strong>Experiencia:</strong> <?php echo htmlspecialchars($experiencia); ?> años</p>
              <p><strong>Email:</strong> <?php echo htmlspecialchars($email_instructor); ?></p>
            </div>
          </div>

          <div class="sidebar-widget">
            <button class="btn btn-primary btn-lg w-100 add-to-cart" data-id="<?php echo htmlspecialchars($_REQUEST['id']); ?>">
              <i class="fas fa-shopping-cart"></i> Agregar al Carrito
            </button>
            <a href="cart.php" class="btn btn-outline-primary w-100 mt-2">
              <i class="fas fa-shopping-basket"></i> Ver Carrito
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function() {
  $('.add-to-cart').click(function() {
    var idcurso = $(this).data('id');
    $.ajax({
      url: 'cart-add.php',
      type: 'POST',
      data: { idcurso: idcurso },
      dataType: 'json',
      success: function(response) {
        if(response.status === 'success') {
          $.toast({
            text: response.message,
            icon: 'success',
            position: 'top-right',
            hideAfter: 4000,
            showHideTransition: 'slide'
          });
        } else {
          $.toast({
            text: response.message,
            icon: 'error',
            position: 'top-right',
            hideAfter: 4000,
            showHideTransition: 'slide'
          });
        }
      },
      error: function() {
        $.toast({
          text: 'Error al agregar al carrito',
          icon: 'error',
          position: 'top-right',
          hideAfter: 4000,
          showHideTransition: 'slide'
        });
      }
    });
  });
});
</script>

<!-- Script de resaltado -->
<script src="assets/js/search-highlight.js"></script>

<?php require_once('footer.php'); ?>
