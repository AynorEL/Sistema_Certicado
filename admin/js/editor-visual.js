/**
 * Editor Visual de Certificados - JavaScript Mejorado
 * Sistema completo y robusto para edición de certificados
 */

class CertificateEditor {
    constructor() {
        this.selectedField = null;
        this.configuration = {
            campos: [],
            imagen_fondo: '',
            width: 2000,
            height: 1414,
            qr_config: {
                size: 300,
                color: '#000000',
                bgColor: '#FFFFFF',
                margin: 0,
                logoEnabled: false
            }
        };
        this.isDragging = false;
        this.isResizing = false;
        this.dragStart = { x: 0, y: 0 };
        this.resizeStart = { width: 0, height: 0, x: 0, y: 0 };
        
        this.init();
    }
    
    init() {
        console.log('Inicializando Editor Visual de Certificados...');
        this.loadData();
        this.loadConfiguration();
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
        this.showNotification('Editor cargado correctamente', 'success');
    }
    
    // Cargar datos de prueba
    async loadData() {
        try {
            const idcurso = document.getElementById('editor').dataset.idcurso;
            console.log('Cargando datos para curso:', idcurso);
            
            const response = await fetch(`cargar_datos_prueba.php?idcurso=${idcurso}`);
            const data = await response.json();
            
            if (data.success) {
                window.datosPrueba = data.datos;
                console.log('Datos cargados exitosamente:', window.datosPrueba);
                
                // Actualizar selects con datos reales
                this.updateSelects();
                
                // Actualizar campos existentes con datos reales
                this.updateExistingFields();
                
                this.showNotification('Datos cargados correctamente', 'success');
            } else {
                console.error('Error al cargar datos:', data.message);
                this.showNotification('Error al cargar datos: ' + data.message, 'error');
                
                // Usar datos de prueba por defecto
                this.loadDefaultData();
            }
        } catch (error) {
            console.error('Error en la petición:', error);
            this.showNotification('Error de conexión al cargar datos', 'error');
            
            // Usar datos de prueba por defecto
            this.loadDefaultData();
        }
    }
    
    // Cargar datos por defecto si falla la carga del servidor
    loadDefaultData() {
        const idcurso = document.getElementById('editor').dataset.idcurso || '1';
        window.datosPrueba = {
            curso: { idcurso: idcurso, titulo: 'Curso de Prueba' },
            alumnos: [
                { idcliente: 1, nombre: 'Juan Carlos', apellido: 'García López', email: 'juan.garcia@email.com' },
                { idcliente: 2, nombre: 'María Elena', apellido: 'Rodríguez Silva', email: 'maria.rodriguez@email.com' },
                { idcliente: 3, nombre: 'Carlos Alberto', apellido: 'Martínez Vega', email: 'carlos.martinez@email.com' },
                { idcliente: 4, nombre: 'Ana Patricia', apellido: 'Hernández Ruiz', email: 'ana.hernandez@email.com' },
                { idcliente: 5, nombre: 'Luis Miguel', apellido: 'Pérez Torres', email: 'luis.perez@email.com' }
            ],
            instructores: [
                { idinstructor: 1, nombre: 'Dr. Roberto', apellido: 'Sánchez Mendoza', email: 'roberto.sanchez@instituto.com' },
                { idinstructor: 2, nombre: 'Lic. Carmen', apellido: 'Vargas Jiménez', email: 'carmen.vargas@instituto.com' }
            ],
            especialistas: [
                { idespecialista: 1, nombre: 'Mg. Patricia', apellido: 'González Castro', email: 'patricia.gonzalez@instituto.com' },
                { idespecialista: 2, nombre: 'Dr. Manuel', apellido: 'Ramírez Flores', email: 'manuel.ramirez@instituto.com' }
            ],
            firmas: [
                { nombre: 'Firma Instructor 1', archivo: 'instructor_firma_1.png', url: '../assets/uploads/firmas/instructor_firma_1.png' },
                { nombre: 'Firma Instructor 2', archivo: 'instructor_firma_2.png', url: '../assets/uploads/firmas/instructor_firma_2.png' },
                { nombre: 'Firma Especialista 1', archivo: 'especialista_firma_1.png', url: '../assets/uploads/firmas/especialista_firma_1.png' },
                { nombre: 'Firma Especialista 2', archivo: 'especialista_firma_2.png', url: '../assets/uploads/firmas/especialista_firma_2.png' }
            ],
            fecha_actual: new Date().toLocaleDateString('es-ES'),
            fecha_actual_iso: new Date().toISOString().split('T')[0],
            codigo_curso: 'CUR-' + String(idcurso).padStart(4, '0')
        };
        
        this.updateSelects();
        this.updateExistingFields();
    }
    
