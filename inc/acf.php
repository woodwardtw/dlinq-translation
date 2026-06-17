<?php
/**
 * ACF Related
 *
 * 
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function dlinq_translation($field, $extra_class = '') {
    $content = get_field($field);
    $content_array = str_replace(array('<br>', '<br/>', '<br />'), "\n", $content);
    $content_array = explode("\n", $content_array);
    //write_log($content_array);
    $html = "";
    $counter = 1;
    foreach ($content_array as $line) {
        if($line != "\r"){
            $line = dlinq_translation_highlights($line);
            $html .= "<div class='line{$extra_class}' data-line='{$counter}'>{$line}</div>";
            $counter++;
        } else {
            $html .= "<br>";
            // Don't increment counter for blank lines
        }
    }

    return $html;
}
	

function dlinq_translation_highlights($line){
	if (have_rows('highlight_words')):

	    // Loop through rows.
	    while (have_rows('highlight_words')) : the_row();

	        // Load sub field value.
	        $word = get_sub_field('word');
	        $color = get_sub_field('color');
	        $words_array = explode(',', $word);

	        foreach ($words_array as $key => $replace) {
	        	$replace = trim($replace); // optional, clean whitespace
	        	$line = str_replace($replace, "<span style='background-color:{$color}'>{$replace}</span>", $line);
	        }

	    endwhile;

	endif;

	return $line;
}

function dlinq_translation_legend(){
	if (have_rows('highlight_words')):

	    // Loop through rows.
	    $html = "";
	    while (have_rows('highlight_words')) : the_row();

	        // Load sub field value.
	        $word = get_sub_field('word');
	        $meaning = get_sub_field('meaning');
	        $color = get_sub_field('color');
	        $html .= "
	        	<div class='def'>
	        		<div class='def-color' style='background-color: {$color}'>	        		
	        		</div>
	        		<div class='def-label'><strong>{$word}</strong>: {$meaning}</div>
	        	</div>
	        ";

	    endwhile;
		return "<div class='def-box'>{$html}</div>";
	endif;
	return "";
}


	//translation custom post type
	
	// Register Custom Post Type translation
	// Post Type Key: translation
	
	function create_translation_cpt() {
	
	  $labels = array(
	    'name' => __( 'Translations', 'Post Type General Name', 'textdomain' ),
	    'singular_name' => __( 'Translation', 'Post Type Singular Name', 'textdomain' ),
	    'menu_name' => __( 'Translation', 'textdomain' ),
	    'name_admin_bar' => __( 'Translation', 'textdomain' ),
	    'archives' => __( 'Translation Archives', 'textdomain' ),
	    'attributes' => __( 'Translation Attributes', 'textdomain' ),
	    'parent_item_colon' => __( 'Translation:', 'textdomain' ),
	    'all_items' => __( 'All Translations', 'textdomain' ),
	    'add_new_item' => __( 'Add New Translation', 'textdomain' ),
	    'add_new' => __( 'Add New', 'textdomain' ),
	    'new_item' => __( 'New Translation', 'textdomain' ),
	    'edit_item' => __( 'Edit Translation', 'textdomain' ),
	    'update_item' => __( 'Update Translation', 'textdomain' ),
	    'view_item' => __( 'View Translation', 'textdomain' ),
	    'view_items' => __( 'View Translations', 'textdomain' ),
	    'search_items' => __( 'Search Translations', 'textdomain' ),
	    'not_found' => __( 'Not found', 'textdomain' ),
	    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
	    'featured_image' => __( 'Featured Image', 'textdomain' ),
	    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
	    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
	    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
	    'insert_into_item' => __( 'Insert into translation', 'textdomain' ),
	    'uploaded_to_this_item' => __( 'Uploaded to this translation', 'textdomain' ),
	    'items_list' => __( 'Translation list', 'textdomain' ),
	    'items_list_navigation' => __( 'Translation list navigation', 'textdomain' ),
	    'filter_items_list' => __( 'Filter Translation list', 'textdomain' ),
	  );
	  $args = array(
	    'label' => __( 'translation', 'textdomain' ),
	    'description' => __( '', 'textdomain' ),
	    'labels' => $labels,
	    'menu_icon' => '',
	    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
	    'taxonomies' => array('category', 'post_tag'),
	    'public' => true,
	    'show_ui' => true,
	    'show_in_menu' => true,
	    'menu_position' => 5,
	    'show_in_admin_bar' => true,
	    'show_in_nav_menus' => true,
	    'can_export' => true,
	    'has_archive' => true,
	    'hierarchical' => false,
	    'exclude_from_search' => false,
	    'show_in_rest' => true,
	    'publicly_queryable' => true,
	    'capability_type' => 'post',
	    'menu_icon' => 'dashicons-universal-access-alt',
	  );
	  register_post_type( 'translation', $args );
	  
	  // flush rewrite rules because we changed the permalink structure
	  global $wp_rewrite;
	  $wp_rewrite->flush_rules();
	}
	add_action( 'init', 'create_translation_cpt', 0 );

function create_location_cpt() {
	
	  $labels = array(
	    'name' => __( 'Locations', 'Post Type General Name', 'textdomain' ),
	    'singular_name' => __( 'Location', 'Post Type Singular Name', 'textdomain' ),
	    'menu_name' => __( 'Locations', 'textdomain' ),
	    'name_admin_bar' => __( 'Location', 'textdomain' ),
		 'all_items' => __( 'All Locations', 'textdomain' ),
	    'add_new_item' => __( 'Add New Location', 'textdomain' ),
	    'add_new' => __( 'Add New', 'textdomain' ),
	    'new_item' => __( 'New Location', 'textdomain' ),
	    'edit_item' => __( 'Edit Location', 'textdomain' ),
	    'update_item' => __( 'Update Location', 'textdomain' ),
	    'view_item' => __( 'View Location', 'textdomain' ),
	    'view_items' => __( 'View Locations', 'textdomain' ),
	    'search_items' => __( 'Search Locations', 'textdomain' ),
	  );
	  $args = array(
	    'label' => __( 'location', 'textdomain' ),
	    'description' => __( '', 'textdomain' ),
	    'labels' => $labels,
	    'menu_icon' => '',
	    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields',),
	    'taxonomies' => array('category', 'post_tag'),
	    'public' => true,
	    'show_ui' => true,
	    'show_in_menu' => true,
	    'menu_position' => 5,
	    'show_in_admin_bar' => true,
	    'show_in_nav_menus' => true,
	    'can_export' => true,
	    'has_archive' => true,
	    'hierarchical' => false,
	    'exclude_from_search' => false,
	    'show_in_rest' => true,
	    'publicly_queryable' => true,
	    'capability_type' => 'post',
		'menu_icon' => 'dashicons-location-alt',
	  );
	  register_post_type( 'location', $args );
	  
	  // flush rewrite rules because we changed the permalink structure
	  global $wp_rewrite;
	  $wp_rewrite->flush_rules();
	}
	add_action( 'init', 'create_location_cpt', 0 );	


// Resolve text_link post_object to a full object in the REST API response.
add_filter( 'rest_prepare_location', 'dlinq_location_rest_text_link', 10, 3 );
function dlinq_location_rest_text_link( $response, $post, $request ) {
	$data = $response->get_data();

	if ( empty( $data['acf']['text_link'] ) ) {
		return $response;
	}

	$linked = $data['acf']['text_link'];

	// ACF may return an ID (int) or a WP_Post-shaped array depending on version.
	$post_id = is_array( $linked ) ? ( $linked['ID'] ?? null ) : ( is_numeric( $linked ) ? (int) $linked : null );

	if ( ! $post_id ) {
		return $response;
	}

	$linked_post = get_post( $post_id );
	if ( ! $linked_post ) {
		return $response;
	}

	$data['acf']['text_link'] = array(
		'ID'         => $linked_post->ID,
		'post_title' => $linked_post->post_title,
		'permalink'  => get_permalink( $linked_post->ID ),
	);

	$response->set_data( $data );
	return $response;
}

// Show audio player in editor when audio_file has a value.
add_action( 'acf/render_field/name=audio_file', 'dlinq_render_audio_player_in_editor' );
function dlinq_render_audio_player_in_editor( $field ) {
	if ( empty( $field['value'] ) ) {
		return;
	}

	$file = $field['value'];
	$url  = is_array( $file ) ? ( $file['url'] ?? '' ) : wp_get_attachment_url( $file );

	if ( ! $url ) {
		return;
	}

	$escaped_url = esc_url( $url );
	echo "<div class='acf-audio-preview'>
			<audio controls style='width: 100%; max-width: 500px;'>
				<source src='{$escaped_url}'>
			</audio>
		</div>";
}

// Show "Edit VTT" button in editor when vtt_file has a value.
add_action( 'acf/render_field/name=vtt_file', 'dlinq_render_edit_vtt_button' );
function dlinq_render_edit_vtt_button( $field ) {
	if ( empty( $field['value'] ) ) {
		return;
	}

	$pages = get_pages( array(
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-templates/vtt-adjustment.php',
		'number'     => 1,
	) );

	if ( empty( $pages ) ) {
		return;
	}

	$edit_url = add_query_arg( 'id', get_the_ID(), get_permalink( $pages[0]->ID ) );

	echo '<div style="margin-top:8px;">';
	printf(
		'<a href="%s" target="_blank" class="button button-secondary">Edit VTT</a>',
		esc_url( $edit_url )
	);
	echo '</div>';
}

	// REST endpoint: save VTT file contents for a translation post.
add_action( 'rest_api_init', 'dlinq_register_vtt_rest_route' );
function dlinq_register_vtt_rest_route() {
	register_rest_route( 'dlinq/v1', '/vtt/(?P<id>\d+)', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'dlinq_rest_save_vtt',
		'permission_callback' => function() {
			return current_user_can( 'publish_posts' );
		},
		'args' => array(
			'id' => array(
				'required' => true,
			),
		),
	) );
}

function dlinq_rest_save_vtt( WP_REST_Request $request ) {
	$translation_id = absint( $request->get_param( 'id' ) );

	// Read directly from parsed JSON body to preserve newlines and special chars.
	$params      = $request->get_json_params();
	$vtt_content = isset( $params['vtt'] ) ? (string) $params['vtt'] : '';

	if ( ! is_string( $vtt_content ) || strpos( ltrim( $vtt_content ), 'WEBVTT' ) !== 0 ) {
		return new WP_Error( 'invalid_vtt', 'Content must be a valid VTT file.', array( 'status' => 400 ) );
	}

	$post = get_post( $translation_id );
	if ( ! $post || 'translation' !== $post->post_type ) {
		return new WP_Error( 'not_found', 'Translation not found.', array( 'status' => 404 ) );
	}

	// ACF file fields store the raw attachment ID in post meta.
	$attachment_id = absint( get_post_meta( $translation_id, 'vtt_file', true ) );
	if ( ! $attachment_id ) {
		return new WP_Error( 'no_vtt', 'No VTT file attached to this translation.', array( 'status' => 404 ) );
	}

	$file_path = get_attached_file( $attachment_id );
	if ( ! $file_path || ! file_exists( $file_path ) ) {
		return new WP_Error( 'file_not_found', 'VTT file not found on disk.', array( 'status' => 404 ) );
	}

	$bytes = file_put_contents( $file_path, $vtt_content );
	if ( false === $bytes ) {
		return new WP_Error( 'write_failed', 'Could not write VTT file — check file permissions.', array( 'status' => 500 ) );
	}

	return rest_ensure_response( array( 'success' => true, 'bytes' => $bytes ) );
}

//save acf json
add_filter('acf/settings/save_json', 'trans_json_save_point');
	
function trans_json_save_point( $path ) {
	
	// update path
	$path = get_stylesheet_directory() . '/acf-json'; //replace w get_stylesheet_directory() for theme
	
	
	// return
	return $path;
	
}


// load acf json
add_filter('acf/settings/load_json', 'trans_json_load_point');

function trans_json_load_point( $paths ) {
	
	// remove original path (optional)
	unset($paths[0]);
	
	
	// append path
	$paths[] = get_stylesheet_directory() . '/acf-json';//replace w get_stylesheet_directory() for theme
	
	
	// return
	return $paths;
	
}