// Cliente: integrar con index.php
// - Llamar a ajax_fetch_vega.php cuando buscar_producto devuelve !encontrado
// - Mostrar vista previa en #preview_producto_inline con botón(s) "Copiar descripción", "Copiar marca", "Copiar imagen"
// - Al pulsar "Copiar imagen" hace POST a ajax_fetch_vega_download.php para guardar imagen en server y guarda la ruta en hidden input foto1_remote
// Inserta/llama a estas funciones dentro de tu script actual (ya tienes la lógica de barcode -> buscar_producto.php).
$(function(){

  // Helper: renderizar panel con datos obtenidos y botones
  function renderVegaResult(data) {
    const container = $('#preview_producto_inline');
    if (!data || !data.found) {
      container.html('');
      return;
    }

    // construir HTML
    const imgHtml = data.image ? `<img src="${data.image}" style="max-width:320px;border:1px solid #ddd;border-radius:8px;display:block;margin-bottom:8px;">` : '';
    const desc = $('<div>').text(data.description || '').html();
    const brand = $('<div>').text(data.brand || '').html();

    const html = `
      <div id="vega_found" style="display:flex; gap:12px; align-items:flex-start;">
        <div style="min-width:320px;">${imgHtml}</div>
        <div style="flex:1;">
          <div style="font-weight:700; margin-bottom:6px;">${desc}</div>
          <div style="color:#666; margin-bottom:12px;"><b>Marca:</b> ${brand}</div>
          <div style="display:flex; gap:8px;">
            <button type="button" id="vega_copy_desc">Copiar descripción</button>
            <button type="button" id="vega_copy_brand">Copiar marca</button>
            ${ data.image ? '<button type="button" id="vega_copy_image">Copiar imagen al servidor</button>' : '' }
            <a href="${data.product_url}" target="_blank" style="margin-left:8px;">Ver en vega.pe</a>
          </div>
          <div id="vega_status" style="margin-top:8px;color:#333;"></div>
        </div>
      </div>
    `;
    container.html(html);
  }

  // Exponer función global para ser llamada desde el lugar donde detectes "no encontrado"
  window.vega_search_and_render = function(barcode) {
    if (!barcode) return;
    $('#preview_producto_inline').html('Buscando en catálogo externo...');
    $.getJSON('ajax_fetch_vega.php', { barcode: barcode })
      .done(function(resp){
        if (!resp.ok || !resp.found) {
          $('#preview_producto_inline').html('No se encontró referencia externa.');
          return;
        }
        renderVegaResult({ found: true, image: resp.image, description: resp.description, brand: resp.brand, product_url: resp.product_url });
      })
      .fail(function(){
        $('#preview_producto_inline').html('Error consultando catálogo externo.');
      });
  };

  // Delegated handlers for buttons
  $(document).on('click', '#vega_copy_desc', function(){
    const txt = $('#vega_found .vtex-store-components-3-x-productBrand, #vega_found').text() || '';
    // use the description returned earlier in the rendered area
    const desc = $('#vega_found').find('div').first().text();
    if (desc) $('#descripcion').val(desc).focus();
  });

  $(document).on('click', '#vega_copy_brand', function(){
    // brand is shown in the preview; copy into marca select2 as a tag (create option if needed)
    const brandText = $('#vega_found').find('div').eq(1).text().replace(/^Marca:\s*/, '').trim();
    if (!brandText) return;
    // If select2 has option with that text as value -> select it; else create option with text value
    if ($('#marca option[value="'+brandText+'"]').length===0) {
      const newOpt = new Option(brandText, brandText, true, true);
      $('#marca').append(newOpt).trigger('change');
    } else {
      $('#marca').val(brandText).trigger('change');
    }
  });

  $(document).on('click', '#vega_copy_image', function(){
    const imageUrl = $('#preview_producto_inline img').attr('src');
    if (!imageUrl) return;
    $('#vega_status').text('Descargando imagen al servidor...');
    $.post('ajax_fetch_vega_download.php', { image_url: imageUrl }, function(resp){
      if (resp && resp.ok) {
        // poner ruta en campo oculto para que upload.php la tome (ver nota abajo)
        if ($('#foto1_remote').length === 0) {
          $('<input>').attr({type:'hidden', id:'foto1_remote', name:'foto1_remote', value: resp.path}).appendTo('#form');
        } else {
          $('#foto1_remote').val(resp.path);
        }
        $('#vega_status').text('Imagen copiada al servidor: ' + resp.path);
        // mostrar la imagen guardada (ruta relativa)
        // si quieres, reemplazar la preview con la imagen guardada:
        // $('#preview_producto_inline img').attr('src', resp.path);
      } else {
        $('#vega_status').text('Error al descargar imagen: ' + (resp && resp.error ? resp.error : 'unknown'));
      }
    }, 'json').fail(function(){ $('#vega_status').text('Error en la petición'); });
  });

});