<?php
    get_header();
?>

    <main class="main-cont" id="ajax-posts"> 
    
            <?php
            display_search_bar();
            echo get_the_content(); ?>
		
			<?php
            $events_to_show = get_option('event_count');
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;

            if(!isset($_GET['stamp'])) {

                echo '<h2>';
                echo get_the_title();
                echo '</h2>';

                $args = array(
                    'post_type'      => 'pastevents',
                    'posts_per_page' => $events_to_show,
                    'paged'          => $paged
                );
            } else {

                echo '<h2>';
                echo $_GET['stamp'];
                echo '</h2>';

                $args = array(
                    'post_type' => 'pastevents',
                    'meta_query' => array(
                        array(
                            'type' => 'DATE',
                            'key' => '_start_eventdatestamp',
                            'value' => $_GET['stamp'],
                            'compare' => '=',
                        )
                    ),
                );
            }

            $past_events = new WP_Query($args);

			while ( $past_events->have_posts() ) :
                $past_events->the_post(); ?>
                
                <div class="single-event">
                    <h4><a href="<?php the_permalink(); ?>">
                        <?php the_title(); ?>
                    </a>
                    </h4>
                
                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('event'); ?></a>
                    <?php the_excerpt(); ?>
                    <div class="event-importance">
                        <?php global $post;
                            $terms = get_the_terms($post->ID , 'importance');
                            if ( $terms != null ){
                                foreach( $terms as $term ) {
                                $term_link = get_term_link( $term, 'importance' );
                                echo 'Event Importance: <a href="' . $term_link . '">' . $term->name . '</a>';
                                unset($term); } } 
                        ?>
                        
                    </div>
                </div>

            <?php endwhile; ?>
                <?php if(get_option('pag_or_load') == 'pag') { ?>
                <div class="events_pagination">
                    <?php echo paginate_links( array(
                            'total'     => $past_events->max_num_pages,
                            'mid_size'  => 2,
                            'prev_next' => false
                          )); ?>
                </div>
                
                        <?php }
                wp_reset_postdata();
            ?>


	</main><!-- main -->
    <?php if(get_option('pag_or_load') == 'load' && !isset($_GET['stamp'])) { ?>
    <a id="more_posts">Load More</a>

<?php }
    get_footer();
?>