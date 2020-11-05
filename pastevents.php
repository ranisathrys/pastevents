<?php
/* 
 * Plugin Name: past events
 */ 

include( plugin_dir_path( __FILE__ ) . 'inc/search-route.php');

//POST TYPE
add_action( 'init', 'pe_post_type' );
function pe_post_type(){
	register_post_type( 'pastevents', [
		'label'  => null,
		'labels' => [
			'name'               => 'Past Events',
			'singular_name'      => 'Event',
			'add_new'            => 'Add Event',
			'add_new_item'       => 'Add New Event',
			'edit_item'          => 'Edit Event',
			'new_item'           => 'New Event',
			'view_item'          => 'View Event',
			'search_items'       => 'Search Events',
			'not_found'          => 'No Events Found',
			'not_found_in_trash' => 'No Events Found',
			'menu_name'          => 'Past Events'
		],
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => null,
		'show_in_admin_bar'   => true,
		'show_in_rest'		  => true,	
		'menu_position'       => 6,
		'menu_icon'           => 'dashicons-calendar',
		'capability_type'   => 'post',
		'hierarchical'        => false,
		'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ], 
		'taxonomies'          => [],
		'has_archive'         => true,
		'rewrite'             => true,
		'query_var'           => true,
	] );
}

// Add a filter to 'template_include' hook
add_filter( 'template_include', 'wpse_force_template' );
function wpse_force_template( $template ) {

    if( is_page('Past Events Archive') ) {
        $template = WP_PLUGIN_DIR .'/'. plugin_basename( dirname(__FILE__) ) .'/templates/archive.php';
    }

    return $template;
}

register_activation_hook( __FILE__, 'create_uploadr_page' );

//Add Events page on activation
function install_events_pg(){
	$events_archive_page = array(
		'post_title'    => 'Past Events Archive',
		'post_content'  => '',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type' => 'page',
		'page_template' => $template
		 );
	   wp_insert_post( $events_archive_page );
}

register_activation_hook(__FILE__, 'install_events_pg');

//STYLE AND SCRIPTS
function pe_add_scripts() {
	if( is_page('Past Events Archive') || is_singular('pastevents')) {
		wp_enqueue_style('events_css', plugins_url('style.css',__FILE__ ), array('fontawesome'), '');
	}
	wp_enqueue_style('calendar_css', plugins_url('/css/calendar.css',__FILE__ ), array(), '');
	wp_enqueue_script('events_js', plugins_url('/js/scripts.js',__FILE__ ), array('jquery'), '', true);

	//select2
	wp_enqueue_style('select2css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css', array(), '4.1.0');
	wp_enqueue_script('select2js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array(), '4.1.0', true);

	wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css', array(), '5.10.0');

	//Like
	wp_localize_script( 'events_js', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	//Calendar meta
	wp_localize_script( 'events_js', 'metaAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php'), 'archLink' => get_permalink( get_page_by_path( 'past-events-archive' ) )));
	//Load more
	wp_localize_script( 'events_js', 'loadMore', array('ajaxurl' => admin_url( 'admin-ajax.php' ), 'noposts' => __('No older posts found')));
	//Search
	wp_localize_script( 'events_js', 'peSearch', array( 'root' => get_site_url(), 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));
}

add_action('wp_enqueue_scripts', 'pe_add_scripts');

function pe_add_admin_scripts() {
	//select2
	wp_enqueue_style('select2css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css', array(), '4.1.0');
	wp_enqueue_script('select2js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', array(), '4.1.0', true);

	wp_enqueue_script('admin_js', plugins_url('/js/admin.js',__FILE__ ), array('jquery'), null, true);
}

add_action('admin_enqueue_scripts', 'pe_add_admin_scripts');

//IMAGE SIZE
add_image_size('event', 300, 200, true);


//WIDGET
class pe_Widget extends WP_Widget {
 
	function __construct() {
		parent::__construct(
			'event_calendar', 
			'Event Calendar',
			array( 'description' => 'Виводить календарик для навігації по минулих подіях' )
		);
	}

	//DISPLAY
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
 
		echo $args['before_widget'];

		//CALENDAR HERE
		?>
		<section>
			<?php
				if ( ! empty( $title ) ):
					echo $args['before_title'] . $title . $args['after_title'];
				endif;
			?>
					<!-- calendar -->

