<?php
/*
Template Name: Lista de Publicaciones
*/

get_header(); ?>

<main>
    <h1><?php the_title(); ?></h1>

    <?php
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'paged'          => $paged
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post(); ?>
            <article>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <p><?php the_excerpt(); ?></p>
            </article>
        <?php endwhile; ?>

        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages
            ));
            ?>
        </div>

    <?php else : ?>
        <p>No hay publicaciones disponibles.</p>
    <?php endif;

    wp_reset_postdata();
    ?>
</main>

<?php get_footer(); ?>
