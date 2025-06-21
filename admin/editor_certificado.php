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

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Editor de Certificado</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
          <li class="breadcrumb-item"><a href="curso.php">Cursos</a></li>
          <li class="breadcrumb-item active">Editor de Certificado</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <!-- Área de trabajo principal -->
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Editor de Certificado - <?php echo htmlspecialchars($curso['nombre_curso']); ?></h3>
            <div class="card-tools">
              <button class="btn btn-sm btn-info" onclick="previsualizarCertificado()">
                <i class="fa fa-eye"></i> Previsualizar
              </button>
              <button class="btn btn-sm btn-warning" onclick="guardarConfiguracion()">
                <i class="fa fa-save"></i> Guardar
              </button>
              <button class="btn btn-sm btn-success" onclick="generarCertificado()">
                <i class="fa fa-file-pdf"></i> Generar PDF
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div id="editor" class="editor-container" style="width: 100%; height: 600px; border: 2px dashed #6c757d; background-color: white; position: relative; overflow: auto; text-align: center;">
              <?php if (!empty($diseño)): ?>
                <img id="fondoCertificado" src="../assets/uploads/cursos/<?php echo htmlspecialchars($diseño); ?>" alt="Diseño del Certificado" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
              <?php else: ?>
                <div class="alert alert-warning p-3">⚠️ Este curso aún no tiene fondo de certificado asignado.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel de controles en fila horizontal -->
    <div class="row mt-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">Controles del Editor</h5>
          </div>
          <div class="card-body">
            <!-- Primera fila: Elementos y datos -->
            <div class="row mb-3">
              <div class="col-md-3">
                <label><strong>Agregar Elementos:</strong></label>
                <div class="btn-group-vertical w-100">
                  <button class="btn btn-primary btn-sm mb-1" onclick="agregarCampo('alumno')">
                    <i class="fa fa-user"></i> Nombre Alumno
                  </button>
                  <button class="btn btn-primary btn-sm mb-1" onclick="agregarCampo('fecha')">
                    <i class="fa fa-calendar"></i> Fecha
                  </button>
                  <button class="btn btn-warning btn-sm mb-1" onclick="agregarCampo('instructor')" id="btnInstructor">
                    <i class="fa fa-chalkboard-teacher"></i> Instructor
                  </button>
                  <button class="btn btn-info btn-sm mb-1" onclick="agregarCampo('especialista')" id="btnEspecialista">
                    <i class="fa fa-user-graduate"></i> Especialista
                  </button>
                </div>
              </div>
              
              <div class="col-md-3">
                <label><strong>Imágenes y QR:</strong></label>
                <div class="btn-group-vertical w-100">
                  <button class="btn btn-success btn-sm mb-1" onclick="agregarCampo('firma_instructor')" id="btnFirmaInstructor">
                    <i class="fa fa-signature"></i> Firma Instructor
                  </button>
                  <button class="btn btn-success btn-sm mb-1" onclick="agregarCampo('firma_especialista')" id="btnFirmaEspecialista">
                    <i class="fa fa-signature"></i> Firma Especialista
                  </button>
                  <button class="btn btn-info btn-sm mb-1" onclick="agregarCampo('qr')">
                    <i class="fa fa-qrcode"></i> Código QR
                  </button>
                </div>
              </div>
              
              <div class="col-md-3">
                <label><strong>Datos de Prueba:</strong></label>
                <div class="form-group">
                  <select id="selectAlumno" class="form-control form-control-sm" onchange="cambiarAlumno()">
                    <option value="">Cargando...</option>
                  </select>
                </div>
                <div class="form-group">
                  <input type="date" id="fechaEmision" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>" onchange="actualizarFecha()">
                </div>
              </div>
              
              <div class="col-md-3">
                <label><strong>Selección de Personal:</strong></label>
                <div class="form-group">
                  <select id="selectInstructor" class="form-control form-control-sm" onchange="cambiarInstructor()">
                    <option value="">Cargando...</option>
                  </select>
                </div>
                <div class="form-group">
                  <select id="selectEspecialista" class="form-control form-control-sm" onchange="cambiarEspecialista()">
                    <option value="">Cargando...</option>
                  </select>
                </div>
                <div class="alert alert-info alert-sm mt-2">
                  <small><i class="fa fa-info-circle"></i> Instructor y Especialista son mutuamente excluyentes</small>
                </div>
              </div>
            </div>

            <!-- Segunda fila: Controles de estilo -->
            <div class="row mb-3">
              <div class="col-md-2">
                <label><strong>Fuente:</strong></label>
                <select id="fontFamily" class="form-control form-control-sm" onchange="aplicarEstilo('fontFamily', this.value)">
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
              
              <div class="col-md-1">
                <label><strong>Tamaño:</strong></label>
                <input type="number" id="fontSize" class="form-control form-control-sm" value="16" min="8" max="72" onchange="aplicarEstilo('fontSize', this.value + 'px')">
              </div>
              
              <div class="col-md-1">
                <label><strong>Color:</strong></label>
                <input type="color" id="textColor" class="form-control form-control-sm" value="#000000" onchange="aplicarEstilo('color', this.value)">
              </div>
              
              <div class="col-md-1">
                <label><strong>Alineación:</strong></label>
                <select id="textAlign" class="form-control form-control-sm" onchange="aplicarEstilo('textAlign', this.value)">
                  <option value="left">Izquierda</option>
                  <option value="center">Centro</option>
                  <option value="right">Derecha</option>
                  <option value="justify">Justificado</option>
                </select>
              </div>
              
              <div class="col-md-1">
                <label><strong>Estilo:</strong></label>
                <select id="fontStyle" class="form-control form-control-sm" onchange="aplicarEstilo('fontStyle', this.value)">
                  <option value="normal">Normal</option>
                  <option value="italic">Cursiva</option>
                </select>
              </div>
              
              <div class="col-md-1">
                <label><strong>Peso:</strong></label>
                <select id="fontWeight" class="form-control form-control-sm" onchange="aplicarEstilo('fontWeight', this.value)">
                  <option value="normal">Normal</option>
                  <option value="bold">Negrita</option>
                  <option value="100">100</option>
                  <option value="300">300</option>
                  <option value="400">400</option>
                  <option value="500">500</option>
                  <option value="600">600</option>
                  <option value="700">700</option>
                  <option value="900">900</option>
                </select>
              </div>
              
              <div class="col-md-1">
                <label><strong>Decoración:</strong></label>
                <select id="textDecoration" class="form-control form-control-sm" onchange="aplicarEstilo('textDecoration', this.value)">
                  <option value="none">Ninguna</option>
                  <option value="underline">Subrayado</option>
                  <option value="line-through">Tachado</option>
                  <option value="overline">Sobrelineado</option>
                </select>
              </div>
              
              <div class="col-md-1">
                <label><strong>Altura:</strong></label>
                <input type="number" id="lineHeight" class="form-control form-control-sm" value="1.2" min="0.5" max="3" step="0.1" onchange="aplicarEstilo('lineHeight', this.value)">
              </div>
              
              <div class="col-md-1">
                <label><strong>Espaciado:</strong></label>
                <input type="number" id="letterSpacing" class="form-control form-control-sm" value="0" min="-5" max="10" step="0.5" onchange="aplicarEstilo('letterSpacing', this.value + 'px')">
              </div>
              
              <div class="col-md-1">
                <label><strong>Rotación:</strong></label>
                <input type="number" id="rotation" class="form-control form-control-sm" value="0" min="-180" max="180" onchange="aplicarEstilo('transform', 'rotate(' + this.value + 'deg)')">
              </div>
              
              <div class="col-md-1">
                <label><strong>Opacidad:</strong></label>
                <input type="range" id="opacity" class="form-control form-control-sm" value="1" min="0" max="1" step="0.1" onchange="aplicarEstilo('opacity', this.value)">
                <small class="form-text text-muted">Valor: <span id="opacityValue">1</span></small>
              </div>
            </div>

            <!-- Tercera fila: Efectos y acciones -->
            <div class="row">
              <div class="col-md-2">
                <label><strong>Fondo:</strong></label>
                <input type="color" id="backgroundColor" class="form-control form-control-sm" value="#ffffff" onchange="aplicarEstilo('backgroundColor', this.value)">
                <div class="form-check">
                  <input type="checkbox" id="useBackground" class="form-check-input" onchange="toggleBackground()">
                  <label class="form-check-label" for="useBackground">Usar fondo</label>
                </div>
              </div>
              
              <div class="col-md-2">
                <label><strong>Bordes:</strong></label>
                <input type="number" id="borderWidth" class="form-control form-control-sm" value="0" min="0" max="10" onchange="aplicarEstilo('borderWidth', this.value + 'px')" placeholder="Ancho">
                <input type="color" id="borderColor" class="form-control form-control-sm mt-1" value="#000000" onchange="aplicarEstilo('borderColor', this.value)">
                <input type="number" id="borderRadius" class="form-control form-control-sm mt-1" value="0" min="0" max="20" onchange="aplicarEstilo('borderRadius', this.value + 'px')" placeholder="Radio">
              </div>
              
              <div class="col-md-2">
                <label><strong>Sombra:</strong></label>
                <input type="color" id="shadowColor" class="form-control form-control-sm" value="#000000" onchange="aplicarEstilo('shadowColor', this.value)">
                <input type="number" id="shadowBlur" class="form-control form-control-sm mt-1" value="0" min="0" max="20" onchange="aplicarEstilo('shadowBlur', this.value)" placeholder="Desenfoque">
                <input type="number" id="shadowOffsetX" class="form-control form-control-sm mt-1" value="0" min="-10" max="10" onchange="aplicarEstilo('shadowOffsetX', this.value)" placeholder="X">
                <input type="number" id="shadowOffsetY" class="form-control form-control-sm mt-1" value="0" min="-10" max="10" onchange="aplicarEstilo('shadowOffsetY', this.value)" placeholder="Y">
              </div>
              
              <div class="col-md-2">
                <label><strong>Tamaño de Firmas:</strong></label>
                <input type="number" id="anchoFirmas" class="form-control form-control-sm" value="120" min="50" max="300" onchange="ajustarTamanioFirmas()" placeholder="Ancho">
                <input type="number" id="altoFirmas" class="form-control form-control-sm mt-1" value="60" min="30" max="150" onchange="ajustarTamanioFirmas()" placeholder="Alto">
              </div>
              
              <div class="col-md-2">
                <label><strong>Acciones Rápidas:</strong></label>
                <div class="btn-group-vertical w-100">
                  <button class="btn btn-danger btn-sm mb-1" onclick="eliminarCampoSeleccionado()">
                    <i class="fa fa-trash"></i> Eliminar Campo
                  </button>
                  <button class="btn btn-secondary btn-sm mb-1" onclick="duplicarCampoSeleccionado()">
                    <i class="fa fa-copy"></i> Duplicar Campo
                  </button>
                  <button class="btn btn-info btn-sm mb-1" onclick="traerAlFrente()">
                    <i class="fa fa-level-up"></i> Traer al Frente
                  </button>
                  <button class="btn btn-info btn-sm mb-1" onclick="enviarAtras()">
                    <i class="fa fa-level-down"></i> Enviar Atrás
                  </button>
                </div>
              </div>
              
              <div class="col-md-2">
                <label><strong>Atajos de Teclado:</strong></label>
                <div class="small text-muted">
                  <div>Supr/Del = Eliminar</div>
                  <div>Ctrl+D = Duplicar</div>
                  <div>Ctrl+↑ = Traer al frente</div>
                  <div>Ctrl+↓ = Enviar atrás</div>
                  <div>Esc = Deseleccionar</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
