jQuery(document).ready(function($) {
    function cambiarPeriodoBalance(periodo) {
        // Obtener los parámetros actuales de la URL
        const urlParams = new URLSearchParams(window.location.search);
        
        // Actualizar el período en los parámetros
        urlParams.set('periodo', periodo);
        
        // Si cambiamos a anual, eliminar el parámetro mes
        if (periodo === 'anual') {
            urlParams.delete('mes');
        }
        
        // Actualizar botones inmediatamente
        document.querySelectorAll('.balance-tab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.periodo === periodo) {
                btn.classList.add('active');
            }
        });

        // Mostrar indicador de carga con animación
        const loadingHtml = `
            <div class="balance-loading">
                <div class="loading-spinner"></div>
                <span>Actualizando...</span>
            </div>
        `;
        $('.balance-content').addClass('loading').append(loadingHtml);

        // Realizar la petición AJAX
        $.ajax({
            url: tablaGastosAjax.ajaxurl, // Usar la URL de AJAX de WordPress
            type: 'POST',
            data: {
                action: 'actualizar_balance',
                periodo: periodo,
                mes: urlParams.get('mes') || new Date().getMonth() + 1,
                anio: urlParams.get('anio') || new Date().getFullYear(),
                nonce: tablaGastosAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Animar la transición del contenido
                    $('.balance-content').fadeOut(300, function() {
                        $(this).html(response.data.html).fadeIn(300);
                    });
                    
                    // Actualizar la URL sin recargar la página
                    window.history.pushState({}, '', '?' + urlParams.toString());
                } else {
                    // Mostrar mensaje de error
                    mostrarMensajeError('Error al actualizar el balance');
                }
            },
            error: function(xhr, status, error) {
                mostrarMensajeError('Error de conexión');
                console.error('Error en la petición AJAX:', error);
            },
            complete: function() {
                // Eliminar indicador de carga
                $('.balance-content').removeClass('loading');
                $('.balance-loading').fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    }

    // Función para mostrar mensajes de error
    function mostrarMensajeError(mensaje) {
        const errorHtml = `
            <div class="balance-error-message">
                ${mensaje}
                <button class="cerrar-error">&times;</button>
            </div>
        `;
        
        // Eliminar mensajes de error anteriores
        $('.balance-error-message').remove();
        
        // Mostrar el nuevo mensaje
        $('.balance-wrapper').prepend(errorHtml);
        
        // Configurar el cierre del mensaje
        $('.cerrar-error').on('click', function() {
            $(this).parent().fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Auto-ocultar después de 5 segundos
        setTimeout(function() {
            $('.balance-error-message').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Hacer la función disponible globalmente
    window.cambiarPeriodoBalance = cambiarPeriodoBalance;

    // Añadir estilos para el indicador de carga y mensajes de error
    $('<style>')
        .text(`
            .balance-content {
                position: relative;
                min-height: 200px;
            }
            
            .balance-content.loading {
                opacity: 0.7;
                pointer-events: none;
            }
            
            .balance-loading {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                color: #666;
                z-index: 100;
            }
            
            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #4CAF50;
                border-radius: 50%;
                margin-bottom: 10px;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .balance-error-message {
                background-color: #ff5252;
                color: white;
                padding: 12px 20px;
                border-radius: 4px;
                margin-bottom: 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                animation: slideIn 0.3s ease-out;
            }
            
            .cerrar-error {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                padding: 0 0 0 15px;
            }
            
            .cerrar-error:hover {
                opacity: 0.8;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateY(-20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        `)
        .appendTo('head');
});