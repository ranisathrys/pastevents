<?php 
/* 
 * Template Name: Single Event
 */

get_header(); ?>
    <?php
    display_search_bar();
    
    while ( have_posts() ) : the_post(); 	$events_with_date = get_posts([
		'post_type' => 'pastevents',
		'post_status' => 'publish',
		'numberposts' => -1
    ]);  
    ?>
    <main class="main-cont" > 
        <h2>
            <?php the_title(); ?>
        </h2> <?php
        the_post_thumbnail(); ?>
        <p class="single-content"><?php the_content(); ?></p>

        <?php
            $likes = get_post_meta($post->ID, "likes", true);
            $likes = ($likes == "") ? 0 : $likes;
        ?>

        <span id='like_counter'><?php echo $likes ?></span>

        <?php
            $nonce = wp_create_nonce("pe_like_nonce");
            $link = admin_url('admin-ajax.php?action=my_user_like&post_id='.$post->ID.'&nonce='.$nonce);
            echo '<a class="pe-like-link" data-nonce="' . $nonce . '" data-post_id="' . $post->ID . '" href="' . $link . '"><span class="pe-like"></span><p class="pe-like-text">Like</p></a>';

            eventposttype_get_the_event_date();
        ?>
 <?php

        comments_template();

    endwhile;
get_footer();