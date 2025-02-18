<?php
/**
 * Plugin Name: Gestión de Gastos y Beneficios
 * Description: Plugin para registrar y gestionar gastos y beneficios en WordPress.
 * Version: 1.0
 * Author: Asier Arnaiz
 * Text Domain: gestion-gastos-beneficios
 */

// Evita el acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Configuración de depuración
if (!defined('GASTOS_BENEFICIOS_DEBUG')) {
    define('GASTOS_BENEFICIOS_DEBUG', false);
}

function debug_log_gastos_beneficios($mensaje) {
    if (GASTOS_BENEFICIOS_DEBUG) {
        error_log('[DEBUG Gastos Beneficios] ' . $mensaje);
    }
}

// Función para cargar los archivos necesarios
function gestion_gastos_beneficios_init() {
    require_once plugin_dir_path(__FILE__) . 'includes/validador.php';
    require_once plugin_dir_path(__FILE__) . 'includes/procesar-formulario.php';
}
add_action('plugins_loaded', 'gestion_gastos_beneficios_init');

// Función para mostrar el mensaje de depuración del CSS
function mostrar_mensaje_debug_css() {
    if (current_user_can('administrator')) {
        ?>
        <div id="css-debug-message" style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 999999;
            font-size: 14px;
            font-family: Arial, sans-serif;
        ">
            CSS cargado correctamente ✓ (<?php echo date('H:i:s'); ?>)
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mensaje = document.getElementById('css-debug-message');
            if (mensaje) {
                setTimeout(function() {
                    mensaje.style.opacity = '0';
                    mensaje.style.transition = 'opacity 0.5s ease';
                    setTimeout(function() {
                        mensaje.remove();
                    }, 500);
                }, 5000);
            }
        });
        </script>
        <?php
    }
}

// Función separada para cargar los estilos y scripts
function cargar_estilos_gastos_beneficios() {
    wp_enqueue_script('jquery');
    
    // CSS principal
    $css_url = plugin_dir_url(__FILE__) . 'assets/styles.css';
    $css_path = plugin_dir_path(__FILE__) . 'assets/styles.css';
    
    // CSS de la tabla
    $tabla_css_url = plugin_dir_url(__FILE__) . 'assets/tabla-gastos.css';
    $tabla_css_path = plugin_dir_path(__FILE__) . 'assets/tabla-gastos.css';
    
    // JS de la tabla
    $tabla_js_url = plugin_dir_url(__FILE__) . 'assets/tabla-gastos.js';
    $tabla_js_path = plugin_dir_path(__FILE__) . 'assets/tabla-gastos.js';
    
    // Cargar CSS principal si existe
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'gastos-beneficios-styles',
            $css_url,
            array(),
            filemtime($css_path)
        );
        debug_log_gastos_beneficios('CSS principal cargado desde: ' . $css_url);
    }
    
    // Cargar CSS de la tabla si existe
    if (file_exists($tabla_css_path)) {
        wp_enqueue_style(
            'tabla-gastos-css',
            $tabla_css_url,
            array(),
            filemtime($tabla_css_path)
        );
        debug_log_gastos_beneficios('CSS de tabla cargado desde: ' . $tabla_css_url);
    }
    
    // Cargar JS de la tabla si existe
    if (file_exists($tabla_js_path)) {
        wp_enqueue_script(
            'tabla-gastos-js',
            $tabla_js_url,
            array('jquery'),
            filemtime($tabla_js_path),
            true
        );
        
        // Pasar variables a JavaScript
        wp_localize_script('tabla-gastos-js', 'tablaGastosAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tabla_gastos_nonce')
        ));
        
        debug_log_gastos_beneficios('JS de tabla cargado desde: ' . $tabla_js_url);
    }
}

// Aseguramos que los estilos se carguen tanto en el frontend como en el admin
add_action('wp_enqueue_scripts', 'cargar_estilos_gastos_beneficios');
add_action('admin_enqueue_scripts', 'cargar_estilos_gastos_beneficios');

// Cargar el formulario en una página mediante un shortcode
function mostrar_formulario_gastos_beneficios() {
    if (!is_user_logged_in()) {
        return '<p>Debes iniciar sesión para acceder a este formulario.</p>';
    }

    wp_enqueue_script('jquery');
    
    ob_start();
    include plugin_dir_path(__FILE__) . 'includes/formulario.php';
    return ob_get_clean();
}

// Registrar el shortcode
function registrar_shortcode_gastos_beneficios() {
    add_shortcode('formulario_gastos_beneficios', 'mostrar_formulario_gastos_beneficios');
}
add_action('init', 'registrar_shortcode_gastos_beneficios');

// Registrar las acciones para procesar el formulario
add_action('admin_post_procesar_gastos_beneficios', 'procesar_formulario_gasto_beneficio');
add_action('admin_post_nopriv_procesar_gastos_beneficios', 'procesar_formulario_gasto_beneficio');

// Función para depuración (solo visible para administradores)
function debug_shortcode_registro() {
    if (current_user_can('administrator')) {
        debug_log_gastos_beneficios('Shortcodes registrados: ' . print_r(array_keys($GLOBALS['shortcode_tags']), true));
    }
}
add_action('wp_footer', 'debug_shortcode_registro');

// Incluir el archivo con la funcionalidad del shortcode
require_once plugin_dir_path(__FILE__) . 'includes/mostrar-tabla-gastos.php';


// Función AJAX para manejar la eliminación de registros
function eliminar_registro_gasto_callback() {
    check_ajax_referer('tabla_gastos_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('No tienes permisos para realizar esta acción');
    }

    global $wpdb;
    $id = intval($_POST['id']);
    
    $resultado = $wpdb->delete(
        $wpdb->prefix . 'gastos_beneficios',
        array('id' => $id),
        array('%d')
    );

    if ($resultado !== false) {
        wp_send_json_success(array(
            'mensaje' => 'Registro eliminado correctamente'
        ));
    } else {
        wp_send_json_error('Error al eliminar el registro');
    }
}
add_action('wp_ajax_eliminar_registro_gasto', 'eliminar_registro_gasto_callback');