					<table id="calendar3">
						<thead>
							<tr><td colspan="4"><select>
						<option value="0">Січень</option>
						<option value="1">Лютий</option>
						<option value="2">Березень</option>
						<option value="3">Квітень</option>
						<option value="4">Травень</option>
						<option value="5">Червень</option>
						<option value="6">Липень</option>
						<option value="7">Серпень</option>
						<option value="8">Веесень</option>
						<option value="9">Жовтень</option>
						<option value="10">Листопад</option>
						<option value="11">Грудень</option>
						</select><td colspan="3"><input type="number" value="" min="0" max="9999" size="4">
							<tr><td>Пн<td>Вт<td>Ср<td>Чт<td>Пт<td>Сб<td>Нд
						<tbody>
					</table>

		</section>
		<?php
		echo $args['after_widget'];
	}
 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Заголовок</label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
 
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}

function event_calendar_load() {
	register_widget( 'pe_Widget' );
}
add_action( 'widgets_init', 'event_calendar_load' ); //action

//QUERY META

function get_post_ids_array_by_meta() {
	$events_with_date = get_posts([
		'post_type' => 'pastevents',
		'post_status' => 'publish',
		'numberposts' => -1
	]);
	
	$posts_with_meta;
	
	if($events_with_date != null) {
		foreach($events_with_date as $event) {
			if(get_post_meta($event->ID, '_start_eventdatestamp')){
				$posts_with_meta[$event->ID] = [get_post_meta($event->ID, '_start_eventdatestamp'), get_permalink($event->ID)];
			}
		}
	}
	return $posts_with_meta;
}

function meta_ajax(){
	echo json_encode(get_post_ids_array_by_meta());
	wp_die();
}

add_action("wp_ajax_meta_ajax", "meta_ajax");

//TAX
function create_events_taxonomies() {

    $labels = array(
        'name'              => _x( 'Importance', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Importance', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search by Importance', 'textdomain' ),
        'menu_name'         => __( 'Importance', 'textdomain' ),
    );
 
    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'importance' ),
    );
 
	register_taxonomy( 'importance', array( 'pastevents' ), $args );
	
	//DEFAULT TERMS
	for( $i = 1; $i < 6; $i++ ) {
		wp_insert_term(
			$i,
			'importance',
			array(
			'slug' => $i,
			'description'=> 'Event importance is ' . $i
			)
		);
	}
}
add_action( 'init', 'create_events_taxonomies', 0 );

//PREVENT ADDING NEW TERMS
	function disallow_insert_term($term, $taxonomy) {

		if ( $taxonomy === 'importance') {

			return new WP_Error(
				'disallow_insert_term', 
				__('Your role does not have permission to add terms to this taxonomy')
			);
		}
		
		return $term;
	}

add_filter('pre_insert_term', 'disallow_insert_term', 10, 2);

//DISPLAY TAGS IN ADMIN
function show_all_tags( $args ) {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] === 'get-tagcloud' )
        unset( $args['number'] );
        $args['hide_empty'] = 0;
    return $args;
}
add_filter( 'get_terms_args', 'show_all_tags' );


//SINGLE TEMPLATE
add_filter('single_template', 'my_custom_template');

function my_custom_template($single) {

    global $post;

    /* Checks for single template by post type */
    if ( $post->post_type == 'pastevents' ) {
        if ( file_exists( plugin_dir_path( __FILE__ ) . 'templates/single-event.php' ) ) {
            return plugin_dir_path( __FILE__ ) . 'templates/single-event.php';
        }
    }

    return $single;

}

//DATE META

function ep_eventposts_metaboxes() {
    add_meta_box( 'ept_event_date_start', 'Start Date and Time', 'ept_event_date', 'pastevents', 'side', 'default', array( 'id' => '_start') );
}
add_action( 'admin_init', 'ep_eventposts_metaboxes' );
  
// Metabox HTML
  
