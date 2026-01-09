<?php
// ---------------------------
// CONEXIÓN
// ---------------------------
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Evitar mostrar warnings en el HTML
$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";

$cn = new mysqli($server, $user, $password, $database);
if ($cn->connect_error) { die("Error BD: " . $cn->connect_error); }
$cn->set_charset("utf8mb4");

// ---------------------------
// HELPERS (Funciones Originales)
// ---------------------------
function ensure_categoria_n1($cn, $val){
    $val = trim($val); if ($val === '') return null;
    if (ctype_digit((string)$val)) return (int)$val;
    $st = $cn->prepare("SELECT id FROM categorias WHERE nombre = ? LIMIT 1");
    $st->bind_param("s",$val); $st->execute(); $r=$st->get_result();
    if ($row = $r->fetch_assoc()){ $st->close(); return (int)$row['id']; }
    $st->close();
    $i = $cn->prepare("INSERT INTO categorias(nombre) VALUES(?)");
    $i->bind_param("s",$val); $i->execute(); $id = $cn->insert_id; $i->close();
    return $id ? (int)$id : null;
}
function ensure_subcategoria($cn,$val,$categoria_id){
    $val = trim($val); if ($val === '' || !$categoria_id) return null;
    if (ctype_digit((string)$val)) return (int)$val;
    $st = $cn->prepare("SELECT id FROM subcategorias WHERE categoria_id=? AND nombre = ? LIMIT 1");
    $st->bind_param("is",$categoria_id,$val); $st->execute(); $r=$st->get_result();
    if ($row=$r->fetch_assoc()){ $st->close(); return (int)$row['id']; }
    $st->close();
    $i = $cn->prepare("INSERT INTO subcategorias(categoria_id,nombre) VALUES(?,?)");
    $i->bind_param("is",$categoria_id,$val); $i->execute(); $id = $cn->insert_id; $i->close();
    return $id ? (int)$id : null;
}
function ensure_subespecifica($cn,$val,$subcategoria_id){
    $val = trim($val); if ($val === '' || !$subcategoria_id) return null;
    if (ctype_digit((string)$val)) return (int)$val;
    $st = $cn->prepare("SELECT id FROM subcategorias_especificas WHERE subcategoria_id=? AND nombre=? LIMIT 1");
    $st->bind_param("is",$subcategoria_id,$val); $st->execute(); $r=$st->get_result();
    if ($row=$r->fetch_assoc()){ $st->close(); return (int)$row['id']; }
    $st->close();
    $i = $cn->prepare("INSERT INTO subcategorias_especificas(subcategoria_id,nombre) VALUES(?,?)");
    $i->bind_param("is",$subcategoria_id,$val); $i->execute(); $id = $cn->insert_id; $i->close();
    return $id ? (int)$id : null;
}
function has_table($cn, $table_name){
    $table_name = $cn->real_escape_string($table_name);
    $res = $cn->query("SHOW TABLES LIKE '{$table_name}'");
    return $res && $res->num_rows > 0;
}
function ensure_marca($cn, $val){
    $val = trim($val); if ($val === '') return null;
    if (ctype_digit((string)$val)) return (int)$val;
    $st = $cn->prepare("SELECT id FROM marcas WHERE nombre = ? LIMIT 1");
    $st->bind_param("s", $val); $st->execute(); $r = $st->get_result();
    if ($row = $r->fetch_assoc()){ $st->close(); return (int)$row['id']; }
    $st->close();
    $i = $cn->prepare("INSERT INTO marcas(nombre) VALUES(?)");
    $i->bind_param("s", $val); $i->execute(); $id = $cn->insert_id; $i->close();
    return $id ? (int)$id : null;
}

