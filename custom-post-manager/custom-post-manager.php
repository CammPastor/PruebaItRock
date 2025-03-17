<?php
/*
Plugin Name: Custom Post Manager
Description: Plugin para gestionar eventos personalizados con un campo de fecha y un shortcode para mostrar eventos futuros.
Version: 1.0
Author: Ruki
*/

// 1. Registrar el Custom Post Type Eventos
function cpm_registrar_cpt_eventos() {
    $labels = array(
        'name' => 'Eventos',
        'singular_name' => 'Evento',
        'menu_name' => 'Eventos',
        'add_new' => 'Añadir Evento',
        'add_new_item' => 'Añadir Nuevo Evento',
        'edit_item' => 'Editar Evento',
        'new_item' => 'Nuevo Evento',
        'view_item' => 'Ver Evento',
        'search_items' => 'Buscar Eventos',
        'not_found' => 'No se encontraron eventos',
        'not_found_in_trash' => 'No se encontraron eventos en la papelera',
    );

    $args = array(
        'label' => 'Eventos',
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'eventos'),
    );

    register_post_type('eventos', $args);
}
add_action('init', 'cpm_registrar_cpt_eventos');

// 2. Agregar el campo personalizado Fecha del Evento
function cpm_agregar_meta_box_evento() {
    add_meta_box(
        'cpm_fecha_evento',
        'Fecha del Evento',
        'cpm_mostrar_meta_box_evento',
        'eventos',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'cpm_agregar_meta_box_evento');

function cpm_mostrar_meta_box_evento($post) {
    $fecha = get_post_meta($post->ID, '_cpm_fecha_evento', true);
    wp_nonce_field('cpm_guardar_fecha_evento', 'cpm_fecha_evento_nonce');
    echo '<input type="date" name="cpm_fecha_evento" value="' . esc_attr($fecha) . '" />';
}

function cpm_guardar_meta_box_evento($post_id) {
    if (!isset($_POST['cpm_fecha_evento_nonce']) || !wp_verify_nonce($_POST['cpm_fecha_evento_nonce'], 'cpm_guardar_fecha_evento')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['cpm_fecha_evento'])) {
        update_post_meta($post_id, '_cpm_fecha_evento', sanitize_text_field($_POST['cpm_fecha_evento']));
    }
}
add_action('save_post', 'cpm_guardar_meta_box_evento');

// 3. Crear el shortcode [eventos_list] para mostrar los eventos futuros
function cpm_mostrar_eventos_futuros($atts) {
    // Obtener la fecha actual en formato Y-m-d
    $fecha_actual = date('Y-m-d');

    // Configurar la consulta WP_Query para obtener eventos futuros
    $args = array(
        'post_type' => 'eventos',
        'posts_per_page' => -1, // Obtener todos los eventos
        'meta_key' => '_cpm_fecha_evento',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => '_cpm_fecha_evento',
                'value' => $fecha_actual,
                'compare' => '>=',  // Solo eventos con fecha futura o igual
                'type' => 'DATE'    // Asegura que la comparación sea correcta para fechas
            )
        )
    );

    // Ejecutar la consulta
    $consulta_eventos = new WP_Query($args);

    // Comprobar si hay eventos
    if ($consulta_eventos->have_posts()) {
        $output = '<ul>';
        while ($consulta_eventos->have_posts()) {
            $consulta_eventos->the_post();
            $fecha_evento = get_post_meta(get_the_ID(), '_cpm_fecha_evento', true);
            // Mostrar el evento y la fecha
            $output .= '<li><strong>' . get_the_title() . '</strong> - Fecha: ' . esc_html($fecha_evento) . ' - Fecha actual: ' . esc_html($fecha_actual) . '</li>';
        }
        $output .= '</ul>';
    } else {
        $output = 'No hay eventos futuros.';
    }

    // Restaurar datos del post original
    wp_reset_postdata();

    return $output;
}
add_shortcode('eventos_list', 'cpm_mostrar_eventos_futuros');
