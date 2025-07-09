<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Verificar que estamos logueados
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$idcurso = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idcurso <= 0) {
    header('Location: curso.php');
    exit;
}

// Obtener información del curso usando PDO
$stmt = $pdo->prepare("SELECT nombre_curso, diseño, config_certificado FROM curso WHERE idcurso = ?");
$stmt->execute([$idcurso]);
$curso = $stmt->fetch();

if (!$curso) {
    header('Location: curso.php');
    exit;
}

$diseño = $curso['diseño'];
$configGuardada = $curso['config_certificado'];

require_once('header.php');
?>

<!-- Incluir archivos CSS y JS del editor visual -->
<link rel="stylesheet" href="css/editor-visual.css">
<link rel="stylesheet" href="css/print-certificado.css">
<script src="js/editor-visual.js" defer></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Editor Visual de Certificado</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
          <li class="breadcrumb-item"><a href="curso.php">Cursos</a></li>
          <li class="breadcrumb-item active">Editor Visual</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <!-- Barra de herramientas superior -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body p-2">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" onclick="agregarCampo('alumno')">
                  <i class="fa fa-user"></i> Nombre Alumno
                </button>
                <button class="btn btn-primary btn-sm" onclick="agregarCampo('fecha')">
                  <i class="fa fa-calendar"></i> Fecha
                </button>
                <button class="btn btn-warning btn-sm" onclick="agregarCampo('instructor')">
                  <i class="fa fa-chalkboard-teacher"></i> Instructor
                </button>
                <button class="btn btn-info btn-sm" onclick="agregarCampo('especialista')">
                  <i class="fa fa-user-graduate"></i> Especialista
                </button>
                <button class="btn btn-success btn-sm" onclick="agregarCampo('firma_instructor')">
                  <i class="fa fa-signature"></i> Firma Instructor
                </button>
                <button class="btn btn-success btn-sm" onclick="agregarCampo('firma_especialista')">
                  <i class="fa fa-signature"></i> Firma Especialista
                </button>
                <button class="btn btn-secondary btn-sm" onclick="agregarCampo('qr')">
                  <i class="fa fa-qrcode"></i> QR
                </button>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-info btn-sm" onclick="previsualizarCertificado()">
                  <i class="fa fa-eye"></i> Previsualizar
                </button>
                <button class="btn btn-success btn-sm" onclick="guardarConfiguracion()">
                  <i class="fa fa-save"></i> Guardar
                </button>
                <button class="btn btn-warning btn-sm" onclick="generarCertificado()">
                  <i class="fa fa-file-pdf"></i> Generar PDF
                </button>
                <button class="btn btn-secondary btn-sm" onclick="imprimirCertificado()">
                  <i class="fa fa-print"></i> Imprimir
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Área de edición principal -->
      <div class="col-md-9">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fa fa-edit"></i> Editor de Certificado - <?php echo htmlspecialchars($curso['nombre_curso']); ?>
              <small class="text-muted">(2000x1414px - Tamaño Real)</small>
            </h5>
          </div>
          <div class="card-body p-0">
            <!-- Contenedor del editor con scroll -->
            <div class="editor-wrapper">
              <!-- Área del canvas con tamaño real -->
              <div id="editor" class="canvas-area" data-idcurso="<?php echo $idcurso; ?>">
                <?php if (!empty($diseño)): ?>
                  <img id="fondoCertificado" src="../assets/uploads/cursos/<?php echo htmlspecialchars($diseño); ?>" alt="Diseño del Certificado">
                <?php else: ?>
                  <div class="no-background">
                    <i class="fa fa-image fa-3x text-muted"></i>
                    <p class="text-muted">No hay imagen de fondo asignada</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel de propiedades -->
      <div class="col-md-3">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fa fa-cog"></i> Propiedades
            </h5>
          </div>
          <div class="card-body">
            <div id="panelPropiedades" class="properties-panel" style="display: none;">
              <!-- El inspector se mostrará dinámicamente cuando se seleccione un campo -->
            </div>
            <div id="noSelectionMessage" class="text-center text-muted" style="padding: 40px 20px;">
              <i class="fa fa-hand-pointer-o fa-3x mb-3"></i>
              <h6>Inspector de Propiedades</h6>
              <p class="mb-0">Selecciona un campo para editar sus propiedades</p>
            </div>
          </div>
        </div>

        <!-- Panel de configuración del QR -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fa fa-qrcode"></i> Diseñador de QR
            </h5>
          </div>
          <div class="card-body">
            <!-- Vista previa del QR -->
            <div class="text-center mb-3">
              <div id="qrPreviewContainer" class="qr-wrapper" style="width: 300px; height: 300px; margin: 0 auto; border: 1px solid #000; position: relative; overflow: hidden;">
                <div id="qrPreview"></div>
                <img id="qrLogo" class="qr-logo" src="img/logo.png" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 20%; pointer-events: none; display: none;">
              </div>
            </div>
            
            <!-- Controles de tamaño -->
            <div class="form-group">
              <label class="form-label">📏 Tamaño del QR:</label>
              <div class="d-flex align-items-center">
                <input type="range" id="qrSizeSlider" class="form-control-range flex-grow-1" min="100" max="600" step="50" value="300" oninput="ajustarQRPreview()">
                <span id="qrSizeValue" class="ml-2 text-muted" style="min-width: 60px;">300px</span>
              </div>
            </div>
            
            <!-- Controles de colores -->
            <div class="form-group">
              <label class="form-label">🎨 Color del QR:</label>
              <input type="color" id="qrColor" class="form-control form-control-sm" value="#000000" onchange="actualizarQRPreview()">
            </div>
            <div class="form-group">
              <label class="form-label">🎨 Color de fondo:</label>
              <input type="color" id="qrBgColor" class="form-control form-control-sm" value="#FFFFFF" onchange="actualizarQRPreview()">
            </div>
            
            <!-- Control de margen -->
            <div class="form-group">
              <label class="form-label">📐 Margen:</label>
              <input type="range" id="qrMargin" class="form-control-range" min="0" max="10" step="1" value="0" onchange="actualizarQRPreview()">
              <small class="form-text text-muted">Margen: <span id="qrMarginValue">0</span>px</small>
            </div>
            
            <!-- Control de logo -->
            <div class="form-group">
              <label class="form-label">🏷️ Logo del QR:</label>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="qrLogoEnabled" onchange="toggleQRLogo()">
                <label class="custom-control-label" for="qrLogoEnabled">Mostrar logo en el centro</label>
              </div>
            </div>
            
            <!-- Botón de aplicar -->
            <button class="btn btn-primary btn-sm btn-block" onclick="aplicarConfiguracionQR()">
              <i class="fa fa-check"></i> Aplicar Diseño a Todos los QR del Curso
            </button>
          </div>
        </div>

        <!-- Panel de datos de prueba -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fa fa-database"></i> Datos de Prueba
            </h5>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Alumno:</label>
              <select id="selectAlumno" class="form-control form-control-sm" onchange="cambiarAlumno()">
                <option value="">Cargando...</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Fecha:</label>
              <input type="date" id="fechaEmision" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" onchange="actualizarFecha()">
            </div>
            <div class="form-group">
              <label class="form-label">Instructor:</label>
              <select id="selectInstructor" class="form-control form-control-sm" onchange="cambiarInstructor()">
                <option value="">Cargando...</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Especialista:</label>
              <select id="selectEspecialista" class="form-control form-control-sm" onchange="cambiarEspecialista()">
                <option value="">Cargando...</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Estilos CSS -->
