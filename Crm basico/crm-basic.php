<?php
/*
Plugin Name: CRM Básico
Description: Plugin CRM básico con formulario y lista de clientes
Version: 1.0
Author: arnaizasier
Date: 2025-02-19
*/

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Incluir archivos de funcionalidad
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/list-handler.php';

// Registrar shortcodes
function register_crm_shortcodes() {
    add_shortcode('crm_formulario', 'display_crm_form');
    add_shortcode('crm_lista', 'display_crm_list');
}
add_action('init', 'register_crm_shortcodes');

// Agregar CSS y JavaScript
function crm_basic_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'crm-basic-ajax',
        plugins_url('js/crm-ajax.js', __FILE__),
        array('jquery'),
        '1.0',
        true
    );
    
    // Pasar variables a JavaScript
    wp_localize_script(
        'crm-basic-ajax',
        'crmAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('crm_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'crm_basic_enqueue_scripts');

// Agregar estilos CSS
function crm_basic_styles() {
    ?>
    <style>
        /* Estilos del formulario */
        .crm-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .crm-form label {
            display: block;
            margin: 10px 0 5px;
            color: #333;
            font-weight: 500;
        }
        
        .crm-form input[type="text"],
        .crm-form input[type="email"],
        .crm-form input[type="tel"],
        .crm-form input[type="date"],
        .crm-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .crm-form input:focus,
        .crm-form textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .crm-form input[type="submit"] {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .crm-form input[type="submit"]:hover {
            background: #0056b3;
        }

        /* Estilos de la tabla */
        .crm-table-container {
            margin: 20px 0;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .crm-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .crm-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .crm-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .crm-table tr:hover {
            background-color: #f8f9fa;
        }

        .crm-table tr:last-child td {
            border-bottom: none;
        }

        /* Estilos de mensajes y loading */
        .crm-message {
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .crm-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .crm-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        #crm-loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .crm-table-container {
                border-radius: 0;
                box-shadow: none;
            }

            .crm-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .crm-table th,
            .crm-table td {
                padding: 10px;
            }
            
            .crm-form {
                margin: 10px;
                padding: 15px;
                border-radius: 0;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'crm_basic_styles');

// Manejar la solicitud AJAX para añadir cliente
add_action('wp_ajax_add_client', 'handle_add_client');
function handle_add_client() {
    check_ajax_referer('crm_nonce', 'nonce');
    
    global $wpdb;
    $user_id = get_current_user_id();
    
    // Verificar campos requeridos
    if (empty($_POST['nombre']) || empty($_POST['telefono'])) {
        wp_send_json_error('El nombre y el teléfono son obligatorios');
        return;
    }
    
    $data = array(
        'user_id' => $user_id,
        'nombre' => sanitize_text_field($_POST['nombre']),
        'apellidos' => sanitize_text_field($_POST['apellidos']),
        'telefono' => sanitize_text_field($_POST['telefono']),
        'email' => sanitize_email($_POST['email']),
        'fecha_nacimiento' => sanitize_text_field($_POST['fecha_nacimiento']),
        'direccion' => sanitize_textarea_field($_POST['direccion']),
        'notas' => sanitize_textarea_field($_POST['notas'])
    );
    
    $result = $wpdb->insert('wp_alfabeta', $data);
    
    if ($result) {
        wp_send_json_success('Cliente añadido correctamente');
    } else {
        wp_send_json_error('Error al añadir el cliente');
    }
}

// Manejar la solicitud AJAX para obtener la lista de clientes
add_action('wp_ajax_get_clients', 'handle_get_clients');
add_action('wp_ajax_nopriv_get_clients', 'handle_get_clients');
function handle_get_clients() {
    ob_start();
    echo display_crm_list();
    $html = ob_get_clean();
    wp_send_json_success($html);
}