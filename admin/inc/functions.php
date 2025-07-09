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
?>