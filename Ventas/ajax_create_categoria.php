<?php
header('Content-Type: application/json');
$user_cpanel = "aputecnologias"; $server="localhost"; $user=$user_cpanel."_ventas_user"; $password="u~77(+79&LB."; $database=$user_cpanel."_ventas_db";
$cn = new mysqli($server,$user,$password,$database); $cn->set_charset("utf8mb4");

$nombre = trim($_POST['nombre'] ?? '');
if ($nombre === '') { echo json_encode(['error'=>'nombre vacío']); exit; }

// evitar duplicados (retornar id si existe)
$stmt = $cn->prepare("SELECT id FROM categorias WHERE nombre = ? LIMIT 1");
$stmt->bind_param("s",$nombre); $stmt->execute(); $r = $stmt->get_result();
if ($row = $r->fetch_assoc()) { echo json_encode(['id'=>$row['id'],'text'=>$nombre]); exit; }

$ins = $cn->prepare("INSERT INTO categorias(nombre) VALUES(?)");
$ins->bind_param("s",$nombre); $ins->execute();
$id = $cn->insert_id;
echo json_encode(['id'=>$id,'text'=>$nombre]);