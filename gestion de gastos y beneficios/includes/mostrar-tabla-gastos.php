<?php
// Asegurarse de que el código solo se ejecute dentro de WordPress
if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente al archivo
}

// Función para mostrar la tabla de gastos y beneficios
function mostrar_tabla_gastos_beneficios() {
    global $wpdb;  // Usamos la clase global $wpdb para interactuar con la base de datos
    
    // Realizamos la consulta para obtener los datos de la tabla 'wp_gastos_beneficios'
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gastos_beneficios ORDER BY fecha DESC");

    // Empezamos a generar la tabla HTML
    ob_start();  // Inicia la captura de la salida

    if ($results) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Tipo</th>';
        echo '<th>Cantidad</th>';
        echo '<th>Categoría</th>';
        echo '<th>Fecha</th>';
        echo '<th>Descripción</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        // Iteramos sobre los resultados y mostramos cada fila de la tabla
        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->tipo) . '</td>';
            echo '<td>' . esc_html($row->cantidad) . ' €</td>';
            echo '<td>' . esc_html($row->categoria) . '</td>';
            echo '<td>' . esc_html($row->fecha) . '</td>';
            echo '<td>' . esc_html($row->descripcion) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        // Si no hay resultados, mostramos un mensaje
        echo 'No hay registros.';
    }

    return ob_get_clean();  // Retorna la salida capturada como contenido del shortcode
}

// Registrar el shortcode [mostrar_tabla]
add_shortcode('mostrar_tabla', 'mostrar_tabla_gastos_beneficios');
