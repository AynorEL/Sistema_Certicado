<?php
require_once('../admin/inc/config.php');

// Verificar que la solicitud viene de PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Leer el POST
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

// POST back to PayPal system for validation
$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSLVERSION, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// Determinar si usar sandbox o producción
$config_statement = $pdo->prepare("SELECT paypal_sandbox FROM configuracion_pago WHERE id = 1");
$config_statement->execute();
$config = $config_statement->fetch(PDO::FETCH_ASSOC);

if ($config && $config['paypal_sandbox']) {
    curl_setopt($ch, CURLOPT_URL, 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
} else {
    curl_setopt($ch, CURLOPT_URL, 'https://ipnpb.paypal.com/cgi-bin/webscr');
}

$res = curl_exec($ch);

if (!$res) {
    error_log("PayPal IPN Error: " . curl_error($ch));
    curl_close($ch);
    exit;
}

$info = curl_getinfo($ch);
$http_code = $info['http_code'];
if ($http_code != 200) {
    error_log("PayPal IPN Error: HTTP code $http_code");
    curl_close($ch);
    exit;
}
curl_close($ch);

if (strcmp($res, "VERIFIED") == 0) {
    // El pago ha sido verificado por PayPal
    $payment_status = $_POST['payment_status'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $custom = $_POST['custom']; // Este es nuestro order_id
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Buscar la inscripción por el custom field
        $stmt = $pdo->prepare("SELECT i.*, p.idpago FROM inscripcion i 
                              LEFT JOIN pago p ON i.idinscripcion = p.idinscripcion 
                              WHERE i.idinscripcion = ?");
        $stmt->execute([$custom]);
        $inscripcion = $stmt->fetch();
        
        if ($inscripcion) {
            if ($payment_status == 'Completed') {
                // Actualizar estado de la inscripción
                $stmt = $pdo->prepare("UPDATE inscripcion SET 
                                      estado_pago = 'Pagado',
                                      fecha_actualizacion = NOW() 
                                      WHERE idinscripcion = ?");
                $stmt->execute([$custom]);
                
                // Actualizar o crear el pago
                if ($inscripcion['idpago']) {
                    $stmt = $pdo->prepare("UPDATE pago SET 
                                          estado = 'Completado',
                                          txn_id = ?,
                                          fecha_actualizacion = NOW() 
                                          WHERE idpago = ?");
                    $stmt->execute([$txn_id, $inscripcion['idpago']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO pago 
                                          (idinscripcion, monto, fecha_pago, metodo_pago, estado, txn_id) 
                                          VALUES (?, ?, NOW(), 'PayPal', 'Completado', ?)");
                    $stmt->execute([$custom, $payment_amount, $txn_id]);
                }
                
                // Enviar email de confirmación
                // Aquí puedes agregar el código para enviar email
                
            } elseif ($payment_status == 'Pending') {
                // Pago pendiente
                $stmt = $pdo->prepare("UPDATE inscripcion SET 
                                      estado_pago = 'Pendiente',
                                      fecha_actualizacion = NOW() 
                                      WHERE idinscripcion = ?");
                $stmt->execute([$custom]);
            }
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("PayPal IPN Database Error: " . $e->getMessage());
    }
    
} elseif (strcmp($res, "INVALID") == 0) {
    // Log para investigar
    error_log("PayPal IPN Invalid: " . $raw_post_data);
}
?> 