function ept_event_date($post, $args) {
    $metabox_id = $args['args']['id'];
    global $post, $wp_locale;

    wp_nonce_field( plugin_basename( __FILE__ ), 'ep_eventposts_nonce' );
  
    $time_adj = current_time( 'timestamp' );
    $month = get_post_meta( $post->ID, $metabox_id . '_month', true );
  
    if ( empty( $month ) ) {
        $month = gmdate( 'm', $time_adj );
    }
  
    $day = get_post_meta( $post->ID, $metabox_id . '_day', true );
  
    if ( empty( $day ) ) {
        $day = gmdate( 'd', $time_adj );
    }
  
    $year = get_post_meta( $post->ID, $metabox_id . '_year', true );
  
    if ( empty( $year ) ) {
        $year = gmdate( 'Y', $time_adj );
    }
  
    $hour = get_post_meta($post->ID, $metabox_id . '_hour', true);
  
    if ( empty($hour) ) {
        $hour = gmdate( 'H', $time_adj );
    }
  
    $min = get_post_meta($post->ID, $metabox_id . '_minute', true);
  
    if ( empty($min) ) {
        $min = '00';
    }
  
    $month_s = '<select name="' . $metabox_id . '_month">';
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        $month_s .= "\t\t\t" . '<option value="' . zeroise( $i, 2 ) . '"';
        if ( $i == $month )
            $month_s .= ' selected="selected"';
        $month_s .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
    }
    $month_s .= '</select>';
  
    echo $month_s;
    echo '<input type="text" name="' . $metabox_id . '_day" value="' . $day  . '" size="2" maxlength="2" />';
    echo '<input type="text" name="' . $metabox_id . '_year" value="' . $year . '" size="4" maxlength="4" /> @ ';
    echo '<input type="text" name="' . $metabox_id . '_hour" value="' . $hour . '" size="2" maxlength="2"/>:';
    echo '<input type="text" name="' . $metabox_id . '_minute" value="' . $min . '" size="2" maxlength="2" />';
  
}
  
// Save the Metabox Data
  
function ep_eventposts_save_meta( $post_id, $post ) {
  
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    if ( !isset( $_POST['ep_eventposts_nonce'] ) )
    return;
  
    if ( !wp_verify_nonce( $_POST['ep_eventposts_nonce'], plugin_basename( __FILE__ ) ) )
        return;
  
    // Is the user allowed to edit the post or page?
    if ( !current_user_can( 'edit_post', $post->ID ) )
        return;
  
		$idkey = '_start';
		$events_meta[$idkey . '_month'] = $_POST[$idkey . '_month'];
		$events_meta[$idkey . '_day'] = $_POST[$idkey . '_day'];
			if($_POST[$idkey . '_hour']<10){
					$events_meta[$idkey . '_hour'] = '0'.$_POST[$idkey . '_hour'];
				} else {
					$events_meta[$idkey . '_hour'] = $_POST[$idkey . '_hour'];
				}
		$events_meta[$idkey . '_year'] = $_POST[$idkey . '_year'];
		$events_meta[$idkey . '_hour'] = $_POST[$idkey . '_hour'];
		$events_meta[$idkey . '_minute'] = $_POST[$idkey . '_minute'];
		$events_meta[$idkey . '_eventtimestamp'] = $events_meta[$idkey . '_year'] . '-'. $events_meta[$idkey . '_month'] . $events_meta[$idkey . '_day'] . $events_meta[$idkey . '_hour'] . $events_meta[$idkey . '_minute'];
		$events_meta[$idkey . '_eventdatestamp'] = $events_meta[$idkey . '_year'] . '-'. $events_meta[$idkey . '_month'] . '-' . $events_meta[$idkey . '_day'];
  
  
    // Add values of $events_meta as custom fields
  
    foreach ( $events_meta as $key => $value ) { // Cycle through the $events_meta array!
        if ( $post->post_type == 'revision' ) return; // Don't store custom data twice
        $value = implode( ',', (array)$value ); // If $value is an array, make it a CSV (unlikely)
        if ( get_post_meta( $post->ID, $key, FALSE ) ) { // If the custom field already has a value
            update_post_meta( $post->ID, $key, $value );
        } else { // If the custom field doesn't have a value
            add_post_meta( $post->ID, $key, $value );
        }
        if ( !$value ) delete_post_meta( $post->ID, $key ); // Delete if blank
    }
  
}
  
add_action( 'save_post', 'ep_eventposts_save_meta', 1, 2 );

  
// Display the date
  
function eventposttype_get_the_event_date() {
    global $post;
    $eventdate = '';
	$day = ' ' . get_post_meta($post->ID, '_start_day', true) . ', ';
	$eventdate = $day;
    $eventdate .= get_post_meta($post->ID, '_start_month', true) . ', ';
    $eventdate .= ' ' . get_post_meta($post->ID, '_start_year', true);
    $eventdate .= ' at ' . get_post_meta($post->ID, '_start_hour', true);
    $eventdate .= ':' . get_post_meta($post->ID, '_start_minute', true);
    echo $eventdate;
}

