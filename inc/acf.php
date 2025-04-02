<?php
/**
 * ACF Related
 *
 * 
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function dlinq_translation($field) {
    $content = get_field($field);
    $content_array = str_replace(array('<br>', '<br/>', '<br />'), "\n", $content);
    $content_array = explode("\n", $content_array);
    //write_log($content_array);
    $html = "";
    foreach ($content_array as $key => $line) {
        if($line != "\r"){
        	$line = dlinq_translation_highlights($line);
        	$html .= "<div class='line' data-line='{$key}'>{$line}</div>";	
        } else {
        	$html .= "<br>";
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