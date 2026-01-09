<?php
header('Content-Type: application/json; charset=utf-8');
$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) { echo json_encode(['error'=>'DB connection']); exit; }
$cn->set_charset("utf8mb4");

$nombre = trim($_POST['nombre'] ?? '');
if ($nombre === '') { echo json_encode(['error'=>'Nombre vacío']); exit; }

// evitar duplicados (retornar id si existe)
$st = $cn->prepare("SELECT id, nombre FROM marcas WHERE nombre = ? LIMIT 1");
$st->bind_param("s", $nombre);
$st->execute();
$r = $st->get_result();
if ($row = $r->fetch_assoc()) {
    echo json_encode(['id' => (int)$row['id'], 'nombre' => $row['nombre']], JSON_UNESCAPED_UNICODE);
    exit;
}

$ins = $cn->prepare("INSERT INTO marcas (nombre) VALUES (?)");
$ins->bind_param("s", $nombre);
$ins->execute();
if ($ins->affected_rows > 0) {
    $id = $cn->insert_id;
    echo json_encode(['id' => (int)$id, 'nombre' => $nombre], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['error' => 'No se pudo crear marca']);
}