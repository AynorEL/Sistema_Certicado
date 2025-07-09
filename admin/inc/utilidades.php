<?php
if (!function_exists('convertirImagenABase64')) {
    function convertirImagenABase64($ruta_imagen) {
        if (!file_exists($ruta_imagen)) {
            return false;
        }
        $tipo_mime = mime_content_type($ruta_imagen);
        $datos_imagen = file_get_contents($ruta_imagen);
        if ($datos_imagen === false) {
            return false;
        }
        return 'data:' . $tipo_mime . ';base64,' . base64_encode($datos_imagen);
    }
} 