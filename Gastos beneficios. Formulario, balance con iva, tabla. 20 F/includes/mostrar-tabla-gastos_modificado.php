<?php
class MostrarTablaGastos {
    private $wpdb;
    private $mes_actual;
    private $anio_actual;
    private $registros_por_pagina;
    private $filtro_tipo;
    private $pagina_actual;

    public function __construct($wpdb, $mes_actual, $anio_actual, $registros_por_pagina = 10, $filtro_tipo = 'todos', $pagina_actual = 1) {
        $this->wpdb = $wpdb;
        $this->mes_actual = $mes_actual;
        $this->anio_actual = $anio_actual;
        $this->registros_por_pagina = $registros_por_pagina;
        $this->filtro_tipo = $filtro_tipo;
        $this->pagina_actual = $pagina_actual;
    }

    public function obtener_datos() {
        $offset = ($this->pagina_actual - 1) * $this->registros_por_pagina;
        $where_tipo = $this->filtro_tipo !== 'todos' ? $this->wpdb->prepare(" AND tipo = %s", $this->filtro_tipo) : '';

        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT id, tipo, cantidad, categoria, fecha, descripcion
            FROM {$this->wpdb->prefix}gastos_beneficios 
            WHERE MONTH(fecha) = %d AND YEAR(fecha) = %d" . $where_tipo . " 
            ORDER BY fecha DESC 
            LIMIT %d OFFSET %d",
            $this->mes_actual,
            $this->anio_actual,
            $this->registros_por_pagina,
            $offset
        ));
    }

    private function obtener_meses_disponibles() {
        return $this->wpdb->get_results(
            "SELECT DISTINCT YEAR(fecha) as anio, MONTH(fecha) as mes
            FROM {$this->wpdb->prefix}gastos_beneficios 
            ORDER BY fecha DESC"
        );
    }

    private function mostrar_selector_meses($meses_disponibles) {
        $html = '<div class="selector-meses">';
        $html .= '<label for="historial-meses">Seleccionar período: </label>';
        $html .= '<select id="historial-meses" onchange="cambiarMes(this.value)">';

        foreach ($meses_disponibles as $mes) {
            $fecha = DateTime::createFromFormat('!m', $mes->mes);
            $nombre_mes = ucfirst(strftime('%B', $fecha->getTimestamp()));
            $selected = ($mes->mes == $this->mes_actual && $mes->anio == $this->anio_actual) ? 'selected' : '';

            $html .= sprintf(
                '<option value="%d-%d" %s>%s %d</option>',
                $mes->mes,
                $mes->anio,
                $selected,
                $nombre_mes,
                $mes->anio
            );
        }

        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    private function mostrar_filtro_tipo() {
        $html = '<div class="filtro-tipo">';
        $html .= '<label for="filtro-tipo">Mostrar: </label>';
        $html .= '<select id="filtro-tipo" onchange="cambiarFiltro(this.value)">';
        $html .= sprintf('<option value="todos" %s>Todos</option>', 
               $this->filtro_tipo === 'todos' ? 'selected' : '');
        $html .= sprintf('<option value="gasto" %s>Solo Gastos</option>', 
               $this->filtro_tipo === 'gasto' ? 'selected' : '');
        $html .= sprintf('<option value="beneficio" %s>Solo Beneficios</option>', 
               $this->filtro_tipo === 'beneficio' ? 'selected' : '');
        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    private function mostrar_tabla($results) {
        if (empty($results)) {
            return '<p class="sin-registros">No hay registros para este mes.</p>';
        }

        $html = '<div class="table-scroll"><table class="tabla-gastos-beneficios">';
        $html .= '<thead><tr>';
        $html .= '<th>Tipo</th>';
        $html .= '<th>Cantidad</th>';
        $html .= '<th>Categoría</th>';
        $html .= '<th>Fecha</th>';
        $html .= '<th>Descripción</th>';
        $html .= '<th class="acciones">Acciones</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($results as $row) {
            $tipo_clase = $row->tipo === 'ingreso' ? 'ingreso' : 'gasto';
            $html .= '<tr class="' . $tipo_clase . '">';
            $html .= '<td>' . ucfirst($row->tipo) . '</td>';
            $html .= '<td>' . number_format($row->cantidad, 2, ',', '.') . '€</td>';
            $html .= '<td>' . ucfirst($row->categoria) . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($row->fecha)) . '</td>';
            $html .= '<td>' . (!empty($row->descripcion) ? $row->descripcion : '-') . '</td>';
            $html .= '<td class="acciones"><button class="boton-eliminar" onclick="eliminarRegistro(' . $row->id . ')">Eliminar</button></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    public function render() {
        $results = $this->obtener_datos();
        $meses_disponibles = $this->obtener_meses_disponibles();

        echo $this->mostrar_selector_meses($meses_disponibles);
        echo $this->mostrar_filtro_tipo();
        echo $this->mostrar_tabla($results);
    }
}

function registrar_shortcode_mostrar_tabla_gastos() {
    add_shortcode('mostrar_tabla_gastos', 'mostrar_tabla_gastos_callback');
}

function mostrar_tabla_gastos_callback($atts) {
    global $wpdb;
    $atts = shortcode_atts(array(
        'mes' => date('m'),
        'anio' => date('Y'),
        'registros_por_pagina' => 10,
        'filtro_tipo' => 'todos',
        'pagina_actual' => 1
    ), $atts);

    $mostrar_tabla_gastos = new MostrarTablaGastos($wpdb, $atts['mes'], $atts['anio'], $atts['registros_por_pagina'], $atts['filtro_tipo'], $atts['pagina_actual']);
    ob_start();
    $mostrar_tabla_gastos->render();
    return ob_get_clean();
}

add_action('init', 'registrar_shortcode_mostrar_tabla_gastos');
?>