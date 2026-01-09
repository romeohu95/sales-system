<?php
// ajax_fetch_vega.php (mejorado)
// Busca el primer producto en vega.pe para un código de barras y extrae image/description/brand.
// Uso: ajax_fetch_vega.php?barcode=7751271030694

header('Content-Type: application/json; charset=utf-8');

$barcode = trim($_GET['barcode'] ?? '');
if ($barcode === '') {
    echo json_encode(['ok' => false, 'error' => 'barcode vacío']);
    exit;
}

$baseHost = "https://www.vega.pe";
$searchUrl = $baseHost . '/' . rawurlencode($barcode) . '?_q=' . rawurlencode($barcode) . '&map=ft';

function http_get($url, $timeout = 20) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 6,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ],
        CURLOPT_ENCODING => '', // aceptar gzip
        CURLOPT_REFERER => $GLOBALS['baseHost']
    ]);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$body, $code, $err];
}

function normalize_url($href, $baseHost) {
    $href = trim($href);
    if ($href === '') return '';
    if (strpos($href, 'http') === 0) return $href;
    if (strpos($href, '//') === 0) return 'https:' . $href;
    if ($href[0] === '/') return rtrim($baseHost, '/') . $href;
    return rtrim($baseHost, '/') . '/' . ltrim($href, '/');
}

// ---------- obtener search HTML ----------
list($searchHtml, $searchCode, $searchErr) = http_get($searchUrl);
$debug = [
    'search_url' => $searchUrl,
    'http_code_search' => $searchCode,
    'curl_error_search' => $searchErr,
    'search_length' => $searchHtml !== false && $searchHtml !== null ? strlen($searchHtml) : 0
];

if ($searchHtml === false || $searchHtml === null || $searchCode >= 400) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo obtener la página de búsqueda', 'debug' => $debug], JSON_UNESCAPED_UNICODE);
    exit;
}

// parse HTML
libxml_use_internal_errors(true);
$doc = new DOMDocument();
@$doc->loadHTML($searchHtml);
$xpath = new DOMXPath($doc);

// 1) Intento directo: buscar el primer div[data-af-element="search-result"] y dentro el primer <a> con /p
$productHref = '';

// Buscar divs result
$divs = $xpath->query("//div[@data-af-element='search-result']");
if ($divs && $divs->length > 0) {
    // recorrer los divs en orden y buscar primer enlace a producto
    for ($i = 0; $i < $divs->length && $productHref === ''; $i++) {
        $div = $divs->item($i);
        // preferir <a> que contenga '/p' en href
        $links = (new DOMXPath($div->ownerDocument))->query(".//a[@href]", $div);
        foreach ($links as $a) {
            $href = $a->getAttribute('href');
            if (!$href) continue;
            $hrefNorm = normalize_url($href, $baseHost);
            $path = parse_url($hrefNorm, PHP_URL_PATH) ?: '';
            // criterio: path que contiene '/p' o '-p' (VTEX)
            if (stripos($path, '/p/') !== false || preg_match('#/[^/]*-p(?:/|$)#i', $path) || stripos($hrefNorm, '/p') !== false) {
                $productHref = $hrefNorm;
                break;
            }
        }
    }
}

// 2) Si no se obtuvo todavía: fallback anterior (anchors generales)
if (!$productHref) {
    $anchorCandidates = [];
    $anchors = $xpath->query("//a[@href]");
    foreach ($anchors as $a) {
        $href = $a->getAttribute('href');
        if (!$href) continue;
        $hrefNorm = normalize_url($href, $baseHost);
        $text = trim($a->textContent);
        $anchorCandidates[] = ['href' => $hrefNorm, 'text' => $text];
    }
    foreach ($anchorCandidates as $c) {
        $u = $c['href'];
        $path = parse_url($u, PHP_URL_PATH) ?: '';
        if (stripos($path, '/p/') !== false) { $productHref = $u; break; }
        if (preg_match('#/[^/]*-p(?:/|$)#i', $path) || preg_match('#/[^/]*-p-#i', $path)) { $productHref = $u; break; }
        if (stripos($u, '/p') !== false && strlen($path) > 4) { $productHref = $u; break; }
    }
    // strategy: regex inside HTML (scripts)
    if (!$productHref) {
        $matches = [];
        preg_match_all('#(\/[a-z0-9\-\_]+(?:\/[a-z0-9\-\_]+)*-p[^\s"\']*)#i', $searchHtml, $matches);
        if (!empty($matches[1])) {
            $productHref = normalize_url($matches[1][0], $baseHost);
        }
    }
    $debug['anchor_candidates_count'] = isset($anchorCandidates) ? count($anchorCandidates) : 0;
    $debug['anchor_candidates_sample'] = isset($anchorCandidates) ? array_slice($anchorCandidates, 0, 6) : [];
}