    // Actualizar selects con datos reales
    updateSelects() {
        if (!window.datosPrueba) return;
        
        // Actualizar select de alumnos
        const selectAlumno = document.getElementById('selectAlumno');
        if (selectAlumno && window.datosPrueba.alumnos) {
            selectAlumno.innerHTML = '<option value="">Seleccionar alumno...</option>';
            window.datosPrueba.alumnos.forEach(alumno => {
                const id = alumno.idcliente || alumno.idusuario;
                selectAlumno.innerHTML += `<option value="${id}">${alumno.nombre} ${alumno.apellido}</option>`;
            });
        }
        
        // Actualizar select de instructores
        const selectInstructor = document.getElementById('selectInstructor');
        if (selectInstructor && window.datosPrueba.instructores) {
            selectInstructor.innerHTML = '<option value="">Seleccionar instructor...</option>';
            window.datosPrueba.instructores.forEach(instructor => {
                const id = instructor.idinstructor || instructor.idusuario;
                selectInstructor.innerHTML += `<option value="${id}">${instructor.nombre} ${instructor.apellido}</option>`;
            });
        }
        
        // Actualizar select de especialistas
        const selectEspecialista = document.getElementById('selectEspecialista');
        if (selectEspecialista && window.datosPrueba.especialistas) {
            selectEspecialista.innerHTML = '<option value="">Seleccionar especialista...</option>';
            window.datosPrueba.especialistas.forEach(especialista => {
                const id = especialista.idespecialista || especialista.idusuario;
                selectEspecialista.innerHTML += `<option value="${id}">${especialista.nombre} ${especialista.apellido}</option>`;
            });
        }
    }
    
    // Actualizar campos existentes con datos reales
    updateExistingFields() {
        if (!window.datosPrueba) return;
        
        // Actualizar campos de texto con datos reales
        $('.editable-field[data-tipo="alumno"]').each(function() {
            if (window.datosPrueba.alumnos && window.datosPrueba.alumnos.length > 0) {
                const alumno = window.datosPrueba.alumnos[0];
                $(this).text(alumno.nombre + ' ' + alumno.apellido);
            }
        });
        
        $('.editable-field[data-tipo="fecha"]').each(function() {
            if (window.datosPrueba.fecha_actual) {
                $(this).text(window.datosPrueba.fecha_actual);
            }
        });
        
        $('.editable-field[data-tipo="codigo"]').each(function() {
            if (window.datosPrueba.codigo_curso) {
                $(this).text(window.datosPrueba.codigo_curso);
            }
        });
        
            // Actualizar campos de firma con datos reales de la BD
    $('.editable-field[data-tipo="firma_instructor"]').each(function() {
        if (window.datosPrueba.instructores && window.datosPrueba.instructores.length > 0) {
            const instructor = window.datosPrueba.instructores[0];
            let firmaUrl = '../assets/img/qr_placeholder.png';
            
            // Usar firma del instructor si existe
            if (instructor.foto && instructor.foto.trim() !== '') {
                firmaUrl = '../assets/uploads/firmas/' + instructor.foto;
            } else if (window.datosPrueba.firmas && window.datosPrueba.firmas.length > 0) {
                // Buscar firma de instructor en las firmas disponibles
                const firmaInstructor = window.datosPrueba.firmas.find(f => f.nombre.includes('instructor'));
                if (firmaInstructor) {
                    firmaUrl = firmaInstructor.url;
                } else {
                    // Usar primera firma disponible como respaldo
                    firmaUrl = window.datosPrueba.firmas[0].url;
                }
            }
            
            $(this).html(`<img src="${firmaUrl}" alt="Firma ${instructor.nombre}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`);
        }
    });
    
    $('.editable-field[data-tipo="firma_especialista"]').each(function() {
        if (window.datosPrueba.especialistas && window.datosPrueba.especialistas.length > 0) {
            const especialista = window.datosPrueba.especialistas[0];
            let firmaUrl = '../assets/img/qr_placeholder.png';
            
            // Usar firma del especialista si existe
            if (especialista.foto && especialista.foto.trim() !== '') {
                firmaUrl = '../assets/uploads/firmas/' + especialista.foto;
            } else if (window.datosPrueba.firmas && window.datosPrueba.firmas.length > 0) {
                // Buscar firma de especialista en las firmas disponibles
                const firmaEspecialista = window.datosPrueba.firmas.find(f => f.nombre.includes('especialista'));
                if (firmaEspecialista) {
                    firmaUrl = firmaEspecialista.url;
                } else if (window.datosPrueba.firmas.length > 2) {
                    // Usar tercera firma disponible como respaldo
                    firmaUrl = window.datosPrueba.firmas[2].url;
                }
            }
            
            $(this).html(`<img src="${firmaUrl}" alt="Firma ${especialista.nombre}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`);
        }
    });
        
        console.log('Campos actualizados con datos reales');
    }
    
    // Cargar configuración guardada
    async loadConfiguration() {
        const idcurso = document.getElementById('editor').dataset.idcurso;
        
        try {
            const response = await fetch(`cargar_config_certificado.php?idcurso=${idcurso}`);
            const data = await response.json();
            
            if (data.success && data.config) {
                // Cargar configuración del QR si existe
                if (data.config.qr_config) {
                    this.configuration.qr_config = { ...this.configuration.qr_config, ...data.config.qr_config };
                }
                
                this.restoreFields(data.config);
                this.showNotification('Configuración cargada', 'info');
            }
        } catch (error) {
            console.error('Error cargando configuración:', error);
        }
    }
    
