jQuery(document).ready(function($) {
    // Función para actualizar la lista de clientes
    function updateClientList() {
        $('#crm-loading').show();
        $.ajax({
            url: crmAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_clients'
            },
            success: function(response) {
                if (response.success) {
                    $('.crm-table-container').replaceWith($(response.data));
                }
            },
            complete: function() {
                $('#crm-loading').hide();
            }
        });
    }

    // Manejar el envío del formulario
    $('#crm-add-client-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $message = $('#crm-message');
        
        $.ajax({
            url: crmAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_client',
                nonce: crmAjax.nonce,
                ...Object.fromEntries(new FormData($form[0]))
            },
            beforeSend: function() {
                $message.removeClass('success error').empty();
                $form.find('input[type="submit"]').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data);
                    $form[0].reset();
                    updateClientList();
                } else {
                    $message.addClass('error').text(response.data);
                }
            },
            error: function() {
                $message.addClass('error').text('Error en la conexión');
            },
            complete: function() {
                $form.find('input[type="submit"]').prop('disabled', false);
            }
        });
    });
});