<style>
.editor-wrapper {
  overflow: auto;
  max-height: 80vh;
  border: 1px solid #dee2e6;
  border-radius: 8px;
}

/* Estilos para el diseñador de QR */
.qr-wrapper {
  position: relative;
  display: inline-block;
  border: 1px solid #000;
  overflow: hidden;
  background: #fff;
}

.qr-wrapper svg {
  width: 100%;
  height: 100%;
  display: block;
}

.qr-logo {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  pointer-events: none;
  z-index: 10;
}

.form-control-range {
  height: 6px;
  border-radius: 3px;
  background: #ddd;
  outline: none;
  -webkit-appearance: none;
}

.form-control-range::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #007bff;
  cursor: pointer;
}

.form-control-range::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #007bff;
  cursor: pointer;
  border: none;
}

.custom-control-input:checked ~ .custom-control-label::before {
  background-color: #007bff;
  border-color: #007bff;
}
  background: #f8f9fa;
  padding: 20px;
}

.canvas-area {
  width: 2000px;
  height: 1414px;
  position: relative;
  background: white;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  margin: 0 auto;
  border-radius: 8px;
  overflow: hidden;
}

#fondoCertificado {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}

.no-background {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: #f8f9fa;
  color: #6c757d;
}

.editable-field {
  position: absolute;
  min-width: 100px;
  min-height: 30px;
  background: rgba(255, 255, 255, 0.9);
  border: 2px dashed #007bff;
  border-radius: 4px;
  padding: 8px;
  cursor: move;
  z-index: 10;
  font-family: Arial, sans-serif;
  font-size: 14px;
  color: #000;
  text-align: left;
  word-wrap: break-word;
  overflow: hidden;
  transition: all 0.2s ease;
}