    // Configurar event listeners
    setupEventListeners() {
        // Click en el fondo para deseleccionar
        const editor = document.getElementById('editor');
        if (editor) {
            editor.addEventListener('click', (e) => {
                if (e.target === editor || e.target.id === 'fondoCertificado') {
                    this.deselectField();
                }
            });
        }
        
        // Event listeners para datos de prueba
        const selectAlumno = document.getElementById('selectAlumno');
        const fechaEmision = document.getElementById('fechaEmision');
        const selectInstructor = document.getElementById('selectInstructor');
        const selectEspecialista = document.getElementById('selectEspecialista');
        
        if (selectAlumno) selectAlumno.addEventListener('change', () => this.changeAlumno());
        if (fechaEmision) fechaEmision.addEventListener('change', () => this.updateFecha());
        if (selectInstructor) selectInstructor.addEventListener('change', () => this.changeInstructor());
        if (selectEspecialista) selectEspecialista.addEventListener('change', () => this.changeEspecialista());
    }
    
    // Configurar atajos de teclado
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (this.selectedField) {
                switch(e.key) {
                    case 'Delete':
                    case 'Backspace':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            this.deleteField();
                        }
                        break;
                    case 'Escape':
                        this.deselectField();
                        break;
                    case 'd':
                    case 'D':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            this.duplicateField();
                        }
                        break;
                    case 's':
                    case 'S':
                        if (e.ctrlKey) {
                            e.preventDefault();
                            this.saveConfiguration();
                        }
                        break;
                }
            }
        });
    }
    
    // Agregar nuevo campo
    addField(type) {
        const field = document.createElement('div');
        field.className = 'editable-field';
        field.setAttribute('contenteditable', 'true');
        field.dataset.tipo = type;
        field.dataset.id = 'field_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        // Posición inicial centrada
        field.style.left = '100px';
        field.style.top = '100px';
        field.style.zIndex = '10';
        
        // Contenido según el tipo
        field.textContent = this.getDefaultContent(type);
        
        // Para campos de imagen, cambiar contenido
        if (type === 'firma_instructor') {
            let firmaUrl = '../assets/img/qr_placeholder.png';
            if (window.datosPrueba && window.datosPrueba.instructores && window.datosPrueba.instructores.length > 0) {
                const instructor = window.datosPrueba.instructores[0];
                if (instructor.foto && instructor.foto.trim() !== '') {
                    firmaUrl = '../assets/uploads/firmas/' + instructor.foto;
                } else if (window.datosPrueba.firmas && window.datosPrueba.firmas.length > 0) {
                    const firmaInstructor = window.datosPrueba.firmas.find(f => f.nombre.includes('instructor'));
                    if (firmaInstructor) {
                        firmaUrl = firmaInstructor.url;
                    } else {
                        firmaUrl = window.datosPrueba.firmas[0].url;
                    }
                }
            }
            field.innerHTML = `<img src="${firmaUrl}" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">`;
            field.setAttribute('contenteditable', 'false');
        } else if (type === 'firma_especialista') {
            let firmaUrl = '../assets/img/qr_placeholder.png';
            if (window.datosPrueba && window.datosPrueba.especialistas && window.datosPrueba.especialistas.length > 0) {
                const especialista = window.datosPrueba.especialistas[0];
                if (especialista.foto && especialista.foto.trim() !== '') {
                    firmaUrl = '../assets/uploads/firmas/' + especialista.foto;
                } else if (window.datosPrueba.firmas && window.datosPrueba.firmas.length > 0) {
                    const firmaEspecialista = window.datosPrueba.firmas.find(f => f.nombre.includes('especialista'));
                    if (firmaEspecialista) {
                        firmaUrl = firmaEspecialista.url;
                    } else if (window.datosPrueba.firmas.length > 2) {
                        firmaUrl = window.datosPrueba.firmas[2].url;
                    }
                }
            }
            field.innerHTML = `<img src="${firmaUrl}" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">`;
            field.setAttribute('contenteditable', 'false');
                    } else if (type === 'qr') {
                const config = this.configuration.qr_config;
                const qrUrl = `generar_qr.php?test=1&size=${config.size}&color=${encodeURIComponent(config.color)}&bgColor=${encodeURIComponent(config.bgColor)}&margin=${config.margin}`;
                field.innerHTML = `<img src="${qrUrl}" alt="QR" style="width: ${config.size}px; height: ${config.size}px; object-fit: contain;">`;
                field.setAttribute('contenteditable', 'false');
        }
        
        // Hacer arrastrable y seleccionable
        this.makeDraggable(field);
        field.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectField(field);
        });
        
        document.getElementById('editor').appendChild(field);
        this.selectField(field);
        this.updateConfiguration();
        
        this.showNotification(`Campo ${this.getTypeName(type)} agregado`, 'success');
    }
    
    // Hacer campo arrastrable (mejorado)
    makeDraggable(field) {
        let isDragging = false;
        let startX, startY, startLeft, startTop;
        
        const mousedownHandler = (e) => {
            // No iniciar arrastre si se hace clic en handles o controles
            if (e.target.classList.contains('resize-handle') || 
                e.target.closest('.resize-handle') ||
                e.target.classList.contains('control-btn')) {
                return;
            }
            
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(field.style.left) || 0;
            startTop = parseInt(field.style.top) || 0;
            
            field.style.cursor = 'grabbing';
            field.style.zIndex = '1000'; // Traer al frente durante el arrastre
            e.preventDefault();
            e.stopPropagation();
        };
        
        const mousemoveHandler = (e) => {
            if (!isDragging) return;
            
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            field.style.left = (startLeft + deltaX) + 'px';
            field.style.top = (startTop + deltaY) + 'px';
        };
        
        const mouseupHandler = () => {
            if (isDragging) {
                isDragging = false;
                field.style.cursor = 'move';
                field.style.zIndex = '10'; // Volver al z-index normal
                this.updateConfiguration();
            }
        };
        
        field.addEventListener('mousedown', mousedownHandler);
        document.addEventListener('mousemove', mousemoveHandler);
        document.addEventListener('mouseup', mouseupHandler);
        
        // Limpiar event listeners cuando se elimine el campo
        field.addEventListener('remove', () => {
            document.removeEventListener('mousemove', mousemoveHandler);
            document.removeEventListener('mouseup', mouseupHandler);
        });
    }
    
    // Seleccionar campo
    selectField(field) {
        // Deseleccionar campo anterior
        if (this.selectedField) {
            this.selectedField.classList.remove('selected');
            this.removeResizeHandles(this.selectedField);
            this.selectedField.style.zIndex = '10';
        }
        
        // Seleccionar nuevo campo
        this.selectedField = field;
        field.classList.add('selected');
        field.style.zIndex = '100'; // Traer al frente
        
        // Agregar handles de redimensionamiento
        this.addResizeHandles(field);
        
        // Mostrar panel de propiedades
        this.showPropertiesPanel(field);
        
        // Mostrar notificación
        this.showNotification(`Campo "${this.getTypeName(field.dataset.tipo)}" seleccionado`, 'info');
    }
    
    // Deseleccionar campo
    deselectField() {
        if (this.selectedField) {
            this.selectedField.classList.remove('selected');
            this.removeResizeHandles(this.selectedField);
            this.selectedField.style.zIndex = '10';
            this.selectedField = null;
            this.showPropertiesPanel(null);
        }
    }
    
    // Agregar handles de redimensionamiento
    addResizeHandles(field) {
        const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
        
        handles.forEach(pos => {
            const handle = document.createElement('div');
            handle.className = `resize-handle ${pos}`;
            handle.dataset.direction = pos;
            
            handle.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                this.startResize(field, pos, e);
            });
            
            field.appendChild(handle);
        });
    }
    
    // Remover handles
    removeResizeHandles(field) {
        const handles = field.querySelectorAll('.resize-handle');
        handles.forEach(handle => handle.remove());
    }
    
    // Iniciar redimensionamiento
    startResize(field, direction, e) {
        this.isResizing = true;
        this.resizeStart = {
            width: field.offsetWidth,
            height: field.offsetHeight,
            left: parseInt(field.style.left) || 0,
            top: parseInt(field.style.top) || 0,
            x: e.clientX,
            y: e.clientY
        };
        
        const moveHandler = (ev) => {
            if (!this.isResizing) return;
            
            const deltaX = ev.clientX - this.resizeStart.x;
            const deltaY = ev.clientY - this.resizeStart.y;
            
            let newWidth = this.resizeStart.width;
            let newHeight = this.resizeStart.height;
            let newLeft = this.resizeStart.left;
            let newTop = this.resizeStart.top;
            
            if (direction.includes('e')) newWidth = Math.max(50, this.resizeStart.width + deltaX);
            if (direction.includes('s')) newHeight = Math.max(30, this.resizeStart.height + deltaY);
            if (direction.includes('w')) {
                newWidth = Math.max(50, this.resizeStart.width - deltaX);
                newLeft = this.resizeStart.left + deltaX;
            }
            if (direction.includes('n')) {
                newHeight = Math.max(30, this.resizeStart.height - deltaY);
                newTop = this.resizeStart.top + deltaY;
            }
            
            field.style.width = newWidth + 'px';
            field.style.height = newHeight + 'px';
            field.style.left = newLeft + 'px';
            field.style.top = newTop + 'px';
        };
        
        const upHandler = () => {
            this.isResizing = false;
            document.removeEventListener('mousemove', moveHandler);
            document.removeEventListener('mouseup', upHandler);
            this.updateConfiguration();
        };
        
        document.addEventListener('mousemove', moveHandler);
        document.addEventListener('mouseup', upHandler);
    }
    
    // Mostrar panel de propiedades (Inspector dinámico)
    showPropertiesPanel(field) {
        const panel = document.getElementById('panelPropiedades');
        const noSelectionMessage = document.getElementById('noSelectionMessage');
        
        if (!field) {
            // Ocultar panel y mostrar mensaje cuando no hay campo seleccionado
            if (panel) panel.style.display = 'none';
            if (noSelectionMessage) noSelectionMessage.style.display = 'block';
            return;
        }
        
        // Ocultar mensaje y mostrar panel
        if (noSelectionMessage) noSelectionMessage.style.display = 'none';
        if (panel) panel.style.display = 'block';
        
        const type = field.dataset.tipo;
        const typeName = this.getTypeName(type);
        
        panel.innerHTML = `
            <div class="inspector-header">
                <h6><i class="fa fa-cog"></i> Inspector de Propiedades</h6>
                <small class="text-muted">${typeName}</small>
            </div>
            
            <div class="field-info">
                <div class="info-row">
                    <span class="info-label">Posición:</span>
                    <span class="info-value">${parseInt(field.style.left) || 0}, ${parseInt(field.style.top) || 0}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tamaño:</span>
                    <span class="info-value">${field.offsetWidth} × ${field.offsetHeight}</span>
                </div>
            </div>
            
            <div class="property-section">
                <h6 class="section-title">Texto</h6>
                
                <div class="property-group">
                    <label>Fuente:</label>
                    <select id="fontFamily" class="form-control form-control-sm" onchange="editor.applyProperty('fontFamily', this.value)">
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
                    <label>Tamaño:</label>
                    <input type="number" id="fontSize" class="form-control form-control-sm" value="${parseInt(field.style.fontSize) || 14}" min="8" max="72" onchange="editor.applyProperty('fontSize', this.value + 'px')">
                </div>
                
                <div class="property-group">
                    <label>Color:</label>
                    <input type="color" id="textColor" class="form-control form-control-sm" value="${field.style.color || '#000000'}" onchange="editor.applyProperty('color', this.value)">
                </div>
                
                <div class="property-group">
                    <label>Alineación:</label>
                    <div class="alignment-buttons">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editor.applyProperty('textAlign', 'left')" title="Izquierda">
                            <i class="fa fa-align-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editor.applyProperty('textAlign', 'center')" title="Centro">
                            <i class="fa fa-align-center"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editor.applyProperty('textAlign', 'right')" title="Derecha">
                            <i class="fa fa-align-right"></i>
                        </button>
                    </div>
                </div>
                
                <div class="property-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="fontBold" onchange="editor.applyProperty('fontWeight', this.checked ? 'bold' : 'normal')">
                        <label for="fontBold">Negrita</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="fontItalic" onchange="editor.applyProperty('fontStyle', this.checked ? 'italic' : 'normal')">
                        <label for="fontItalic">Cursiva</label>
                    </div>
                </div>
            </div>
            
            <div class="property-section">
                <h6 class="section-title">Acciones</h6>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-outline-primary" onclick="editor.duplicateField()" title="Duplicar">
                        <i class="fa fa-copy"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="editor.deleteField()" title="Eliminar">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Establecer valores actuales
        setTimeout(() => {
            const fontFamily = document.getElementById('fontFamily');
            const fontBold = document.getElementById('fontBold');
            const fontItalic = document.getElementById('fontItalic');
            
            if (fontFamily) fontFamily.value = field.style.fontFamily || 'Arial';
            if (fontBold) fontBold.checked = field.style.fontWeight === 'bold';
            if (fontItalic) fontItalic.checked = field.style.fontStyle === 'italic';
        }, 10);
    }
    
    // Aplicar propiedad
    applyProperty(property, value) {
        if (this.selectedField) {
            this.selectedField.style[property] = value;
            this.updateConfiguration();
        }
    }
    
    // Eliminar campo
    deleteField() {
        if (this.selectedField && confirm('¿Estás seguro de que quieres eliminar este campo?')) {
            this.selectedField.remove();
            this.selectedField = null;
            this.showPropertiesPanel(null);
            this.updateConfiguration();
            this.showNotification('Campo eliminado', 'success');
        }
    }
    
    // Duplicar campo
    duplicateField() {
        if (this.selectedField) {
            const clone = this.selectedField.cloneNode(true);
            clone.style.left = (parseInt(this.selectedField.style.left) + 20) + 'px';
            clone.style.top = (parseInt(this.selectedField.style.top) + 20) + 'px';
            clone.classList.remove('selected');
            clone.dataset.id = 'field_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            clone.querySelectorAll('.resize-handle').forEach(el => el.remove());
            
            this.makeDraggable(clone);
            clone.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectField(clone);
            });
            
            document.getElementById('editor').appendChild(clone);
            this.updateConfiguration();
            this.showNotification('Campo duplicado', 'success');
        }
    }
    
    // Actualizar configuración
    updateConfiguration() {
        const fields = document.querySelectorAll('.editable-field');
        this.configuration.campos = [];
        
        fields.forEach(field => {
            let texto = field.textContent || field.innerHTML;
            // Forzar placeholder para los tipos dinámicos
            switch(field.dataset.tipo) {
                case 'alumno':
                    texto = 'NOMBRE DEL ALUMNO';
                    break;
                case 'fecha':
                    texto = 'FECHA DE EMISIÓN';
                    break;
                case 'instructor':
                    texto = 'NOMBRE DEL INSTRUCTOR';
                    break;
                case 'especialista':
                    texto = 'NOMBRE DEL ESPECIALISTA';
                    break;
                // Puedes agregar más casos si tienes otros campos dinámicos
            }
            const fieldConfig = {
                tipo: field.dataset.tipo,
                left: parseInt(field.style.left) || 0,
                top: parseInt(field.style.top) || 0,
                width: field.offsetWidth,
                height: field.offsetHeight,
                fontSize: parseInt(field.style.fontSize) || 14,
                fontFamily: field.style.fontFamily || 'Arial',
                color: field.style.color || '#000000',
                textAlign: field.style.textAlign || 'left',
                fontWeight: field.style.fontWeight || 'normal',
                fontStyle: field.style.fontStyle || 'normal',
                texto: texto
            };
            this.configuration.campos.push(fieldConfig);
        });
    }
    
    // Guardar configuración
    async saveConfiguration() {
        this.updateConfiguration();
        
        const idcurso = document.getElementById('editor').dataset.idcurso;
        
        try {
            const response = await fetch('guardar_config_certificado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    idcurso: idcurso,
                    configuracion: this.configuration
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Configuración guardada exitosamente', 'success');
            } else {
                this.showNotification(data.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error de conexión al guardar', 'error');
        }
    }
    
    // Restaurar campos
    restoreFields(config) {
        if (!config.campos) return;
        
        // Limpiar campos existentes
        document.querySelectorAll('.editable-field').forEach(field => field.remove());
        
        // Restaurar cada campo
        config.campos.forEach(campo => {
            const field = document.createElement('div');
            field.className = 'editable-field';
            field.dataset.tipo = campo.tipo;
            field.dataset.id = 'field_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            field.setAttribute('contenteditable', 'true');
            
            field.style.left = campo.left + 'px';
            field.style.top = campo.top + 'px';
            field.style.width = campo.width + 'px';
            field.style.height = campo.height + 'px';
            field.style.fontSize = campo.fontSize + 'px';
            field.style.fontFamily = campo.fontFamily || 'Arial';
            field.style.color = campo.color || '#000000';
            field.style.textAlign = campo.textAlign || 'left';
            field.style.fontWeight = campo.fontWeight || 'normal';
            field.style.fontStyle = campo.fontStyle || 'normal';
            field.style.zIndex = '10';
            
            // Establecer contenido
            if (campo.tipo === 'firma_instructor') {
                let firmaUrl = '../assets/img/qr_placeholder.png';
                if (window.datosPrueba && window.datosPrueba.instructores && window.datosPrueba.instructores.length > 0) {
                    const instructor = window.datosPrueba.instructores[0];
                    if (instructor.foto && instructor.foto.trim() !== '') {
                        firmaUrl = '../assets/uploads/firmas/' + instructor.foto;
                    } else if (window.datosPrueba.firmas && window.datosPrueba.firmas.length > 0) {
                        const firmaInstructor = window.datosPrueba.firmas.find(f => f.nombre.includes('instructor'));
                        if (firmaInstructor) {
                            firmaUrl = firmaInstructor.url;
                        } else {
                            firmaUrl = window.datosPrueba.firmas[0].url;
                        }
                    }
                }
                field.innerHTML = `<img src="${firmaUrl}" alt="Firma Instructor" style="max-width: 120px; max-height: 60px; object-fit: contain;">`;
                field.setAttribute('contenteditable', 'false');
            } else if (campo.tipo === 'firma_especialista') {
                let firmaUrl = '../assets/img/qr_placeholder.png';
                if (window.datosPrueba && window.datosPrueba.especialistas && window.datosPrueba.especialistas.length > 0) {
                    const especialista = window.datosPrueba.especialistas[0];
                    if (especialista.foto && especialista.foto.trim() !== '') {
                        firmaUrl = '../assets/uploads/firmas/' + especialista.foto;
                    } else if (window.datosPrueba.firmas && window.datosPrueba.firmas.length > 0) {
                        const firmaEspecialista = window.datosPrueba.firmas.find(f => f.nombre.includes('especialista'));
                        if (firmaEspecialista) {
                            firmaUrl = firmaEspecialista.url;
                        } else if (window.datosPrueba.firmas.length > 2) {
                            firmaUrl = window.datosPrueba.firmas[2].url;
                        }
                    }
                }
                field.innerHTML = `<img src="${firmaUrl}" alt="Firma Especialista" style="max-width: 120px; max-height: 60px; object-fit: contain;">`;
                field.setAttribute('contenteditable', 'false');
            } else if (campo.tipo === 'qr') {
                const config = this.configuration.qr_config;
                const qrUrl = `generar_qr.php?test=1&size=${config.size}&color=${encodeURIComponent(config.color)}&bgColor=${encodeURIComponent(config.bgColor)}&margin=${config.margin}`;
                field.innerHTML = `<img src="${qrUrl}" alt="QR" style="width: ${config.size}px; height: ${config.size}px; object-fit: contain;">`;
                field.setAttribute('contenteditable', 'false');
            } else {
                field.textContent = campo.texto || this.getDefaultContent(campo.tipo);
            }
            
            // Hacer arrastrable y seleccionable
            this.makeDraggable(field);
            field.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectField(field);
            });
            
            document.getElementById('editor').appendChild(field);
        });
    }
    
    // Funciones para datos de prueba
    changeAlumno() {
        const select = document.getElementById('selectAlumno');
        if (select && select.value) {
            const fields = document.querySelectorAll('[data-tipo="alumno"]');
            fields.forEach(field => {
                field.textContent = select.options[select.selectedIndex].text;
            });
            this.updateConfiguration();
        }
    }
    
    updateFecha() {
        const input = document.getElementById('fechaEmision');
        if (input && input.value) {
            const fecha = new Date(input.value).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const fields = document.querySelectorAll('[data-tipo="fecha"]');
            fields.forEach(field => {
                field.textContent = fecha.toUpperCase();
            });
            this.updateConfiguration();
        }
    }
    
    changeInstructor() {
        const select = document.getElementById('selectInstructor');
        if (select && select.value) {
            const fields = document.querySelectorAll('[data-tipo="instructor"]');
            fields.forEach(field => {
                field.textContent = select.options[select.selectedIndex].text;
            });
            this.updateConfiguration();
        }
    }
    
    changeEspecialista() {
        const select = document.getElementById('selectEspecialista');
        if (select && select.value) {
            const fields = document.querySelectorAll('[data-tipo="especialista"]');
            fields.forEach(field => {
                field.textContent = select.options[select.selectedIndex].text;
            });
            this.updateConfiguration();
        }
    }
    
    // Funciones de utilidad
    getDefaultContent(type) {
        switch(type) {
            case 'alumno': return 'NOMBRE DEL ALUMNO';
            case 'fecha': return 'FECHA DE EMISIÓN';
            case 'instructor': return 'NOMBRE DEL INSTRUCTOR';
            case 'especialista': return 'NOMBRE DEL ESPECIALISTA';
            default: return 'CAMPO';
        }
    }
    
    getTypeName(type) {
        const names = {
            'alumno': 'Nombre del Alumno',
            'fecha': 'Fecha de Emisión',
            'instructor': 'Instructor',
            'especialista': 'Especialista',
            'firma_instructor': 'Firma del Instructor',
            'firma_especialista': 'Firma del Especialista',
            'qr': 'Código QR'
        };
        return names[type] || type;
    }
    
    // Mostrar notificación
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Funciones públicas para botones
    previsualizarCertificado() {
        const idcurso = document.getElementById('editor').dataset.idcurso;
        const selectAlumno = document.getElementById('selectAlumno');
        const idalumno = selectAlumno && selectAlumno.value ? selectAlumno.value : 1;
        window.open(`previsualizar_certificado_final.php?idcurso=${idcurso}&idalumno=${idalumno}`, '_blank');
    }
    
    generarCertificado() {
        const idcurso = document.getElementById('editor').dataset.idcurso;
        window.open(`generar-certificado.php?idcurso=${idcurso}`, '_blank');
    }
    
    imprimirCertificado() {
        // Guardar configuración antes de imprimir
        this.saveConfiguration().then(() => {
            // Crear una nueva ventana para impresión
            const printWindow = window.open('', '_blank');
            const editor = document.getElementById('editor');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Certificado - Impresión</title>
                    <link rel="stylesheet" href="css/print-certificado.css">
                    <style>
                        body { margin: 0; padding: 0; }
                        .certificado-impresion {
                            width: 100%;
                            height: 100vh;
                            position: relative;
                            overflow: hidden;
                        }
                    </style>
                </head>
                <body>
                    <div class="certificado-impresion">
                        ${editor.outerHTML}
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                            window.onafterprint = function() {
                                window.close();
                            };
                        };
                    </script>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
    }
    
    // Funciones para configuración del QR
    cambiarTamañoQR() {
        const size = document.getElementById('qrSize').value;
        this.configuration.qr_config.size = parseInt(size);
        this.actualizarTodosLosQR();
    }
    
    cambiarColorQR() {
        const color = document.getElementById('qrColor').value;
        const bgColor = document.getElementById('qrBgColor').value;
        this.configuration.qr_config.color = color;
        this.configuration.qr_config.bgColor = bgColor;
        this.actualizarTodosLosQR();
    }
    
    cambiarMargenQR() {
        const margin = document.getElementById('qrMargin').value;
        this.configuration.qr_config.margin = parseInt(margin);
        this.actualizarTodosLosQR();
    }
    
    aplicarConfiguracionQR() {
        // Guardar la configuración actual del QR
        this.configuration.qr_config = {
            size: parseInt(document.getElementById('qrSizeSlider').value),
            color: document.getElementById('qrColor').value,
            bgColor: document.getElementById('qrBgColor').value,
            margin: parseInt(document.getElementById('qrMargin').value),
            logoEnabled: document.getElementById('qrLogoEnabled').checked
        };
        
        // Actualizar todos los QR en el editor
        this.actualizarTodosLosQR();
        
        // Guardar en la base de datos
        this.saveConfiguration();
        
        // Mostrar mensaje de éxito
        this.showNotification('¡Diseño de QR aplicado! El diseño se ha guardado para este curso.', 'success');
    }
    
    actualizarTodosLosQR() {
        const config = this.configuration.qr_config;
        const qrUrl = `generar_qr_svg.php?size=${config.size}&color=${encodeURIComponent(config.color)}&bgColor=${encodeURIComponent(config.bgColor)}&margin=${config.margin}&data=CERTIFICADO-TEST-${Date.now()}`;
        
        // Actualizar todos los campos QR en el editor
        $('.editable-field[data-tipo="qr"]').each(function() {
            const field = $(this);
            
            // Cargar el SVG del QR
            fetch(qrUrl)
                .then(response => response.text())
                .then(svg => {
                    field.html(svg);
                    
                    // Aplicar el tamaño configurado
                    field.css({
                        'width': config.size + 'px',
                        'height': config.size + 'px'
                    });
                    
                    // Si el logo está habilitado, agregarlo
                    if (config.logoEnabled) {
                        const logo = $('<img>', {
                            src: 'img/logo.png',
                            class: 'qr-logo',
                            style: `position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: ${config.size * 0.2}px; pointer-events: none; z-index: 10;`
                        });
                        field.append(logo);
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar QR:', error);
                    field.html('<div style="width:100%;height:100%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#666;">Error QR</div>');
                });
        });
    }
    
    cargarConfiguracionQR() {
        if (this.configuration.qr_config) {
            document.getElementById('qrSizeSlider').value = this.configuration.qr_config.size || 300;
            document.getElementById('qrSizeValue').textContent = (this.configuration.qr_config.size || 300) + 'px';
            document.getElementById('qrColor').value = this.configuration.qr_config.color || '#000000';
            document.getElementById('qrBgColor').value = this.configuration.qr_config.bgColor || '#FFFFFF';
            document.getElementById('qrMargin').value = this.configuration.qr_config.margin || 0;
            document.getElementById('qrMarginValue').textContent = this.configuration.qr_config.margin || 0;
            document.getElementById('qrLogoEnabled').checked = this.configuration.qr_config.logoEnabled || false;
            
            // Inicializar la vista previa del QR
            this.inicializarQRPreview();
        }
    }
    
    // Nuevas funciones para el diseñador de QR (adaptadas del código original)
    inicializarQRPreview() {
        this.actualizarQRPreview();
        this.toggleQRLogo();
    }
    
    ajustarQRPreview() {
        const size = parseInt(document.getElementById('qrSizeSlider').value);
        const container = document.getElementById('qrPreviewContainer');
        const logo = document.getElementById('qrLogo');
        
        // Cambiar tamaño del contenedor
        container.style.width = size + 'px';
        container.style.height = size + 'px';
        document.getElementById('qrSizeValue').textContent = size + 'px';
        
        // Ajustar logo proporcionalmente (20% del tamaño del QR)
        if (logo && logo.style.display !== 'none') {
            logo.style.width = (size * 0.2) + 'px';
        }
        
        this.configuration.qr_config.size = size;
        this.actualizarQRPreview();
    }
    
    actualizarQRPreview() {
        const config = this.configuration.qr_config;
        const qrUrl = `generar_qr_svg.php?size=${config.size}&color=${encodeURIComponent(config.color)}&bgColor=${encodeURIComponent(config.bgColor)}&margin=${config.margin}&data=CERTIFICADO-TEST-${Date.now()}`;
        
        // Cargar el SVG del QR
        fetch(qrUrl)
            .then(response => response.text())
            .then(svg => {
                document.getElementById('qrPreview').innerHTML = svg;
            })
            .catch(error => {
                console.error('Error al cargar QR:', error);
                document.getElementById('qrPreview').innerHTML = '<div style="width:100%;height:100%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#666;">Error QR</div>';
            });
    }
    
    toggleQRLogo() {
        const logo = document.getElementById('qrLogo');
        const enabled = document.getElementById('qrLogoEnabled').checked;
        
        if (enabled) {
            logo.style.display = 'block';
            logo.style.width = (this.configuration.qr_config.size * 0.2) + 'px'; // 20% proporcional
        } else {
            logo.style.display = 'none';
        }
        
        this.configuration.qr_config.logoEnabled = enabled;
    }
}

// Inicializar el editor cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando editor...');
    window.editor = new CertificateEditor();
    
    // Hacer las funciones disponibles globalmente
    window.agregarCampo = (type) => window.editor.addField(type);
    window.guardarConfiguracion = () => window.editor.saveConfiguration();
    window.previsualizarCertificado = () => window.editor.previsualizarCertificado();
    window.generarCertificado = () => window.editor.generarCertificado();
    window.imprimirCertificado = () => window.editor.imprimirCertificado();
    window.cambiarAlumno = () => window.editor.changeAlumno();
    window.actualizarFecha = () => window.editor.updateFecha();
    window.cambiarInstructor = () => window.editor.changeInstructor();
    window.cambiarEspecialista = () => window.editor.changeEspecialista();
    
    // Funciones para configuración del QR
    window.cambiarTamañoQR = () => window.editor.cambiarTamañoQR();
    window.cambiarColorQR = () => window.editor.cambiarColorQR();
    window.cambiarMargenQR = () => window.editor.cambiarMargenQR();
    window.aplicarConfiguracionQR = () => window.editor.aplicarConfiguracionQR();
    
    // Nuevas funciones para el diseñador de QR
    window.ajustarQRPreview = () => window.editor.ajustarQRPreview();
    window.actualizarQRPreview = () => window.editor.actualizarQRPreview();
    window.toggleQRLogo = () => window.editor.toggleQRLogo();
    
    // Cargar configuración del QR al inicializar
    setTimeout(() => {
        window.editor.cargarConfiguracionQR();
    }, 500);
    
    console.log('Editor inicializado correctamente');
}); 