let datosCertificado = {};
let configGuardada = [];

// Variable global para el campo seleccionado
let campoSeleccionado = null;

document.addEventListener("DOMContentLoaded", function () {
  const idCurso = <?php echo (int)$idcurso; ?>;
  cargarDatosCertificado(idCurso);
  cargarConfiguracionGuardada(idCurso);
  
  // Detectar el tamaño real de la imagen del certificado
  const fondoCertificado = document.getElementById('fondoCertificado');
  if (fondoCertificado) {
    fondoCertificado.onload = function() {
      ajustarEditorAlTamanioReal();
    };
    
    // Si la imagen ya está cargada
    if (fondoCertificado.complete) {
      ajustarEditorAlTamanioReal();
    }
  }
  
  // Evento para actualizar valor de opacidad en tiempo real
  const opacitySlider = document.getElementById('opacity');
  if (opacitySlider) {
    opacitySlider.addEventListener('input', function() {
      document.getElementById('opacityValue').textContent = this.value;
    });
  }
  
  // Evento para aplicar sombra cuando se cambian los valores
  const shadowInputs = ['shadowOffsetX', 'shadowOffsetY', 'shadowBlur', 'shadowColor'];
  shadowInputs.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
      input.addEventListener('change', function() {
        if (campoSeleccionado) {
          const offsetX = document.getElementById('shadowOffsetX').value || 0;
          const offsetY = document.getElementById('shadowOffsetY').value || 0;
          const blur = document.getElementById('shadowBlur').value || 0;
          const color = document.getElementById('shadowColor').value || '#000000';
          
          if (blur > 0) {
            campoSeleccionado.style.textShadow = `${offsetX}px ${offsetY}px ${blur}px ${color}`;
          } else {
            campoSeleccionado.style.textShadow = 'none';
          }
        }
      });
    }
  });
});

// Función para ajustar el editor al tamaño real de la imagen
function ajustarEditorAlTamanioReal() {
  const fondoCertificado = document.getElementById('fondoCertificado');
  const editor = document.getElementById('editor');
  
  if (fondoCertificado && editor) {
    // Esperar a que la imagen esté completamente cargada
    if (fondoCertificado.complete && fondoCertificado.naturalWidth > 0) {
      const imgWidth = fondoCertificado.naturalWidth;
      const imgHeight = fondoCertificado.naturalHeight;
      
      console.log('Tamaño real de la imagen:', imgWidth, 'x', imgHeight);
      
      // Calcular el tamaño de visualización manteniendo la proporción
      const maxWidth = 800; // Ancho máximo del editor
      const maxHeight = 600; // Alto máximo del editor
      
      let displayWidth = imgWidth;
      let displayHeight = imgHeight;
      
      // Redimensionar si es muy grande
      if (displayWidth > maxWidth || displayHeight > maxHeight) {
        const ratio = Math.min(maxWidth / displayWidth, maxHeight / displayHeight);
        displayWidth = Math.round(displayWidth * ratio);
        displayHeight = Math.round(displayHeight * ratio);
      }
      
      console.log('Tamaño de visualización:', displayWidth, 'x', displayHeight);
      
      // Guardar las proporciones para conversión de coordenadas
      window.editorScaleX = imgWidth / displayWidth;
      window.editorScaleY = imgHeight / displayHeight;
      
      // Ajustar el contenedor del editor
      editor.style.width = displayWidth + 'px';
      editor.style.height = displayHeight + 'px';
      editor.style.position = 'relative';
      editor.style.margin = '0 auto';
      editor.style.display = 'block';
      
      // Posicionar la imagen absolutamente dentro del editor
      fondoCertificado.style.position = 'absolute';
      fondoCertificado.style.top = '0';
      fondoCertificado.style.left = '0';
      fondoCertificado.style.width = '100%';
      fondoCertificado.style.height = '100%';
      fondoCertificado.style.objectFit = 'contain';
      fondoCertificado.style.margin = '0';
      
      console.log('Editor ajustado. Escala X:', window.editorScaleX, 'Escala Y:', window.editorScaleY);
    } else {
      // Si la imagen no está cargada, intentar de nuevo en 100ms
      setTimeout(ajustarEditorAlTamanioReal, 100);
    }
  }
}

function cargarDatosCertificado(idCurso) {
  fetch(`obtener_datos_certificado.php?idcurso=${idCurso}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        datosCertificado = data;
        llenarSelects(data);
      } else {
        alert('Error al cargar datos: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error al cargar datos del certificado');
    });
}

function llenarSelects(data) {
  console.log('Datos recibidos:', data);
  
  // Llenar select de alumnos
  const selectAlumno = document.getElementById('selectAlumno');
  if (selectAlumno) {
    selectAlumno.innerHTML = '<option value="">Seleccionar alumno</option>';
    if (data.inscritos && data.inscritos.length > 0) {
      data.inscritos.forEach(alumno => {
        const option = document.createElement('option');
        option.value = alumno.idcliente;
        option.textContent = `${alumno.nombre} ${alumno.apellido}`;
        selectAlumno.appendChild(option);
      });
      console.log('Alumnos cargados:', data.inscritos.length);
    } else {
      console.log('No hay inscritos aprobados');
      selectAlumno.innerHTML = '<option value="">No hay alumnos aprobados</option>';
    }
  }
  
  // Llenar select de instructores
  const selectInstructor = document.getElementById('selectInstructor');
  if (selectInstructor) {
    selectInstructor.innerHTML = '<option value="">Seleccionar instructor</option>';
    if (data.instructores && data.instructores.length > 0) {
      data.instructores.forEach(instructor => {
        const option = document.createElement('option');
        option.value = instructor.idinstructor;
        option.textContent = `${instructor.nombre} ${instructor.apellido}`;
        selectInstructor.appendChild(option);
      });
      console.log('Instructores cargados:', data.instructores.length);
    } else {
      console.log('No hay instructores');
      selectInstructor.innerHTML = '<option value="">No hay instructores</option>';
    }
  }
  
  // Llenar select de especialistas
  const selectEspecialista = document.getElementById('selectEspecialista');
  if (selectEspecialista) {
    selectEspecialista.innerHTML = '<option value="">Seleccionar especialista</option>';
    if (data.especialistas && data.especialistas.length > 0) {
      data.especialistas.forEach(especialista => {
        const option = document.createElement('option');
        option.value = especialista.idespecialista;
        option.textContent = `${especialista.nombre} ${especialista.apellido}`;
        selectEspecialista.appendChild(option);
      });
      console.log('Especialistas cargados:', data.especialistas.length);
    } else {
      console.log('No hay especialistas');
      selectEspecialista.innerHTML = '<option value="">No hay especialistas</option>';
    }
  }
}

function cargarConfiguracionGuardada(idCurso) {
  fetch(`cargar_config_certificado.php?idcurso=${idCurso}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.config) {
        configGuardada = data.config;
        restaurarCampos(data.config);
      }
    })
    .catch(error => {
      console.error('Error al cargar configuración:', error);
    });
}