.editable-field:hover {
  background: rgba(255, 255, 255, 0.95);
  border-color: #0056b3;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.editable-field.selected {
  border: 2px solid #28a745;
  background: rgba(40, 167, 69, 0.1);
  box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
}

.editable-field[contenteditable="true"]:focus {
  outline: none;
  border-color: #28a745;
  background: rgba(255, 255, 255, 0.98);
}

.resize-handle {
  position: absolute;
  width: 8px;
  height: 8px;
  background: #007bff;
  border: 1px solid white;
  border-radius: 50%;
  cursor: pointer;
  z-index: 11;
}

.resize-handle.nw { top: -4px; left: -4px; cursor: nw-resize; }
.resize-handle.n { top: -4px; left: 50%; transform: translateX(-50%); cursor: n-resize; }
.resize-handle.ne { top: -4px; right: -4px; cursor: ne-resize; }
.resize-handle.e { top: 50%; right: -4px; transform: translateY(-50%); cursor: e-resize; }
.resize-handle.se { bottom: -4px; right: -4px; cursor: se-resize; }
.resize-handle.s { bottom: -4px; left: 50%; transform: translateX(-50%); cursor: s-resize; }
.resize-handle.sw { bottom: -4px; left: -4px; cursor: sw-resize; }
.resize-handle.w { top: 50%; left: -4px; transform: translateY(-50%); cursor: w-resize; }

.properties-panel {
  min-height: 200px;
}

.property-group {
  margin-bottom: 15px;
}

.property-group label {
  font-weight: 600;
  font-size: 12px;
  color: #495057;
  margin-bottom: 5px;
  display: block;
}

.property-group input,
.property-group select {
  width: 100%;
  padding: 6px 8px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 12px;
}

.property-group input[type="color"] {
  height: 35px;
  padding: 2px;
}

.property-group input[type="checkbox"] {
  width: auto;
  margin-right: 8px;
}

.checkbox-group {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.checkbox-group label {
  margin-bottom: 0;
  font-weight: normal;
}

.btn-group-sm .btn {
  padding: 4px 8px;
  font-size: 11px;
}

.field-info {
  background: #e9ecef;
  padding: 8px;
  border-radius: 4px;
  margin-bottom: 10px;
  font-size: 11px;
}

.field-info strong {
  color: #495057;
}

/* Responsive */
@media (max-width: 768px) {
  .editor-wrapper {
    max-height: 60vh;
    padding: 10px;
  }
  
  .canvas-area {
    width: 100%;
    height: auto;
    max-width: 800px;
    max-height: 566px;
  }
}
</style>

<script>
// Variables globales
let campoSeleccionado = null;
let configuracion = {
  campos: [],
  imagen_fondo: '<?php echo $diseño; ?>',
  width: 2000,
  height: 1414
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
  cargarDatosPrueba();
  cargarConfiguracion();
  
  // Event listener para deseleccionar al hacer clic en el fondo
  document.getElementById('editor').addEventListener('click', function(e) {
    if (e.target === this || e.target.id === 'fondoCertificado') {
      deseleccionarCampo();
    }
  });
});

