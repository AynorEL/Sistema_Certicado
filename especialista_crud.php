<?php
// Desactivar la salida de errores al navegador
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Definir la constante de la ruta raíz del proyecto si no está definida
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Asegurarnos de que siempre devolvamos JSON
header('Content-Type: application/json');

// Función para manejar errores de PHP
function handleError($errno, $errstr, $errfile, $errline) {
    $response = [
        'status' => 'error',
        'message' => 'Error en el servidor: ' . $errstr,
        'error_details' => [
            'type' => $errno,
            'file' => $errfile,
            'line' => $errline
        ]
    ];
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    echo json_encode($response);
    exit;
}

// Función para manejar excepciones no capturadas
function handleException($exception) {
    $response = [
        'status' => 'error',
        'message' => 'Error en el servidor: ' . $exception->getMessage(),
        'error_details' => [
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]
    ];
    error_log("PHP Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    echo json_encode($response);
    exit;
}

// Registrar manejadores de errores
set_error_handler('handleError');
set_exception_handler('handleException');

// Función para enviar respuesta JSON
function sendJsonResponse($status, $message, $data = null, $pagination = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if ($pagination !== null) {
        $response['pagination'] = $pagination;
    }
    
    echo json_encode($response);
    exit;
}

// Función para validar datos requeridos
function validateRequiredFields($fields, $data) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            return false;
        }
    }
    return true;
}

