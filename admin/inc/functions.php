<?php
// Funciones de manejo de archivos
function get_ext($pdo, $fname) {
	$up_filename = $_FILES[$fname]["name"];
	$file_basename = substr($up_filename, 0, strripos($up_filename, '.')); // strip extention
	$file_ext = substr($up_filename, strripos($up_filename, '.')); // strip name
	return $file_ext;
}

function ext_check($pdo, $allowed_ext, $my_ext) {
	$arr1 = array();
	$arr1 = explode("|", $allowed_ext);	
	$count_arr1 = count(explode("|", $allowed_ext));	

	for($i=0; $i<$count_arr1; $i++) {
		$arr1[$i] = '.'.$arr1[$i];
	}
	
	$str = '';
	$stat = 0;
	for($i=0; $i<$count_arr1; $i++) {
		if($my_ext == $arr1[$i]) {
			$stat = 1;
			break;
		}
	}

	return ($stat == 1) ? true : false;
}

function get_ai_id($pdo, $tbl_name) 
{
	$statement = $pdo->prepare("SHOW TABLE STATUS LIKE '$tbl_name'");
	$statement->execute();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as $row)
	{
		$next_id = $row['Auto_increment'];
	}
	return $next_id;
}

// Funciones de seguridad y validación
function clean($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

// Función para verificar si un archivo es una imagen válida
function is_valid_image($file) {
	$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
	return in_array($file['type'], $allowed_types);
}

// Función para subir una imagen
function upload_image($file, $target_dir) {
	if (!is_valid_image($file)) {
		return false;
	}

	$target_file = $target_dir . basename($file["name"]);
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
	$new_filename = uniqid() . '.' . $imageFileType;
	$target_file = $target_dir . $new_filename;

	if (move_uploaded_file($file["tmp_name"], $target_file)) {
		return $new_filename;
	}
	return false;
}

// Funciones de interfaz de usuario
function show_error($message) {
	return '<div class="alert alert-danger">' . $message . '</div>';
}

function show_success($message) {
	return '<div class="alert alert-success">' . $message . '</div>';
}

// Funciones de autenticación y redirección
function is_authenticated() {
	return isset($_SESSION['user']);
}

function redirect($url) {
	header("Location: $url");
	exit;
}

/**
 * Validar si se puede eliminar un cargo
 */
function validarEliminacionCargo($pdo, $idcargo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleado WHERE idcargo = ?");
    $stmt->execute([$idcargo]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este cargo porque hay {$count} empleado(s) que lo están utilizando. Por favor, reasigna o elimina esos empleados primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un cliente
 */
function validarEliminacionCliente($pdo, $idcliente) {
    $errores = [];
    
    // Verificar certificados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificado_generado WHERE idcliente = ?");
    $stmt->execute([$idcliente]);
    $certificados = $stmt->fetchColumn();
    if ($certificados > 0) {
        $errores[] = "{$certificados} certificado(s) generado(s)";
    }
    
    // Verificar inscripciones
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscripcion WHERE idcliente = ?");
    $stmt->execute([$idcliente]);
    $inscripciones = $stmt->fetchColumn();
    if ($inscripciones > 0) {
        $errores[] = "{$inscripciones} inscripción(es) activa(s)";
    }
    
    // Verificar usuarios
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE idcliente = ?");
    $stmt->execute([$idcliente]);
    $usuarios = $stmt->fetchColumn();
    if ($usuarios > 0) {
        $errores[] = "{$usuarios} cuenta(s) de usuario";
    }
    
    if (!empty($errores)) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este cliente porque tiene: " . implode(', ', $errores) . ". Por favor, elimina estos registros primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un curso
 */
function validarEliminacionCurso($pdo, $idcurso) {
    $errores = [];
    
    // Verificar certificados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificado_generado WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $certificados = $stmt->fetchColumn();
    if ($certificados > 0) {
        $errores[] = "{$certificados} certificado(s) generado(s)";
    }
    
    // Verificar inscripciones
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscripcion WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $inscripciones = $stmt->fetchColumn();
    if ($inscripciones > 0) {
        $errores[] = "{$inscripciones} inscripción(es) activa(s)";
    }
    
    // Verificar horas lectivas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM hora_lectiva WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $horas = $stmt->fetchColumn();
    if ($horas > 0) {
        $errores[] = "{$horas} hora(s) lectiva(s)";
    }
    
    // Verificar módulos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modulo WHERE idcurso = ?");
    $stmt->execute([$idcurso]);
    $modulos = $stmt->fetchColumn();
    if ($modulos > 0) {
        $errores[] = "{$modulos} módulo(s)";
    }
    
    if (!empty($errores)) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este curso porque tiene: " . implode(', ', $errores) . ". Por favor, elimina estos registros primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar una categoría
 */
function validarEliminacionCategoria($pdo, $idcategoria) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM curso WHERE idcategoria = ?");
    $stmt->execute([$idcategoria]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar esta categoría porque hay {$count} curso(s) que la están utilizando. Por favor, reasigna o elimina esos cursos primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un instructor
 */
function validarEliminacionInstructor($pdo, $idinstructor) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM curso WHERE idinstructor = ?");
    $stmt->execute([$idinstructor]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este instructor porque hay {$count} curso(s) que lo están utilizando. Por favor, reasigna o elimina esos cursos primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un especialista
 */
function validarEliminacionEspecialista($pdo, $idespecialista) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM curso WHERE idespecialista = ?");
    $stmt->execute([$idespecialista]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este especialista porque hay {$count} curso(s) que lo están utilizando. Por favor, reasigna o elimina esos cursos primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un rol
 */
function validarEliminacionRol($pdo, $idrol) {
    $errores = [];
    
    // Verificar usuarios
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE idrol = ?");
    $stmt->execute([$idrol]);
    $usuarios = $stmt->fetchColumn();
    if ($usuarios > 0) {
        $errores[] = "{$usuarios} usuario(s)";
    }
    
    // Verificar módulos_rol
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modulo_rol WHERE idrol = ?");
    $stmt->execute([$idrol]);
    $modulos = $stmt->fetchColumn();
    if ($modulos > 0) {
        $errores[] = "{$modulos} módulo(s) asignado(s)";
    }
    
    if (!empty($errores)) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este rol porque tiene: " . implode(', ', $errores) . ". Por favor, reasigna o elimina estos registros primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un módulo
 */
function validarEliminacionModulo($pdo, $idmodulo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modulo_rol WHERE idmodulo = ?");
    $stmt->execute([$idmodulo]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este módulo porque tiene {$count} rol(es) asignado(s). Por favor, elimina esas asignaciones primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar una inscripción
 */
function validarEliminacionInscripcion($pdo, $idinscripcion) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pago WHERE idinscripcion = ?");
    $stmt->execute([$idinscripcion]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar esta inscripción porque tiene {$count} pago(s) asociado(s). Por favor, elimina esos pagos primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un admin
 */
function validarEliminacionAdmin($pdo, $idadmin) {
    // Verificar que no se elimine el admin actual
    if (isset($_SESSION['user']['id_usuario']) && $_SESSION['user']['id_usuario'] == $idadmin) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No puedes eliminar tu propia cuenta de administrador. Usa otra cuenta para eliminar este admin."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un empleado
 */
function validarEliminacionEmpleado($pdo, $idempleado) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE idempleado = ?");
    $stmt->execute([$idempleado]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este empleado porque tiene {$count} cuenta(s) de usuario asociada(s). Por favor, elimina esas cuentas primero."
        ];
    }
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar una entidad
 */
function validarEliminacionEntidad($pdo, $identidad) {
    // Según la estructura de la BD, no hay relación directa entre entidad y cliente
    // Si en el futuro se agrega una columna identidad a cliente, se puede descomentar:
    
    /*
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cliente WHERE identidad = ?");
    $stmt->execute([$identidad]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar esta entidad porque hay {$count} cliente(s) que la están utilizando. Por favor, reasigna o elimina esos clientes primero."
        ];
    }
    */
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un género
 */
function validarEliminacionGenero($pdo, $idgenero) {
    // Por ahora, los géneros se pueden eliminar sin problemas
    // ya que no hay relación implementada en la tabla cliente
    // Si en el futuro se agrega la columna idgenero a cliente, 
    // se puede descomentar el código siguiente:
    
    /*
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cliente WHERE idgenero = ?");
    $stmt->execute([$idgenero]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ No se puede eliminar este género porque hay {$count} cliente(s) que lo están utilizando. Por favor, reasigna o elimina esos clientes primero."
        ];
    }
    */
    
    return ['puede_eliminar' => true];
}

/**
 * Validar si se puede eliminar un pago
 */
function validarEliminacionPago($pdo, $idpago) {
    // Los pagos generalmente se pueden eliminar sin problemas
    // Pero verificamos que exista
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pago WHERE idpago = ?");
    $stmt->execute([$idpago]);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        return [
            'puede_eliminar' => false,
            'mensaje' => "⚠️ El pago no existe o ya fue eliminado."
        ];
    }
    
    return ['puede_eliminar' => true];
}
?>