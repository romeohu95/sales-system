<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) { echo json_encode(['error'=>'Error DB']); exit; }
$cn->set_charset("utf8mb4");

$nombre = trim($_POST['nombre'] ?? '');
if ($nombre === '') { echo json_encode(['error'=>'Nombre vacío']); exit; }

// Verificar duplicados
$st = $cn->prepare("SELECT id, nombre FROM clientes WHERE nombre = ? LIMIT 1");
$st->bind_param("s", $nombre);
$st->execute();
$r = $st->get_result();
if ($row = $r->fetch_assoc()) {
    echo json_encode(['id' => (int)$row['id'], 'text' => $row['nombre']]);
    exit;
}

$ins = $cn->prepare("INSERT INTO clientes (nombre) VALUES (?)");
$ins->bind_param("s", $nombre);
$ins->execute();
$newId = $cn->insert_id;

echo json_encode(['id' => (int)$newId, 'text' => $nombre]);
?>