//SHORTCODE
function ep_show_events_callback($atts) {             //TO DO: SWITCH CASES

extract(shortcode_atts(array(
	'posts' => 5,
	'tag' => 'default',
	'from' => '1970-01-01',
	'to' => '2070-12-28'
), $atts));

if( $tag == 'default') {
	$tag = array( '1', '2', '3', '4', '5' );
} elseif( $tag == '1' ) {
	$tag = array( '2', '3', '4', '5' );
} elseif( $tag == '2' ) {
	$tag = array( '3', '4', '5' );
} elseif( $tag == '3' ) {
	$tag = array( '4', '5' );
} elseif( $tag == '4' ) {
	$tag = array( '5' );
} elseif( $tag == '5' ){
	$tag = array('');
}

if( (string)$from != '1970-01-01' && (string)$to != '2070-12-28' ) {
	$posts = -1;
} elseif((string)$from != '1970-01-01' || (string)$to != '2070-12-28') {
	$posts = -1;
}

$args = array(
	'post_type' => 'pastevents',
	'posts_per_page' => $posts,
	'tax_query' => array(
        array(
            'taxonomy' => 'importance',
            'field'    => 'name',
            'terms'    => $tag,
        ),
	),
	'meta_query' => array(
		
        array(
			'type' => 'DATE',
            'key' => '_start_eventdatestamp',
			'value' => [$from, $to],
			'compare' => 'BETWEEN',
		)
    ),
);

$html = "";

$event_posts = new WP_Query( $args );

if( $event_posts->have_posts() ) : while( $event_posts->have_posts() ) : $event_posts->the_post();
	global $post;
	$terms = get_the_terms($post->ID , 'importance');

	if ( $terms != null ){
		foreach( $terms as $term ) {
		$term_link = get_term_link( $term, 'importance' );
		$date = get_post_meta($post->ID, '_start_day', true) . '-' . get_post_meta($post->ID, '_start_month', true) . '-' . get_post_meta($post->ID, '_start_year', true);
		
		$html .= "<div class=\"shortcode\"><h2>" . get_the_title() . " </h2>";
		$html .= "<p>" . get_the_excerpt() . "</p>";
		$html .= "<a href=\"" . get_permalink() . "\" class=\"button\">Read more</a>";
		$html .= "<a class=\"event-term\" href=\"" . $term_link . "\">" . $term->name . "</a>";
		$html .= "<p>" . $date ."</p></div>";
	unset($term); } } 
endwhile; wp_reset_postdata(); endif;

return $html;
}
add_shortcode( 'events', 'ep_show_events_callback' );

//LOAD MORE
function more_post_ajax(){

	$ppp = (isset($_POST["ppp"])) ? $_POST["ppp"] : 4;
	$page = (isset($_POST['pageNumber'])) ? $_POST['pageNumber'] : 0;

    header("Content-Type: text/html");

    $args = array(
        'post_type' => 'pastevents',
        'posts_per_page' => $ppp,
		'paged'    => $page
	);

    $loop = new WP_Query($args);

    $out = '';

	if ($loop -> have_posts()) :  while ($loop -> have_posts()) : $loop -> the_post();
		$permalink = get_the_permalink();
		$title = get_the_title();
		$content = get_the_excerpt();
		$img = get_the_post_thumbnail_url(get_the_ID(), 'event');

		$terms = get_the_terms($post->ID , 'importance');
		if ( $terms != null ){
			foreach( $terms as $term ) {
			$term_link = get_term_link( $term, 'importance' );

			$out .= '<div class="single-event">
					<h4>
						<a href="' . $permalink . '">'. $title .
						'</a> 
					</h4>
					<a href="' . $permalink . '">' . '<img src="' . $img . '"></a>
						<p>' . $content . '</p>
						<div class="clear">Event Importance: <a href="' . $term_link . '">' . $term->name . '</a></div>
					</div>';
			unset($term); 
			} 
		}

    endwhile;
    endif;
    wp_reset_postdata();
    wp_die($out);
}

add_action('wp_ajax_nopriv_more_post_ajax', 'more_post_ajax');
add_action('wp_ajax_more_post_ajax', 'more_post_ajax');

