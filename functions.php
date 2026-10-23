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
	'/podcast.php',                         // Podcast feed and transcripts for translations.
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


//allow vtt files
function custom_upload_mimes( $existing_mimes ) {
    // Add .vtt to the list of allowable mime types
    $existing_mimes['vtt'] = 'text/vtt';
    return $existing_mimes;
}
add_filter( 'upload_mimes', 'custom_upload_mimes' );

// Pass $html = true for linked names with thumbnails; default stays plain text for the podcast feed.
function dlinq_translation_speaker_primary( $post_id = null, $html = false ) {
	$speakers = get_field( 'speaker', $post_id );
	if ( ! $speakers ) {
		return '';
	}
	$names = array();
	foreach ( $speakers as $speaker ) {
		$name = get_the_title( $speaker->ID );
		$tags = get_the_terms( $speaker->ID, 'post_tag' );
		$tag_text = '';
		if ( $tags && ! is_wp_error( $tags ) ) {
			$tag_names = wp_list_pluck( $tags, 'name' );
			$tag_text  = ' (' . implode( ', ', $tag_names ) . ')';
		}
		if ( ! $html ) {
			$names[] = $name . $tag_text;
			continue;
		}
		$thumb   = get_the_post_thumbnail( $speaker->ID, 'thumbnail', array( 'class' => 'participant-thumb', 'alt' => '' ) );
		if ( ! $thumb ) {
			$thumb = '<img class="participant-thumb" src="' . esc_url( get_stylesheet_directory_uri() . '/imgs/weave.png' ) . '" alt="">';
		}
		$names[] = '<span class="participant"><a href="' . esc_url( get_permalink( $speaker->ID ) ) . '">'
			. $thumb . esc_html( $name ) . '</a> ' . esc_html( $tag_text ) . '</span>';
	}
	$lable = '';
	//$label = count( $names ) > 1 ? 'Participants:' : 'Participant:';
	if ( $html ) {
		return '<div class="participants"><span class="participants-label">' . $label . '</span> ' . implode( ' ', $names ) . '</div>';
	}
	return $label . ' ' . implode( ', ', $names );
}

/**
 * On a single Speaker page, restrict any Query Loop block configured for the
 * "translation" post type to the translations connected via the bidirectional
 * ACF relationship field, instead of all translations.
 */
function dlinq_translation_speaker_query_loop( $query, $block, $page ) {
	if ( ! is_singular( 'speaker' ) || ! isset( $query['post_type'] ) || 'translation' !== $query['post_type'] ) {
		return $query;
	}

	$translations = get_field( 'translations', get_queried_object_id() );
	$query['post__in'] = $translations ? wp_list_pluck( $translations, 'ID' ) : array( 0 );
	$query['orderby']  = 'post__in';

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'dlinq_translation_speaker_query_loop', 10, 3 );
/**
 * Keep a Speaker's featured image in sync with its ACF bio_photo field.
 * ACF stores the raw attachment ID in post meta regardless of return format.
 */
function dlinq_translation_sync_bio_photo( $post_id ) {
	if ( 'speaker' !== get_post_type( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$photo_id = (int) get_post_meta( $post_id, 'bio_photo', true );
	if ( $photo_id ) {
		set_post_thumbnail( $post_id, $photo_id );
	} else {
		delete_post_thumbnail( $post_id );
	}
}
add_action( 'acf/save_post', 'dlinq_translation_sync_bio_photo', 20 );

// One-time backfill for Speakers saved before the sync existed.
function dlinq_translation_backfill_bio_photos() {
	if ( get_option( 'dlinq_bio_photo_backfilled' ) ) {
		return;
	}

	$speaker_ids = get_posts( array(
		'post_type'      => 'speaker',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	// Only set, never clear, so manually chosen featured images survive.
	foreach ( $speaker_ids as $speaker_id ) {
		$photo_id = (int) get_post_meta( $speaker_id, 'bio_photo', true );
		if ( $photo_id ) {
			set_post_thumbnail( $speaker_id, $photo_id );
		}
	}

	update_option( 'dlinq_bio_photo_backfilled', 1 );
}
add_action( 'admin_init', 'dlinq_translation_backfill_bio_photos' );
