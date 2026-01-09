<?php
header("Content-Type: application/json; charset=utf-8");
$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) {
    echo json_encode(["encontrado" => false, "error" => $cn->connect_error]);
    exit;
}
$cn->set_charset("utf8mb4");

$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
if ($codigo === '') { echo json_encode(["encontrado" => false]); exit; }

// Detectar si la columna marca_id existe en productos
$hasMarcaId = false;
$resCols = $cn->query("SHOW COLUMNS FROM productos LIKE 'marca_id'");
if ($resCols && $resCols->num_rows > 0) $hasMarcaId = true;

// Preparar SELECT dinámico según exista o no marca_id
if ($hasMarcaId) {
    $sql = "SELECT id, foto1, descripcion, marca_id, categoria_n1_id, categoria_n2_id, categoria_n3_id FROM productos WHERE barcode = ? LIMIT 1";
} else {
    $sql = "SELECT id, foto1, descripcion, marca, categoria_n1_id, categoria_n2_id, categoria_n3_id FROM productos WHERE barcode = ? LIMIT 1";
}

$stmt = $cn->prepare($sql);
if (!$stmt) { echo json_encode(["encontrado" => false, "error" => "Error en prepare: " . $cn->error]); exit; }
$stmt->bind_param("s", $codigo);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo json_encode(["encontrado" => false]); exit; }

$row = $res->fetch_assoc();
$producto_id = (int)$row['id'];
$foto1 = $row['foto1'] ?? null;
$descripcion = $row['descripcion'] ?? null;
$cat1 = $row['categoria_n1_id'] ?? null;
$cat2 = $row['categoria_n2_id'] ?? null;
$cat3 = $row['categoria_n3_id'] ?? null;

$marca_id = null;
$marca_text = null;
$marca_name = null;

// Si existe marca_id, obtener nombre desde tabla marcas (si existe)
if ($hasMarcaId) {
    $marca_id = $row['marca_id'] !== null ? (int)$row['marca_id'] : null;
    // comprobar existencia de tabla marcas
    $hasMarcasTable = false;
    $rt = $cn->query("SHOW TABLES LIKE 'marcas'");
    if ($rt && $rt->num_rows > 0) $hasMarcasTable = true;

    if ($marca_id && $hasMarcasTable) {
        $s = $cn->prepare("SELECT nombre FROM marcas WHERE id = ? LIMIT 1");
        if ($s) {
            $s->bind_param("i", $marca_id);
            $s->execute();
            $r = $s->get_result();
            if ($ro = $r->fetch_assoc()) {
                $marca_name = $ro['nombre'];
                $marca_text = $marca_name; // compatibilidad
            }
            $s->close();
        }
    } else {
        // si no hay marcas table o marca_id es null, intentar buscar campo texto 'marca' en row (si existe)
        if (isset($row['marca'])) $marca_text = $row['marca'];
    }
} else {
    // esquema antiguo: marca es texto
    $marca_text = $row['marca'] ?? null;
    $marca_name = $marca_text;
}

// obtener nombres de categorias si existen
$cat1_name = $cat2_name = $cat3_name = null;
if ($cat1) {
  $s = $cn->prepare("SELECT nombre FROM categorias WHERE id=?"); $s->bind_param("i",$cat1); $s->execute(); $r=$s->get_result(); if($ro=$r->fetch_assoc()) $cat1_name = $ro['nombre']; $s->close();
}
if ($cat2) {
  $s = $cn->prepare("SELECT nombre FROM subcategorias WHERE id=?"); $s->bind_param("i",$cat2); $s->execute(); $r=$s->get_result(); if($ro=$r->fetch_assoc()) $cat2_name = $ro['nombre']; $s->close();
}
if ($cat3) {
  $s = $cn->prepare("SELECT nombre FROM subcategorias_especificas WHERE id=?"); $s->bind_param("i",$cat3); $s->execute(); $r=$s->get_result(); if($ro=$r->fetch_assoc()) $cat3_name = $ro['nombre']; $s->close();
}

// buscar ultimo kardex
$sql2 = "SELECT stock, precio_unitario, precio_paquete, precio_caja_saco, fecha_vencimiento, costo_compra
         FROM kardex WHERE producto_id = ? ORDER BY fecha_ingreso DESC, id DESC LIMIT 1";
$stmt2 = $cn->prepare($sql2);
if ($stmt2) {
    $stmt2->bind_param("i", $producto_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $k = $res2->fetch_assoc();

} else {
    $k = [];
}

echo json_encode([
  "encontrado" => true,
  "foto1" => $foto1,
  "descripcion" => $descripcion,
  // Para compatibilidad con frontend: retorno marca_id (si aplica), marca_name y marca (texto)
  "marca_id" => $marca_id,
  "marca_name" => $marca_name,
  "marca" => $marca_text,
  "stock" => $k['stock'] ?? 0,
  "precio_unitario" => $k['precio_unitario'] ?? "",
  "precio_paquete" => $k['precio_paquete'] ?? "",
  "precio_caja_saco" => $k['precio_caja_saco'] ?? "",
  "fecha_vencimiento" => $k['fecha_vencimiento'] ?? "",
  "costo_compra" => $k['costo_compra'] ?? "",
  "categoria_n1_id" => $cat1,
  "categoria_n1_name" => $cat1_name,
  "categoria_n2_id" => $cat2,
  "categoria_n2_name" => $cat2_name,
  "categoria_n3_id" => $cat3,
  "categoria_n3_name" => $cat3_name
], JSON_UNESCAPED_UNICODE);