function restaurarCampos(config) {
  if (!config || !config.campos) return;
  
  // Limpiar campos existentes
  const camposExistentes = document.querySelectorAll('.campo-editable');
  camposExistentes.forEach(campo => campo.remove());
  
  // Restaurar las proporciones del editor
  if (config.editorScaleX && config.editorScaleY) {
    window.editorScaleX = config.editorScaleX;
    window.editorScaleY = config.editorScaleY;
  }
  
  // Restaurar cada campo
  config.campos.forEach(campo => {
    const elemento = document.createElement('div');
    elemento.className = 'campo-editable';
    elemento.dataset.tipo = campo.tipo;
    elemento.style.position = 'absolute';
    elemento.style.left = campo.left + 'px';
    elemento.style.top = campo.top + 'px';
    elemento.style.width = campo.width + 'px';
    elemento.style.height = campo.height + 'px';
    elemento.style.fontSize = campo.fontSize || '16px';
    elemento.style.fontFamily = campo.fontFamily || 'Arial';
    elemento.style.color = campo.color || '#000000';
    elemento.style.textAlign = campo.textAlign || 'left';
    elemento.style.fontWeight = campo.fontWeight || 'normal';
    elemento.style.cursor = 'move';
    elemento.style.userSelect = 'none';
    elemento.style.zIndex = '1000';
    elemento.style.backgroundColor = 'rgba(255, 255, 0, 0.3)';
    elemento.style.border = '1px dashed #666';
    elemento.style.padding = '2px';
    
    // Asignar contenido según el tipo
    elemento.textContent = obtenerContenidoCampo(campo.tipo);
    
    // Hacer el campo arrastrable
    hacerArrastrable(elemento);
    
    // Agregar al editor
    document.getElementById('editor').appendChild(elemento);
  });
  
  console.log('Campos restaurados:', config.campos.length);
}

// Función para aplicar estilos al campo seleccionado
function aplicarEstilo(propiedad, valor) {
  if (!campoSeleccionado) {
    alert('Por favor selecciona un campo primero haciendo clic en él');
    return;
  }
  
  // Aplicar el estilo
  campoSeleccionado.style[propiedad] = valor;
  
  // Actualizar controles específicos
  if (propiedad === 'opacity') {
    document.getElementById('opacityValue').textContent = valor;
  }
  
  console.log(`Estilo aplicado: ${propiedad} = ${valor}`);
}

// Función para seleccionar un campo
function seleccionarCampo(campo) {
  // Deseleccionar campo anterior
  if (campoSeleccionado) {
    campoSeleccionado.style.border = '1px dashed #666';
    campoSeleccionado.style.backgroundColor = 'rgba(255, 255, 0, 0.3)';
  }
  
  // Seleccionar nuevo campo
  campoSeleccionado = campo;
  campo.style.border = '3px solid #007bff';
  campo.style.backgroundColor = 'rgba(0, 123, 255, 0.2)';
  campo.style.boxShadow = '0 0 10px rgba(0, 123, 255, 0.5)';
  
  // Cargar valores actuales en los controles
  cargarValoresEnControles(campo);
  
  // Mostrar información del campo seleccionado
  mostrarInfoCampoSeleccionado(campo);
  
  console.log('Campo seleccionado:', campo.dataset.tipo);
}

// Función para mostrar información del campo seleccionado
function mostrarInfoCampoSeleccionado(campo) {
  const tipo = campo.dataset.tipo;
  const nombreTipo = obtenerNombreTipo(tipo);
  
  // Crear o actualizar el indicador de campo seleccionado
  let indicador = document.getElementById('campoSeleccionadoInfo');
  if (!indicador) {
    indicador = document.createElement('div');
    indicador.id = 'campoSeleccionadoInfo';
    indicador.style.cssText = `
      position: fixed;
      top: 10px;
      left: 10px;
      background: #007bff;
      color: white;
      padding: 8px 12px;
      border-radius: 5px;
      font-size: 12px;
      z-index: 10000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    `;
    document.body.appendChild(indicador);
  }
  
  indicador.innerHTML = `<i class="fa fa-hand-pointer-o"></i> Campo seleccionado: <strong>${nombreTipo}</strong>`;
  
  // Ocultar el indicador después de 3 segundos
  setTimeout(() => {
    if (indicador) {
      indicador.style.opacity = '0.7';
    }
  }, 3000);
}

// Función para obtener el nombre legible del tipo de campo
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

// Función para cargar valores actuales en los controles
function cargarValoresEnControles(campo) {
  // Tipo de fuente
  const fontFamily = campo.style.fontFamily || 'Arial';
  document.getElementById('fontFamily').value = fontFamily;
  
  // Tamaño de fuente
  const fontSize = parseInt(campo.style.fontSize) || 16;
  document.getElementById('fontSize').value = fontSize;
  
  // Color de texto
  const color = campo.style.color || '#000000';
  document.getElementById('textColor').value = color;
  
  // Alineación
  const textAlign = campo.style.textAlign || 'left';
  document.getElementById('textAlign').value = textAlign;
  
  // Estilo de fuente
  const fontStyle = campo.style.fontStyle || 'normal';
  document.getElementById('fontStyle').value = fontStyle;
  
  // Peso de fuente
  const fontWeight = campo.style.fontWeight || 'normal';
  document.getElementById('fontWeight').value = fontWeight;
  
  // Decoración de texto
  const textDecoration = campo.style.textDecoration || 'none';
  document.getElementById('textDecoration').value = textDecoration;
  
  // Altura de línea
  const lineHeight = campo.style.lineHeight || '1.2';
  document.getElementById('lineHeight').value = parseFloat(lineHeight);
  
  // Espaciado entre letras
  const letterSpacing = campo.style.letterSpacing || '0px';
  document.getElementById('letterSpacing').value = parseInt(letterSpacing) || 0;
  
  // Rotación
  const transform = campo.style.transform || 'none';
  const rotationMatch = transform.match(/rotate\(([^)]+)deg\)/);
  const rotation = rotationMatch ? parseInt(rotationMatch[1]) : 0;
  document.getElementById('rotation').value = rotation;
  
  // Opacidad
  const opacity = campo.style.opacity || '1';
  document.getElementById('opacity').value = opacity;
  document.getElementById('opacityValue').textContent = opacity;
  
  // Color de fondo
  const backgroundColor = campo.style.backgroundColor || '#ffffff';
  document.getElementById('backgroundColor').value = backgroundColor;
  document.getElementById('useBackground').checked = backgroundColor !== 'rgba(0, 0, 0, 0)' && backgroundColor !== 'transparent';
  
  // Bordes
  const borderWidth = campo.style.borderWidth || '0px';
  document.getElementById('borderWidth').value = parseInt(borderWidth) || 0;
  
  const borderColor = campo.style.borderColor || '#000000';
  document.getElementById('borderColor').value = borderColor;
  
  const borderRadius = campo.style.borderRadius || '0px';
  document.getElementById('borderRadius').value = parseInt(borderRadius) || 0;
  
  // Sombras
  const textShadow = campo.style.textShadow || 'none';
  if (textShadow !== 'none') {
    const shadowMatch = textShadow.match(/(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+(.+)/);
    if (shadowMatch) {
      document.getElementById('shadowOffsetX').value = parseInt(shadowMatch[1]);
      document.getElementById('shadowOffsetY').value = parseInt(shadowMatch[2]);
      document.getElementById('shadowBlur').value = parseInt(shadowMatch[3]);
      document.getElementById('shadowColor').value = shadowMatch[4];
    }
  }
}

// Función para alternar el uso de fondo
function toggleBackground() {
  const useBackground = document.getElementById('useBackground').checked;
  const backgroundColor = document.getElementById('backgroundColor').value;
  
  if (useBackground) {
    aplicarEstilo('backgroundColor', backgroundColor);
  } else {
    aplicarEstilo('backgroundColor', 'transparent');
  }
}