// Función para agregar un nuevo campo
function agregarCampo(tipo) {
  const campo = document.createElement('div');
  campo.className = 'editable-field';
  campo.setAttribute('contenteditable', 'true');
  campo.dataset.tipo = tipo;
  
  // Posición inicial centrada
  campo.style.left = '100px';
  campo.style.top = '100px';
  
  // Contenido según el tipo
  switch(tipo) {
    case 'alumno':
      campo.textContent = 'NOMBRE DEL ALUMNO';
      break;
    case 'fecha':
      campo.textContent = 'FECHA DE EMISIÓN';
      break;
    case 'instructor':
      campo.textContent = 'NOMBRE DEL INSTRUCTOR';
      break;
    case 'especialista':
      campo.textContent = 'NOMBRE DEL ESPECIALISTA';
      break;
    case 'firma_instructor':
      campo.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
      campo.setAttribute('contenteditable', 'false');
      break;
    case 'firma_especialista':
      campo.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
      campo.setAttribute('contenteditable', 'false');
      break;
    case 'qr':
      campo.innerHTML = '<img src="generar_qr.php?test=1" alt="QR" style="width: 120px; height: 120px; object-fit: contain;">';
      campo.setAttribute('contenteditable', 'false');
      break;
  }
  
  // Hacer arrastrable y seleccionable
  hacerArrastrable(campo);
  campo.addEventListener('click', function(e) {
    e.stopPropagation();
    seleccionarCampo(campo);
  });
  
  document.getElementById('editor').appendChild(campo);
  seleccionarCampo(campo);
  actualizarConfiguracion();
}

// Función para hacer un campo arrastrable
function hacerArrastrable(elemento) {
  let isDragging = false;
  let startX, startY, startLeft, startTop;
  
  elemento.addEventListener('mousedown', function(e) {
    if (e.target.classList.contains('resize-handle')) return;
    
    isDragging = true;
    startX = e.clientX;
    startY = e.clientY;
    startLeft = parseInt(elemento.style.left) || 0;
    startTop = parseInt(elemento.style.top) || 0;
    
    elemento.style.cursor = 'grabbing';
    e.preventDefault();
  });
  
  document.addEventListener('mousemove', function(e) {
    if (!isDragging) return;
    
    const deltaX = e.clientX - startX;
    const deltaY = e.clientY - startY;
    
    elemento.style.left = (startLeft + deltaX) + 'px';
    elemento.style.top = (startTop + deltaY) + 'px';
  });
  
  document.addEventListener('mouseup', function() {
    if (isDragging) {
      isDragging = false;
      elemento.style.cursor = 'move';
      actualizarConfiguracion();
    }
  });
}

// Función para seleccionar un campo
function seleccionarCampo(campo) {
  // Deseleccionar campo anterior
  if (campoSeleccionado) {
    campoSeleccionado.classList.remove('selected');
    removerHandles(campoSeleccionado);
  }
  
  // Seleccionar nuevo campo
  campoSeleccionado = campo;
  campo.classList.add('selected');
  
  // Agregar handles de redimensionamiento
  agregarHandles(campo);
  
  // Mostrar panel de propiedades
  mostrarPanelPropiedades(campo);
}

// Función para deseleccionar campo
function deseleccionarCampo() {
  if (campoSeleccionado) {
    campoSeleccionado.classList.remove('selected');
    removerHandles(campoSeleccionado);
    campoSeleccionado = null;
    mostrarPanelPropiedades(null);
  }
}

// Función para agregar handles de redimensionamiento
function agregarHandles(campo) {
  const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
  
  handles.forEach(pos => {
    const handle = document.createElement('div');
    handle.className = `resize-handle ${pos}`;
    handle.dataset.direction = pos;
    
    handle.addEventListener('mousedown', function(e) {
      e.stopPropagation();
      iniciarRedimension(campo, pos, e);
    });
    
    campo.appendChild(handle);
  });
}

// Función para remover handles
function removerHandles(campo) {
  const handles = campo.querySelectorAll('.resize-handle');
  handles.forEach(handle => handle.remove());
}

