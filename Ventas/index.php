<?php
// CONEXIÓN PARA BUSCAR LOS NOMBRES DE GET
$user_cpanel = "aputecnologias";
$server = "localhost";
$user = $user_cpanel . "_ventas_user";
$password = "u~77(+79&LB.";
$database = $user_cpanel . "_ventas_db";
$cn = new mysqli($server, $user, $password, $database);
$cn->set_charset("utf8mb4");

// OBTENER IDs DESDE URL
$get_cliente_id = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
$get_tienda_id  = isset($_GET['tienda']) ? (int)$_GET['tienda'] : 0;

$pre_cliente = null;
$pre_tienda = null;

// BUSCAR DATOS SI EXISTEN IDs
if ($get_cliente_id > 0) {
    $sql = $cn->prepare("SELECT id, nombre FROM clientes WHERE id = ? LIMIT 1");
    $sql->bind_param("i", $get_cliente_id);
    $sql->execute();
    $res = $sql->get_result();
    if ($row = $res->fetch_assoc()) {
        $pre_cliente = $row; 
        if ($get_tienda_id > 0) {
            $sql2 = $cn->prepare("SELECT id, nombre FROM tiendas WHERE id = ? AND cliente_id = ? LIMIT 1");
            $sql2->bind_param("ii", $get_tienda_id, $get_cliente_id);
            $sql2->execute();
            $res2 = $sql2->get_result();
            if ($row2 = $res2->fetch_assoc()) { $pre_tienda = $row2; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Producto</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- CSS Externo -->
<link href="assets/css/select2.min.css" rel="stylesheet" />
<link href="_style.css?v=<?php echo filemtime('_style.css'); ?>" rel="stylesheet" />

<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/select2.min.js"></script>
<script src="assets/js/html5-qrcode.min.js"></script>
</head>
<body>

<!-- Pasar datos de PHP a JS globalmente -->
<script>
    window.PRE_SELECTED_DATA = {
        cliente: <?php echo $pre_cliente ? json_encode($pre_cliente) : 'null'; ?>,
        tienda: <?php echo $pre_tienda ? json_encode($pre_tienda) : 'null'; ?>
    };
</script>

<h2>Registrar Producto</h2>

<form id="form" enctype="multipart/form-data">

<!-- Barcode + Botón Scanner -->
<div class="input-group-scan">
    <div class="floating-label" style="flex-grow:1;">
      <input type="text" name="barcode" id="barcode" autocomplete="off" class="floating" autofocus />
      <label for="barcode" class="float-text">Código de barras</label>
    </div>
    <button type="button" class="btn-open-scan" id="start_scan_btn" title="Escanear código">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/><path d="M20 4h-3.17L15 2H9L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 11.5V13H9v2.5L5.5 12 9 8.5V11h6V8.5l3.5 3.5-3.5 3.5z" opacity=".3"/><path d="M20 4h-3.17L15 2H9L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
    </button>
</div>

<!-- NUEVO CAMPO WONG -->
<div class="floating-label">
  <input type="text" name="wong" id="wong" autocomplete="off" class="floating">
  <label for="wong" class="float-text">Pegar URL Wong.pe</label>
</div>

<div id="preview_producto_inline" style="margin-top:8px;"></div>

<br>

<label style="display:block; margin-bottom:10px; font-weight:bold;">Basarse en producto existente (referencia):</label>

<!-- FOTO 1 -->
<div class="file-upload-wrapper" id="foto1_container">
  <span class="file-upload-label">Foto 1</span>
  <div class="file-upload-box">
    <div class="file-upload-placeholder"><svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg></div>
    <img class="file-upload-preview" alt="Vista previa">
    <div class="file-upload-btn"><svg viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg></div>
    <input type="file" name="foto1" id="foto1_input" class="file-upload-input" accept="image/*" capture="camera">
  </div>
</div>

<!-- FOTO 2 -->
<div class="file-upload-wrapper">
  <span class="file-upload-label">Foto 2</span>
  <div class="file-upload-box">
    <div class="file-upload-placeholder"><svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg></div>
    <img class="file-upload-preview" alt="Vista previa">
    <div class="file-upload-btn"><svg viewBox="0 0 24 24"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/></svg></div>
    <input type="file" name="foto2" class="file-upload-input" accept="image/*" capture="camera">
  </div>
</div>

<br><br>

<label>Fecha de Vencimiento (Mes/Año):</label><br>
<input type="month" name="fecha_vencimiento" id="fecha_vencimiento" style="margin-bottom:20px;"><br>

<!-- === SECCIÓN KARDEX: CLIENTE Y TIENDA === -->
<div style="background:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:10px; margin-bottom:20px;">
    <h3 style="margin-top:0; font-size:14px; color:#666; text-transform:uppercase;">Datos del Proveedor (Kardex)</h3>
    <div class="floating-label">
      <select name="cliente_id" id="cliente" class="floating-select" style="width:100%"></select>
      <label for="cliente" class="float-text">Cliente / Proveedor</label>
    </div>
    <div class="floating-label">
      <select name="tienda_id" id="tienda" class="floating-select" style="width:100%" disabled></select>
      <label for="tienda" class="float-text">Tienda del Cliente</label>
    </div>
</div>

<!-- Selects Floating -->
<div class="floating-label">
  <select name="marca_id" id="marca" class="floating-select" style="width:100%"></select>
  <label for="marca" class="float-text">Marca</label>
</div>

<div class="floating-label">
  <select name="categoria_n1" id="categoria_n1" class="floating-select" style="width:100%"></select>
  <label for="categoria_n1" class="float-text">Categoría principal</label>
</div>

<div class="floating-label">
  <select name="categoria_n2" id="categoria_n2" class="floating-select" style="width:100%"></select>
  <label for="categoria_n2" class="float-text">Subcategoría</label>
</div>

<div class="floating-label">
  <select name="categoria_n3" id="categoria_n3" class="floating-select" style="width:100%"></select>
  <label for="categoria_n3" class="float-text">Subcategoría específica</label>
</div>

<!-- Descripcion + Checkbox -->
<div class="floating-label combined">
  <input type="text" name="descripcion" id="descripcion" autocomplete="off" class="floating" required/>
  <label for="descripcion" class="float-text">Descripción</label>
  <div class="checkbox-inside">
    <input type="checkbox" id="copiar_precios" checked>
    <label for="copiar_precios" style="position:static; transform:none; color:#333; font-size:14px; pointer-events:auto;">COP. REF.</label>
  </div>
</div>
<div id="desc_suggestions" style="display:none;"></div>

<br>

<!-- Precios -->
<div class="floating-label">
  <input type="number" step="0.01" name="precio_caja_saco" id="precio_caja_saco" autocomplete="off" class="floating">
  <label for="precio_caja_saco" class="float-text">Precio por Caja o Saco (S/)</label>
</div>
<div class="floating-label">
  <input type="number" step="0.01" name="precio_paquete" id="precio_paquete" autocomplete="off" class="floating">
  <label for="precio_paquete" class="float-text">Precio por Paquete (S/)</label>
</div>
<div class="floating-label">
  <input type="number" step="0.01" name="precio_unitario" id="precio_unitario" autocomplete="off" class="floating">
  <label for="precio_unitario" class="float-text">Precio Unitario (S/)</label>
</div>
<div class="floating-label">
  <input type="number" name="stock" id="stock" autocomplete="off" class="floating" required>
  <label for="stock" class="float-text">Stock (unidades)</label>
</div>
<div class="floating-label">
  <input type="number" name="costo_compra" id="costo_compra" autocomplete="off" class="floating">
  <label for="costo_compra" class="float-text">Costo de Compra</label>
</div>

<button type="submit">Registrar</button>
</form>

<div class="progress-container">
    <div class="progress-bar" id="progress"></div>
</div>
<div id="resultado"></div>

<!-- MODAL DEL SCANNER -->
<div id="scanner_modal">
    <div class="close-scan-btn" id="close_scan">✕</div>
    <div id="reader"></div>
    <div class="mask"><div class="mask-window"><div class="laser"></div></div><div class="scan-text">Enfoca el código de <b>BARRAS</b><br>dentro del recuadro</div></div>
</div>

<!-- Lógica JS Externa (Llama a _script_3.js) -->
<script src="_script_3.js?v=<?php echo filemtime('_script_3.js'); ?>"></script>








</body>
</html>