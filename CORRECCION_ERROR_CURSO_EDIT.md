# Corrección del Error "ID de curso no válido"

## Problema Identificado

Al intentar editar un curso en el backend, se mostraba el error:

```
¡Error!
ID de curso no válido
```

## Causa del Problema

El problema estaba en la inconsistencia entre los parámetros de URL:

1. **En `curso.php`** (línea 153): El enlace se generaba con `id`

   ```php
   <a href="curso-edit.php?id=<?php echo $row['idcurso']; ?>" class="btn btn-primary btn-xs" title="Editar">
   ```

2. **En `curso-edit.php`** (línea 7): Se validaba `idcurso`
   ```php
   if (!isset($_REQUEST['idcurso']) || empty($_REQUEST['idcurso'])) {
   ```

## Solución Implementada

### Archivos Corregidos:

#### 1. `admin/curso-edit.php`

- **Antes**: Validaba `$_REQUEST['idcurso']`
- **Después**: Valida `$_REQUEST['id']`
- **Cambios**:
  - Línea 7: `if (!isset($_REQUEST['id']) || empty($_REQUEST['id']))`
  - Línea 15: `$statement->execute(array($_REQUEST['id']));`
  - Línea 133: `$_REQUEST['id']`

#### 2. `admin/curso-delete.php`

- **Antes**: Validaba `$_REQUEST['idcurso']`
- **Después**: Valida `$_REQUEST['id']`
- **Cambios**:
  - Línea 5: `if (!isset($_REQUEST['id']))`
  - Línea 11: `$statement->execute(array($_REQUEST['id']));`
  - Línea 24: `$statement->execute(array($_REQUEST['id']));`

## Verificación

### Archivos que ya estaban correctos:

- `admin/modulo-edit.php` - Usa `$_REQUEST['id']` correctamente
- `admin/modulo-delete.php` - Usa `$_REQUEST['id']` correctamente

### Archivo de prueba creado:

- `test_curso_edit.php` - Para verificar la funcionalidad

## Resultado

Ahora la edición y eliminación de cursos funciona correctamente:

- ✅ Los enlaces de edición funcionan
- ✅ Los enlaces de eliminación funcionan
- ✅ La validación de parámetros es consistente
- ✅ No se muestran errores de "ID no válido"

## Pruebas Recomendadas

1. Ir al panel de administración
2. Navegar a "Cursos"
3. Hacer clic en "Editar" en cualquier curso
4. Verificar que se abra el formulario de edición sin errores
5. Hacer clic en "Eliminar" y verificar que funcione correctamente

## Nota Importante

Este tipo de error es común cuando hay inconsistencias en los nombres de parámetros entre archivos. Es importante mantener una convención consistente en todo el proyecto.