// Función para iniciar redimensionamiento
function iniciarRedimension(campo, direccion, e) {
  const startX = e.clientX;
  const startY = e.clientY;
  const startWidth = campo.offsetWidth;
  const startHeight = campo.offsetHeight;
  const startLeft = parseInt(campo.style.left) || 0;
  const startTop = parseInt(campo.style.top) || 0;
  
  function mover(ev) {
    const deltaX = ev.clientX - startX;
    const deltaY = ev.clientY - startY;
    
    let newWidth = startWidth;
    let newHeight = startHeight;
    let newLeft = startLeft;
    let newTop = startTop;
    
    if (direccion.includes('e')) newWidth = Math.max(50, startWidth + deltaX);
    if (direccion.includes('s')) newHeight = Math.max(30, startHeight + deltaY);
    if (direccion.includes('w')) {
      newWidth = Math.max(50, startWidth - deltaX);
      newLeft = startLeft + deltaX;
    }
    if (direccion.includes('n')) {
      newHeight = Math.max(30, startHeight - deltaY);
      newTop = startTop + deltaY;
    }
    
    campo.style.width = newWidth + 'px';
    campo.style.height = newHeight + 'px';
    campo.style.left = newLeft + 'px';
    campo.style.top = newTop + 'px';
  }
  
  function soltar() {
    document.removeEventListener('mousemove', mover);
    document.removeEventListener('mouseup', soltar);
    actualizarConfiguracion();
  }
  
  document.addEventListener('mousemove', mover);
  document.addEventListener('mouseup', soltar);
}

// Función para mostrar panel de propiedades
function mostrarPanelPropiedades(campo) {
  const panel = document.getElementById('panelPropiedades');
  
  if (!campo) {
    panel.innerHTML = `
      <div class="text-center text-muted">
        <i class="fa fa-hand-pointer-o fa-2x"></i>
        <p>Selecciona un campo para editar sus propiedades</p>
      </div>
    `;
    return;
  }
  
  const tipo = campo.dataset.tipo;
  const nombreTipo = obtenerNombreTipo(tipo);
  
  panel.innerHTML = `
    <div class="field-info">
      <strong>Tipo:</strong> ${nombreTipo}<br>
      <strong>Posición:</strong> ${parseInt(campo.style.left) || 0}, ${parseInt(campo.style.top) || 0}<br>
      <strong>Tamaño:</strong> ${campo.offsetWidth} x ${campo.offsetHeight}
    </div>
    
    <div class="property-group">
      <label>Fuente:</label>
      <select id="fontFamily" class="form-control" onchange="aplicarPropiedad('fontFamily', this.value)">
        <option value="Arial">Arial</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Courier New">Courier New</option>
        <option value="Georgia">Georgia</option>
        <option value="Verdana">Verdana</option>
        <option value="Helvetica">Helvetica</option>
        <option value="Tahoma">Tahoma</option>
        <option value="Trebuchet MS">Trebuchet MS</option>
        <option value="Impact">Impact</option>
        <option value="Comic Sans MS">Comic Sans MS</option>
      </select>
    </div>
    
    <div class="property-group">
      <label>Tamaño de fuente:</label>
      <input type="number" id="fontSize" class="form-control" value="${parseInt(campo.style.fontSize) || 14}" min="8" max="72" onchange="aplicarPropiedad('fontSize', this.value + 'px')">
    </div>
    
    <div class="property-group">
      <label>Color:</label>
      <input type="color" id="textColor" class="form-control" value="${campo.style.color || '#000000'}" onchange="aplicarPropiedad('color', this.value)">
    </div>
    
    <div class="property-group">
      <label>Alineación:</label>
      <div class="btn-group btn-group-sm w-100">
        <button type="button" class="btn btn-outline-secondary" onclick="aplicarPropiedad('textAlign', 'left')">
          <i class="fa fa-align-left"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="aplicarPropiedad('textAlign', 'center')">
          <i class="fa fa-align-center"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="aplicarPropiedad('textAlign', 'right')">
          <i class="fa fa-align-right"></i>
        </button>
      </div>
    </div>
    
    <div class="property-group">
      <div class="checkbox-group">
        <input type="checkbox" id="fontBold" onchange="aplicarPropiedad('fontWeight', this.checked ? 'bold' : 'normal')">
        <label for="fontBold">Negrita</label>
      </div>
      <div class="checkbox-group">
        <input type="checkbox" id="fontItalic" onchange="aplicarPropiedad('fontStyle', this.checked ? 'italic' : 'normal')">
        <label for="fontItalic">Cursiva</label>
      </div>
    </div>
    
    <div class="property-group">
      <button class="btn btn-danger btn-sm w-100" onclick="eliminarCampo()">
        <i class="fa fa-trash"></i> Eliminar Campo
      </button>
    </div>
  `;
  
  // Establecer valores actuales
  setTimeout(() => {
    if (document.getElementById('fontFamily')) {
      document.getElementById('fontFamily').value = campo.style.fontFamily || 'Arial';
    }
    if (document.getElementById('fontBold')) {
      document.getElementById('fontBold').checked = campo.style.fontWeight === 'bold';
    }
    if (document.getElementById('fontItalic')) {
      document.getElementById('fontItalic').checked = campo.style.fontStyle === 'italic';
    }
  }, 10);
}

