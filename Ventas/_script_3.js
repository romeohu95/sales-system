$(document).ready(function(){

  // ============================================
  // 1. PREVENIR ENVÍO DE FORMULARIO CON ENTER
  // ============================================
  $('#barcode').on('keydown', function(e) {
      if (e.which === 13) {
          e.preventDefault();
          return false;
      }
  });

  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      if(event.target.tagName !== 'TEXTAREA') {
        event.preventDefault();
        return false;
      }
    }
  });

  // ============================================
  // 2. LÓGICA DEL SCANNER MODAL
  // ============================================
  let html5QrcodeScanner = null;
  let audioCtx = null;

  function playBeep() {
      try {
          if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
          const osc = audioCtx.createOscillator();
          const gain = audioCtx.createGain();
          osc.type = "square";
          osc.frequency.value = 1200;
          gain.gain.value = 0.15;
          osc.connect(gain);
          gain.connect(audioCtx.destination);
          osc.start();
          osc.stop(audioCtx.currentTime + 0.08);
      } catch (e) { console.warn("Audio no soportado"); }
  }

  function onScanSuccess(decodedText, decodedResult) {
      playBeep();
      if (navigator.vibrate) navigator.vibrate(200);
      stopScanner();
      $('#barcode').val(decodedText).trigger('input').focus();
      refreshFloatingStates();
  }

  function onScanFailure(error) { /* Ignorar errores de no lectura */ }

  function startScanner() {
      $('#scanner_modal').fadeIn(200);
      if (html5QrcodeScanner) {
          html5QrcodeScanner.clear().then(() => {
              initScannerInstance();
          }).catch(err => {
              console.error("Error clearing scanner", err);
              initScannerInstance();
          });
      } else {
          initScannerInstance();
      }
  }

  function initScannerInstance() {
      try {
          html5QrcodeScanner = new Html5QrcodeScanner(
              "reader", 
              { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0, showTorchButtonIfSupported: true }, 
              false 
          );
          html5QrcodeScanner.render(onScanSuccess, onScanFailure);
      } catch (err) {
          console.error("Error iniciando Html5QrcodeScanner", err);
          alert("Error al iniciar la cámara. Verifica permisos HTTPS.");
      }
  }

  function stopScanner() {
      $('#scanner_modal').fadeOut(200);
      if (html5QrcodeScanner) {
          html5QrcodeScanner.clear().catch(error => { console.error("Failed to clear html5QrcodeScanner. ", error); });
      }
  }

  $('#start_scan_btn').on('click', function(e) { e.preventDefault(); startScanner(); });
  $('#close_scan').on('click', function() { stopScanner(); });


  // ============================================
  // 3. UPLOAD LOGIC
  // ============================================
  function initFileUploads() {
    $('.file-upload-input').each(function(){
      const input = $(this);
      const box = input.closest('.file-upload-box');
      const preview = box.find('.file-upload-preview');
      const placeholder = box.find('.file-upload-placeholder');

      input.off('change').on('change', function(e){
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(evt) { preview.attr('src', evt.target.result).show(); placeholder.hide(); }
          reader.readAsDataURL(file);
        } else { preview.hide(); placeholder.show(); }
      });
      // Drag & Drop
      box.on('dragover dragenter', function(e){ e.preventDefault(); e.stopPropagation(); box.addClass('drag-over'); });
      box.on('dragleave drop', function(e){ e.preventDefault(); e.stopPropagation(); box.removeClass('drag-over'); });
      box.on('drop', function(e){
        const dt = e.originalEvent.dataTransfer;
        if (dt.files && dt.files.length > 0) { input[0].files = dt.files; input.trigger('change'); }
      });
    });
  }
  initFileUploads();

  // ============================================
  // 4. HELPER VISUAL UPDATE
  // ============================================
  function refreshFloatingStates() {
    $('.floating-label input.floating').each(function(){
      const $input = $(this);
      const $wrap = $input.closest('.floating-label');
      if ($input.val() !== null && $input.val().toString().trim() !== '') $wrap.addClass('filled');
      else $wrap.removeClass('filled');
    });
    $('.floating-label select').each(function(){
        const $select = $(this);
        const $wrap = $select.closest('.floating-label');
        if ($select.val() && $select.val() !== '') $wrap.addClass('filled');
        else $wrap.removeClass('filled');
    });
  }

  $('.floating-label input.floating').each(function(){
    const $input = $(this);
    const $wrap = $input.closest('.floating-label');
    $input.on('focus', function(){ $wrap.addClass('focused'); });
    $input.on('blur', function(){ $wrap.removeClass('focused'); refreshFloatingStates(); });
    $input.on('input change', refreshFloatingStates);
    refreshFloatingStates();
  });
  window.refreshFloatingStates = refreshFloatingStates;

  // ============================================
  // 5. CONFIGURACIÓN SELECT2
  // ============================================
  const s2config = {
      placeholder: "", allowClear: true, tags: true, width: '100%',
      escapeMarkup: function (markup) { return markup; },
      language: {
          searching: function() { return '<div class="select2-spinner-box"><div class="select2-search-spinner"></div><span>Buscando...</span></div>'; },
          noResults: function() { return "No se encontraron resultados"; },
          errorLoading: function() { return "Error cargando resultados"; }
      }
  };

  $("#cliente").select2($.extend({}, s2config, { ajax: { url:'ajax_get_clientes.php', dataType:'json', delay:250, processResults: d=>({results:d}) } }));
  $('#cliente').on('select2:select', function(e) {
      var data = e.params.data;
      if (data.id == data.text) { 
           $.post('ajax_create_cliente.php', { nombre: data.text }, function(response){
               if(response.id) {
                   var newOption = new Option(response.text, response.id, true, true);
                   $('#cliente').find('option[value="'+data.text+'"]').remove();
                   $('#cliente').append(newOption).trigger('change');
               }
           }, 'json');
      }
      $('#tienda').prop('disabled', false).val(null).trigger('change');
  });
  $('#cliente').on('select2:unselect', function(e) { $('#tienda').prop('disabled', true).val(null).trigger('change'); });

  $("#tienda").select2($.extend({}, s2config, {
      ajax: { url: 'ajax_get_tiendas.php', dataType: 'json', delay: 250, data: function(params){ return { cliente_id: $('#cliente').val(), q: params.term }; }, processResults: d=>({results:d}) },
      createTag: function (params) {
          var term = $.trim(params.term);
          if (term === '' || !$('#cliente').val()) return null;
          return { id: term, text: term, newOption: true };
      }
  }));
  $('#tienda').on('select2:select', function(e) {
      var data = e.params.data;
      var cli = $('#cliente').val();
      if (data.newOption || data.id == data.text) {
           if (!cli) { alert("Primero selecciona un cliente."); $('#tienda').val(null).trigger('change'); return; }
           $.post('ajax_create_tienda.php', { nombre: data.text, cliente_id: cli }, function(response){
               if(response.id) {
                   var newOption = new Option(response.text, response.id, true, true);
                   $('#tienda').find('option[value="'+data.id+'"]').remove();
                   $('#tienda').append(newOption).trigger('change');
               }
           }, 'json');
      }
  });

  $("#marca").select2($.extend({}, s2config, { ajax: { url:'ajax_get_marcas.php', dataType:'json', delay:250, processResults: d=>({results:d}) } }));
  $("#categoria_n1").select2($.extend({}, s2config, { ajax: { url:'ajax_get_categorias.php', dataType:'json', delay:250, processResults: d=>({results:d}) } }));
  $("#categoria_n2").select2($.extend({}, s2config, { ajax: { url:'ajax_get_subcategorias.php', dataType:'json', delay:250, data: function(params){ return { categoria_id: $('#categoria_n1').val(), q: params.term }; }, processResults: d=>({results:d}) } }));
  $("#categoria_n3").select2($.extend({}, s2config, { ajax: { url:'ajax_get_subespeciales.php', dataType:'json', delay:250, data: function(params){ return { subcategoria_id: $('#categoria_n2').val(), q: params.term }; }, processResults: d=>({results:d}) } }));

  $('.floating-label select').on('select2:open', function(e){
      $(this).closest('.floating-label').addClass('focused');
      setTimeout(function(){
          const searchField = document.querySelector('.select2-container--open .select2-search__field');
          if(searchField) { searchField.placeholder = "Escribe para buscar..."; searchField.focus(); }
      }, 0);
  });
  $('.floating-label select').on('select2:close', function(e){ $(this).closest('.floating-label').removeClass('focused'); refreshFloatingStates(); });
  $('.floating-label select').on('change', function(e){ refreshFloatingStates(); });
  $('#categoria_n1').on('change', function(){ $('#categoria_n2').val(null).trigger('change'); $('#categoria_n3').val(null).trigger('change'); });
  $('#categoria_n2').on('change', function(){ $('#categoria_n3').val(null).trigger('change'); });


  // ============================================
  // 6. INTEGRACIÓN WONG (DISEÑO CORREGIDO)
  // ============================================
  let wongTimer = null;

  // Inyectar estilos para el spinner
  $('head').append(`
    <style>
      @keyframes spinWong { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
      .wong-spinner {
          display: inline-block;
          width: 24px;
          height: 24px;
          border: 3px solid rgba(13, 71, 161, 0.3);
          border-radius: 50%;
          border-top-color: #0d47a1;
          animation: spinWong 1s ease-in-out infinite;
          margin-top: 10px;
      }
    </style>
  `);

  $('#wong').on('input paste', function(){
      const url = $(this).val().trim();
      if (url.length < 10 || url.indexOf('wong.pe') === -1) return;

      clearTimeout(wongTimer);
      $('#wong').closest('.floating-label').css('opacity', '0.6'); 
      
      // LOADER CORREGIDO: Texto arriba, Spinner abajo
      $('#preview_producto_inline').html(`
        <div style="text-align:center; padding:20px; background:#e3f2fd; border-radius:8px; border:1px solid #90caf9;">
            <div style="color:#0d47a1; font-weight:bold; font-size:14px;">Analizando Wong.pe...</div>
            <div class="wong-spinner"></div>
        </div>
      `);

      wongTimer = setTimeout(() => {
          $.getJSON('ajax_fetch_wong.php', { url: url }, function(data){
              $('#wong').closest('.floating-label').css('opacity', '1');

              if (!data.ok) {
                  $('#preview_producto_inline').html(`<div style="color:red; text-align:center;">Error: ${data.error}</div>`);
                  return;
              }

              // Llenado automático básico
              if(data.description) $('#descripcion').val(data.description).trigger('input');
              
              function addSelect2OptionWong(sel, txt) {
                if(!txt) return;
                const normalize = (str) => str.toString().normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase().trim();
                const search = normalize(txt);
                let foundVal = null;
                $(sel).find('option').each(function(){ if(normalize($(this).text())===search) foundVal=$(this).val(); });
                if(foundVal) $(sel).val(foundVal).trigger('change');
                else $(sel).append(new Option(txt, txt, true, true)).trigger('change');
              }
              if(data.brand) addSelect2OptionWong('#marca', data.brand);


              // =======================
              // CONSTRUCCIÓN DEL PANEL
              // =======================
              let htmlPanel = `<div style="background:#e3f2fd; border:1px solid #90caf9; border-radius:8px; padding:15px; margin-bottom:20px;">
                  
                  <!-- BOTÓN APLICAR TODO (ENCIMA DEL TÍTULO) -->
                  <button type="button" id="btn_wong_apply_all" class="action-btn" style="width:100%; display:block; margin-bottom:15px; background:#ff9800; color:white; border:none; padding:10px; border-radius:6px; font-size:14px; font-weight:bold; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.2); text-transform:uppercase;">
                     <i class="fa fa-check-circle"></i> Aplicar Todo
                  </button>

                  <h5 style="color:#0d47a1; font-weight:bold; margin:0 0 15px 0; border-bottom:1px solid #bbdefb; padding-bottom:8px; font-size:15px;">
                     <i class="fa fa-magic"></i> Datos Detectados
                  </h5>
                  
                  ${data.image ? `
                  <div style="text-align:center; margin-bottom:15px;">
                      <img src="${data.image}" style="max-height:120px; border-radius:4px; border:1px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                      <br>
                      <button type="button" id="btn_import_wong_img" class="action-btn" style="margin-top:8px; background:#28a745; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;">
                         <i class="fa fa-download"></i> Usar Imagen
                      </button>
                  </div>` : ''}
                  
                  <div style="display:flex; flex-direction:column; gap:10px;">`;

              // Botón Cat 1
              if(data.cat1) {
                  htmlPanel += `
                  <div style="display:flex; align-items:center; justify-content:space-between; background:white; padding:10px; border-radius:6px;">
                      <div style="line-height:1.2;">
                          <span style="font-size:11px; color:#666; text-transform:uppercase; font-weight:bold;">Categoría Principal</span><br>
                          <span style="color:#333; font-weight:500;">${data.cat1}</span>
                      </div>
                      <button type="button" class="btn-copy-cat" id="btn_cat1" data-target="#categoria_n1" data-val="${data.cat1}" style="background:#007bff; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;">Aplicar</button>
                  </div>`;
              }
              // Botón Cat 2
              if(data.cat2) {
                  htmlPanel += `
                  <div style="display:flex; align-items:center; justify-content:space-between; background:white; padding:10px; border-radius:6px;">
                      <div style="line-height:1.2;">
                          <span style="font-size:11px; color:#666; text-transform:uppercase; font-weight:bold;">Sub-Categoría</span><br>
                          <span style="color:#333; font-weight:500;">${data.cat2}</span>
                      </div>
                      <button type="button" class="btn-copy-cat" id="btn_cat2" data-target="#categoria_n2" data-val="${data.cat2}" style="background:#17a2b8; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;">Aplicar</button>
                  </div>`;
              }
              // Botón Cat 3
              if(data.cat3) {
                  htmlPanel += `
                  <div style="display:flex; align-items:center; justify-content:space-between; background:white; padding:10px; border-radius:6px;">
                      <div style="line-height:1.2;">
                          <span style="font-size:11px; color:#666; text-transform:uppercase; font-weight:bold;">Sub-Específica</span><br>
                          <span style="color:#333; font-weight:500;">${data.cat3}</span>
                      </div>
                      <button type="button" class="btn-copy-cat" id="btn_cat3" data-target="#categoria_n3" data-val="${data.cat3}" style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px;">Aplicar</button>
                  </div>`;
              }

              htmlPanel += `</div></div>`; // Fin Panel

              $('#preview_producto_inline').html(htmlPanel);

              // ======================
              // LISTENERS DEL PANEL
              // ======================
              if(data.image) {
                  $('#btn_import_wong_img').off('click').on('click', function(){ importarImagenWong(data.image); });
              }

              $('.btn-copy-cat').off('click').on('click', function(){
                  var target = $(this).data('target');
                  var val = $(this).data('val');
                  addSelect2OptionWong(target, val);
                  // Feedback
                  var $btn = $(this);
                  $btn.text('¡Aplicado!').css('background', '#28a745').prop('disabled', true);
                  setTimeout(() => { $btn.text('Aplicar').css('background', '').prop('disabled', false); }, 1500);
              });

              // --- BOTÓN APLICAR TODO (Secuencial) ---
              $('#btn_wong_apply_all').off('click').on('click', function(){
                  const btn = $(this);
                  btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Aplicando...');
                  
                  // 1. Aplicar Imagen (si existe y botón visible)
                  if ($('#btn_import_wong_img').length && !$('#btn_import_wong_img').prop('disabled')) {
                      $('#btn_import_wong_img').click();
                  }

                  // 2. Secuencia de Categorías (con delays para dar tiempo a Select2)
                  setTimeout(() => { 
                      if($('#btn_cat1').length) $('#btn_cat1').click(); 
                  }, 100);

                  setTimeout(() => { 
                      if($('#btn_cat2').length) $('#btn_cat2').click(); 
                  }, 600); // Dar tiempo a que cargue la Cat 1

                  setTimeout(() => { 
                      if($('#btn_cat3').length) $('#btn_cat3').click(); 
                      
                      // Finalizar
                      btn.html('<i class="fa fa-check"></i> ¡Listo!').css('background', '#28a745');
                      setTimeout(() => { 
                          btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Aplicar Todo').css('background', '#ff9800'); 
                      }, 2000);

                  }, 1100);
              });

              refreshFloatingStates();

          }).fail(function(){
              $('#wong').closest('.floating-label').css('opacity', '1');
              $('#preview_producto_inline').html(`<div style="color:red; text-align:center;">Error conectando con Wong.</div>`);
          });
      }, 600);
  });

  function importarImagenWong(imageUrl) {
      const btn = $('#btn_import_wong_img');
      btn.text('Descargando...').prop('disabled', true).css('opacity',0.7);

      $.post('ajax_fetch_vega_download.php', { image_url: imageUrl }, function(resp){
          if (resp && resp.ok) {
              if ($('#foto1_remote').length === 0) $('<input>').attr({type:'hidden', id:'foto1_remote', name:'foto1_remote', value: resp.path}).appendTo('#form');
              else $('#foto1_remote').val(resp.path);

              const box = $('#foto1_container .file-upload-box');
              box.find('.file-upload-preview').attr('src', resp.path).show();
              box.find('.file-upload-placeholder').hide();
              btn.text('¡Listo!').css('background', '#28a745');
          } else {
              btn.text('Error').prop('disabled', false).css('background','#dc3545');
              alert("Error: " + (resp.error || "Fallo descarga"));
          }
      }, 'json');
  }


  // ============================================
  // 7. LOGIC BARCODE Y VEGA
  // ============================================
  let barcodeExists = false;
  window.vega_last_result = null;
  const foto1OriginalHtml = $('#foto1_container').html(); 

  function positionSuggestions() {
    const $inp = $('#descripcion');
    if(!$inp.length) return;
    const off = $inp.offset();
    const h = $inp.outerHeight();
    const w = $inp.outerWidth();
    $('#desc_suggestions').css({ top: off.top + h + 6 + 'px', left: off.left + 'px', width: w + 'px' });
  }

  function vegaSpinnerHtml(text='Buscando en catálogo externo...') {
      return `<div class="vega-spinner-wrap"><div class="vega-spinner"></div><div class="vega-spinner-text">${text}</div></div>`;
  }
  
  function ejecutarBusqueda(val) {
      if (!val) {
          barcodeExists = false;
          $('#preview_producto_inline').html('');
          return;
      }
      $('#preview_producto_inline').css('opacity', '0.5');
      fetch("buscar_producto.php?codigo=" + encodeURIComponent(val))
          .then(r => r.json())
          .then(data => {
              $('#preview_producto_inline').css('opacity', '1');
              if (!data.encontrado) {
                  barcodeExists = false;
                  $('#preview_producto_inline').html('');
                  if (typeof window.vega_search_and_render === 'function') window.vega_search_and_render(val);
              } else {
                  barcodeExists = true;
                  $('#preview_producto_inline').html(
                      `<div>
                          <img src="${data.foto1}" style="width:100%; max-width:330px; border:1px solid #ddd; border-radius:8px; display:block; margin-bottom: 12px;">
                          <div style="background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                              <label style="font-weight:700; display:block; color:#555;">Stock actual:</label>
                              <div style="font-size:24px; font-weight:bold; color:#000; margin-top:4px;">${data.stock}</div>
                          </div>
                      </div>`
                  );
                  $('#precio_unitario').val(data.precio_unitario ?? '');
                  $('#precio_paquete').val(data.precio_paquete ?? '');
                  $('#precio_caja_saco').val(data.precio_caja_saco ?? '');
                  $('#stock').val(data.stock ?? '');
                  $('#costo_compra').val(data.costo_compra ?? '');
                  $('#descripcion').val(data.descripcion ?? '').prop('readOnly', true).css('background','#f3f3f3').css('cursor','not-allowed');

                  setSelect('#marca', data.marca_id, data.marca_name||data.marca);
                  setSelect('#categoria_n1', data.categoria_n1_id, data.categoria_n1_name);
                  setTimeout(() => setSelect('#categoria_n2', data.categoria_n2_id, data.categoria_n2_name), 200);
                  setTimeout(() => setSelect('#categoria_n3', data.categoria_n3_id, data.categoria_n3_name), 400);
                  refreshFloatingStates();
              }
          })
          .catch(err => { 
              console.error(err); 
              barcodeExists = false; 
              $('#preview_producto_inline').html(''); 
          });
  }

  function setSelect(selector, id, text) {
      if (id) {
          if ($(selector).find("option[value='"+id+"']").length) {
              $(selector).val(id).trigger('change');
          } else {
              const newOption = new Option(text, id, true, true);
              $(selector).append(newOption).trigger('change');
          }
      }
  }

  let barcodeTimer = null;
  $('#barcode').on('input', function(){
    clearTimeout(barcodeTimer);
    let val = $(this).val().trim();
    barcodeTimer = setTimeout(() => { ejecutarBusqueda(val); }, 350);
  });

  $('#barcode').on('change keyup', function(){
    if (!barcodeExists) $('#descripcion').prop('readOnly', false).css('background','#fff').css('cursor','text');
  });

  let descTimer = null;
  $('#descripcion').on('input', function(){
    if (barcodeExists) { $('#desc_suggestions').hide(); return; }
    clearTimeout(descTimer);
    const term = $(this).val().trim();
    if (term.length < 3) { $('#desc_suggestions').hide(); return; }
    descTimer = setTimeout(() => {
      $.getJSON('ajax_get_productos_existentes.php', { q: term }, function(data){
        if (!Array.isArray(data) || data.length === 0) { $('#desc_suggestions').hide(); return; }
        const $list = $('#desc_suggestions').empty();
        data.forEach(item => {
          const label = (item.NOMBRES ? item.NOMBRES : '') + (item.ITEM ? ' ('+item.ITEM+')' : '');
          const priceInfo = item.UNIDAD ? ' — P.Unit: '+item.UNIDAD : '';
          const $it = $(`<div class="sugg-item" data-json='${JSON.stringify(item).replace(/'/g,"&#39;")}'>${label}${priceInfo}</div>`);
          $list.append($it);
        });
        $('#copiar_precios').prop('checked', true);
        positionSuggestions();
        $list.show();
      }).fail(() => { $('#desc_suggestions').hide(); });
    }, 250);
  });

  $(document).on('click', '#desc_suggestions .sugg-item', function(){
    const json = $(this).attr('data-json');
    let item = {};
    try { item = JSON.parse(json.replace(/&#39;/g,"'")); } catch(e){ item = {}; }
    if (item.NOMBRES) $('#descripcion').val(item.NOMBRES).trigger('input').focus();
    if ($('#copiar_precios').is(':checked')) {
      if (item.UNIDAD != null) $('#precio_unitario').val(item.UNIDAD).trigger('input');
      if (item.PAQUETE != null) $('#precio_paquete').val(item.PAQUETE).trigger('input');
      if (item.CAJA_O_SACO != null) $('#precio_caja_saco').val(item.CAJA_O_SACO).trigger('input');
      if (item.COMPRA != null) $('#costo_compra').val(item.COMPRA).trigger('input');
    }
    $('#desc_suggestions').hide();
  });

  $(document).on('click', function(e){
    if (!$(e.target).closest('#descripcion, #desc_suggestions').length) $('#desc_suggestions').hide();
  });
  $(window).on('resize scroll', positionSuggestions);

  function renderVegaResult(data) {
    const container = $('#preview_producto_inline');
    if (!data || !data.found) { container.html(''); window.vega_last_result = null; return; }
    window.vega_last_result = { description: data.description||'', brand: data.brand||'', image: data.image||'', product_url: data.product_url||'' };
    
    const imgHtml = data.image ? `<img src="${data.image}" id="vega_image_preview" style="max-width:100%; width:320px; border:1px solid #ddd; border-radius:8px; display:block; margin-bottom:12px;">` : '';
    const escapedDesc = $('<div>').text(window.vega_last_result.description).html();
    const escapedBrand = $('<div>').text(window.vega_last_result.brand).html();
    
    const html = `
    <div id="vega_found" style="display:block;">
        ${imgHtml}
        <div>
            <div id="vega_desc" data-desc="${escapedDesc}" style="font-weight:700; margin-bottom:6px; font-size:15px;">${escapedDesc}</div>
            <div id="vega_brand_wrap" style="color:#666; margin-bottom:12px;"><span id="vega_brand" data-brand="${escapedBrand}"><b>Marca:</b> ${escapedBrand}</span></div>
            <div style="display:flex; gap:8px; margin-top:10px; flex-wrap: wrap;">
                <button type="button" id="vega_copy_desc" class="action-btn">Copiar descripción</button>
                <button type="button" id="vega_copy_brand" class="action-btn">Copiar marca</button>
                ${data.image ? '<button type="button" id="vega_copy_image" class="action-btn">Copiar imagen</button>' : ''}
                <a href="${data.product_url}" target="_blank" class="action-btn" style="text-align:center;">Ver en vega.pe</a>
            </div>
            <div id="vega_status" style="margin-top:8px;color:#333; font-size:13px;"></div>


        </div>
    </div>`;
    container.html(html);
  }

  window.vega_search_and_render = function(barcode) {
    if (!barcode) return;
    $('#preview_producto_inline').html(vegaSpinnerHtml('Buscando en catálogo externo...'));
    $.getJSON('ajax_fetch_vega.php', { barcode: barcode }).done(function(resp){
        if (!resp.ok || !resp.found) { $('#preview_producto_inline').html('No se encontró referencia externa.'); window.vega_last_result = null; return; }
        renderVegaResult({ found: true, image: resp.image, description: resp.description, brand: resp.brand, product_url: resp.product_url });
    }).fail(function(){ $('#preview_producto_inline').html('Error consultando catálogo externo.'); });
  };
  
  // Listeners Vega
  $(document).on('click', '#vega_copy_desc', function(){
    if (!window.vega_last_result) return;
    $('#descripcion').val(window.vega_last_result.description).trigger('input').focus();
  });
  $(document).on('click', '#vega_copy_brand', function(){
    if (!window.vega_last_result) return;
    let br = window.vega_last_result.brand;
    if ($('#marca').find("option[value='"+br+"']").length) $('#marca').val(br).trigger('change');
    else $('#marca').append(new Option(br, br, true, true)).trigger('change');
  });
  $(document).on('click', '#vega_copy_image', function(){
    const imageUrl = $('#vega_image_preview').attr('src');
    if (!imageUrl) return;
    $('#vega_status').text('Descargando imagen...');
    $.post('ajax_fetch_vega_download.php', { image_url: imageUrl }, function(resp){
      if (resp && resp.ok) {
        if ($('#foto1_remote').length === 0) $('<input>').attr({type:'hidden', id:'foto1_remote', name:'foto1_remote', value: resp.path}).appendTo('#form');
        else $('#foto1_remote').val(resp.path);
        $('#vega_status').text('Imagen copiada exitosamente.');
        const box = $('#foto1_container .file-upload-box');
        box.find('.file-upload-preview').attr('src', resp.path).show();
        box.find('.file-upload-placeholder').hide();
      } else {
        $('#vega_status').text('Error descargando imagen.');
      }
    }, 'json');
  });


  // --- Submit ---
  $('#form').on('submit', function(e){
    e.preventDefault();
    let form = document.getElementById('form');
    let fd = new FormData(form);
    let xhr = new XMLHttpRequest();
    xhr.open("POST","upload.php",true);
    xhr.upload.onprogress = function(e){ if (e.lengthComputable) { let porcentaje = (e.loaded / e.total) * 100; $('#progress').css('width', porcentaje + '%'); } };
    xhr.onload = function(){
      $('#resultado').html(xhr.responseText);
      $('#progress').css('width','0%');
      barcodeExists = false;
      $('#descripcion').prop('readOnly', false).css('background','#fff').css('cursor','text');
      $('#desc_suggestions').hide();
      window.vega_last_result = null;
      $('#preview_producto_inline').html('');
      $('.floating-select').val(null).trigger('change');
      $('.file-upload-preview').hide();
      $('.file-upload-placeholder').show();
      $('#form')[0].reset();
      $('#tienda').prop('disabled', true);
      refreshFloatingStates();
      $('#barcode').focus();
    };
    xhr.send(fd);
  });

  // --- Pre-selección ---
  if (window.PRE_SELECTED_DATA) {
      if (window.PRE_SELECTED_DATA.cliente) {
          var cli = window.PRE_SELECTED_DATA.cliente;
          var option = new Option(cli.nombre, cli.id, true, true);
          $('#cliente').append(option).trigger('change');
      }
      if (window.PRE_SELECTED_DATA.cliente && window.PRE_SELECTED_DATA.tienda) {
          var tda = window.PRE_SELECTED_DATA.tienda;
          var optionTda = new Option(tda.nombre, tda.id, true, true);
          $('#tienda').append(optionTda).trigger('change');
      }
  }

});