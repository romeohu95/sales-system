<?php
header('Content-Type: application/json');
$user_cpanel = "aputecnologias"; $server="localhost"; $user=$user_cpanel."_ventas_user"; $password="u~77(+79&LB."; $database=$user_cpanel."_ventas_db";
$cn = new mysqli($server,$user,$password,$database); $cn->set_charset("utf8mb4");

$nombre = trim($_POST['nombre'] ?? '');
$sub = intval($_POST['subcategoria_id'] ?? 0);
if ($nombre==='' || $sub==0) { echo json_encode(['error'=>'datos incompletos']); exit; }

// si existe devolver id
$stmt = $cn->prepare("SELECT id FROM subcategorias_especificas WHERE subcategoria_id = ? AND nombre = ? LIMIT 1");
$stmt->bind_param("is",$sub,$nombre); $stmt->execute(); $r = $stmt->get_result();
if ($row = $r->fetch_assoc()) { echo json_encode(['id'=>$row['id'],'text'=>$nombre]); exit; }

$ins = $cn->prepare("INSERT INTO subcategorias_especificas(subcategoria_id,nombre) VALUES(?,?)");
$ins->bind_param("is",$sub,$nombre); $ins->execute();
$id = $cn->insert_id;
echo json_encode(['id'=>$id,'text'=>$nombre]);