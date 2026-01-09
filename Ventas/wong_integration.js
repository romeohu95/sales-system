$(document).ready(function(){
    
    let wongTimer = null;

    $('#wong').on('input paste', function(){
        const url = $(this).val().trim();
        
        // Validar que parece una URL de wong
        if (url.length < 10 || url.indexOf('wong.pe') === -1) return;

        clearTimeout(wongTimer);

        // Mostrar indicador de carga (opcional, visual)
        $('#wong').css('opacity', '0.5');

        wongTimer = setTimeout(() => {
            $.getJSON('ajax_fetch_wong.php', { url: url }, function(data){
                $('#wong').css('opacity', '1');

                if (!data.ok) {
                    console.log("Error Wong: " + data.error);
                    return;
                }

                console.log("Datos encontrados en Wong:", data);

                // 1. Llenar Descripción
                if(data.description) {
                    $('#descripcion').val(data.description).trigger('input');
                }

                // 2. Llenar Marca (Select2)
                if(data.brand) {
                    addSelect2Option('#marca', data.brand);
                }

                // 3. Llenar Categorías
                if(data.cat1) addSelect2Option('#categoria_n1', data.cat1);
                
                // Esperamos un poco para cat2 y cat3 por si dependen de cat1 (cascada)
                // Aunque como creamos la opción directa, debería funcionar.
                setTimeout(() => {
                    if(data.cat2) addSelect2Option('#categoria_n2', data.cat2);
                }, 200);

                setTimeout(() => {
                    if(data.cat3) addSelect2Option('#categoria_n3', data.cat3);
                }, 400);

                // 4. Manejar Imagen
                if(data.image) {
                    // Mostrar preview
                    $('#preview_producto_inline').html(
                        `<div style="text-align:center; background:#f0f0f0; padding:10px; border-radius:8px; margin-bottom:10px;">
                            <p style="font-size:12px; color:#666; margin:0 0 5px 0;">Imagen detectada en Wong:</p>
                            <img src="${data.image}" style="max-width:100%; max-height:200px; border-radius:4px;">
                            <br>
                            <button type="button" id="btn_import_wong_img" class="action-btn" style="margin-top:8px;">Usar esta imagen</button>
                        </div>`
                    );

                    // Acción al hacer clic en "Usar esta imagen"
                    $('#btn_import_wong_img').off('click').on('click', function(){
                        importarImagenWong(data.image);
                    });
                }

            }).fail(function(){
                $('#wong').css('opacity', '1');
                alert("Error al intentar conectar con Wong.");
            });
        }, 500); // Esperar 500ms después de pegar
    });

    // Función auxiliar para agregar/seleccionar opción en Select2
    function addSelect2Option(selector, text) {
        const $select = $(selector);
        // Buscar si existe una opción con ese texto (ignorando mayúsculas/minúsculas)
        let foundValue = null;
        
        $select.find('option').each(function(){
            if ($(this).text().toUpperCase() === text.toUpperCase()) {
                foundValue = $(this).val();
                return false; // break
            }
        });

        if (foundValue) {
            // Si existe, seleccionar
            $select.val(foundValue).trigger('change');
        } else {
            // Si no existe, crear opción nueva y seleccionar
            // (Asume que tu backend soporta recibir texto nuevo o que select2 tiene tags:true)
            const newOption = new Option(text, text, true, true);
            $select.append(newOption).trigger('change');
        }
    }

    // Función para descargar la imagen de Wong a tu servidor
    function importarImagenWong(imageUrl) {
        const btn = $('#btn_import_wong_img');
        btn.text('Descargando...').prop('disabled', true);

        // Usamos tu script existente ajax_fetch_vega_download.php que ya descarga imágenes
        // Ojo: Asegúrate que ese script permita descargar desde cualquier dominio o agrega 'wongfood.vtexassets.com' a su lista blanca.
        $.post('ajax_fetch_vega_download.php', { image_url: imageUrl }, function(resp){
            if (resp && resp.ok) {
                // Asignar al input hidden que espera tu formulario (segun tu código anterior es foto1_remote)
                if ($('#foto1_remote').length === 0) {
                    $('<input>').attr({type:'hidden', id:'foto1_remote', name:'foto1_remote', value: resp.path}).appendTo('#form');
                } else {
                    $('#foto1_remote').val(resp.path);
                }

                // Actualizar visualización del input file
                const box = $('#foto1_container .file-upload-box');
                box.find('.file-upload-preview').attr('src', resp.path).show();
                box.find('.file-upload-placeholder').hide();
                
                btn.text('¡Imagen importada!').css('background', '#4caf50').css('color', 'white');
                setTimeout(() => $('#preview_producto_inline').html(''), 2000); // Limpiar preview
            } else {
                btn.text('Error descarga').prop('disabled', false);
                alert("Error: " + (resp.error || "No se pudo guardar la imagen"));
            }
        }, 'json').fail(function(){
            btn.text('Error red').prop('disabled', false);
        });
    }

});