try {
    require_once ROOT_PATH . "/conexion/coneccion.php";
    
    // Incluir validadorsql.php si es necesario
    if (file_exists("../php/validadorsql.php")) {
        require_once "../php/validadorsql.php";
    }

    $conn = Conexion::getInstance();
    error_log("Conexión establecida correctamente");

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    error_log("Acción solicitada: " . $action);

    switch ($action) {
        case 'list':
            error_log("Iniciando listado de especialistas");
            // Parámetros de paginación y búsqueda
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15; // O el valor por defecto que uses
            $search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
            
            // Calcular el offset
            $offset = ($page - 1) * $per_page;
            
            // Construir la consulta base con LEFT JOIN para incluir todos los especialistas
            // Incluimos la columna de la firma y le damos un alias con la ruta completa para que coincida con JS
            // Incluimos el subdirectorio del proyecto en la URL
            $query = "SELECT e.*, CONCAT('/sistemacertificadosm/biblioteca/firmas/', e.firma_especialista) AS firma_especialista_url, c.nombre_cargo
                     FROM especialista e
                     LEFT JOIN cargo c ON e.idcargo = c.idcargo";
            
            // Agregar condición de búsqueda si existe
            $params = [];
            if (!empty($search_term)) {
                $query .= " WHERE e.nombres_especialista LIKE :search 
                           OR e.apellidos LIKE :search";
                $params[':search'] = "%$search_term%";
            }
            
            // Agregar ordenamiento y límites
            $query .= " ORDER BY e.idespecialista DESC LIMIT :limit OFFSET :offset"; // Ordenar por ID o nombre según preferencia
            
            // Preparar y ejecutar la consulta principal
            $stmt = $conn->prepare($query);
            
            // Bindear parámetros de búsqueda si existen
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            // Bindear parámetros de paginación
            $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de lista: " . implode(" ", $stmt->errorInfo()));
            }
            
            // Obtener los datos
            $especialistas = $stmt->fetchAll();
            
            // Obtener el total de registros para la paginación
            $count_query = "SELECT COUNT(*) as total FROM especialista e"; // Contar solo especialistas
             // Agregar condición de búsqueda si existe (debe ser la misma que la principal)
            if (!empty($search_term)) {
                $count_query .= " WHERE e.nombres_especialista LIKE :search 
                                OR e.apellidos LIKE :search";
            }

            $count_stmt = $conn->prepare($count_query);
             // Bindear parámetros de búsqueda si existen (debe ser la misma que la principal)
            if (!empty($search_term)) {
                 $count_stmt->bindValue(':search', "%$search_term%");
            }

            
            if (!$count_stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de conteo: " . implode(" ", $count_stmt->errorInfo()));
            }
            
            $total = $count_stmt->fetch()['total'];
            
            // Calcular información de paginación
            $total_pages = ceil($total / $per_page);
            
            $pagination = [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_entries' => $total,
                'total_pages' => $total_pages
            ];
            
            echo json_encode([
                'status' => 'success',
                'data' => $especialistas,
                'pagination' => $pagination
            ]);
            break;

        case 'get':
             if (!isset($_GET['id'])) {
                throw new Exception('ID no proporcionado');
            }
            
            $stmt = $conn->prepare("SELECT e.*, c.nombre_cargo 
                                  FROM especialista e 
                                  LEFT JOIN cargo c ON e.idcargo = c.idcargo 
                                  WHERE e.idespecialista = :id");
            $stmt->execute([':id' => $_GET['id']]);
            $especialista = $stmt->fetch();
            
            if ($especialista) {
                echo json_encode(['status' => 'success', 'data' => $especialista]);
            } else {
                throw new Exception('Especialista no encontrado');
            }
            
            break;

        case 'save':
            if (!isset($_POST['nombres_especialista']) || !isset($_POST['apellidos']) || !isset($_POST['cargo'])) {
                throw new Exception('Faltan campos requeridos');
            }
            
            $id = isset($_POST['idespecialista']) ? (int)$_POST['idespecialista'] : 0;
            $data = [
                ':nombres' => $_POST['nombres_especialista'],
                ':apellidos' => $_POST['apellidos'],
                ':cargo' => $_POST['cargo']
            ];

            $firma_path = null; // Inicializamos la ruta de la firma como nula

            // Manejo de la subida de archivo de firma
            if (isset($_FILES['firma_especialista']) && $_FILES['firma_especialista']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['firma_especialista']['tmp_name'];
                $fileName = $_FILES['firma_especialista']['name'];
                $fileSize = $_FILES['firma_especialista']['size'];
                $fileType = $_FILES['firma_especialista']['type'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // Directorio donde se guardarán las firmas (ajusta según tu estructura)
                $uploadFileDir = ROOT_PATH . '/biblioteca/firmas/';
                $allowedfileExtensions = array('jpg', 'jpeg', 'png');

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    // Generar un nombre de archivo único
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadFileDir . $newFileName;

                    // Asegurarse de que el directorio de subida existe
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $firma_path = $newFileName; // Guardamos solo el nombre del archivo
                        error_log("Archivo de firma subido con éxito: " . $newFileName);
                    } else {
                        throw new Exception('Error al mover el archivo subido.');
                    }
                } else {
                    throw new Exception('Tipo de archivo de firma no permitido. Solo JPG, JPEG, PNG.');
                }
            }

            // Preparar la consulta SQL
            if ($id > 0) {
                // Actualizar
                $query = "UPDATE especialista
                          SET nombres_especialista = :nombres,
                              apellidos = :apellidos,
                              idcargo = :cargo";
                if ($firma_path) {
                    $query .= ", firma_especialista = :firma_path"; // Añadir campo firma si se subió un archivo
                    $data[':firma_path'] = $firma_path;
                }
                $query .= " WHERE idespecialista = :id";
                $data[':id'] = $id;
                $stmt = $conn->prepare($query);

            } else {
                // Insertar
                 $query = "INSERT INTO especialista
                                      (nombres_especialista, apellidos, idcargo";
                 if ($firma_path) {
                     $query .= ", firma_especialista"; // Añadir campo firma si se subió un archivo
                 }
                 $query .= ") VALUES (:nombres, :apellidos, :cargo";
                 if ($firma_path) {
                      $query .= ", :firma_path"; // Añadir valor de firma si se subió un archivo
                     $data[':firma_path'] = $firma_path;
                 }
                 $query .= ")";
                 $stmt = $conn->prepare($query);
            }

            if ($stmt->execute($data)) {
                echo json_encode(['status' => 'success', 'message' => 'Operación exitosa']);
            } else {
                // Si falla la ejecución, intentamos eliminar el archivo subido si existe
                if ($firma_path && file_exists($dest_path)) {
                    unlink($dest_path);
                    error_log("Eliminado archivo de firma después de fallo en DB: " . $dest_path);
                }
                throw new Exception('Error al guardar en la base de datos: ' . implode(" ", $stmt->errorInfo()));
            }
            break;

        case 'delete':
             if (!isset($_POST['idespecialista'])) {
                throw new Exception('ID no proporcionado');
            }
            
            // Obtenemos el ID del especialista a eliminar
            $id_especialista_to_delete = $_POST['idespecialista'];

            // Opcional: Si quieres eliminar el archivo de firma al eliminar el especialista
            // Primero, obtenemos el nombre del archivo de firma asociado
            $stmt_firma = $conn->prepare("SELECT firma_especialista FROM especialista WHERE idespecialista = :id");
            $stmt_firma->execute([':id' => $id_especialista_to_delete]);
            $firma_info = $stmt_firma->fetch(PDO::FETCH_ASSOC);

            if ($firma_info && $firma_info['firma_especialista']) {
                $file_to_delete = ROOT_PATH . '/biblioteca/firmas/' . $firma_info['firma_especialista'];
                if (file_exists($file_to_delete)) {
                    if (unlink($file_to_delete)) {
                        error_log("Archivo de firma eliminado con éxito: " . $file_to_delete);
                    } else {
                        error_log("Error al eliminar archivo de firma: " . $file_to_delete);
                    }
                } else {
                    error_log("Archivo de firma no encontrado para eliminar: " . $file_to_delete);
                }
            }

            // Ahora eliminamos el registro de la base de datos
            $stmt = $conn->prepare("DELETE FROM especialista WHERE idespecialista = :id");
            if ($stmt->execute([':id' => $id_especialista_to_delete])) {
                echo json_encode(['status' => 'success', 'message' => 'Eliminado exitosamente']);
            } else {
                throw new Exception('Error al eliminar de la base de datos: ' . implode(" ", $stmt->errorInfo()));
            }
            break;

        case 'getCargos':
            try {
                error_log("Iniciando obtención de cargos");
                // Verificar si la tabla existe
                $check_table = $conn->query("SHOW TABLES LIKE 'cargo'");
                if ($check_table->rowCount() === 0) {
                    throw new Exception("La tabla 'cargo' no existe en la base de datos");
                }
                
                // Verificar la estructura de la tabla
                $check_columns = $conn->query("SHOW COLUMNS FROM cargo");
                $columns = $check_columns->fetchAll(PDO::FETCH_COLUMN);
                error_log("Columnas de la tabla cargo: " . implode(", ", $columns));
                
                // Obtener los cargos
                $stmt = $conn->query("SELECT idcargo, nombre_cargo FROM cargo ORDER BY nombre_cargo");
                if (!$stmt) {
                    error_log("Error en la consulta de cargos: " . implode(" ", $conn->errorInfo()));
                    throw new Exception("Error al preparar la consulta de cargos");
                }
                $cargos = $stmt->fetchAll();
                error_log("Cargos obtenidos: " . count($cargos));
                
                if (count($cargos) === 0) {
                    error_log("No se encontraron cargos en la tabla");
                }
                
                echo json_encode(['status' => 'success', 'data' => $cargos]);
            } catch (Exception $e) {
                error_log("Error en getCargos: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Error al cargar la lista de cargos: ' . $e->getMessage()]);
            }
            break;

        default:
            throw new Exception('Acción no válida');
    }

} catch (Exception $e) {
    error_log("Especialista CRUD Exception: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 