//ADMIN
add_action('admin_menu', 'setup_page');
add_action('admin_init', 'register_settings');

function setup_page() {
    add_menu_page(__("Events Options"), __("Evenets Options"), "manage_options", __FILE__, 'display_options', 'dashicons-star-half');
}

function register_settings() {
    // Add options to database if they don't already exist
    add_option("event_count", "", "", "yes");
    add_option("pag_or_load", "", "", "yes");
    add_option("events_css", "", "", "yes");

    // Register settings that this form is allowed to update
    register_setting('events_settings', 'event_count');
    register_setting('events_settings', 'pag_or_load');
    register_setting('events_settings', 'events_css');
}
?>

<?php

function display_options() {
    if (!current_user_can('manage_options'))
        wp_die(__("You don't have access to this page"));
    ?>
    <div class="wrap">
        <h2><? _e("Test settings") ?></h2>

        <form method="post" action="options.php">

            <?php settings_fields('events_settings'); ?>

            <table class="form-table">
                <tr valign="top">
					<?php 
						$all_events = wp_count_posts('pastevents');
						$event_count = intval($all_events->publish);
					?>
                    <th scope="row">Events to Display</th>
                    <td><input type="number" name="event_count" min="-1" max="<?php echo $event_count; ?>" value="<?php echo get_option('event_count'); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Paginate or Load More</th>
                    <td>
					    <select id="pag_or_load" name="pag_or_load">
							<option value="pag" <?php if(get_option('pag_or_load') == 'pag') echo 'selected'; ?>>Pagination</option>
							<option value="load" <?php if(get_option('pag_or_load') == 'load') echo 'selected'; ?>>Load More</option>
						</select>
				</td>
                </tr>


                <tr valign="top">
                    <th scope="row">Custom CSS</th>
                    <td><textarea style="resize: none;" cols="70" rows="15" name="events_css"><?php echo get_option('events_css'); ?></textarea></td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save changes') ?>" />
            </p>

        </form>
    </div>
    <?php
}

function pe_add_admin_styles() {
        $custom_css =get_option('events_css');
        wp_add_inline_style( 'events_css', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'pe_add_admin_styles' );

//LIKE

add_action("wp_ajax_event_like", "event_like");
add_action("wp_ajax_nopriv_event_like", "please_login");

function event_like() {

	if ( !wp_verify_nonce( $_REQUEST['nonce'], "pe_like_nonce")) {
		wp_die();
	}

	// fetch like_count for a post, set it to 0 if it's empty, increment by 1 when a click is registered 
	$like_count = get_post_meta($_REQUEST["post_id"], "likes", true);
	$like_count = ($like_count == '') ? 0 : $like_count;
	//add user check
	$user_meta = get_user_meta(get_current_user_id(), 'liked', false);
	if( !in_array($_REQUEST["post_id"], $user_meta)) {
		$new_like_count = $like_count + 1;
		add_user_meta(get_current_user_id(), 'liked', $_REQUEST["post_id"], false);
	} else {
		$new_like_count = $like_count - 1;
		delete_user_meta(get_current_user_id(), 'liked', $_REQUEST["post_id"], false);
	}

	// Update the value of 'likes' meta key for the specified post, creates new meta data for the post if none exists
	$like = update_post_meta($_REQUEST["post_id"], "likes", $new_like_count);

	// If above action fails, result type is set to 'error' and like_count set to old value, if success, updated to new_like_count  
	if($like === false) {
		$result['type'] = "error";
		$result['like_count'] = $like_count;
	 }
	 else {
		$result['type'] = "success";
		$result['like_count'] = $new_like_count;
	 }
	 
	 // Check if action was fired via Ajax call. If yes, JS code will be triggered, else the user is redirected to the post page
	 if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		$result = json_encode($result);
		echo $result;
	 }
	 else {
		header("Location: ".$_SERVER["HTTP_REFERER"]);
	 }
  
	 die();
}

function please_login() {
	echo "You must log in to like";
	die();
}

