<?php
session_start();
require_once('inc/config.php');

// Verificar si existe una cookie de sesión
if(!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    try {
        $token = $_COOKIE['remember_token'];
        $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE remember_token = ? AND estado = ?");
        $statement->execute(array($token, 'Activo'));
        
        if($statement->rowCount() > 0) {
            $user = $statement->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user'] = array(
                'id_usuario' => $user['id_usuario'],
                'nombre_completo' => $user['nombre_completo'],
                'correo' => $user['correo'],
                'telefono' => $user['telefono'],
                'foto' => $user['foto'],
                'rol' => $user['rol'],
                'estado' => $user['estado']
            );
            header("Location: index.php");
            exit;
        }
    } catch(PDOException $e) {
        // Si hay error, simplemente continuamos con el login normal
    }
}

// Si ya está logueado, redirigir a index
if(isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if(isset($_POST['login'])) {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $remember = isset($_POST['remember']) ? true : false;
    
    try {
        $statement = $pdo->prepare("SELECT * FROM usuarios_admin WHERE correo=? AND estado=?");
        $statement->execute(array($correo,'Activo'));
        $total = $statement->rowCount();
        
        if($total > 0) {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach($result as $row) {
                // Verificar si la contraseña está hasheada o en texto plano
                if(password_verify($contrasena, $row['contrasena']) || $contrasena === $row['contrasena']) {
                    // Si la contraseña está en texto plano, actualizarla a hash
                    if($contrasena === $row['contrasena']) {
                        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                        $update_stmt = $pdo->prepare("UPDATE usuarios_admin SET contrasena = ? WHERE id_usuario = ?");
                        $update_stmt->execute([$hashed_password, $row['id_usuario']]);
                    }
                    
                    $_SESSION['user'] = array(
                        'id_usuario' => $row['id_usuario'],
                        'nombre_completo' => $row['nombre_completo'],
                        'correo' => $row['correo'],
                        'telefono' => $row['telefono'],
                        'foto' => $row['foto'],
                        'rol' => $row['rol'],
                        'estado' => $row['estado']
                    );

                    // Si el usuario marcó "Recordarme"
                    if($remember) {
                        // Generar token único
                        $token = bin2hex(random_bytes(32));
                        
                        // Guardar token en la base de datos
                        $update_stmt = $pdo->prepare("UPDATE usuarios_admin SET remember_token = ? WHERE id_usuario = ?");
                        $update_stmt->execute([$token, $row['id_usuario']]);
                        
                        // Crear cookie que expira en 30 días
                        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
                    }
                    
                    header("Location: index.php");
                    exit;
                }
            }
        }
        $error_message = "El correo electrónico o la contraseña son incorrectos";
    } catch(PDOException $e) {
        $error_message = "Error en la conexión: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Certificados</title>
    <link href="bootstrap-5.3.7-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 400px;
            padding: 2rem;
            text-align: center;
            background: #2a2b38;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .field {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5em;
            background-color: #1f2029;
            border-radius: 4px;
            padding: .8em 1em;
        }
        .input-icon {
            height: 1.2em;
            width: 1.2em;
            fill: #ffeba7;
        }
        .input-field {
            background: none;
            border: none;
            outline: none;
            width: 100%;
            color: #d3d3d3;
            font-size: 1rem;
        }
        .title {
            margin-bottom: 1.5rem;
            font-size: 1.8em;
            font-weight: 500;
            color: #f5f5f5;
        }
        .btn {
            margin: 1.5rem 0;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1em;
            text-transform: uppercase;
            padding: 0.8em 1.5em;
            background-color: #ffeba7;
            color: #5e6681;
            box-shadow: 0 8px 24px 0 rgb(255 235 167 / 20%);
            transition: all .3s ease-in-out;
            width: 100%;
        }
        .btn-link {
            color: #f5f5f5;
            display: block;
            font-size: .9em;
            transition: color .3s ease-out;
            text-decoration: none;
        }
        .field input:focus::placeholder {
            opacity: 0;
            transition: opacity .3s;
        }
        .btn:hover {
            background-color: #5e6681;
            color: #ffeba7;
            box-shadow: 0 8px 24px 0 rgb(16 39 112 / 20%);
        }
        .btn-link:hover {
            color: #ffeba7;
        }
        .alert {
            background-color: #ff4444;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .remember-me {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem 0;
            color: #f5f5f5;
        }
        .remember-me input[type="checkbox"] {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h4 class="title">Iniciar Sesión</h4>
        <?php if(isset($error_message)): ?>
            <div class="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="field">
                <svg class="input-icon" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                    <path d="M207.8 20.73c-93.45 18.32-168.7 93.66-187 187.1c-27.64 140.9 68.65 266.2 199.1 285.1c19.01 2.888 36.17-12.26 36.17-31.49l.0001-.6631c0-15.74-11.44-28.88-26.84-31.24c-84.35-12.98-149.2-86.13-149.2-174.2c0-102.9 88.61-185.5 193.4-175.4c91.54 8.869 158.6 91.25 158.6 183.2l0 16.16c0 22.09-17.94 40.05-40 40.05s-40.01-17.96-40.01-40.05v-120.1c0-8.847-7.161-16.02-16.01-16.02l-31.98 .0036c-7.299 0-13.2 4.992-15.12 11.68c-24.85-12.15-54.24-16.38-86.06-5.106c-38.75 13.73-68.12 48.91-73.72 89.64c-9.483 69.01 43.81 128 110.9 128c26.44 0 50.43-9.544 69.59-24.88c24 31.3 65.23 48.69 109.4 37.49C465.2 369.3 496 324.1 495.1 277.2V256.3C495.1 107.1 361.2-9.332 207.8 20.73zM239.1 304.3c-26.47 0-48-21.56-48-48.05s21.53-48.05 48-48.05s48 21.56 48 48.05S266.5 304.3 239.1 304.3z"></path>
                </svg>
                <input autocomplete="off" id="correo" placeholder="Correo electrónico" class="input-field" name="correo" type="email" required>
            </div>
            <div class="field">
                <svg class="input-icon" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                    <path d="M80 192V144C80 64.47 144.5 0 224 0C303.5 0 368 64.47 368 144V192H384C419.3 192 448 220.7 448 256V448C448 483.3 419.3 512 384 512H64C28.65 512 0 483.3 0 448V256C0 220.7 28.65 192 64 192H80zM144 192H304V144C304 99.82 268.2 64 224 64C179.8 64 144 99.82 144 144V192z"></path>
                </svg>
                <input autocomplete="off" id="contrasena" placeholder="Contraseña" class="input-field" name="contrasena" type="password" required>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Recordarme</label>
            </div>
            <button class="btn" type="submit" name="login">Iniciar Sesión</button>
            <a href="#" class="btn-link">¿Olvidaste tu contraseña?</a>
        </form>
    </div>
    <script src="bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
