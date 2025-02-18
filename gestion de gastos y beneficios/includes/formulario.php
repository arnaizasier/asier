<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="formulario-gastos-beneficios-wrapper">
    <form id="formulario-gastos-beneficios" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="procesar_gastos_beneficios">
        <?php wp_nonce_field('guardar_gasto_beneficio', 'gasto_beneficio_nonce'); ?>
        
        <fieldset>
            <legend>Información de Gastos y Beneficios</legend>
            
            <!-- Campo: Tipo (Gasto o Beneficio) -->
            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecciona una opción</option>
                    <option value="gasto">Gasto</option>
                    <option value="beneficio">Beneficio</option>
                </select>
            </div>

            <!-- Campo: Cantidad en euros -->
            <div class="form-group">
                <label for="cantidad">Cantidad (€):</label>
                <input type="number" id="cantidad" name="cantidad" step="0.01" min="0" required>
            </div>

            <!-- Campo: Categoría -->
            <div class="form-group">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" disabled>
                    <option value="">Selecciona un tipo primero</option>
                </select>
            </div>

            <!-- Campo: Descripción -->
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
            </div>

            <!-- Campo: Fecha -->
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <!-- Botón de enviar -->
            <div class="form-group">
                <button type="submit">Guardar</button>
            </div>
        </fieldset>
    </form>
    
    <?php
    // Mensajes de éxito/error (ahora DESPUÉS del formulario)
if (isset($_GET['success'])) {
    echo '<div class="mensaje-exito" role="alert">Tu registro se ha añadido correctamente</div>';
}
if (isset($_GET['error'])) {
    $error_message = 'Error al añadir tu registro';
        switch ($_GET['error']) {
            case 'nonce':
                $error_message = 'Error de seguridad. Por favor, intenta de nuevo.';
                break;
            case 'tipo':
                $error_message = 'El tipo seleccionado no es válido.';
                break;
            case 'cantidad':
                $error_message = 'La cantidad debe ser mayor que 0.';
                break;
            case 'db':
                $error_message = 'Error al guardar en la base de datos.';
                break;
        }
        echo '<div class="mensaje-error" role="alert">' . esc_html($error_message) . '</div>';
    }
    ?>
</div>