//CLI
if(defined('WP_CLI') && WP_CLI) {
class EventsCLI
{
	function delete($args, $assoc_args) {           //TO DO: SWITCH CASES
		$posts = get_posts(get_cat_ID('importance'));

		if(isset($assoc_args['date'])) {
			$date = $assoc_args['date'];
		}

		if(isset($assoc_args['importance'])) {
			$importance = $assoc_args['importance'];

			if($importance == 5) {
				$importance = [1, 2, 3, 4];
			} elseif($importance == 4) {
				$importance = [1, 2, 3];
			} elseif($importance == 3) {
				$importance = [1, 2];
			} elseif($importance == 2) {
				$importance = [1];
			} elseif($importance == 1) {
				WP_CLI::line('No Events To Delete');
				return;
			}
		}

		if(isset($date)) {
			$args = array(
				'post_type' => 'pastevents',
				'posts_per_page' => -1,
				'meta_query' => array(
					
					array(
						'type' => 'DATE',
						'key' => '_start_eventdatestamp',
						'value' => $date,
						'compare' => '<=',
					)
				),
			);

			$posts_to_delete = get_posts($args);

			foreach($posts_to_delete as $post) {
				wp_trash_post($post->ID);
			}
		} 
		
		if(isset($importance)) {
	
			$args = array(
				'post_type' => 'pastevents',
				'posts_per_page' => -1, 
				'tax_query' => array(
					array(
						'taxonomy' => 'importance',
						'field'    => 'name',
						'terms'    => $importance,
					),
				),
			);

			$posts_to_delete = get_posts($args);

			foreach($posts_to_delete as $post) {
				wp_trash_post($post->ID);
			}
		}

		WP_CLI::line('Events deleted');
	}
}

WP_CLI::add_command('past-events', 'EventsCLI');
}

//USER ROLE
function pe_add_roles() {
	add_role( 'pe_editor', 'Редактор Подій', array(
		'read'            		=> true,
        'create_posts'    		=> true,
		'edit_posts'      		=> true,
		'edit_pages'	  		=> true,
		'edit_others_pages' 	=> true,
		'edit_others_posts' 	=> true,
		'publish_pages'   		=> true,
		'publish_posts'			=> true,
		'edit_published_pages'  => true,
		'edit_published_posts'  => true
		) 
	);
}
register_activation_hook( __FILE__, 'pe_add_roles' );

// Add Metaboxes
add_action('admin_init', 'pe_add_meta_boxes', 1);
function pe_add_meta_boxes() {
	add_meta_box( 'repeatable-fields', 'Repeatable Fields', 'repeatable_meta_box_display', 'pastevents', 'normal', 'default');
	add_meta_box( 'related_posts', 'Related Posts', 'pe_related_posts_display', 'pastevents', 'normal', 'default');
}

//REPEATER
function repeatable_meta_box_display() {
	global $post;

	$repeatable_fields = get_post_meta($post->ID, 'repeatable_fields', true);

	wp_nonce_field( 'repeatable_meta_box_nonce', 'repeatable_meta_box_nonce' );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function( $ ){
		$( '#add-row' ).on('click', function() {
			var row = $( '.empty-row.screen-reader-text' ).clone(true);
			row.removeClass( 'empty-row screen-reader-text' );
			row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			return false;
		});
  	
		$( '.remove-row' ).on('click', function() {
			$(this).parents('tr').remove();
			return false;
		});
	});
	</script>
  
	<table id="repeatable-fieldset-one" width="100%">
	<thead>
		<tr>
			<th width="30%">Name</th>
			<th width="30%">Surname</th>
			<th width="30%">Social Media URL</th>
			<th width="10%"></th>
		</tr>
	</thead>
	<tbody>
	<?php
	
	if ( $repeatable_fields ) :
	
	foreach ( $repeatable_fields as $field ) {
	?>
	<tr>
		<td><input type="text" class="widefat" name="name[]" value="<?php if($field['name'] != '') echo esc_attr( $field['name'] ); ?>" /></td>

		<td><input type="text" class="widefat" name="surname[]" value="<?php if($field['surname'] != '') echo esc_attr( $field['surname'] ); ?>" /></td>
	
		<td><input type="text" class="widefat" name="url[]" value="<?php if ($field['url'] != '') echo esc_attr( $field['url'] ); else echo 'http://'; ?>" /></td>
	
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<?php
	}
	else :
	// blank
	?>
	<tr>
		<td><input type="text" class="widefat" name="name[]" /></td>

		<td><input type="text" class="widefat" name="surname[]" /></td>
	
		<td><input type="text" class="widefat" name="url[]" value="http://" /></td>
	
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<?php endif; ?>
	
	<!-- empty hidden one for jQuery -->
	<tr class="empty-row screen-reader-text">
		<td><input type="text" class="widefat" name="name[]" /></td>

		<td><input type="text" class="widefat" name="surname[]" /></td>
	
		<td><input type="text" class="widefat" name="url[]" value="http://" /></td>
		  
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	</tbody>
	</table>
	
	<p><a id="add-row" class="button" href="#">Add another</a></p>
	<?php
}

