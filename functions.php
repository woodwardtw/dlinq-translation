<?php
/**
 * UnderStrap functions and definitions
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// UnderStrap's includes directory.
$understrap_inc_dir = 'inc';

// Array of files to include.
$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567.
	'/editor.php',                          // Load Editor functions.
	'/acf.php', 
	'/block-editor.php',                    // Load Block Editor functions.
	'/deprecated.php',                      // Load deprecated functions.
);

// Load WooCommerce functions if WooCommerce is activated.
if ( class_exists( 'WooCommerce' ) ) {
	$understrap_includes[] = '/woocommerce.php';
}

// Load Jetpack compatibility file if Jetpack is activiated.
if ( class_exists( 'Jetpack' ) ) {
	$understrap_includes[] = '/jetpack.php';
}

// Include files.
foreach ( $understrap_includes as $file ) {
	require_once get_theme_file_path( $understrap_inc_dir . $file );
}

function dlinq_translation_index(){
	$query_args = array(
		'post_type' => 'translation',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC'
	);
	/** @phpstan-ignore-next-line */
	$translations = new WP_Query( $query_args );
	$html = '<ul class="translation-list">';
	foreach ( $translations->posts as $translation ) {
		// Process each translation post
		$title = $translation->post_title;
		$post_id = $translation->ID;
		$post_link = get_permalink($post_id);
		$html .= "<li>{$title}<br>
		post id: {$post_id}<br>
		post: <a href='{$post_link}'>{$post_link}</a><br>
		json link: <a href='" . get_permalink($post_id) . "'>json link</a><br>
		</li> ";
	}
	return $html . '</ul>';
}

/** @phpstan-ignore-next-line */
add_shortcode('list-translations', 'dlinq_translation_index');
