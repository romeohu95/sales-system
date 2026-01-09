<?php
error_reporting(0); // IMPORTANTE: Evita errores de PHP que rompen el JSON
header('Content-Type: application/json; charset=utf-8');

$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) { echo json_encode([]); exit; }
$cn->set_charset("utf8mb4");

$q = $_GET['q'] ?? '';

// Buscar clientes
$sql = $cn->prepare("SELECT id, nombre FROM clientes WHERE nombre LIKE CONCAT('%',?,'%') ORDER BY nombre LIMIT 50");
$sql->bind_param("s", $q);
$sql->execute();
$res = $sql->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = ['id' => (int)$r['id'], 'text' => $r['nombre']];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>