add_action('save_post', 'repeatable_meta_box_save');
function repeatable_meta_box_save($post_id) {
	if ( ! isset( $_POST['repeatable_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['repeatable_meta_box_nonce'], 'repeatable_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$old = get_post_meta($post_id, 'repeatable_fields', true);
	$new = array();
	
	$names = $_POST['name'];
	$surnames = $_POST['surname'];
	$urls = $_POST['url'];
	
	$count = count( $names );
	
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $names[$i] != '' ) :
			$new[$i]['name'] = stripslashes( strip_tags( $names[$i] ) );

			if ( $surnames[$i] == '' )
				$new[$i]['surname'] = '';
			else
				$new[$i]['surname'] = stripslashes( $surnames[$i] );
		
			if ( $urls[$i] == 'http://' )
				$new[$i]['url'] = '';
			else
				$new[$i]['url'] = stripslashes( $urls[$i] );
		endif;
	}

	if ( !empty( $new ) && $new != $old )
		update_post_meta( $post_id, 'repeatable_fields', $new );
	elseif ( empty($new) && $old )
		delete_post_meta( $post_id, 'repeatable_fields', $old );
}

//RELATED POSTS
function pe_related_posts_display($post) {

	wp_nonce_field( plugin_basename( __FILE__ ), 'pe_nonce' );
	
	$related_posts = get_posts( 
		array(
			'post_type' => 'post',
			'posts_per_page' => -1
		) 
	);

	?>
	<select width="100%" class="js-example-basic-multiple" name="related_posts[]" multiple="multiple"> <?php
		foreach( $related_posts as $related_post ) { 
			$title = $related_post->post_title;
			$id = $related_post->ID;
			?>
			<option 
				value="<?php echo $id; ?>"
				<?php
				$meta = array(get_post_meta( $post->ID, 'related_posts', true ));
				if(in_array($id, $meta) ) echo ' selected'; ?> 
			>
					<?php echo $title; ?>
			</option>
		<?php }
  	?> 
	</select>

  <?php

}

add_action('save_post', 'pe_multiple_meta_box_save');
function pe_multiple_meta_box_save($post_id) {
	if ( !isset( $_POST['pe_nonce'] ) )
    return;
  
    if ( !wp_verify_nonce( $_POST['pe_nonce'], plugin_basename( __FILE__ ) ) )
        return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$related = $_POST['related_posts'];

	if( isset( $related ) )
		update_post_meta( $post_id, 'related_posts', $related );
	else
		delete_post_meta( $post_id, 'related_posts' );

}

//SEARCH

// Display search html

function display_search_bar() { ?>
	<div class="search-bar">
		<i class="fas fa-search search-open"></i>
	</div>

	<div class="search-overlay">
		<div class="search-overlay-top">
			<div class="container">
				<i class="fas fa-search"></i>
				<input type="text" class="search-term" id="search-term">
				<i class="far fa-window-close search-close"></i>
			</div>
		</div>

		<div class="filter-form">
			<form method="post" class="filter">
				<div class="checkboxes">
				<label for="importance">Select event importance:</label>
					<input class="importance" type="checkbox" name="importance[]" value="1" class="importance-check"> 1
					<input class="importance" type="checkbox" name="importance[]" value="2" class="importance-check"> 2
					<input class="importance" type="checkbox" name="importance[]" value="3" class="importance-check"> 3
					<input class="importance" type="checkbox" name="importance[]" value="4" class="importance-check"> 4
					<input class="importance" type="checkbox" name="importance[]" value="5" class="importance-check"> 5
				</div>

				<div class="date-range">
					<label>Select date range:</label>
					From
					<input type="date" name="date-from" id="date-from">
					To
					<input type="date" name="date-to" id="date-to">
				</div>
				<input type="submit" value="Submit" id="filter-submit">
			</form>
			<div id="submit-ajax"></div>

		</div>

		<div class="results-container">
			<div id="overlay-results">
				<!-- results here -->
			</div>
		</div>
	</div>
<?php }