// ---------------------------
// HELPERS NUEVOS (Cliente/Tienda)
// ---------------------------
function ensure_cliente($cn, $val){
    $val = trim($val); if ($val === '') return null;
    if (ctype_digit((string)$val)) return (int)$val;
    $st = $cn->prepare("SELECT id FROM clientes WHERE nombre = ? LIMIT 1");
    $st->bind_param("s", $val); $st->execute(); $r = $st->get_result();
    if ($row = $r->fetch_assoc()){ $st->close(); return (int)$row['id']; }
    $st->close();
    $i = $cn->prepare("INSERT INTO clientes(nombre) VALUES(?)");
    $i->bind_param("s", $val); $i->execute(); $id = $cn->insert_id; $i->close();
    return $id ? (int)$id : null;
}
function ensure_tienda($cn, $val, $cliente_id){
    $val = trim($val); if ($val === '' || !$cliente_id) return null;
    if (ctype_digit((string)$val)) return (int)$val;
    $st = $cn->prepare("SELECT id FROM tiendas WHERE nombre = ? AND cliente_id = ? LIMIT 1");
    $st->bind_param("si", $val, $cliente_id); $st->execute(); $r = $st->get_result();
    if ($row = $r->fetch_assoc()){ $st->close(); return (int)$row['id']; }
    $st->close();
    $i = $cn->prepare("INSERT INTO tiendas(nombre, cliente_id) VALUES(?, ?)");
    $i->bind_param("si", $val, $cliente_id); $i->execute(); $id = $cn->insert_id; $i->close();
    return $id ? (int)$id : null;
}

