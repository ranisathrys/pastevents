<?php

add_action( 'rest_api_init', 'pe_register_search' );

// register rest route
function pe_register_search() {
    register_rest_route( 'pe/v1', 'search', array(
        'methods'  => WP_REST_SERVER::READABLE, // GET
        'callback' => 'pe_search_results', // callback
        'permission_callback' => '__return_true'
    ) );
}

function pe_search_results($data) {

    $from = $data['fromDate'];
    $to = $data['toDate'];

    // TO DO: think of a better way to check for params
    
    // check if importance is set
    if($data['impArr']) {
        $importance = $data['impArr'];
        $i = json_decode(urldecode($importance), true);
    }

    // query all events
    if((int) $data['toDate'] === 0 && (int) $data['fromDate'] === 0 && (int) $data['impArr'] === 0) {
        $events = new WP_Query(array(
            'post_type'      => 'pastevents',
            'posts_per_page' => -1,
            's'              => sanitize_text_field($data['term'])
        ));
    }
    
    // query events by terms
    if($data['impArr'] && (int) $data['toDate'] === 0 && (int) $data['fromDate'] === 0) { 
        
        $events = new WP_Query(array(
            'post_type'      => 'pastevents',
            'posts_per_page' => -1,
            's'              => sanitize_text_field($data['term']),
            'tax_query' => array(
                array(
                    'taxonomy' => 'importance',
                    'field'    => 'name',
                    'terms'    => $i['terms'],
                ),
            ), 
        ));

    }
    
    // query events by date
    if(!$data['impArr'] && (int) $data['toDate'] != 0 && (int) $data['fromDate'] != 0) {
        $events = new WP_Query(array(
            'post_type'      => 'pastevents',
            'posts_per_page' => -1,
            's'              => sanitize_text_field($data['term']),
            'meta_query' => array(
                array(
                    'type' => 'DATE',
                    'key' => '_start_eventdatestamp',
                    'value' => [$from, $to],
                    'compare' => 'BETWEEN'
                )
            ),  
        ));
    }
    
    // query events both by date and by terms
    if($data['impArr'] && (int) $data['toDate'] != 0 && (int) $data['fromDate'] != 0) {
        $importance = $data['impArr'];
        $i = json_decode(urldecode($importance), true);

        $events = new WP_Query(array(
            'post_type'      => 'pastevents',
            'posts_per_page' => -1,
            's'              => sanitize_text_field($data['term']),
            'meta_query' => array(
                array(
                    'type' => 'DATE',
                    'key' => '_start_eventdatestamp',
                    'value' => [$from, $to],
                    'compare' => 'BETWEEN'
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'importance',
                    'field'    => 'name',
                    'terms'    => $i['terms'],
                ),
            ), 
        ));
    } 
    
    // query up to a sertain date
    if($data['impArr'] && (int) $data['toDate'] != 0 && (int) $data['fromDate'] === 0) {
        $importance = $data['impArr'];
        $i = json_decode(urldecode($importance), true);

        $events = new WP_Query(array(
            'post_type'      => 'pastevents',
            'posts_per_page' => -1,
            's'              => sanitize_text_field($data['term']),
            'meta_query' => array(
                array(
                    'type' => 'DATE',
                    'key' => '_start_eventdatestamp',
                    'value' => $to,
                    'compare' => '<='
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'importance',
                    'field'    => 'name',
                    'terms'    => $i['terms'],
                ),
            ), 
        ));
    }
    
    // query from a sertain date
    if($data['impArr'] && (int) $data['toDate'] === 0 && (int) $data['fromDate'] != 0) {
        $importance = $data['impArr'];
        $i = json_decode(urldecode($importance), true);

        $events = new WP_Query(array(
            'post_type'      => 'pastevents',
            'posts_per_page' => -1,
            's'              => sanitize_text_field($data['term']),
            'meta_query' => array(
                array(
                    'type' => 'DATE',
                    'key' => '_start_eventdatestamp',
                    'value' => $from,
                    'compare' => '>='
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'importance',
                    'field'    => 'name',
                    'terms'    => $i['terms'],
                ),
            ), 
        ));
    }

    $eventResults = array();

    while($events->have_posts()) {
        $events->the_post();
        $related = get_post_meta(get_the_ID(), 'related_posts');
        $relDetails = array();

        // retrieve related posts data
        foreach($related as $relID) {
            foreach($relID as $id) {
                array_push($relDetails, array(
                    'relID'    => $id,
                    'relTitle' => get_the_title($id),
                    'relLink'  => get_the_permalink($id)
                ));
            }
        }

        // push data to populate endpoint with
        array_push($eventResults, array(
            'ID'        => get_the_ID(),
            'title'     => get_the_title(),
            'permalink' => get_the_permalink(),
            'meta'      => $relDetails,
            'person'    => get_post_meta(get_the_ID(), 'repeatable_fields', true),
        ));
    }

    return $eventResults;
}