// Función mejorada para hacer campos arrastrables
function hacerArrastrable(elemento) {
  let isDragging = false;
  let currentX;
  let currentY;
  let initialX;
  let initialY;
  let xOffset = 0;
  let yOffset = 0;

  elemento.addEventListener('mousedown', dragStart);
  elemento.addEventListener('click', function(e) {
    e.stopPropagation();
    seleccionarCampo(elemento);
  });

  function dragStart(e) {
    initialX = e.clientX - xOffset;
    initialY = e.clientY - yOffset;

    if (e.target === elemento) {
      isDragging = true;
    }
  }

  function dragEnd(e) {
    initialX = currentX;
    initialY = currentY;
    isDragging = false;
  }

  function drag(e) {
    if (isDragging) {
      e.preventDefault();
      
      currentX = e.clientX - initialX;
      currentY = e.clientY - initialY;

      xOffset = currentX;
      yOffset = currentY;

      setTranslate(currentX, currentY, elemento);
    }
  }

  function setTranslate(xPos, yPos, el) {
    el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
  }

  document.addEventListener('mousemove', drag);
  document.addEventListener('mouseup', dragEnd);
}

// Función para manejar la lógica mutuamente excluyente entre instructor y especialista
function manejarExclusionInstructorEspecialista(tipoSeleccionado) {
    const btnInstructor = document.getElementById('btnInstructor');
    const btnEspecialista = document.getElementById('btnEspecialista');
    const btnFirmaInstructor = document.getElementById('btnFirmaInstructor');
    const btnFirmaEspecialista = document.getElementById('btnFirmaEspecialista');
    const selectInstructor = document.getElementById('selectInstructor');
    const selectEspecialista = document.getElementById('selectEspecialista');
    
    if (tipoSeleccionado === 'instructor') {
        // Si se selecciona instructor, deshabilitar especialista
        btnEspecialista.disabled = true;
        btnFirmaEspecialista.disabled = true;
        selectEspecialista.disabled = true;
        selectEspecialista.value = '';
        
        // Habilitar instructor
        btnInstructor.disabled = false;
        btnFirmaInstructor.disabled = false;
        selectInstructor.disabled = false;
        
        // Remover campos de especialista existentes
        removerCamposPorTipo('especialista');
        removerCamposPorTipo('firma_especialista');
        
    } else if (tipoSeleccionado === 'especialista') {
        // Si se selecciona especialista, deshabilitar instructor
        btnInstructor.disabled = true;
        btnFirmaInstructor.disabled = true;
        selectInstructor.disabled = true;
        selectInstructor.value = '';
        
        // Habilitar especialista
        btnEspecialista.disabled = false;
        btnFirmaEspecialista.disabled = false;
        selectEspecialista.disabled = false;
        
        // Remover campos de instructor existentes
        removerCamposPorTipo('instructor');
        removerCamposPorTipo('firma_instructor');
    }
}

// Función para remover campos por tipo
function removerCamposPorTipo(tipo) {
    const campos = document.querySelectorAll('.campo-editable');
    campos.forEach(campo => {
        if (campo.dataset.tipo === tipo) {
            if (campo === campoSeleccionado) {
                campoSeleccionado = null;
                ocultarControles();
            }
            campo.remove();
        }
    });
    actualizarConfiguracion();
}

// Función para verificar si hay campos de instructor o especialista
function verificarCamposExistentes() {
    const campos = document.querySelectorAll('.campo-editable');
    let hayInstructor = false;
    let hayEspecialista = false;
    
    campos.forEach(campo => {
        if (campo.dataset.tipo === 'instructor' || campo.dataset.tipo === 'firma_instructor') {
            hayInstructor = true;
        }
        if (campo.dataset.tipo === 'especialista' || campo.dataset.tipo === 'firma_especialista') {
            hayEspecialista = true;
        }
    });
    
    // Aplicar lógica de exclusión
    if (hayInstructor) {
        manejarExclusionInstructorEspecialista('instructor');
    } else if (hayEspecialista) {
        manejarExclusionInstructorEspecialista('especialista');
    } else {
        // Si no hay ninguno, habilitar ambos
        const btnInstructor = document.getElementById('btnInstructor');
        const btnEspecialista = document.getElementById('btnEspecialista');
        const btnFirmaInstructor = document.getElementById('btnFirmaInstructor');
        const btnFirmaEspecialista = document.getElementById('btnFirmaEspecialista');
        const selectInstructor = document.getElementById('selectInstructor');
        const selectEspecialista = document.getElementById('selectEspecialista');
        
        if (btnInstructor && btnEspecialista) {
            btnInstructor.disabled = false;
            btnEspecialista.disabled = false;
            btnFirmaInstructor.disabled = false;
            btnFirmaEspecialista.disabled = false;
            selectInstructor.disabled = false;
            selectEspecialista.disabled = false;
        }
    }
}

// Modificar la función agregarCampo para incluir la lógica de exclusión
function agregarCampo(tipo) {
    // Verificar si ya existe un campo del mismo tipo
    const camposExistentes = document.querySelectorAll(`[data-tipo="${tipo}"]`);
    if (camposExistentes.length > 0) {
        if (confirm(`Ya existe un campo de ${tipo}. ¿Quieres agregar otro?`)) {
            // Continuar con la adición
        } else {
            return;
        }
    }
    
    // Aplicar lógica de exclusión para instructor/especialista
    if (tipo === 'instructor' || tipo === 'firma_instructor') {
        manejarExclusionInstructorEspecialista('instructor');
    } else if (tipo === 'especialista' || tipo === 'firma_especialista') {
        manejarExclusionInstructorEspecialista('especialista');
    }
    
    // Continuar con la función original de agregarCampo
    const campo = document.createElement('div');
    campo.className = 'campo-editable';
    campo.dataset.tipo = tipo;
    campo.style.cssText = `
        position: absolute;
        left: 50px;
        top: 50px;
        min-width: 100px;
        min-height: 30px;
        background: rgba(255, 255, 255, 0.9);
        border: 2px dashed #007bff;
        padding: 5px;
        cursor: grab;
        z-index: 10;
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #000;
        text-align: left;
        word-wrap: break-word;
        overflow: hidden;
    `;
    
    // Establecer contenido según el tipo
    switch (tipo) {
        case 'alumno':
            campo.textContent = 'NOMBRE DEL ALUMNO';
            break;
        case 'fecha':
            campo.textContent = 'FECHA';
            break;
        case 'instructor':
            campo.textContent = 'NOMBRE DEL INSTRUCTOR';
            break;
        case 'especialista':
            campo.textContent = 'NOMBRE DEL ESPECIALISTA';
            break;
        case 'firma_instructor':
            campo.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
            break;
        case 'firma_especialista':
            campo.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
            break;
        case 'qr':
            campo.innerHTML = '<img src="generar_qr.php?test=1" alt="QR" style="width: 120px; height: 120px; object-fit: contain;">';
            break;
        default:
            campo.textContent = 'CAMPO';
    }
    
    // Agregar event listeners
    agregarEventListeners(campo);
    
    // Agregar al editor
    editor.appendChild(campo);
    
    // Seleccionar el campo recién creado
    seleccionarCampo(campo);
    
    // Actualizar configuración
    actualizarConfiguracion();
}

// Modificar la función de cargar configuración para verificar campos existentes
function cargarConfiguracion() {
    fetch('cargar_config_certificado.php?idcurso=<?php echo $idcurso; ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.config) {
                const config = JSON.parse(data.config);
                if (config.campos) {
                    config.campos.forEach(campo => {
                        const elemento = document.createElement('div');
                        elemento.className = 'campo-editable';
                        elemento.dataset.tipo = campo.tipo;
                        elemento.style.cssText = `
                            position: absolute;
                            left: ${campo.left}px;
                            top: ${campo.top}px;
                            width: ${campo.width || 'auto'}px;
                            height: ${campo.height || 'auto'}px;
                            font-family: ${campo.fontFamily || 'Arial'};
                            font-size: ${campo.fontSize || '14px'};
                            color: ${campo.color || '#000000'};
                            text-align: ${campo.textAlign || 'left'};
                            font-weight: ${campo.fontWeight || 'normal'};
                            font-style: ${campo.fontStyle || 'normal'};
                            text-decoration: ${campo.textDecoration || 'none'};
                            line-height: ${campo.lineHeight || 'normal'};
                            letter-spacing: ${campo.letterSpacing || 'normal'};
                            transform: ${campo.rotation ? `rotate(${campo.rotation}deg)` : 'none'};
                            transform-origin: center center;
                            background-color: ${campo.backgroundColor || 'transparent'};
                            border: ${campo.borderWidth ? `${campo.borderWidth}px solid ${campo.borderColor || '#000000'}` : 'none'};
                            border-radius: ${campo.borderRadius || '0px'};
                            opacity: ${campo.opacity || '1'};
                            text-shadow: ${campo.shadowColor ? `${campo.shadowOffsetX || 0}px ${campo.shadowOffsetY || 0}px ${campo.shadowBlur || 0}px ${campo.shadowColor}` : 'none'};
                            padding: 5px;
                            cursor: grab;
                            z-index: 10;
                            word-wrap: break-word;
                            overflow: hidden;
                        `;
                        
                        // Establecer contenido según el tipo
                        switch (campo.tipo) {
                            case 'alumno':
                                elemento.textContent = 'NOMBRE DEL ALUMNO';
                                break;
                            case 'fecha':
                                elemento.textContent = 'FECHA';
                                break;
                            case 'instructor':
                                elemento.textContent = 'NOMBRE DEL INSTRUCTOR';
                                break;
                            case 'especialista':
                                elemento.textContent = 'NOMBRE DEL ESPECIALISTA';
                                break;
                            case 'firma_instructor':
                                elemento.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
                                break;
                            case 'firma_especialista':
                                elemento.innerHTML = '<img src="../assets/img/qr_placeholder.png" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">';
                                break;
                            case 'qr':
                                elemento.innerHTML = '<img src="generar_qr.php?test=1" alt="QR" style="width: 120px; height: 120px; object-fit: contain;">';
                                break;
                            default:
                                elemento.textContent = campo.texto || 'CAMPO';
                        }
                        
                        agregarEventListeners(elemento);
                        editor.appendChild(elemento);
                    });
                    
                    // Verificar campos existentes después de cargar
                    setTimeout(verificarCamposExistentes, 100);
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar configuración:', error);
        });
}

