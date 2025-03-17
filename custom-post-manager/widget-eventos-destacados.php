<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Custom_Post_Manager_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'eventos_destacados';
    }

    public function get_title() {
        return 'Evento Destacado';
    }

    public function get_icon() {
        return 'fa fa-calendar'; // Icono de calendario
    }

    public function get_categories() {
        return [ 'general' ]; // Categoría en Elementor
    }

    protected function _register_controls() {
        

        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Contenido', 'custom-post-manager' ),
            ]
        );

        $this->add_control(
            'cantidad_eventos',
            [
                'label' => __( 'Cantidad de Eventos', 'custom-post-manager' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 10,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        // Obtener la cantidad de eventos seleccionados desde la configuración del widget
        $settings = $this->get_settings_for_display();
        $cantidad_eventos = $settings['cantidad_eventos'];

        // Realizar la consulta para obtener el próximo evento y los eventos cercanos
        $args = [
            'post_type' => 'eventos',
            'posts_per_page' => $cantidad_eventos,
            'meta_key' => '_cpm_fecha_evento',
            'meta_value' => date('Y-m-d'),
            'meta_compare' => '>=',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        ];

        $eventos_query = new WP_Query($args);

        if ($eventos_query->have_posts()) :
            while ($eventos_query->have_posts()) : $eventos_query->the_post();
                $fecha_evento = get_post_meta(get_the_ID(), '_cpm_fecha_evento', true);
                ?>
                <div class="evento-destacado">
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p>Fecha: <?php echo esc_html($fecha_evento); ?></p>
                    <div class="evento-imagen">
                        <?php the_post_thumbnail('full'); ?>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>No se encontraron eventos.</p>';
        endif;
    }

    protected function _content_template() {
        ?>
        <#
        var cantidad_eventos = settings.cantidad_eventos;
        #>
        <div class="widget-eventos">
            <p>{{{ cantidad_eventos }}} eventos próximos</p>
        </div>
        <?php
    }
}

// Registrar el widget en Elementor
function registrar_widget_eventos_destacados( $widgets_manager ) {
    require_once( __DIR__ . '/widget-eventos-destacados.php' );
    $widgets_manager->register( new \Custom_Post_Manager_Widget() );
}

add_action( 'elementor/widgets/register', 'registrar_widget_eventos_destacados' );