// Si aun no se tiene productHref devolver debug para inspección
if (!$productHref) {
    $debug['product_href_found_by_anchor'] = null;
    $debug['search_html_head'] = substr($searchHtml, 0, 1600);
    echo json_encode(['ok' => false, 'error' => 'No se encontró enlace de producto en resultados', 'debug' => $debug], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener página de producto
list($prodHtml, $prodCode, $prodErr) = http_get($productHref);
$debug['product_url'] = $productHref;
$debug['http_code_product'] = $prodCode;
$debug['curl_error_product'] = $prodErr;
$debug['product_length'] = $prodHtml !== false && $prodHtml !== null ? strlen($prodHtml) : 0;

if ($prodHtml === false || $prodHtml === null || $prodCode >= 400) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo obtener página de producto', 'debug' => $debug], JSON_UNESCAPED_UNICODE);
    exit;
}

// parse product page
$doc2 = new DOMDocument();
libxml_use_internal_errors(true);
@$doc2->loadHTML($prodHtml);
$xpath2 = new DOMXPath($doc2);

// description: prefer span with productBrand or h1
$description = '';
// Este selector busca la clase usada en el snippet: vtex-product-summary-2-x-productBrand (o productBrand)
$nodes = $xpath2->query("//span[contains(@class,'vtex-product-summary-2-x-productBrand') or contains(@class,'vtex-product-summary-2-x-productName') or contains(@class,'vtex-product-summary-2-x-productBrand')]");
if ($nodes && $nodes->length) {
    $description = trim($nodes->item(0)->textContent);
}
if (!$description) {
    $h1 = $xpath2->query("//h1");
    if ($h1 && $h1->length) $description = trim($h1->item(0)->textContent);
}

// brand
$brand = '';
$nodesB = $xpath2->query("//span[contains(@class,'vtex-product-summary-2-x-productBrandName') or contains(@class,'vtex-product-summary-2-x-brandName') or contains(@class,'productBrandName')]");
if ($nodesB && $nodesB->length) {
    $brand = trim($nodesB->item(0)->textContent);
}

// image: buscar la img que aparece dentro del div del resultado (mejor aún, buscar img dentro de .vtex-product-summary-2-x-imageContainer)
$imageUrl = '';
$imgNode = $xpath2->query("//div[contains(@class,'vtex-product-summary-2-x-imageContainer')]//img[1]");
if ($imgNode && $imgNode->length) {
    $img = $imgNode->item(0);
    $imageUrl = $img->getAttribute('src') ?: $img->getAttribute('data-src') ?: $img->getAttribute('data-original');
}
if (!$imageUrl) {
    // meta og:image
    $meta = $xpath2->query("//meta[@property='og:image' or @name='og:image']");
    if ($meta && $meta->length) $imageUrl = $meta->item(0)->getAttribute('content');
}
if (!$imageUrl) {
    // fallback primer img razonable
    $allImgs = $xpath2->query("//img[@src or @data-src]");
    for ($i=0; $i < $allImgs->length && !$imageUrl; $i++) {
        $img = $allImgs->item($i);
        $src = $img->getAttribute('src') ?: $img->getAttribute('data-src');
        if (!$src) continue;
        if (preg_match('#\.(svg|icon|sprite)#i', $src)) continue;
        if (preg_match('#(product|arquivos|images|gallery|vtexassets)#i', $src) || strlen($src) > 40) {
            $imageUrl = $src;
            break;
        }
    }
}

// normalizar image url
if ($imageUrl) {
    $imageUrl = trim($imageUrl);
    if (strpos($imageUrl, '//') === 0) $imageUrl = 'https:' . $imageUrl;
    if (strpos($imageUrl, 'http') !== 0) $imageUrl = rtrim($baseHost, '/') . '/' . ltrim($imageUrl, '/');
}

$found = ($description !== '' || $brand !== '' || $imageUrl !== '');
$response = [
    'ok' => true,
    'found' => $found,
    'product_url' => $productHref,
    'description' => $description,
    'brand' => $brand,
    'image' => $imageUrl,
    'debug' => $debug
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);