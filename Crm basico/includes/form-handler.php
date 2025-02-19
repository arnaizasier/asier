<?php
if (!defined('ABSPATH')) {
    exit;
}

function display_crm_form() {
    if (!is_user_logged_in()) {
        return '<p style="color: red;">Debes estar logueado para añadir clientes.</p>';
    }
    
    $form = '
    <div class="crm-form">
        <div id="crm-message"></div>
        <form id="crm-add-client-form" method="post">
            <div>
                <label>Nombre</label>
                <input type="text" name="nombre" required>
            </div>
            
            <div>
                <label>Apellidos (opcional)</label>
                <input type="text" name="apellidos">
            </div>
            
            <div>
                <label>Teléfono</label>
                <input type="tel" name="telefono" required>
            </div>
            
            <div>
                <label>Correo electrónico (opcional)</label>
                <input type="email" name="email">
            </div>
            
            <div>
                <label>Fecha de nacimiento (opcional)</label>
                <input type="date" name="fecha_nacimiento">
            </div>
            
            <div>
                <label>Dirección (opcional)</label>
                <textarea name="direccion" rows="3"></textarea>
            </div>
            
            <div>
                <label>Notas (opcional)</label>
                <textarea name="notas" rows="3"></textarea>
            </div>
            
            <div>
                <input type="submit" value="Añadir Cliente">
            </div>
        </form>
    </div>
    ';
    
    return $form;
}