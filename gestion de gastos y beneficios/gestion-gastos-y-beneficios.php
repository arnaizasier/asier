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
    define('GASTOS_BENEFICIOS_DEBUG', true);
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

// Función separada para cargar los estilos
function cargar_estilos_gastos_beneficios() {
    wp_enqueue_script('jquery');
    wp_enqueue_style(
        'gastos-beneficios-styles',
        plugin_dir_url(__FILE__) . 'assets/style.css',
        array(),
        time() // Esto forzará una recarga del CSS durante el desarrollo
    );
    
    // Añadir log para depuración
    error_log('CSS URL: ' . plugin_dir_url(__FILE__) . 'assets/style.css');
}
add_action('wp_enqueue_scripts', 'cargar_estilos_gastos_beneficios');

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
