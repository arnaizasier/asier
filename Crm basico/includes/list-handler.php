<?php
if (!defined('ABSPATH')) {
    exit;
}

function display_crm_list() {
    global $wpdb;
    
    $clientes = $wpdb->get_results("SELECT * FROM wp_alfabeta ORDER BY nombre ASC");
    
    if (empty($clientes)) {
        return '<p>No hay clientes registrados.</p>';
    }
    
    $output = '
    <div class="crm-table-container">
        <div id="crm-loading">Cargando...</div>
        <table class="crm-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Fecha Nacimiento</th>
                    <th>Dirección</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($clientes as $cliente) {
        $output .= sprintf(
            '<tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>',
            esc_html($cliente->nombre),
            esc_html($cliente->apellidos),
            esc_html($cliente->telefono),
            esc_html($cliente->email),
            esc_html($cliente->fecha_nacimiento),
            esc_html($cliente->direccion),
            esc_html($cliente->notas)
        );
    }
    
    $output .= '</tbody></table></div>';
    
    return $output;
}