// Llamar a verificarCamposExistentes al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(verificarCamposExistentes, 500);
});

function cambiarAlumno() {
  const selectAlumno = document.getElementById('selectAlumno');
  const camposAlumno = document.querySelectorAll('[data-tipo="alumno"]');
  
  if (selectAlumno.value) {
    const alumnoSeleccionado = selectAlumno.options[selectAlumno.selectedIndex].text;
    camposAlumno.forEach(campo => {
      campo.textContent = alumnoSeleccionado;
    });
  } else {
    camposAlumno.forEach(campo => {
      campo.textContent = 'Nombre del Alumno';
    });
  }
}

function actualizarFecha() {
  const fechaInput = document.getElementById('fechaEmision');
  const camposFecha = document.querySelectorAll('[data-tipo="fecha"]');
  
  if (fechaInput.value) {
    const fecha = new Date(fechaInput.value).toLocaleDateString('es-ES');
    camposFecha.forEach(campo => {
      campo.textContent = fecha;
    });
  } else {
    camposFecha.forEach(campo => {
      campo.textContent = 'Fecha de Emisión';
    });
  }
}

function cambiarInstructor() {
  const selectInstructor = document.getElementById('selectInstructor');
  const camposInstructor = document.querySelectorAll('[data-tipo="instructor"]');
  
  if (selectInstructor.value) {
    const instructorSeleccionado = selectInstructor.options[selectInstructor.selectedIndex].text;
    camposInstructor.forEach(campo => {
      campo.textContent = instructorSeleccionado;
    });
  } else {
    camposInstructor.forEach(campo => {
      campo.textContent = 'Nombre del Instructor';
    });
  }
}

function cambiarEspecialista() {
  const selectEspecialista = document.getElementById('selectEspecialista');
  const camposEspecialista = document.querySelectorAll('[data-tipo="especialista"]');
  
  if (selectEspecialista.value) {
    const especialistaSeleccionado = selectEspecialista.options[selectEspecialista.selectedIndex].text;
    camposEspecialista.forEach(campo => {
      campo.textContent = especialistaSeleccionado;
    });
  } else {
    camposEspecialista.forEach(campo => {
      campo.textContent = 'Nombre del Especialista';
    });
  }
}

function ajustarTamanioFirmas() {
  const ancho = document.getElementById('anchoFirmas').value;
  const alto = document.getElementById('altoFirmas').value;
  
  const firmasInstructor = document.querySelectorAll('[data-tipo="firma_instructor"]');
  const firmasEspecialista = document.querySelectorAll('[data-tipo="firma_especialista"]');
  
  firmasInstructor.forEach(campo => {
    campo.style.width = ancho + 'px';
    campo.style.height = alto + 'px';
  });
  
  firmasEspecialista.forEach(campo => {
    campo.style.width = ancho + 'px';
    campo.style.height = alto + 'px';
  });
}

