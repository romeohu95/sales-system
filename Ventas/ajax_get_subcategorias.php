<?php
header('Content-Type: application/json');
$user_cpanel = "aputecnologias"; $server="localhost"; $user=$user_cpanel."_ventas_user"; $password="u~77(+79&LB."; $database=$user_cpanel."_ventas_db";
$cn = new mysqli($server,$user,$password,$database); $cn->set_charset("utf8mb4");

$cat = $_GET['categoria_id'] ?? 0;
$q = $_GET['q'] ?? '';
$sql = $cn->prepare("SELECT id, nombre FROM subcategorias WHERE categoria_id = ? AND nombre LIKE CONCAT('%',?,'%') ORDER BY nombre LIMIT 50");
$sql->bind_param("is",$cat,$q);
$sql->execute();
$res = $sql->get_result();
$out=[];
while($r=$res->fetch_assoc()) $out[]=['id'=>$r['id'],'text'=>$r['nombre']];
echo json_encode($out);