// Función para aplicar propiedad
function aplicarPropiedad(propiedad, valor) {
  if (campoSeleccionado) {
    campoSeleccionado.style[propiedad] = valor;
    actualizarConfiguracion();
  }
}

// Función para eliminar campo
function eliminarCampo() {
  if (campoSeleccionado && confirm('¿Estás seguro de que quieres eliminar este campo?')) {
    campoSeleccionado.remove();
    campoSeleccionado = null;
    mostrarPanelPropiedades(null);
    actualizarConfiguracion();
  }
}

// Función para obtener nombre legible del tipo
function obtenerNombreTipo(tipo) {
  const nombres = {
    'alumno': 'Nombre del Alumno',
    'fecha': 'Fecha de Emisión',
    'instructor': 'Instructor',
    'especialista': 'Especialista',
    'firma_instructor': 'Firma del Instructor',
    'firma_especialista': 'Firma del Especialista',
    'qr': 'Código QR'
  };
  return nombres[tipo] || tipo;
}

// Función para actualizar configuración
function actualizarConfiguracion() {
  const campos = document.querySelectorAll('.editable-field');
  configuracion.campos = [];
  
  campos.forEach(campo => {
    const campoConfig = {
      tipo: campo.dataset.tipo,
      left: parseInt(campo.style.left) || 0,
      top: parseInt(campo.style.top) || 0,
      width: campo.offsetWidth,
      height: campo.offsetHeight,
      fontSize: parseInt(campo.style.fontSize) || 14,
      fontFamily: campo.style.fontFamily || 'Arial',
      color: campo.style.color || '#000000',
      textAlign: campo.style.textAlign || 'left',
      fontWeight: campo.style.fontWeight || 'normal',
      fontStyle: campo.style.fontStyle || 'normal',
      texto: campo.textContent || campo.innerHTML
    };
    
    configuracion.campos.push(campoConfig);
  });
}

// Función para guardar configuración
function guardarConfiguracion() {
  actualizarConfiguracion();
  
  const idcurso = document.getElementById('editor').dataset.idcurso;
  
  fetch('guardar_config_certificado.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      idcurso: idcurso,
      config: configuracion
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Guardado!',
        text: 'La configuración se ha guardado exitosamente',
        timer: 2000,
        showConfirmButton: false
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message || 'Error al guardar la configuración'
      });
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Error de conexión al guardar'
    });
  });
}