function guardarConfiguracion() {
  const campos = document.querySelectorAll('.campo-editable');
  const configuracion = [];
  
  campos.forEach(campo => {
    // Usar las posiciones CSS directamente
    const left = parseInt(campo.style.left) || 0;
    const top = parseInt(campo.style.top) || 0;
    const width = parseInt(campo.style.width) || campo.offsetWidth;
    const height = parseInt(campo.style.height) || campo.offsetHeight;
    
    // Extraer rotación del transform
    let rotation = 0;
    const transform = campo.style.transform;
    if (transform && transform.includes('rotate')) {
      const match = transform.match(/rotate\(([^)]+)deg\)/);
      if (match) {
        rotation = parseFloat(match[1]);
      }
    }
    
    const config = {
      tipo: campo.dataset.tipo,
      left: left,
      top: top,
      width: width,
      height: height,
      fontSize: parseInt(campo.style.fontSize) || 16,
      fontFamily: campo.style.fontFamily || 'Arial',
      color: campo.style.color || '#000000',
      textAlign: campo.style.textAlign || 'left',
      fontWeight: campo.style.fontWeight || 'normal',
      fontStyle: campo.style.fontStyle || 'normal',
      textDecoration: campo.style.textDecoration || 'none',
      lineHeight: parseFloat(campo.style.lineHeight) || 1.2,
      letterSpacing: parseInt(campo.style.letterSpacing) || 0,
      rotation: rotation,
      opacity: parseFloat(campo.style.opacity) || 1,
      backgroundColor: campo.style.backgroundColor || 'transparent',
      borderWidth: parseInt(campo.style.borderWidth) || 0,
      borderColor: campo.style.borderColor || '#000000',
      borderRadius: parseInt(campo.style.borderRadius) || 0,
      shadowColor: '#000000',
      shadowBlur: 0,
      shadowOffsetX: 0,
      shadowOffsetY: 0,
      texto: obtenerContenidoCampo(campo)
    };
    
    // Extraer sombra de texto si existe
    const textShadow = campo.style.textShadow;
    if (textShadow && textShadow !== 'none') {
      const shadowMatch = textShadow.match(/(\d+)px\s+(\d+)px\s+(\d+)px\s+(#[0-9a-fA-F]{6})/);
      if (shadowMatch) {
        config.shadowOffsetX = parseInt(shadowMatch[1]);
        config.shadowOffsetY = parseInt(shadowMatch[2]);
        config.shadowBlur = parseInt(shadowMatch[3]);
        config.shadowColor = shadowMatch[4];
      }
    }
    
    configuracion.push(config);
  });
  
  // Agregar información del editor
  const configuracionCompleta = {
    campos: configuracion,
    editorScaleX: window.editorScaleX || 1,
    editorScaleY: window.editorScaleY || 1,
    imagenOriginal: {
      width: document.getElementById('fondoCertificado')?.naturalWidth || 800,
      height: document.getElementById('fondoCertificado')?.naturalHeight || 600
    },
    editorDisplay: {
      width: document.getElementById('editor')?.offsetWidth || 800,
      height: document.getElementById('editor')?.offsetHeight || 600
    }
  };
  
  console.log('Configuración a guardar:', configuracionCompleta);
  
  const idCurso = <?php echo (int)$idcurso; ?>;
  
  fetch('guardar_config_certificado.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      idcurso: idCurso,
      configuracion: configuracionCompleta
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('✅ Configuración guardada exitosamente');
      configGuardada = configuracionCompleta;
    } else {
      alert('❌ Error al guardar: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('❌ Error al guardar la configuración');
  });
}

function previsualizarCertificado() {
  const selectAlumno = document.getElementById('selectAlumno');
  const selectInstructor = document.getElementById('selectInstructor');
  const selectEspecialista = document.getElementById('selectEspecialista');
  const fechaInput = document.getElementById('fechaEmision');
  
  if (!selectAlumno.value) {
    alert('Por favor selecciona un alumno para previsualizar');
    return;
  }
  
  // Obtener datos seleccionados
  const alumnoId = selectAlumno.value;
  const instructorId = selectInstructor.value;
  const especialistaId = selectEspecialista.value;
  const fecha = fechaInput.value;
  
  // Crear ventana de previsualización
  const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
  previewWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Previsualización del Certificado</title>
      <style>
        body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
        .certificado-preview { 
          width: 100%; 
          max-width: 800px; 
          margin: 0 auto; 
          border: 2px solid #333; 
          position: relative;
          background: white;
        }
        .campo-preview {
          position: absolute;
          padding: 5px;
          border: 1px dashed #007bff;
          background: rgba(0, 123, 255, 0.1);
          font-size: 14px;
        }
        .firma-preview img {
          max-width: 120px;
          max-height: 60px;
        }
        .qr-preview img {
          width: 120px;
          height: 120px;
        }
        .loading {
          text-align: center;
          padding: 50px;
          font-size: 18px;
        }
      </style>
    </head>
    <body>
      <div class="loading">Cargando previsualización...</div>
    </body>
    </html>
  `);
  
  // Cargar datos y generar previsualización
  fetch(`previsualizar_certificado.php?idcurso=<?php echo (int)$idcurso; ?>&idalumno=${alumnoId}&idinstructor=${instructorId}&idespecialista=${especialistaId}&fecha=${fecha}`)
    .then(response => response.text())
    .then(html => {
      previewWindow.document.body.innerHTML = html;
    })
    .catch(error => {
      previewWindow.document.body.innerHTML = `<div style="color: red; padding: 20px;">Error al cargar la previsualización: ${error.message}</div>`;
    });
}

function generarCertificado() {
  const selectAlumno = document.getElementById('selectAlumno');
  const selectInstructor = document.getElementById('selectInstructor');
  const selectEspecialista = document.getElementById('selectEspecialista');
  const fechaInput = document.getElementById('fechaEmision');
  
  if (!selectAlumno.value) {
    alert('Por favor selecciona un alumno para generar el certificado');
    return;
  }
  
  // Obtener datos seleccionados
  const alumnoId = selectAlumno.value;
  const instructorId = selectInstructor.value;
  const especialistaId = selectEspecialista.value;
  const fecha = fechaInput.value;
  
  // Mostrar indicador de carga
  const btnGenerar = event.target;
  const originalText = btnGenerar.innerHTML;
  btnGenerar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando...';
  btnGenerar.disabled = true;
  
  // Generar certificado
  fetch(`generar_certificado_final.php?idcurso=<?php echo (int)$idcurso; ?>&idalumno=${alumnoId}&idinstructor=${instructorId}&idespecialista=${especialistaId}&fecha=${fecha}`)
    .then(response => {
      if (response.ok) {
        return response.blob();
      }
      throw new Error('Error al generar el certificado');
    })
    .then(blob => {
      // Crear enlace de descarga
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `certificado_curso_<?php echo (int)$idcurso; ?>_alumno_${alumnoId}.pdf`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
      
      alert('Certificado generado correctamente');
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error al generar el certificado: ' + error.message);
    })
    .finally(() => {
      // Restaurar botón
      btnGenerar.innerHTML = originalText;
      btnGenerar.disabled = false;
    });
}

// --- HANDLES DE REDIMENSIONAMIENTO TIPO CANVA ---

function agregarHandlesRedimension(campo) {
  // Elimina handles previos
  let handles = campo.querySelectorAll('.resize-handle');
  handles.forEach(h => h.remove());

  const posiciones = [
    {dir: 'nw', cursor: 'nwse-resize'},
    {dir: 'ne', cursor: 'nesw-resize'},
    {dir: 'sw', cursor: 'nesw-resize'},
    {dir: 'se', cursor: 'nwse-resize'},
    {dir: 'n', cursor: 'ns-resize'},
    {dir: 's', cursor: 'ns-resize'},
    {dir: 'e', cursor: 'ew-resize'},
    {dir: 'w', cursor: 'ew-resize'}
  ];

  posiciones.forEach(pos => {
    const handle = document.createElement('div');
    handle.className = 'resize-handle resize-' + pos.dir;
    handle.style.position = 'absolute';
    handle.style.width = '12px';
    handle.style.height = '12px';
    handle.style.background = '#fff';
    handle.style.border = '2px solid #007bff';
    handle.style.borderRadius = '50%';
    handle.style.zIndex = '3000';
    handle.style.cursor = pos.cursor;
    handle.style.boxShadow = '0 0 2px #007bff';
    handle.style.userSelect = 'none';
    switch(pos.dir) {
      case 'nw': handle.style.left = '-7px'; handle.style.top = '-7px'; break;
      case 'ne': handle.style.right = '-7px'; handle.style.top = '-7px'; break;
      case 'sw': handle.style.left = '-7px'; handle.style.bottom = '-7px'; break;
      case 'se': handle.style.right = '-7px'; handle.style.bottom = '-7px'; break;
      case 'n': handle.style.left = '50%'; handle.style.top = '-7px'; handle.style.transform = 'translateX(-50%)'; break;
      case 's': handle.style.left = '50%'; handle.style.bottom = '-7px'; handle.style.transform = 'translateX(-50%)'; break;
      case 'e': handle.style.right = '-7px'; handle.style.top = '50%'; handle.style.transform = 'translateY(-50%)'; break;
      case 'w': handle.style.left = '-7px'; handle.style.top = '50%'; handle.style.transform = 'translateY(-50%)'; break;
    }
    handle.addEventListener('mousedown', function(e) {
      e.stopPropagation();
      iniciarRedimension(e, campo, pos.dir);
    });
    campo.appendChild(handle);
  });
}

function iniciarRedimension(e, campo, direccion) {
  e.preventDefault();
  const startX = e.clientX;
  const startY = e.clientY;
  const startWidth = campo.offsetWidth;
  const startHeight = campo.offsetHeight;
  const startLeft = parseInt(campo.style.left);
  const startTop = parseInt(campo.style.top);

  function mover(ev) {
    let dx = ev.clientX - startX;
    let dy = ev.clientY - startY;
    let newWidth = startWidth;
    let newHeight = startHeight;
    let newLeft = startLeft;
    let newTop = startTop;
    if (direccion.includes('e')) newWidth = Math.max(20, startWidth + dx);
    if (direccion.includes('s')) newHeight = Math.max(20, startHeight + dy);
    if (direccion.includes('w')) {
      newWidth = Math.max(20, startWidth - dx);
      newLeft = startLeft + dx;
    }
    if (direccion.includes('n')) {
      newHeight = Math.max(20, startHeight - dy);
      newTop = startTop + dy;
    }
    campo.style.width = newWidth + 'px';
    campo.style.height = newHeight + 'px';
    campo.style.left = newLeft + 'px';
    campo.style.top = newTop + 'px';
  }
  function soltar() {
    document.removeEventListener('mousemove', mover);
    document.removeEventListener('mouseup', soltar);
  }
  document.addEventListener('mousemove', mover);
  document.addEventListener('mouseup', soltar);
}

// Llama a agregarHandlesRedimension cada vez que se selecciona un campo
const seleccionarCampoOriginalCanva = seleccionarCampo;
seleccionarCampo = function(campo) {
  seleccionarCampoOriginalCanva(campo);
  agregarHandlesRedimension(campo);
};
// --- FIN HANDLES ---

// Función para eliminar el campo seleccionado
function eliminarCampoSeleccionado() {
    if (campoSeleccionado) {
        if (confirm('¿Estás seguro de que quieres eliminar este campo?')) {
            campoSeleccionado.remove();
            campoSeleccionado = null;
            ocultarControles();
            actualizarConfiguracion();
        }
    } else {
        alert('Por favor selecciona un campo para eliminar');
    }
}

// Función para duplicar el campo seleccionado
function duplicarCampoSeleccionado() {
    if (campoSeleccionado) {
        const clon = campoSeleccionado.cloneNode(true);
        clon.style.left = (parseInt(campoSeleccionado.style.left) + 20) + 'px';
        clon.style.top = (parseInt(campoSeleccionado.style.top) + 20) + 'px';
        clon.id = 'campo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        // Remover clases de selección
        clon.classList.remove('campo-seleccionado');
        clon.querySelectorAll('.control-flotante, .resize-handle').forEach(el => el.remove());
        
        // Agregar event listeners
        agregarEventListeners(clon);
        
        editor.appendChild(clon);
        actualizarConfiguracion();
    } else {
        alert('Por favor selecciona un campo para duplicar');
    }
}

// Función para traer al frente
function traerAlFrente() {
    if (campoSeleccionado) {
        editor.appendChild(campoSeleccionado);
        actualizarConfiguracion();
    } else {
        alert('Por favor selecciona un campo');
    }
}

// Función para enviar atrás
function enviarAtras() {
    if (campoSeleccionado) {
        const primerHijo = editor.firstElementChild;
        if (primerHijo && primerHijo !== campoSeleccionado) {
            editor.insertBefore(campoSeleccionado, primerHijo);
            actualizarConfiguracion();
        }
    } else {
        alert('Por favor selecciona un campo');
    }
}

// Mejorar la función de atajos de teclado
document.addEventListener('keydown', function(e) {
    if (campoSeleccionado) {
        switch(e.key) {
            case 'Delete':
            case 'Backspace':
                e.preventDefault();
                eliminarCampoSeleccionado();
                break;
            case 'd':
            case 'D':
                if (e.ctrlKey) {
                    e.preventDefault();
                    duplicarCampoSeleccionado();
                }
                break;
            case 'ArrowUp':
                if (e.ctrlKey) {
                    e.preventDefault();
                    traerAlFrente();
                }
                break;
            case 'ArrowDown':
                if (e.ctrlKey) {
                    e.preventDefault();
                    enviarAtras();
                }
                break;
        }
    }
    
    if (e.key === 'Escape') {
        deseleccionarCampo();
    }
});

// Mejorar la función de mostrar controles flotantes
function mostrarControlesFlotantes(campo) {
    // Remover controles existentes
    ocultarControles();
    
    // Crear controles flotantes
    const controles = document.createElement('div');
    controles.className = 'control-flotante';
    controles.style.cssText = `
        position: absolute;
        top: -40px;
        right: 0;
        background: #007bff;
        color: white;
        padding: 5px;
        border-radius: 3px;
        font-size: 12px;
        z-index: 1000;
        display: flex;
        gap: 5px;
    `;
    
    // Botón eliminar
    const btnEliminar = document.createElement('button');
    btnEliminar.innerHTML = '<i class="fa fa-trash"></i>';
    btnEliminar.style.cssText = 'background: #dc3545; border: none; color: white; padding: 2px 5px; border-radius: 2px; cursor: pointer;';
    btnEliminar.onclick = eliminarCampoSeleccionado;
    controles.appendChild(btnEliminar);
    
    // Botón duplicar
    const btnDuplicar = document.createElement('button');
    btnDuplicar.innerHTML = '<i class="fa fa-copy"></i>';
    btnDuplicar.style.cssText = 'background: #6c757d; border: none; color: white; padding: 2px 5px; border-radius: 2px; cursor: pointer;';
    btnDuplicar.onclick = duplicarCampoSeleccionado;
    controles.appendChild(btnDuplicar);
    
    // Botón traer al frente
    const btnFrente = document.createElement('button');
    btnFrente.innerHTML = '<i class="fa fa-level-up"></i>';
    btnFrente.style.cssText = 'background: #17a2b8; border: none; color: white; padding: 2px 5px; border-radius: 2px; cursor: pointer;';
    btnFrente.onclick = traerAlFrente;
    controles.appendChild(btnFrente);
    
    // Botón enviar atrás
    const btnAtras = document.createElement('button');
    btnAtras.innerHTML = '<i class="fa fa-level-down"></i>';
    btnAtras.style.cssText = 'background: #17a2b8; border: none; color: white; padding: 2px 5px; border-radius: 2px; cursor: pointer;';
    btnAtras.onclick = enviarAtras;
    controles.appendChild(btnAtras);
    
    campo.appendChild(controles);
    
    // Agregar manijas de redimensionamiento
    agregarManijasRedimensionamiento(campo);
}

// Función para agregar manijas de redimensionamiento
function agregarManijasRedimensionamiento(campo) {
    const posiciones = [
        { top: '0%', left: '0%', cursor: 'nw-resize' },
        { top: '0%', left: '50%', cursor: 'n-resize' },
        { top: '0%', left: '100%', cursor: 'ne-resize' },
        { top: '50%', left: '100%', cursor: 'e-resize' },
        { top: '100%', left: '100%', cursor: 'se-resize' },
        { top: '100%', left: '50%', cursor: 's-resize' },
        { top: '100%', left: '0%', cursor: 'sw-resize' },
        { top: '50%', left: '0%', cursor: 'w-resize' }
    ];
    
    posiciones.forEach((pos, index) => {
        const handle = document.createElement('div');
        handle.className = 'resize-handle';
        handle.style.cssText = `
            position: absolute;
            width: 8px;
            height: 8px;
            background: #007bff;
            border: 1px solid white;
            border-radius: 50%;
            cursor: ${pos.cursor};
            top: ${pos.top};
            left: ${pos.left};
            transform: translate(-50%, -50%);
            z-index: 1001;
        `;
        
        handle.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            iniciarRedimensionamiento(campo, index, e);
        });
        
        campo.appendChild(handle);
    });
}

// Función para iniciar redimensionamiento
function iniciarRedimensionamiento(campo, handleIndex, e) {
    const startX = e.clientX;
    const startY = e.clientY;
    const startWidth = campo.offsetWidth;
    const startHeight = campo.offsetHeight;
    const startLeft = parseInt(campo.style.left) || 0;
    const startTop = parseInt(campo.style.top) || 0;
    
    function onMouseMove(e) {
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        
        let newWidth = startWidth;
        let newHeight = startHeight;
        let newLeft = startLeft;
        let newTop = startTop;
        
        // Ajustar según la posición de la manija
        switch(handleIndex) {
            case 0: // Esquina superior izquierda
                newWidth = startWidth - deltaX;
                newHeight = startHeight - deltaY;
                newLeft = startLeft + deltaX;
                newTop = startTop + deltaY;
                break;
            case 1: // Borde superior
                newHeight = startHeight - deltaY;
                newTop = startTop + deltaY;
                break;
            case 2: // Esquina superior derecha
                newWidth = startWidth + deltaX;
                newHeight = startHeight - deltaY;
                newTop = startTop + deltaY;
                break;
            case 3: // Borde derecho
                newWidth = startWidth + deltaX;
                break;
            case 4: // Esquina inferior derecha
                newWidth = startWidth + deltaX;
                newHeight = startHeight + deltaY;
                break;
            case 5: // Borde inferior
                newHeight = startHeight + deltaY;
                break;
            case 6: // Esquina inferior izquierda
                newWidth = startWidth - deltaX;
                newHeight = startHeight + deltaY;
                newLeft = startLeft + deltaX;
                break;
            case 7: // Borde izquierdo
                newWidth = startWidth - deltaX;
                newLeft = startLeft + deltaX;
                break;
        }
        
        // Aplicar límites mínimos
        newWidth = Math.max(50, newWidth);
        newHeight = Math.max(30, newHeight);
        
        campo.style.width = newWidth + 'px';
        campo.style.height = newHeight + 'px';
        campo.style.left = newLeft + 'px';
        campo.style.top = newTop + 'px';
    }
    
    function onMouseUp() {
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
        actualizarConfiguracion();
    }
    
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
}

// Mejorar la función de actualizar opacidad
document.getElementById('opacity').addEventListener('input', function() {
    const valor = this.value;
    document.getElementById('opacityValue').textContent = valor;
    aplicarEstilo('opacity', valor);
});

// Función para aplicar sombra de texto
function aplicarSombraTexto() {
    const color = document.getElementById('shadowColor').value;
    const blur = document.getElementById('shadowBlur').value;
    const offsetX = document.getElementById('shadowOffsetX').value;
    const offsetY = document.getElementById('shadowOffsetY').value;
    
    const sombra = `${offsetX}px ${offsetY}px ${blur}px ${color}`;
    aplicarEstilo('textShadow', sombra);
}

// Agregar event listeners para sombra
document.getElementById('shadowColor').addEventListener('change', aplicarSombraTexto);
document.getElementById('shadowBlur').addEventListener('change', aplicarSombraTexto);
document.getElementById('shadowOffsetX').addEventListener('change', aplicarSombraTexto);
document.getElementById('shadowOffsetY').addEventListener('change', aplicarSombraTexto);

// Función para aplicar bordes
function aplicarBordes() {
    const width = document.getElementById('borderWidth').value;
    const color = document.getElementById('borderColor').value;
    const radius = document.getElementById('borderRadius').value;
    
    aplicarEstilo('borderWidth', width + 'px');
    aplicarEstilo('borderColor', color);
    aplicarEstilo('borderStyle', 'solid');
    aplicarEstilo('borderRadius', radius + 'px');
}

// Agregar event listeners para bordes
document.getElementById('borderWidth').addEventListener('change', aplicarBordes);
document.getElementById('borderColor').addEventListener('change', aplicarBordes);
document.getElementById('borderRadius').addEventListener('change', aplicarBordes);

// Mejorar la función de toggle background
function toggleBackground() {
    const checkbox = document.getElementById('useBackground');
    const colorInput = document.getElementById('backgroundColor');
    
    if (checkbox.checked) {
        aplicarEstilo('backgroundColor', colorInput.value);
        aplicarEstilo('padding', '5px');
    } else {
        aplicarEstilo('backgroundColor', 'transparent');
        aplicarEstilo('padding', '0px');
    }
}

// Función para deseleccionar campo
function deseleccionarCampo() {
    if (campoSeleccionado) {
        campoSeleccionado.classList.remove('campo-seleccionado');
        campoSeleccionado = null;
        ocultarControles();
    }
}

// Mejorar la función de click fuera para deseleccionar
editor.addEventListener('click', function(e) {
    if (e.target === editor || e.target.id === 'fondoCertificado') {
        deseleccionarCampo();
    }
});

// Función para mejorar la experiencia de arrastre
function mejorarArrastre(campo) {
    let isDragging = false;
    let startX, startY, startLeft, startTop;
    
    campo.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('resize-handle') || 
            e.target.classList.contains('control-flotante') ||
            e.target.closest('.control-flotante')) {
            return;
        }
        
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        startLeft = parseInt(campo.style.left) || 0;
        startTop = parseInt(campo.style.top) || 0;
        
        campo.style.cursor = 'grabbing';
        e.preventDefault();
    });
    
    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        
        campo.style.left = (startLeft + deltaX) + 'px';
        campo.style.top = (startTop + deltaY) + 'px';
    });
    
    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            campo.style.cursor = 'grab';
            actualizarConfiguracion();
        }
    });
}

// Aplicar mejoras de arrastre a campos existentes
function aplicarMejorasArrastre() {
    document.querySelectorAll('.campo-editable').forEach(campo => {
        campo.style.cursor = 'grab';
        mejorarArrastre(campo);
    });
}

// Llamar después de cargar la configuración
setTimeout(aplicarMejorasArrastre, 1000);

// Función para agregar event listeners a un campo
function agregarEventListeners(campo) {
    campo.addEventListener('click', function(e) {
        e.stopPropagation();
        seleccionarCampo(campo);
    });
    
    campo.addEventListener('dblclick', function(e) {
        e.stopPropagation();
        // Permitir edición de texto si es un campo de texto
        if (this.dataset.tipo !== 'firma_instructor' && 
            this.dataset.tipo !== 'firma_especialista' && 
            this.dataset.tipo !== 'qr') {
            this.contentEditable = true;
            this.focus();
        }
    });
    
    campo.addEventListener('blur', function() {
        this.contentEditable = false;
        actualizarConfiguracion();
    });
    
    campo.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.blur();
        }
    });
}

// Función para actualizar la configuración
function actualizarConfiguracion() {
    const campos = document.querySelectorAll('.campo-editable');
    const configuracion = {
        campos: []
    };
    
    campos.forEach(campo => {
        const campoConfig = {
            tipo: campo.dataset.tipo,
            left: parseInt(campo.style.left) || 0,
            top: parseInt(campo.style.top) || 0,
            width: parseInt(campo.style.width) || campo.offsetWidth,
            height: parseInt(campo.style.height) || campo.offsetHeight,
            fontFamily: campo.style.fontFamily || 'Arial',
            fontSize: parseInt(campo.style.fontSize) || 14,
            color: campo.style.color || '#000000',
            textAlign: campo.style.textAlign || 'left',
            fontWeight: campo.style.fontWeight || 'normal',
            fontStyle: campo.style.fontStyle || 'normal',
            textDecoration: campo.style.textDecoration || 'none',
            lineHeight: campo.style.lineHeight || 'normal',
            letterSpacing: campo.style.letterSpacing || 'normal',
            backgroundColor: campo.style.backgroundColor || 'transparent',
            borderWidth: parseInt(campo.style.borderWidth) || 0,
            borderColor: campo.style.borderColor || '#000000',
            borderRadius: parseInt(campo.style.borderRadius) || 0,
            opacity: parseFloat(campo.style.opacity) || 1,
            rotation: 0, // Extraer rotación del transform
            shadowColor: '#000000',
            shadowBlur: 0,
            shadowOffsetX: 0,
            shadowOffsetY: 0,
            texto: obtenerContenidoCampo(campo)
        };
        
        // Extraer rotación del transform
        const transform = campo.style.transform;
        if (transform && transform.includes('rotate')) {
            const match = transform.match(/rotate\(([^)]+)deg\)/);
            if (match) {
                campoConfig.rotation = parseFloat(match[1]);
            }
        }
        
        configuracion.campos.push(campoConfig);
    });
    
    // Guardar en localStorage para respaldo
    localStorage.setItem('configuracion_certificado_temp', JSON.stringify(configuracion));
}

// Función para obtener el contenido de un campo
function obtenerContenidoCampo(campo) {
    const tipo = campo.dataset.tipo;
    
    switch (tipo) {
        case 'alumno':
            return campo.textContent || 'NOMBRE DEL ALUMNO';
        case 'fecha':
            return campo.textContent || 'FECHA';
        case 'instructor':
            return campo.textContent || 'NOMBRE DEL INSTRUCTOR';
        case 'especialista':
            return campo.textContent || 'NOMBRE DEL ESPECIALISTA';
        case 'firma_instructor':
            return 'Firma Instructor';
        case 'firma_especialista':
            return 'Firma Especialista';
        case 'qr':
            return 'Código QR';
        default:
            return campo.textContent || 'CAMPO';
    }
}

// Función para seleccionar un campo
function seleccionarCampo(campo) {
    // Deseleccionar campo anterior
    if (campoSeleccionado) {
        campoSeleccionado.classList.remove('campo-seleccionado');
    }
    
    // Seleccionar nuevo campo
    campoSeleccionado = campo;
    campo.classList.add('campo-seleccionado');
    
    // Mostrar controles flotantes
    mostrarControlesFlotantes(campo);
    
    // Actualizar controles de estilo
    actualizarControlesEstilo(campo);
}

// Función para actualizar controles de estilo
function actualizarControlesEstilo(campo) {
    // Actualizar controles con los valores del campo seleccionado
    document.getElementById('fontFamily').value = campo.style.fontFamily || 'Arial';
    document.getElementById('fontSize').value = parseInt(campo.style.fontSize) || 14;
    document.getElementById('textColor').value = campo.style.color || '#000000';
    document.getElementById('textAlign').value = campo.style.textAlign || 'left';
    document.getElementById('fontStyle').value = campo.style.fontStyle || 'normal';
    document.getElementById('fontWeight').value = campo.style.fontWeight || 'normal';
    document.getElementById('textDecoration').value = campo.style.textDecoration || 'none';
    document.getElementById('lineHeight').value = parseFloat(campo.style.lineHeight) || 1.2;
    document.getElementById('letterSpacing').value = parseInt(campo.style.letterSpacing) || 0;
    document.getElementById('opacity').value = parseFloat(campo.style.opacity) || 1;
    document.getElementById('backgroundColor').value = campo.style.backgroundColor || '#ffffff';
    document.getElementById('borderWidth').value = parseInt(campo.style.borderWidth) || 0;
    document.getElementById('borderColor').value = campo.style.borderColor || '#000000';
    document.getElementById('borderRadius').value = parseInt(campo.style.borderRadius) || 0;
    
    // Extraer rotación
    const transform = campo.style.transform;
    if (transform && transform.includes('rotate')) {
        const match = transform.match(/rotate\(([^)]+)deg\)/);
        if (match) {
            document.getElementById('rotation').value = parseFloat(match[1]);
        } else {
            document.getElementById('rotation').value = 0;
        }
    } else {
        document.getElementById('rotation').value = 0;
    }
    
    // Actualizar valor de opacidad
    document.getElementById('opacityValue').textContent = document.getElementById('opacity').value;
}

// Función para aplicar estilo al campo seleccionado
function aplicarEstilo(propiedad, valor) {
    if (campoSeleccionado) {
        campoSeleccionado.style[propiedad] = valor;
        actualizarConfiguracion();
    }
}

// Función para ocultar controles
function ocultarControles() {
    const controles = document.querySelectorAll('.control-flotante, .resize-handle');
    controles.forEach(control => control.remove());
}

// Función para restaurar campos desde configuración
function restaurarCampos(config) {
    if (config.campos) {
        config.campos.forEach(campo => {
            const elemento = document.createElement('div');
            elemento.className = 'campo-editable';
            elemento.dataset.tipo = campo.tipo;
            elemento.style.cssText = `
                position: absolute;
                left: ${campo.left}px;
                top: ${campo.top}px;
                width: ${campo.width || 'auto'}px;
                height: ${campo.height || 'auto'}px;
                font-family: ${campo.fontFamily || 'Arial'};
                font-size: ${campo.fontSize || '14px'};
                color: ${campo.color || '#000000'};
                text-align: ${campo.textAlign || 'left'};
                font-weight: ${campo.fontWeight || 'normal'};
                font-style: ${campo.fontStyle || 'normal'};
                text-decoration: ${campo.textDecoration || 'none'};
                line-height: ${campo.lineHeight || 'normal'};
                letter-spacing: ${campo.letterSpacing || 'normal'};
                transform: ${campo.rotation ? `rotate(${campo.rotation}deg)` : 'none'};
                transform-origin: center center;
                background-color: ${campo.backgroundColor || 'transparent'};
                border: ${campo.borderWidth ? `${campo.borderWidth}px solid ${campo.borderColor || '#000000'}` : 'none'};
                border-radius: ${campo.borderRadius || '0px'};
                opacity: ${campo.opacity || '1'};
                text-shadow: ${campo.shadowColor ? `${campo.shadowOffsetX || 0}px ${campo.shadowOffsetY || 0}px ${campo.shadowBlur || 0}px ${campo.shadowColor}` : 'none'};
                padding: 5px;
                cursor: grab;
                z-index: 10;
                word-wrap: break-word;
                overflow: hidden;
            `;
            
            // Establecer contenido según el tipo
            elemento.textContent = obtenerContenidoCampo({ dataset: { tipo: campo.tipo } });
            
            agregarEventListeners(elemento);
            editor.appendChild(elemento);
        });
        
        // Verificar campos existentes después de cargar
        setTimeout(verificarCamposExistentes, 100);
    }
}
</script>

<style>
.campo-editable:hover {
  background-color: rgba(255, 255, 0, 0.5) !important;
  border-color: #ffc107 !important;
}

.editor-container {
  position: relative;
  background-color: #f8f9fa;
}

#fondoCertificado {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}
</style>

<?php require_once('footer.php'); ?> 