<?php
// ajax_fetch_wong.php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$url = trim($_REQUEST['url'] ?? '');

if (empty($url)) { echo json_encode(['ok' => false, 'error' => 'URL vacía']); exit; }
if (!preg_match("~^(?:f|ht)tps?://~i", $url)) { $url = "https://" . $url; }

// 1. Descargar HTML
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING       => "", 
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0
]);
$html = curl_exec($ch);
curl_close($ch);

if (!$html) { echo json_encode(['ok' => false, 'error' => 'Error de conexión']); exit; }

function limpiar($str) {
    return trim(strip_tags(html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

$description = '';
$brand = '';
$cat1 = ''; $cat2 = ''; $cat3 = '';
$image = '';

// =======================================================================
// 1. ESTRATEGIA DEFINITIVA: PATRÓN JSON VTEX (ENCONTRADO POR TI)
// =======================================================================
// Buscamos: "categories":{"type":"json","json":["\u002F..."]}
// Capturamos lo que está dentro de las primeras comillas del array "json"
if (preg_match('/"categories"\s*:\s*\{[^}]*"json"\s*:\s*\[\s*"([^"]+)"/i', $html, $m)) {
    // $m[1] contiene: \u002FAguas y Bebidas\u002F...
    
    // TRUCO: Decodificar el Unicode (\u002F -> /)
    // Usamos json_decode poniéndole comillas para que PHP interprete los escapes
    $fullPath = json_decode('"' . $m[1] . '"');
    
    // Si json_decode falla (devuelve null), usamos fallback manual
    if (!$fullPath) {
        $fullPath = str_replace('\u002F', '/', $m[1]);
    }

    // Ahora tenemos: /Aguas y Bebidas/Bebidas Regeneradoras/Rehidratantes/
    $parts = explode('/', trim($fullPath, '/'));
    
    if (isset($parts[0])) $cat1 = limpiar($parts[0]);
    if (isset($parts[1])) $cat2 = limpiar($parts[1]);
    if (isset($parts[2])) $cat3 = limpiar($parts[2]);
}


// =======================================================================
// RESTO DE EXTRACCIONES (YA FUNCIONABAN)
// =======================================================================

// DESCRIPCIÓN
if (preg_match('/"productName"\s*:\s*"([^"]+)"/', $html, $m)) {
    $description = limpiar($m[1]);
} elseif (preg_match('/<meta property="og:title" content="([^"]+)"/i', $html, $m)) {
    $description = limpiar($m[1]);
}
$description = str_replace([' - Wong.pe', ' | Wong'], '', $description);

// MARCA
if (preg_match('/"brand"\s*:\s*"([^"]+)"/', $html, $m)) {
    $brand = limpiar($m[1]);
} elseif (preg_match('/<meta property="product:brand" content="([^"]+)"/i', $html, $m)) {
    $brand = limpiar($m[1]);
}

// IMAGEN
if (preg_match('/ids\\\\?u002F(\d+)/', $html, $matches) || preg_match('/ids\/(\d+)/', $html, $matches)) {
    $id = $matches[1];
    $image = "https://wongfood.vtexassets.com/arquivos/ids/{$id}-1000-1000?width=1000&height=1000&aspect=true";
} elseif (preg_match('/"imageUrl"\s*:\s*"([^"]+)"/', $html, $m)) {
    $image = stripslashes($m[1]);
}

echo json_encode([
    'ok' => true,
    'description' => $description,
    'brand' => $brand,
    'cat1' => $cat1,
    'cat2' => $cat2,
    'cat3' => $cat3,
    'image' => $image
]);
?>