// Función para cargar configuración
function cargarConfiguracion() {
  const idcurso = document.getElementById('editor').dataset.idcurso;
  
  fetch(`cargar_config_certificado.php?idcurso=${idcurso}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.config) {
        restaurarCampos(data.config);
      }
    })
    .catch(error => {
      console.error('Error al cargar configuración:', error);
    });
}

// Función para restaurar campos
function restaurarCampos(config) {
  if (!config.campos) return;
  
  // Limpiar campos existentes
  document.querySelectorAll('.editable-field').forEach(campo => campo.remove());
  
  // Restaurar cada campo
  config.campos.forEach(campo => {
    const elemento = document.createElement('div');
    elemento.className = 'editable-field';
    elemento.dataset.tipo = campo.tipo;
    elemento.setAttribute('contenteditable', 'true');
    
    elemento.style.left = campo.left + 'px';
    elemento.style.top = campo.top + 'px';
    elemento.style.width = campo.width + 'px';
    elemento.style.height = campo.height + 'px';
    elemento.style.fontSize = campo.fontSize + 'px';
    elemento.style.fontFamily = campo.fontFamily || 'Arial';
    elemento.style.color = campo.color || '#000000';
    elemento.style.textAlign = campo.textAlign || 'left';
    elemento.style.fontWeight = campo.fontWeight || 'normal';
    elemento.style.fontStyle = campo.fontStyle || 'normal';
    
    // Establecer contenido
    if (campo.tipo === 'firma_instructor' || campo.tipo === 'firma_especialista') {
      elemento.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
      elemento.setAttribute('contenteditable', 'false');
    } else if (campo.tipo === 'qr') {
      elemento.innerHTML = '<img src="generar_qr.php?test=1" alt="QR" style="width: 120px; height: 120px; object-fit: contain;">';
      elemento.setAttribute('contenteditable', 'false');
    } else {
      elemento.textContent = campo.texto || obtenerContenidoCampo(campo.tipo);
    }
    
    // Hacer arrastrable y seleccionable
    hacerArrastrable(elemento);
    elemento.addEventListener('click', function(e) {
      e.stopPropagation();
      seleccionarCampo(elemento);
    });
    
    document.getElementById('editor').appendChild(elemento);
  });
}

// Función para obtener contenido por defecto
function obtenerContenidoCampo(tipo) {
  switch(tipo) {
    case 'alumno': return 'NOMBRE DEL ALUMNO';
    case 'fecha': return 'FECHA DE EMISIÓN';
    case 'instructor': return 'NOMBRE DEL INSTRUCTOR';
    case 'especialista': return 'NOMBRE DEL ESPECIALISTA';
    default: return 'CAMPO';
  }
}

// Funciones para datos de prueba
function cargarDatosPrueba() {
  // Cargar alumnos
  fetch('obtener_datos_certificado.php?tipo=alumnos')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const select = document.getElementById('selectAlumno');
        select.innerHTML = '<option value="">Seleccionar alumno...</option>';
        data.alumnos.forEach(alumno => {
          select.innerHTML += `<option value="${alumno.idcliente}">${alumno.nombre} ${alumno.apellido}</option>`;
        });
      }
    });
  
  // Cargar instructores
  fetch('obtener_datos_certificado.php?tipo=instructores')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const select = document.getElementById('selectInstructor');
        select.innerHTML = '<option value="">Seleccionar instructor...</option>';
        data.instructores.forEach(instructor => {
          select.innerHTML += `<option value="${instructor.idinstructor}">${instructor.nombre} ${instructor.apellido}</option>`;
        });
      }
    });
  
  // Cargar especialistas
  fetch('obtener_datos_certificado.php?tipo=especialistas')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const select = document.getElementById('selectEspecialista');
        select.innerHTML = '<option value="">Seleccionar especialista...</option>';
        data.especialistas.forEach(especialista => {
          select.innerHTML += `<option value="${especialista.idespecialista}">${especialista.nombre} ${especialista.apellido}</option>`;
        });
      }
    });
}

function cambiarAlumno() {
  const select = document.getElementById('selectAlumno');
  const campos = document.querySelectorAll('[data-tipo="alumno"]');
  campos.forEach(campo => {
    campo.textContent = select.options[select.selectedIndex].text;
  });
}

function actualizarFecha() {
  const input = document.getElementById('fechaEmision');
  const fecha = new Date(input.value).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  
  const campos = document.querySelectorAll('[data-tipo="fecha"]');
  campos.forEach(campo => {
    campo.textContent = fecha.toUpperCase();
  });
}

function cambiarInstructor() {
  const select = document.getElementById('selectInstructor');
  const campos = document.querySelectorAll('[data-tipo="instructor"]');
  campos.forEach(campo => {
    campo.textContent = select.options[select.selectedIndex].text;
  });
}

function cambiarEspecialista() {
  const select = document.getElementById('selectEspecialista');
  const campos = document.querySelectorAll('[data-tipo="especialista"]');
  campos.forEach(campo => {
    campo.textContent = select.options[select.selectedIndex].text;
  });
}

// Funciones de utilidad
function previsualizarCertificado() {
  const idcurso = document.getElementById('editor').dataset.idcurso;
  window.open(`previsualizar_certificado_final.php?idcurso=${idcurso}`, '_blank');
}

function generarCertificado() {
  const idcurso = document.getElementById('editor').dataset.idcurso;
  window.open(`generar-certificado.php?idcurso=${idcurso}`, '_blank');
}

// Atajos de teclado
document.addEventListener('keydown', function(e) {
  if (campoSeleccionado) {
    switch(e.key) {
      case 'Delete':
      case 'Backspace':
        if (e.ctrlKey) {
          e.preventDefault();
          eliminarCampo();
        }
        break;
      case 'Escape':
        deseleccionarCampo();
        break;
    }
  }
});
</script>

<?php require_once('footer.php'); ?>