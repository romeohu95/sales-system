<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) { echo json_encode(['error'=>'Error de Conexión DB']); exit; }
$cn->set_charset("utf8mb4");

$nombre = trim($_POST['nombre'] ?? '');
$cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;

// Validación estricta: No crear tienda sin cliente padre
if ($nombre === '' || $cliente_id === 0) { 
    echo json_encode(['error' => 'Datos incompletos: Se requiere Nombre y Cliente ID']); 
    exit; 
}

// 1. Verificar si ya existe esa tienda PARA ESE CLIENTE
$st = $cn->prepare("SELECT id, nombre FROM tiendas WHERE cliente_id = ? AND nombre = ? LIMIT 1");
$st->bind_param("is", $cliente_id, $nombre);
$st->execute();
$r = $st->get_result();

if ($row = $r->fetch_assoc()) {
    // Ya existe, devolver la existente
    echo json_encode(['id' => (int)$row['id'], 'text' => $row['nombre']]);
    exit;
}

// 2. Insertar nueva tienda vinculada al cliente
$ins = $cn->prepare("INSERT INTO tiendas (cliente_id, nombre) VALUES (?, ?)");
$ins->bind_param("is", $cliente_id, $nombre);

if ($ins->execute()) {
    $newId = $cn->insert_id;
    echo json_encode(['id' => (int)$newId, 'text' => $nombre]);
} else {
    echo json_encode(['error' => 'Error al insertar en BD: ' . $ins->error]);
}
?>