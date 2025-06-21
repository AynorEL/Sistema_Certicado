# Implementación de Firmas Digitales para Instructores y Especialistas

## Descripción

Se han agregado campos para firmas digitales en las tablas `instructor` y `especialista` del sistema de certificados.

## Cambios Realizados

### 1. Base de Datos

Se agregaron los siguientes campos:

- **Tabla `instructor`**: Campo `firma_digital` (TEXT)
- **Tabla `especialista`**: Campo `firma_especialista` (TEXT)

### 2. Archivos Modificados

#### Para Instructores:

- `admin/instructor-add.php` - Agregado campo de subida de firma digital
- `admin/instructor-edit.php` - Agregado campo para editar firma digital
- `admin/instructor.php` - Agregada columna para mostrar firma digital
- `admin/instructor-delete.php` - Eliminación de archivo de firma al borrar instructor

#### Para Especialistas:

- `admin/especialista-add.php` - Agregado campo de subida de firma especialista
- `admin/especialista-edit.php` - Agregado campo para editar firma especialista
- `admin/especialista.php` - Agregada columna para mostrar firma especialista
- `admin/especialista-delete.php` - Eliminación de archivo de firma al borrar especialista

#### Configuración:

- `admin/inc/config.php` - Agregada constante FIRMAS_PATH

## Instrucciones de Implementación

### Paso 1: Ejecutar SQL

Ejecutar el archivo `agregar_campos_firmas.sql` en la base de datos:

```sql
-- Agregar campo firma_digital a la tabla instructor
ALTER TABLE instructor
ADD firma_digital TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;

-- Agregar campo firma_especialista a la tabla especialista
ALTER TABLE especialista
ADD firma_especialista TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
```

### Paso 2: Verificar Permisos de Directorio

Asegurarse de que el directorio `assets/uploads/firmas/` tenga permisos de escritura para que PHP pueda subir archivos.

### Paso 3: Probar Funcionalidad

1. Ir al panel de administración
2. Probar agregar un nuevo instructor con firma digital
3. Probar agregar un nuevo especialista con firma especialista
4. Verificar que las firmas se muestren correctamente en las tablas
5. Probar editar instructores y especialistas existentes
6. Probar eliminar instructores y especialistas

## Características Implementadas

### Validaciones:

- Solo se permiten archivos de imagen (JPG, PNG, GIF)
- Tamaño máximo de archivo: 2MB
- Validación de tipos MIME
- Manejo de errores de subida

### Funcionalidades:

- Subida de archivos con nombres únicos (timestamp)
- Visualización de firmas en las tablas
- Edición de firmas (reemplazo de archivos)
- Eliminación automática de archivos al borrar registros
- Previsualización de firmas actuales en formularios de edición

### Seguridad:

- Validación de tipos de archivo
- Límite de tamaño de archivo
- Nombres de archivo únicos para evitar conflictos
- Limpieza de archivos al eliminar registros

## Estructura de Archivos

Los archivos de firma se guardan en:

- `assets/uploads/firmas/instructor_firma_[timestamp].[extension]`
- `assets/uploads/firmas/especialista_firma_[timestamp].[extension]`

## Notas Importantes

- Los campos de firma son obligatorios al crear nuevos registros
- Al editar, las firmas son opcionales (se mantiene la actual si no se sube una nueva)
- Los archivos se eliminan automáticamente del servidor cuando se elimina un registro
- Las firmas se muestran como miniaturas en las tablas para facilitar la identificación
- Todas las firmas se almacenan en la carpeta específica `assets/uploads/firmas/` para mejor organización
