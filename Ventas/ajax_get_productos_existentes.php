<?php
header('Content-Type: application/json; charset=utf-8');

$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) {
    echo json_encode([]);
    exit;
}
$cn->set_charset("utf8mb4");

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode([]);
    exit;
}

// Buscar en productos_existentes por ITEM o NOMBRES
$like = "%{$q}%";

$sql = "
SELECT
  pe.id,
  pe.ITEM,
  pe.GRUPO,
  pe.NOMBRES,
  pe.COMPRA   AS pe_COMPRA,
  pe.CAJA_O_SACO AS pe_CAJA_O_SACO,
  pe.PAQUETE  AS pe_PAQUETE,
  pe.UNIDAD   AS pe_UNIDAD,
  p.id AS producto_id,
  p.barcode AS barcode,
  k.precio_unitario    AS kardex_precio_unitario,
  k.precio_paquete     AS kardex_precio_paquete,
  k.precio_caja_saco   AS kardex_precio_caja_saco,
  k.costo_compra       AS kardex_costo_compra
FROM productos_existentes pe
LEFT JOIN productos p ON p.barcode = pe.ITEM
LEFT JOIN (
    -- obtener último kardex por producto (por fecha_ingreso,id)
    SELECT k1.producto_id, k1.precio_unitario, k1.precio_paquete, k1.precio_caja_saco, k1.costo_compra
    FROM kardex k1
    JOIN (
        SELECT producto_id, MAX(CONCAT(DATE_FORMAT(fecha_ingreso, '%Y%m%d%H%i%S'), LPAD(id,10,'0'))) AS mx
        FROM kardex
        GROUP BY producto_id
    ) k2 ON k1.producto_id = k2.producto_id
    WHERE CONCAT(DATE_FORMAT(k1.fecha_ingreso, '%Y%m%d%H%i%S'), LPAD(k1.id,10,'0')) = k2.mx
) k ON k.producto_id = p.id
WHERE (pe.NOMBRES LIKE ? OR pe.ITEM LIKE ?)
ORDER BY pe.NOMBRES
LIMIT 50
";

$stmt = $cn->prepare($sql);
if ($stmt === false) {
    echo json_encode([]);
    exit;
}
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    // Prioridad para precios: si existe kardex (más actual), usar kardex; si no, usar valores de productos_existentes (pe_)
    $precio_unitario = $r['kardex_precio_unitario'] !== null ? $r['kardex_precio_unitario'] : $r['pe_UNIDAD'];
    $precio_paquete  = $r['kardex_precio_paquete'] !== null ? $r['kardex_precio_paquete'] : $r['pe_PAQUETE'];
    $precio_caja     = $r['kardex_precio_caja_saco'] !== null ? $r['kardex_precio_caja_saco'] : $r['pe_CAJA_O_SACO'];
    $costo_compra    = $r['kardex_costo_compra'] !== null ? $r['kardex_costo_compra'] : $r['pe_COMPRA'];

    $text = $r['NOMBRES'] ? $r['NOMBRES'] . ($r['ITEM'] ? " ({$r['ITEM']})" : '') : ($r['ITEM'] ?? 'Sin nombre');

    $out[] = [
        'id' => (int)$r['id'],
        'text' => $text,
        'ITEM' => $r['ITEM'],
        'GRUPO' => $r['GRUPO'],
        'NOMBRES' => $r['NOMBRES'],
        // Precios mapeados según tu petición:
        'COMPRA' => $costo_compra,           // costo_compra (kardex OR pe.COMPRA)
        'CAJA_O_SACO' => $precio_caja,       // precio_caja_saco (kardex OR pe.CAJA_O_SACO)
        'PAQUETE' => $precio_paquete,        // precio_paquete (kardex OR pe.PAQUETE)
        'UNIDAD' => $precio_unitario,        // precio_unitario (kardex OR pe.UNIDAD)
        // Referencias adicionales
        'producto_id' => $r['producto_id'] !== null ? (int)$r['producto_id'] : null,
        'barcode' => $r['barcode']
    ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);