// ---------------------------
// DATOS ENTRADA
// ---------------------------
$barcode = trim($_POST['barcode'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$marca_input = isset($_POST['marca_id']) ? trim($_POST['marca_id']) : (isset($_POST['marca']) ? trim($_POST['marca']) : '');

// CLIENTE Y TIENDA (Para el Kardex)
$cliente_input = isset($_POST['cliente_id']) ? trim($_POST['cliente_id']) : '';
$tienda_input  = isset($_POST['tienda_id']) ? trim($_POST['tienda_id']) : '';

$precio_unitario = isset($_POST['precio_unitario']) ? floatval($_POST['precio_unitario']) : 0;
$precio_paquete = isset($_POST['precio_paquete']) ? floatval($_POST['precio_paquete']) : 0;
$precio_caja_saco = isset($_POST['precio_caja_saco']) ? floatval($_POST['precio_caja_saco']) : 0;
$stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
$fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento']."-01" : null;
$costo_compra = isset($_POST['costo_compra']) ? floatval($_POST['costo_compra']) : 0;
$fecha_ingreso = date("Y-m-d H:i:s");

// CATEGORIAS
$cat1 = $_POST['categoria_n1_id'] ?? ($_POST['categoria_n1'] ?? null);
$cat2 = $_POST['categoria_n2_id'] ?? ($_POST['categoria_n2'] ?? null);
$cat3 = $_POST['categoria_n3_id'] ?? ($_POST['categoria_n3'] ?? null);

// FOTOS
$foto1_remote = trim($_POST['foto1_remote'] ?? ''); 
$foto2_remote = trim($_POST['foto2_remote'] ?? '');
$foto1_url = trim($_POST['foto1_url'] ?? ''); 
$foto2_url = trim($_POST['foto2_url'] ?? '');

// ---------------------------
// PROCESAR IDs
// ---------------------------
$cat1_id = $cat1 ? ensure_categoria_n1($cn, $cat1) : null;
$cat2_id = $cat2 ? ensure_subcategoria($cn, $cat2, $cat1_id) : null;
$cat3_id = $cat3 ? ensure_subespecifica($cn, $cat3, $cat2_id) : null;

// Marcas
$hasMarcasTable = has_table($cn, 'marcas');
$hasMarcaIdColumn = false;
$resCols = $cn->query("SHOW COLUMNS FROM productos LIKE 'marca_id'");
if ($resCols && $resCols->num_rows > 0) $hasMarcaIdColumn = true;
$marca_id = null; $marca_text = null;
if ($hasMarcasTable) {
    $marca_id = ensure_marca($cn, $marca_input);
    if ($marca_id) {
        $st = $cn->prepare("SELECT nombre FROM marcas WHERE id=? LIMIT 1");
        $st->bind_param("i",$marca_id); $st->execute(); $r=$st->get_result();
        if ($row = $r->fetch_assoc()) $marca_text = $row['nombre'];
        $st->close();
    } else { $marca_text = is_string($marca_input) ? $marca_input : null; }
} else { $marca_text = is_string($marca_input) ? $marca_input : null; }

// Procesar Clientes y Tiendas
$cliente_id_final = null;
$tienda_id_final = null;

$hasClientesTable = has_table($cn, 'clientes');
$hasTiendasTable = has_table($cn, 'tiendas');

if ($hasClientesTable && $cliente_input) {
    $cliente_id_final = ensure_cliente($cn, $cliente_input);
    if ($hasTiendasTable && $tienda_input && $cliente_id_final) {
        $tienda_id_final = ensure_tienda($cn, $tienda_input, $cliente_id_final);
    }
}

// ---------------------------
// EXISTENCIA PRODUCTO
// ---------------------------
$sql = "SELECT id, foto1, foto2 FROM productos WHERE barcode=? LIMIT 1";
$stmt = $cn->prepare($sql);
$stmt->bind_param("s", $barcode);
$stmt->execute();
$res = $stmt->get_result();
$existe = $res->num_rows > 0;

$uploadDir = "uploads/"; if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

// FOTOS LOGIC
$new_foto1 = null; $new_foto2 = null;
if (isset($_FILES['foto1']) && $_FILES['foto1']['error'] === 0) {
    $t = $uploadDir . time() . "_1_" . basename($_FILES["foto1"]["name"]);
    if(move_uploaded_file($_FILES["foto1"]["tmp_name"], $t)) $new_foto1 = $t;
} elseif ($foto1_remote !== '' && file_exists(__DIR__ . '/' . ltrim($foto1_remote, '/'))) {
    $new_foto1 = $foto1_remote;
} elseif ($foto1_url !== '') { $new_foto1 = $foto1_url; }

if (isset($_FILES['foto2']) && $_FILES['foto2']['error'] === 0) {
    $t = $uploadDir . time() . "_2_" . basename($_FILES["foto2"]["name"]);
    if(move_uploaded_file($_FILES["foto2"]["tmp_name"], $t)) $new_foto2 = $t;
} elseif ($foto2_remote !== '' && file_exists(__DIR__ . '/' . ltrim($foto2_remote, '/'))) {
    $new_foto2 = $foto2_remote;
} elseif ($foto2_url !== '') { $new_foto2 = $foto2_url; }

// ---------------------------
// A) ACTUALIZAR PRODUCTO EXISTENTE
// ---------------------------
if ($existe) {
    $row = $res->fetch_assoc();
    $producto_id = (int)$row['id'];
    $final_foto1 = $new_foto1 !== null ? $new_foto1 : ($row['foto1']??'');
    $final_foto2 = $new_foto2 !== null ? $new_foto2 : ($row['foto2']??'');

    // UPDATE solo de datos del PRODUCTO (Sin clientes/tiendas)
    if ($hasMarcaIdColumn) {
        $mVal = $marca_id !== null ? intval($marca_id) : null;
        $c1 = $cat1_id ? intval($cat1_id) : null;
        $c2 = $cat2_id ? intval($cat2_id) : null;
        $c3 = $cat3_id ? intval($cat3_id) : null;
        
        $sqlUpd = "UPDATE productos SET marca_id = ?, categoria_n1_id = ?, categoria_n2_id = ?, categoria_n3_id = ?, foto1 = ?, foto2 = ? WHERE id = ?";
        $upd = $cn->prepare($sqlUpd);
        $upd->bind_param("iiiissi", $mVal, $c1, $c2, $c3, $final_foto1, $final_foto2, $producto_id);
        $upd->execute(); $upd->close();
    } else {
        $mText = $marca_text ?? '';
        $c1 = $cat1_id ? intval($cat1_id) : null;
        $c2 = $cat2_id ? intval($cat2_id) : null;
        $c3 = $cat3_id ? intval($cat3_id) : null;

        $sqlUpd = "UPDATE productos SET marca = ?, categoria_n1_id = ?, categoria_n2_id = ?, categoria_n3_id = ?, foto1 = ?, foto2 = ? WHERE id = ?";
        $upd = $cn->prepare($sqlUpd);
        $upd->bind_param("siiissi", $mText, $c1, $c2, $c3, $final_foto1, $final_foto2, $producto_id);
        $upd->execute(); $upd->close();
    }

    // INSERTAR EN KARDEX (AQUÍ van cliente y tienda)
    $sql2 = "INSERT INTO kardex (producto_id, fecha_ingreso, precio_unitario, precio_paquete, precio_caja_saco, stock, fecha_vencimiento, costo_compra, cliente_id, tienda_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $st2 = $cn->prepare($sql2);
    
    // Asegurarse de pasar NULL si no hay cliente/tienda
    $cli_val = $cliente_id_final ? $cliente_id_final : null;
    $tda_val = $tienda_id_final ? $tienda_id_final : null;
    
    $st2->bind_param("isdddisdii", $producto_id, $fecha_ingreso, $precio_unitario, $precio_paquete, $precio_caja_saco, $stock, $fecha_vencimiento, $costo_compra, $cli_val, $tda_val);
    $st2->execute(); $st2->close();

    echo "<h2>Producto Actualizado</h2>
    <b>Producto existente. Se registró un nuevo ingreso en KARDEX asignado al Cliente/Tienda seleccionados.</b><br><br>
    <img src='" . htmlspecialchars($final_foto1) . "' width='150'><br>
    <button onclick=\"location.href='index.php'\">Registrar otro</button>";
    exit;
}

// ---------------------------
// B) NUEVO PRODUCTO
// ---------------------------
$foto1 = $new_foto1; 
$foto2 = $new_foto2;
if (!$foto1) die("Error: Debe subir la foto 1.");

$barcode_e = $cn->real_escape_string($barcode);
$descripcion_e = $cn->real_escape_string($descripcion);
$foto1_e = $cn->real_escape_string($foto1);
$foto2_e = $cn->real_escape_string($foto2);

$c1_sql = $cat1_id ? intval($cat1_id) : "NULL";
$c2_sql = $cat2_id ? intval($cat2_id) : "NULL";
$c3_sql = $cat3_id ? intval($cat3_id) : "NULL";

// INSERT PRODUCTO (SIN cliente/tienda)
if ($hasMarcaIdColumn) {
    $m_sql = $marca_id !== null ? intval($marca_id) : "NULL";
    $sqlIns = "INSERT INTO productos (foto1,foto2,barcode,descripcion,marca_id,categoria_n1_id,categoria_n2_id,categoria_n3_id)
               VALUES ('$foto1_e','$foto2_e','$barcode_e','$descripcion_e', $m_sql, $c1_sql, $c2_sql, $c3_sql)";
} else {
    $m_sql = $cn->real_escape_string($marca_text ?? '');
    $sqlIns = "INSERT INTO productos (foto1,foto2,barcode,descripcion,marca,categoria_n1_id,categoria_n2_id,categoria_n3_id)
               VALUES ('$foto1_e','$foto2_e','$barcode_e','$descripcion_e','$m_sql', $c1_sql, $c2_sql, $c3_sql)";
}

if(!$cn->query($sqlIns)) die("Error INSERT producto: " . $cn->error);
$producto_id = $cn->insert_id;

// INSERT KARDEX (CON cliente/tienda)
$sql2 = "INSERT INTO kardex (producto_id, fecha_ingreso, precio_unitario, precio_paquete, precio_caja_saco, stock, fecha_vencimiento, costo_compra, cliente_id, tienda_id) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$st2 = $cn->prepare($sql2);

$cli_val = $cliente_id_final ? $cliente_id_final : null;
$tda_val = $tienda_id_final ? $tienda_id_final : null;

$st2->bind_param("isdddisdii", $producto_id, $fecha_ingreso, $precio_unitario, $precio_paquete, $precio_caja_saco, $stock, $fecha_vencimiento, $costo_compra, $cli_val, $tda_val);
$st2->execute(); $st2->close();

echo "<h2>Producto Registrado</h2>
<b>Se creó el producto base y se asignó el stock inicial al Cliente/Tienda en KARDEX.</b><br><br>
<img src='" . htmlspecialchars($foto1) . "' width='150'><br>
<b>Código:</b> " . htmlspecialchars($barcode) . "<br>
<button onclick=\"location.href='index.php'\">Registrar otro</button>";
?>