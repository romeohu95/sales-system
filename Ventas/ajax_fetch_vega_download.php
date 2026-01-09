<?php
// ajax_fetch_vega_download.php
header('Content-Type: application/json; charset=utf-8');

$imageUrl = $_POST['image_url'] ?? '';

if (empty($imageUrl)) {
    echo json_encode(['ok' => false, 'error' => 'No se recibió URL de imagen']);
    exit;
}

// 1. LISTA BLANCA DE DOMINIOS PERMITIDOS
// Aquí agregamos 'wongfood.vtexassets.com' que es donde Wong guarda las fotos
$allowedHosts = [
    'vega.pe',
    'www.vega.pe',
    'wong.pe',
    'www.wong.pe',
    'wongfood.vtexassets.com', // <--- ESTE ES EL QUE FALTABA
    'vtexassets.com'           // Por si acaso usan otro subdominio
];

$parsedUrl = parse_url($imageUrl);
$host = strtolower($parsedUrl['host'] ?? '');

$isAllowed = false;
foreach ($allowedHosts as $allowed) {
    // Verificamos si el host coincide o termina en el dominio permitido
    if ($host === $allowed || substr($host, -strlen($allowed)) === $allowed) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    echo json_encode(['ok' => false, 'error' => 'Dominio no permitido: ' . $host]);
    exit;
}

// 2. PREPARAR CARPETA Y NOMBRE
$uploadDir = 'uploads/'; // Asegúrate de que esta carpeta exista y tenga permisos
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generar nombre único basado en la URL para no duplicar descargas
$ext = pathinfo($parsedUrl['path'], PATHINFO_EXTENSION);
if (!$ext || strlen($ext) > 4) $ext = 'jpg'; // Default a jpg si no detecta extensión

// Limpiar la extensión de posibles parámetros (ej: jpg?v=123)
$ext = explode('?', $ext)[0];

$filename = 'remote_' . md5($imageUrl) . '.' . $ext;
$filepath = $uploadDir . $filename;

// 3. DESCARGAR LA IMAGEN
$ch = curl_init($imageUrl);
$fp = fopen($filepath, 'wb');

curl_setopt_array($ch, [
    CURLOPT_FILE => $fp,
    CURLOPT_HEADER => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0
]);

curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
fclose($fp);

// 4. VERIFICAR RESULTADO
if ($httpCode == 200 && file_exists($filepath) && filesize($filepath) > 0) {
    echo json_encode(['ok' => true, 'path' => $filepath]);
} else {
    // Si falló, borramos el archivo vacío
    if (file_exists($filepath)) unlink($filepath);
    echo json_encode(['ok' => false, 'error' => 'Error al descargar (HTTP ' . $httpCode . '): ' . $error]);
}
?>