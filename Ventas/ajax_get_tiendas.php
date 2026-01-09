<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) { echo json_encode([]); exit; }
$cn->set_charset("utf8mb4");

$cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
$q = $_GET['q'] ?? '';

// Si no hay cliente padre, no devolvemos tiendas
if ($cliente_id === 0) {
    echo json_encode([]);
    exit;
}

$sql = $cn->prepare("SELECT id, nombre FROM tiendas WHERE cliente_id = ? AND nombre LIKE CONCAT('%',?,'%') ORDER BY nombre LIMIT 50");
$sql->bind_param("is", $cliente_id, $q);
$sql->execute();
$res = $sql->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = ['id' => (int)$r['id'], 'text' => $r['nombre']];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>